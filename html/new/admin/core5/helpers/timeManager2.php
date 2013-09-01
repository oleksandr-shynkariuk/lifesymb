<?php
class haTimeManager2 {
	var $_cache = array();
	var $useCache = FALSE;

	var $companyT = null;
	var $customerT = null;

	var $resourceIds = array();
	var $resourceSet = FALSE;
	var $locationIds = array();
	var $serviceIds = array();
	var $maxDuration = 0;
	var $maxLeadin = 0;

	var $services = array();
	var $locations = array();
	var $virtualIndex = 0;
	var $chunkSize = 1;

	var $checkNow = 0;

	var $customerSide = false;
	var $plugins = array();
	var $isBundle = false;
	var $bundleGap = 0;
	var $processCompleted = FALSE;	

	var $minBlockStart = 0;
	var $maxBlockEnd = 0;
	var $filters = array();
	var $internalResourceIds = array();

	const SLOT_TYPE_SELECTABLE = 0;
	const SLOT_TYPE_FIXED = 1;

	function haTimeManager2(){
		$this->useCache = FALSE;
		$t = new ntsTime;
		$this->companyT = $t;
		$this->customerT = $t;
		$this->customerSide = false;
		$this->isBundle = false;
		$this->resourceSet = false;

		$this->SLT_INDX = array(
			'location_id'	=> 0,
			'resource_id'	=> 1,
			'service_id'	=> 2,
			'seats'			=> 3,
			'type'			=> 4,
			);

		$this->maxDuration = 0;
		$this->maxLeadin = 0;

		$this->chunkSize = 10;
		$services = ntsObjectFactory::getAll( 'service' );
		reset( $services );
		foreach( $services as $s ){
			$serviceId = $s->getId();
			$this->services[ $serviceId ] = $s->getByArray();

			$thisDuration = $this->services[ $serviceId ]['duration'] + $this->services[ $serviceId ]['lead_out'];
			if( $thisDuration > $this->maxDuration )
				$this->maxDuration = $thisDuration;

			$thisLeadin = $this->services[ $serviceId ]['lead_in'];
			if( $thisLeadin > $this->maxLeadin )
				$this->maxLeadin = $thisLeadin;
			}

		$maxTravel = 0;
		$locations = ntsObjectFactory::getAll( 'location' );
		reset( $locations );
		foreach( $locations as $l ){
			$locationId = $l->getId();
			$this->locations[ $locationId ] = $l->getByArray();

			$travel = $l->getProp('_travel');
			$thisMaxTravel = array_values($travel) ? max(array_values($travel)) : 0;
			if( $thisMaxTravel > $maxTravel ){
				$maxTravel = $thisMaxTravel;
				}
			}

		$this->maxDuration = $this->maxDuration + $maxTravel;
		$this->maxLeadin = $this->maxLeadin + $maxTravel;

		$this->allServiceIds = array_keys( $this->services );
		$this->allLocationIds = ntsObjectFactory::getAllIds( 'location' );
		$this->allResourceIds = ntsObjectFactory::getAllIds( 'resource' );

		$ntsdb =& dbWrapper::getInstance();

	/* get internal resources ids */
		$this->internalResourceIds = array();
		$where = array(
			'obj_class'		=> array('=', 'resource'),
			'meta_name'		=> array('=', '_internal'),
			'meta_value'	=> array( '<>', 0 )
			);
		$result = $ntsdb->select( 'obj_id', 'objectmeta', $where );
		while( $i = $result->fetch() )
		{
			$this->internalResourceIds[] = $i['obj_id'];
		}

		$this->checkNow = time();

	/* min/max block start/end */
		$this->minBlockStart = 0;
		$this->maxBlockEnd = 24 * 60 * 60;

		$sql =<<<EOT
		SELECT
			MIN(starts_at) AS min, MAX(ends_at) AS max, MAX(starts_at) AS maxstart
		FROM
			{PRFX}timeblocks
EOT;
		$result = $ntsdb->runQuery( $sql );
		if( $minmax = $result->fetch() ){
			if( $minmax['min'] )
				$this->minBlockStart = $minmax['min'];
			
			$max1 = $minmax['max'];
			$max2 = $minmax['maxstart'] + $this->maxDuration;
			$this->maxBlockEnd = max( $max1, $max2 );
			}

	/* load plugins if any */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		$this->plugins = array();
		reset( $activePlugins );
		foreach( $activePlugins as $plg ){
			$checkFile = $plm->getPluginFolder( $plg ) . '/getAllTime.php';
			if( file_exists($checkFile) )
				$this->plugins[] = $checkFile;
			}
		}

	function addFilter( $key, $values )
	{
		switch( $key )
		{
			case 'resource':
				if( ! in_array(0, $values) )
				{
					if( $this->resourceIds )
						$this->resourceIds = array_intersect($this->resourceIds, $value);
					else
						$this->resourceIds = $values;
				}
				break;

			case 'service':
				if( ! in_array(0, $values) )
				{
					if( $this->serviceIds )
						$this->serviceIds = array_intersect($this->serviceIds, $value);
					else
						$this->serviceIds = $values;
				}
				break;
		}
		$this->filters[ $key ] = $values;
	}

	function setResource( $res ){
		$resIds = array();

		if( is_object($res) )
			$resIds = array( $res->getId() );
		elseif( is_array($res) )
			$resIds = $res;
		elseif( $res )
			$resIds = array( $res );
		else
			$resIds = array();

		if( isset($this->filters['resource']) && $this->filters['resource'] )
		{
			if( $resIds )
				$resIds = array_intersect( $resIds, $this->filters['resource'] );
			else
				$resIds = $this->filters['resource'];
		}
		$this->resourceIds = $resIds;
		}

	function setLocation( $loc ){
		$locIds = array();

		if( is_object($loc) )
			$locIds = array( $loc->getId() );
		elseif( is_array($loc) )
			$locIds = $loc;
		elseif( $loc )
			$locIds = array( $loc );
		else
			$locIds = array();

		if( isset($this->filters['location']) && $this->filters['location'] )
		{
			if( $locIds )
				$locIds = array_intersect( $locIds, $this->filters['location'] );
			else
				$locIds = $this->filters['location'];
		}
		$this->locationIds = $locIds;
		}

	function setService( $ser ){
		$serIds = array();

		if( is_object($ser) )
			$serIds = array( $ser->getId() );
		elseif( is_array($ser) )
			$serIds = $ser;
		elseif( $ser )
			$serIds = array( $ser );
		else
			$serIds = array();

		if( isset($this->filters['service']) && $this->filters['service'] )
		{
			if( $serIds )
				$serIds = array_intersect( $serIds, $this->filters['service'] );
			else
				$serIds = $this->filters['service'];
		}
		$this->serviceIds = $serIds;
		}

	function getService(){
		return $this->serviceIds;
		}
		
	function getDatesWithSomething( $startDate, $howManyDates ){
		$ntsdb =& dbWrapper::getInstance();

		$return = array();
		$okDate = $startDate;

		$mainWhere = array();
		$mainWhere['location_id'] = array( 'IN', $this->locationIds );
		$mainWhere['resource_id'] = array( 'IN', $this->resourceIds ); 
		$mainWhere['service_id'] = array( 'IN', $this->serviceIds ); 
		if( $this->processCompleted ){
			$mainWhere['completed'] = array( '<>', HA_STATUS_CANCELLED );
			$mainWhere['completed '] = array( '<>', HA_STATUS_NOSHOW );
			}

		$this->companyT->setDateDb( $startDate );
		$this->companyT->modify( '+1 day' );
		$checkDate = $this->companyT->formatDate_Db();

	// check with appointments
		while( $okDate ){
			$return[] = $okDate;

		// ok full
			if( count($return) >= $howManyDates ){
				break;
				}

			$this->companyT->setDateDb( $okDate );
			$this->companyT->modify( '+1 day' );
			$tomorrow = $this->companyT->formatDate_Db();

			$this->companyT->setDateDb( $okDate );
			$endTime = $this->companyT->getEndDay();

		// get next date to check
			$nextDate = '';

		// 1 - get nearest appointment which end is greater than the end of this day
			$nextDate1 = '';
			$where = $mainWhere;
			$where['starts_at + duration + lead_out'] = array('>', $endTime);
			$nextApp = $this->getAppointments( $where, 'ORDER BY (starts_at-lead_in) ASC', array(0,1), array('starts_at', 'lead_in') );
			if( $nextApp ){
				$ts = $nextApp[0]['starts_at'] - $nextApp[0]['lead_in'];
				if( $ts <= $endTime )
					$ts = $endTime + 1;
				$this->companyT->setTimestamp( $ts );
				$nextDate1 = $this->companyT->formatDate_Db();
				}

		// 2 - get nearest blocks
			if( $nextDate1 && ($nextDate1 == $tomorrow) ){
				$nextDate = $nextDate1;
				}
			else {
				$nextDate2 = '';
				$blocksWhere = array();
				if( $this->locationIds ){
					if( $blocksWhere )
						$blocksWhere[] = 'AND';
					$blocksWhere[] = array(
						array( 'location_id' => array( 'IN', $this->locationIds) ),
						array( 'location_id' => array( '=', 0 ) ),
						);
					}

				if( $this->resourceIds ){
					if( $blocksWhere )
						$blocksWhere[] = 'AND';
					$blocksWhere[] = array(
						array( 'resource_id' => array( 'IN', $this->resourceIds) ),
						array( 'resource_id' => array( '=', 0 ) ),
						);
					}
				if( $this->serviceIds ){
					if( $blocksWhere )
						$blocksWhere[] = 'AND';
					$blocksWhere[] = array(
						array( 'service_id' => array( 'IN', $this->serviceIds) ),
						array( 'service_id' => array( '=', 0 ) ),
						);
					}

				$where = $blocksWhere;
				if( $where )
					$where[] = 'AND';
				$where[] = array( 'valid_to' => array('>', $okDate) );

			/* first check if we have future blocks at all */
				$result = $ntsdb->select( 'MIN(valid_from) AS min_valid_from, MAX(valid_to) AS max_valid_to', 'timeblocks', $where );
				$e = $result->fetch();
				if( $e && $e['min_valid_from'] ){
					if( $e['min_valid_from'] > $okDate )
						$checkFrom = $e['min_valid_from'];
					else {
						$checkFrom = $tomorrow;
						}
					$checkTo = $e['max_valid_to'];

					while( $checkFrom ){
						$this->companyT->setDateDb( $checkFrom );
						$checkFromWeekday = $this->companyT->getWeekday();

						$where = $blocksWhere;
						if( $where )
							$where[] = 'AND';
						$where[] = array( 'valid_to' => array('>=', $checkFrom) );
						$where[] = 'AND';
						$where[] = array( 'applied_on' => array('=', $checkFromWeekday) );

						/* ok, now find for exact date */
						$result2 = $ntsdb->select( 'MIN(valid_from) AS min_valid_from', 'timeblocks', $where );
						$e2 = $result2->fetch();

						if( $e2 && $e2['min_valid_from'] ){
							$nextDate2 = $checkFrom;
							break;
							}
						else {
							$this->companyT->modify( '+1 day' );
							$checkFrom = $this->companyT->formatDate_Db();
							if( $nextDate1 && ($checkFrom >= $nextDate1) ){
								break;
								}
							if( $checkFrom > $checkTo ){
								break;
								}
							}
						}
					}

				if( $nextDate2 && $nextDate1 ){
					$nextDate = ($nextDate2 < $nextDate1) ? $nextDate2 : $nextDate1;
					}
				elseif( $nextDate2 ){
					$nextDate = $nextDate2;
					}
				elseif( $nextDate1 ){
					$nextDate = $nextDate1;
					}
				}
			$okDate = $nextDate;
			}

		return $return;
		}

	function countAppointments( $dateWhere = array(), $groupBy = '' ){
		if( ! isset($dateWhere['completed']) ){
			$dateWhere['completed'] = array( '=', 0 );
			}

		$lrsWhere = array();
		if( ! isset($dateWhere['location_id']) )
			$lrsWhere['location_id'] = array( '<>', 0 );
		if( ! isset($dateWhere['resource_id']) )
			$lrsWhere['resource_id'] = array( '<>', 0 );
		if( ! isset($dateWhere['service_id']) )
			$lrsWhere['service_id'] = array( '<>', 0 );

		$where = array_merge( $dateWhere, $lrsWhere );

		if( isset($where['resource_id']) && ($where['resource_id'][0] == 'IN') && (! $where['resource_id'][1]) ){
			$return = 0;
			return $return;
			}
		
		$what = array();
		$what[] = 'COUNT(id) AS count';
		if( $groupBy )
			$what[] = $groupBy;
		$what = join( ',', $what );
		
		$ntsdb =& dbWrapper::getInstance();
		$other = $groupBy ? 'GROUP BY ' . $groupBy : '';
		$result = $ntsdb->select( $what, 'appointments', $where, $other );

		if( $groupBy ){
			$return = array();
			if( $result ){
				while( $e = $result->fetch() ){
					$return[ $e[$groupBy] ] = $e['count'];
					}
				}
			}
		else {
			$return = 0;
			if( $result ){
				$e = $result->fetch();
				$return = $e['count'];
				}
			}
		return $return;
		}

	function queryAppointments( $where = array(), $addon = '', $limit = array(), $fields = array() ){
		global $NTS_SKIP_APPOINTMENTS;
		if( $NTS_SKIP_APPOINTMENTS )
			$where[' id '] = array('NOT IN', $NTS_SKIP_APPOINTMENTS);
		if( ! isset($where['completed']) ){
			$where['completed'] = array( '=', 0 );
			}

		if( $limit ){
			$addon .= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
			}

		$ntsdb =& dbWrapper::getInstance();
		if( ! $fields )
			$fields = 'id, starts_at, lead_in, duration, lead_out, location_id, resource_id, service_id, seats, approved, completed, customer_id';

		if( isset($where['resource_id']) && ($where['resource_id'][0] == 'IN') && (! $where['resource_id'][1]) ){
			$return = NULL;
			}
		else {
			$return = $ntsdb->select(
				$fields,
				'appointments',
				$where,
				$addon
				);
			}
		return $return;
		}

	function getAllTime( $startTime, $endTime, $classOnly = false ){
		$now = time();
//		$timeUnit = 5 * 60;
		$timeUnit = NTS_TIME_UNIT * 60;

		$ntsdb =& dbWrapper::getInstance();
		$return = array();

		$this->companyT->setTimestamp( $endTime );
		$toDate = $this->companyT->formatDate_Db();
		$this->companyT->setTimestamp( $startTime );
		$fromDate = $this->companyT->formatDate_Db();
		
		$rexDate = $fromDate;
		$dates = array();
		$firstWeekdays = array();
		$timesIndex = array();
		$di = 0;
		while( $rexDate <= $toDate ){
			$rexWeekday = $this->companyT->getWeekday();
			$startDay = $this->companyT->getStartDay(); 
			if( ! isset($firstWeekdays[$rexWeekday]) )
				$firstWeekdays[$rexWeekday] = $di;
			$dates[ $rexDate ] = $startDay;

	/* build times index that means i will not need to call heavy time functions in the loop */
	/* it's for damn daylight savings time */
	/* probably we'll use the getTransitions() method by so far the dumb way */
			if( $this->minBlockStart )
				$this->companyT->modify( '+' . $this->minBlockStart . ' seconds' );
			for( $calcTs = ($startDay + $this->minBlockStart); $calcTs <= ($startDay + $this->maxBlockEnd); $calcTs += $timeUnit )
			{
				$timesIndex[ $calcTs ] = $this->companyT->getTimestamp();
				$this->companyT->modify( '+' . $timeUnit . ' seconds' );
			}

			$this->companyT->setDateDb($rexDate);
			$this->companyT->modify( '+1 day' );
			$rexDate = $this->companyT->formatDate_Db();
			$di++;
			}

		$datesIndex = array_keys( $dates );
		$daysCount = count( $dates );

		if( $this->isBundle ){
			$this->isBundle = false;
			$saveServices = $this->serviceIds;

			$subReturn = array();
			reset( $saveServices );
			foreach( $saveServices as $sid ){
				$this->setService( $sid );
				$subReturn[] = $this->getAllTime( $startTime, $endTime, $classOnly );
				}

			$return = array();
			$thisIndex = 0;
			reset( $subReturn[$thisIndex] );
			foreach( $subReturn[$thisIndex] as $ts => $arr ){
				$duration = $this->services[ $saveServices[$thisIndex] ]['duration'];

				$finalTs = array();
				$finalTs[] = $ts;
				$ts2 = $ts;
				for( $thisIndex2 = 1; $thisIndex2 < count($subReturn); $thisIndex2++ ){
					$thisFound = false;

					$ts2 = $ts2 + $duration;
					$gap = 0;
					while( $gap <= $this->bundleGap ){
						if( isset($subReturn[$thisIndex2][$ts2]) ){
							$finalTs[] = $ts2;
							$duration = $this->services[ $saveServices[$thisIndex2] ]['duration'];
							$thisFound = true;
							continue 2;
							}
						$gap = $gap + $timeUnit;
						$ts2 = $ts2 + $timeUnit;
						}
					if( ! $thisFound )
						break;
					}
				if( count($finalTs) == count($subReturn) ){
//					$return[ $ts ] = $arr;
					$return[ $ts ] = $finalTs;
					}
				}

		/* save back */
			$this->setService( $saveServices );
			$this->isBundle = true;
			return $return;
			}

	/* build where */
		$where = array();
		$serviceIds = $this->serviceIds;
		if( $classOnly ){
			if( $serviceIds )
				$checkSids = $serviceIds;
			else
				$checkSids = array_keys($this->services);

			$serviceIds = array();
			foreach( $checkSids as $checkSid ){
				if( $this->services[$checkSid]['class_type'] )
					$serviceIds[] = $checkSid;
				}
			}

		if( $this->locationIds )
		{
			$where[] = "(location_id IN (" . join(',', $this->locationIds) . ") OR location_id=0)";
		}
		if( $this->resourceIds )
		{
			$where[] = "resource_id IN (" . join(',', $this->resourceIds) . ")";
		}
		if( $serviceIds )
		{
			$where[] = "(service_id IN (" . join(',', $serviceIds) . ") OR service_id=0)";
		}

		$limitWeekdays = array();
		if( count($firstWeekdays) < 7 )
		{
			$limitWeekdays = array_keys($firstWeekdays);
		}
		if( isset($this->filters['weekday']) )
		{
			$limitWeekdays = $limitWeekdays ? array_intersect($limitWeekdays, $this->filters['weekday']) : $this->filters['weekday'];
		}

		if( $limitWeekdays )
		{
			$where[] = "(applied_on IN (" . join(',', $limitWeekdays) . "))";
		}
		$where = $where ? 'WHERE ' . join( ' AND ', $where ) : '';

		/* cache */
		$cacheKey = $startTime . '_' . $endTime . '_' . $where;
		if( $this->useCache && isset($this->_cache[$cacheKey]) )
		{
			echo 'CACHE!';
			return $this->_cache[$cacheKey];
		}

		$sql =<<<EOT
		SELECT
			id,
			valid_from,
			valid_to,
			applied_on,
			location_id,
			resource_id,
			service_id,
			starts_at,
			ends_at,
			selectable_every,
			capacity,
			min_from_now,
			max_from_now
		FROM
			{PRFX}timeblocks
		$where
EOT;
		$result = $ntsdb->runQuery( $sql );
		while( $b1 = $result->fetch() ){
			if( ! isset($firstWeekdays[$b1['applied_on']]) )
				continue;

			$lids = ( $b1['location_id'] == 0 ) ? $this->allLocationIds : array( $b1['location_id'] );
			$rids = ( $b1['resource_id'] == 0 ) ? $this->allResourceIds : array( $b1['resource_id'] );
			$sids = ( $b1['service_id'] == 0 ) ? $this->allServiceIds : array( $b1['service_id'] );

			$bbs = array();
			reset( $lids );
			foreach( $lids as $lid ){
				if( $this->locationIds && (! in_array($lid, $this->locationIds)))
					continue;
				reset( $rids );
				foreach( $rids as $rid ){
					if( $this->resourceIds && (! in_array($rid, $this->resourceIds)))
						continue;
					reset( $sids );
					foreach( $sids as $sid ){
						if( $this->serviceIds && (! in_array($sid, $this->serviceIds)))
							continue;
						$b = array();
						$b['location_id'] = $lid; 
						$b['resource_id'] = $rid;
						$b['service_id'] = $sid;
						$bbs[] = $b;
						}
					}
				}

			$di = $firstWeekdays[ $b1['applied_on'] ];
			while( $di < $daysCount ){
				$thisDate = $datesIndex[$di];
				if( isset($this->filters['date']) )
				{
					if( isset($this->filters['date']['from']) )
					{
						if( $thisDate > $this->filters['date']['to'] )
							break;
						if( $thisDate < $this->filters['date']['from'] )
						{
							$di += 7;
							continue;
						}
					}
					else
					{
						if( $thisDate > max($this->filters['date']) )
							break;
						if( ! in_array($thisDate, $this->filters['date']) )
						{
							$di += 7;
							continue;
						}
					}
				}

				if( $thisDate > $b1['valid_to'] )
					break;
				if( $thisDate < $b1['valid_from'] )
				{
					$di += 7;
					continue;
				}

				$startDay = $dates[ $datesIndex[$di] ];

				reset( $bbs );
				foreach( $bbs as $b2 ){
					$b = $b1;
//					$rid = $b1['resource_id'];
					$rid = $b2['resource_id'];
					$lid = $b2['location_id'];
					$sid = $b2['service_id'];
					if( ! isset($this->services[$sid]) ){
						continue;
						}

					if( $this->customerSide && $this->internalResourceIds && in_array($rid, $this->internalResourceIds) )
					{
						continue;
					}


					$b['location_id'] = $lid;
					$b['service_id'] = $sid;
					$b['resource_id'] = $rid;
					$seats = $b1['capacity'];
					$type = 0; // selectable every
					$slot = array( $lid, $rid, $sid, $seats );

					$leadIn = $this->services[$sid]['lead_in']; 
					$leadOut = $this->services[$sid]['lead_out'];
					$duration = $this->services[$sid]['duration'];

					$minFromNow = $b1['min_from_now'];
					$maxFromNow = $b1['max_from_now']; 

					$checkStart = $startTime;
					$checkEnd = $endTime;
					if( $this->customerSide ){
						$serviceStartTime = $now + $minFromNow;
						$serviceEndTime = $now + $maxFromNow;

						if( $serviceEndTime > $serviceStartTime){
							$checkStart = ($serviceStartTime > $startTime) ? $serviceStartTime : $startTime;
							$checkEnd = ($serviceEndTime < $endTime) ? $serviceEndTime : $endTime;
							}
						}

					$ts = $b1['starts_at'];
					if( $b1['selectable_every'] ){
						$slot[ $this->SLT_INDX['type'] ] = self::SLOT_TYPE_SELECTABLE;

						$extendCheck = 0;
						if( $b1['ends_at'] == 24 * 60 * 60 ){
							// ends at midnight, I should find a block tomorrow
							$t = new ntsTime;
							$t->setDateDb( $datesIndex[$di] );
							$t->modify( '+1 day' );
							$tomorrow = $t->formatDate_Db();
							$tomorrowWeekday = $t->getWeekday();
							$tomorrowWhere = array(
								'location_id'	=> array( '=', $b1['location_id'] ),
								'resource_id'	=> array( '=', $b1['resource_id'] ),
								'service_id'	=> array( '=', $b1['service_id'] ),
								'starts_at'		=> array( '=', 0 ),
								'valid_from'	=> array( '<=', $tomorrow ),
								'valid_to'		=> array( '>=', $tomorrow ),
								'applied_on'	=> array( '=', $tomorrowWeekday ),
								);
							$tomorrowBlocks = $this->getBlocksByWhere( $tomorrowWhere );
							if( $tomorrowBlocks ){
								foreach( $tomorrowBlocks as $tb ){
									if( $tb['ends_at'] > $extendCheck )
										$extendCheck = $tb['ends_at'];
									}
								}
							}

						$checkBlockEnd = ($b1['ends_at'] - $leadOut - $duration + $extendCheck);
						while( $ts <= $checkBlockEnd ){
							$addTs = $timesIndex[ ($startDay + $ts) ];

							if( $addTs > $checkEnd ){
								break;
								}

							if( ($addTs >= $checkStart) && ($addTs > $this->checkNow) ){
								$thisOk = TRUE;
							/* check time filter */
								if( isset($this->filters['time']) )
								{
									if( $ts < $this->filters['time'][0] )
									{
										$thisOk = FALSE;
									}
									if( ($ts + $duration) > $this->filters['time'][1] )
									{
										$thisOk = FALSE;
									}
								}

								if( $thisOk ){
									if( ! isset($return[$addTs]) )
										$return[$addTs] = array();
									$return[$addTs][] = $slot;
									}
								}

							$ts = $ts + $b1['selectable_every'];
							}
						}
				// fixed time
					else {
						$slot[ $this->SLT_INDX['type'] ] = self::SLOT_TYPE_FIXED;
						$addTs = $timesIndex[ ($startDay + $ts) ];
						if( ($addTs <= $checkEnd) && ($addTs >= $checkStart) && ($addTs > $this->checkNow) ){
							$thisOk = TRUE;

						/* check time filter */
							if( isset($this->filters['time']) )
							{
								if( $ts < $this->filters['time'][0] )
								{
									$thisOk = FALSE;
								}
								if( ($ts + $duration) > $this->filters['time'][1] )
								{
									$thisOk = FALSE;
								}
							}

							if( $thisOk ){
								if( ! isset($return[$addTs]) )
									$return[$addTs] = array();
								$return[$addTs][] = $slot;
								}
							}
						}
					}
				$di += 7;
				}
			}
//		$result->free();
		if( ! array_keys($return) )
		{
			return $return;
		}

		ksort( $return );

	// check appointments
		$where = array(
			'(starts_at + duration + lead_out)'	=> array('>', $startTime),
			'starts_at'							=> array('<', $endTime)
			);

		if( $this->processCompleted ){
			$where['completed'] = array( '<>', HA_STATUS_CANCELLED );
			$where['completed '] = array( '<>', HA_STATUS_NOSHOW );
			}

		$occupiedPerLocation = array();
		if( $result = $this->queryAppointments($where) ){
			while( $a = $result->fetch() ){
				$appStart = $a['starts_at'] - $a['lead_in'];
				$appEnd = $a['starts_at'] + $a['duration'] + $a['lead_out'];

				for( $ts = ($appStart - $this->maxDuration); $ts < ($appEnd + $this->maxLeadin); $ts += $timeUnit ){
					if( isset($return[$ts]) && $return[$ts] ){
						if( 
							($this->locations[$a['location_id']]['capacity'] > 0) &&
							($ts >= $appStart) && 
							($ts < $appEnd)
							)
						{
							if( ! isset($occupiedPerLocation[$ts][$a['location_id']]) )
								$occupiedPerLocation[$ts][$a['location_id']] = 0;
							$occupiedPerLocation[$ts][$a['location_id']] += $a['seats'];
						}

//						$slotCount = count($return[$ts]);
//						for( $si = ($slotCount - 1); $si >= 0; $si-- ){
						foreach( $return[$ts] as $si => $sslot ){
							$slotLocationId = $return[$ts][$si][0];
							$slotServiceId = $return[$ts][$si][2];

							if( $slotLocationId == $a['location_id'] ){
								$travelTime = 0;
								}
							else {
								$travelTime = isset($this->locations[$slotLocationId]['_travel'][$a['location_id']]) ? $this->locations[$slotLocationId]['_travel'][$a['location_id']] : 0;
								}

							$slotDuration = isset($this->services[$slotServiceId]) ? $this->services[$slotServiceId]['duration'] + $this->services[$slotServiceId]['lead_out'] : 0;
							if( ($ts + $slotDuration + $travelTime) <= $appStart )
								continue;

							$slotLeadin = $this->services[ $slotServiceId ]['lead_in'];
							if( ($ts - $slotLeadin - $travelTime) >= $appEnd )
								continue;

							$slotResourceId = $return[$ts][$si][1];

							$slotSeats = $return[$ts][$si][3];
							$removeSeats = 0;

							/* this resource, this service - delete everything but the start at this location */
							if( 
								( $slotResourceId == $a['resource_id'] ) &&
								( $slotServiceId == $a['service_id'] )
								)
								{
								if( ($slotLocationId == $a['location_id']) ){
								/* this slot */
									if( (! $this->services[ $a['service_id'] ]['class_type']) || ($a['starts_at'] == $ts ) ){
										$removeSeats = $a['seats'];
										}
								/* other slot */
									else {
										$removeSeats = $slotSeats;
										}
									}
								/* other location - remove everything */
								else {
									$removeSeats = $slotSeats;
									}
								}
						/* this resource, other service - delete everything  - UPDATE DON'T DELETE */
							elseif(
								( $slotResourceId == $a['resource_id'] )
								)
								{
								$removeSeats = $a['seats'];
//								$removeSeats = $slotSeats;
								}
						/* this location */
							elseif( 
								( $slotLocationId == $a['location_id'] )
								)
								{
								/* delete everything - if locks location */
									if (
										$this->services[ $a['service_id'] ]['blocks_location'] OR 
										$this->services[ $slotServiceId ][ 'blocks_location' ]
										)
									{
										$removeSeats = $slotSeats;
									}

								/* if location has limited capacity */
									elseif(
										( $this->locations[$slotLocationId]['capacity'] > 0 )
										)
									{
										if( isset($occupiedPerLocation[$ts][$slotLocationId]) && ($occupiedPerLocation[$ts][$slotLocationId] >= $this->locations[$slotLocationId]['capacity']) )
										{
											$removeSeats = $slotSeats;
										}
									}
								}
						/* any resource, any service - continue */
							else {
								// nothing
								}

							if( $removeSeats ){
								if( $removeSeats < $slotSeats ){
									$return[$ts][$si][3] = $slotSeats - $removeSeats;
									}
								else {
//									array_splice( $return[$ts], $si, 1 );
									unset( $return[$ts][$si] );
									}
								}
							}
						if( ! $return[$ts] )
							unset($return[$ts]);
						}
					}
				}
			}

	// check timeoff
		$returnTss = array_keys( $return );
		if( ! $returnTss )
		{
			return $return;
		}
		$returnTsFrom = $returnTss[0];
		$returnTsTo = $returnTss[count($returnTss) - 1];

		$where = array(
			'(ends_at)'	=> array('>', $startTime),
			'starts_at'	=> array('<', $endTime)
			);

		if( $result = $this->queryTimeoff($where) ){
			while( $a = $result->fetch() ){
				$appStart = $a['starts_at'];
				$appEnd = $a['ends_at'];
				
				$checkTsFrom = ($appStart - $this->maxDuration);
				$checkTsTo = ($appEnd + $this->maxLeadin);
				if( $checkTsFrom < $returnTsFrom )
					$checkTsFrom = $returnTsFrom;
				if( $checkTsTo > $returnTsTo )
					$checkTsTo = $returnTsTo;

				for( $ts = $checkTsFrom; $ts <= $checkTsTo; $ts += $timeUnit ){
					if( isset($return[$ts]) && $return[$ts] ){
						foreach( $return[$ts] as $si => $sslot ){
							$slotServiceId = $return[$ts][$si][2];

							$slotDuration = isset($this->services[$slotServiceId]) ? $this->services[$slotServiceId]['duration'] + $this->services[$slotServiceId]['lead_out'] : 0;
							if( ($ts + $slotDuration) <= $appStart )
								continue;

							$slotLeadin = $this->services[ $slotServiceId ]['lead_in'];
							if( ($ts - $slotLeadin) >= $appEnd )
								continue;

							$slotResourceId = $return[$ts][$si][1];

							$slotSeats = $return[$ts][$si][3];
							$removeSeats = 0;

							/* this resource - delete everything */
							if( 
								( $slotResourceId == $a['resource_id'] )
								)
								{
								$removeSeats = $slotSeats;
								}
						/* any resource - continue */
							else {
								// nothing
								}

							if( $removeSeats ){
								if( $removeSeats < $slotSeats ){
									$return[$ts][$si][3] = $slotSeats - $removeSeats;
									}
								else {
									unset( $return[$ts][$si] );
									}
								}
							}

						if( ! $return[$ts] )
							unset($return[$ts]);
						}
					}
				}
			}

		/* check plugins */
		reset( $this->plugins );
		foreach( $this->plugins as $plgFile ){
			require( $plgFile );
			}

		if( $this->useCache )
		{
			$this->_cache[$cacheKey] = $return;
		}

		return $return;
		}

/* returns the next available times according to current settings */
	function getNearestTimes( $start = 0 ){
		$ntsdb =& dbWrapper::getInstance();
		if( ! $start )
		{
			$start = time();
			if( isset($this->filters['date']) )
			{
				$this->companyT->setDateDb( min($this->filters['date']) );
				$thisStart = $this->companyT->getTimestamp();
				if( $thisStart > $start )
					$start = $thisStart;
			}
		}

		$now = time();
		$saveLids = $this->locationIds;
		$saveRids = $this->resourceIds;
		$saveSids = $this->serviceIds;

		$return = array(
			'location'	=> array(),
			'resource'	=> array(),
			'service'	=> array()
			);
		$shouldFind = array();

		$shouldFind['location'] = array();
		$allIds = $this->locationIds ? $this->locationIds : $this->allLocationIds;
		foreach( $allIds as $id )
			$shouldFind['location'][$id] = 1;

		$shouldFind['resource'] = array();
		$allIds = $this->resourceIds ? $this->resourceIds : $this->allResourceIds;
		foreach( $allIds as $id )
			$shouldFind['resource'][$id] = 1;
			
		$shouldFind['service'] = array();
		$allIds = $this->serviceIds ? $this->serviceIds : $this->allServiceIds;
		foreach( $allIds as $id )
			$shouldFind['service'][$id] = 1;

	/* first go */
		$first_days = 3;
	
		$this->companyT->setTimestamp( $start );
		$startRexDate = $this->companyT->formatDate_Db();
		$rexDate = $startRexDate;
		if( $first_days > 1 )
		{
			$this->companyT->modify( '+' . ($first_days - 1) . ' days' );
		}

		$timesEndCheck = $this->companyT->getEndDay();
		$this->companyT->setDateDb( $rexDate );
		$timesStartCheck = $this->companyT->getStartDay();
		if( $timesStartCheck < $start ){
			$timesStartCheck = $start;
			}
		$times = $this->getAllTime( $timesStartCheck, $timesEndCheck );

		reset( $times );
		foreach( $times as $ts => $slots ){
			reset( $slots );
			foreach( $slots as $slot ){
				list( $lid, $rid, $sid, $seats ) = $slot;
				if( ! isset($return['location'][$lid]) ){
					$return['location'][$lid] = $ts;
					unset( $shouldFind['location'][$lid] );
					}
				if( ! isset($return['resource'][$rid]) ){
					$return['resource'][$rid] = $ts;
					unset( $shouldFind['resource'][$rid] );
					}
				if( ! isset($return['service'][$sid]) ){
					$return['service'][$sid] = $ts;
					unset( $shouldFind['service'][$sid] );
					}
				}
			}

		$this->companyT->setDateDb( $rexDate );
		$this->companyT->modify( '+' . $first_days . ' day' );
		$startRexDate = $this->companyT->formatDate_Db();

	/* go check - location */
		if( $shouldFind['location'] )
		{
			$rexDate = $startRexDate;
			$this->setLocation( array_keys($shouldFind['location']) );
			$this->resourceIds = $saveRids;
			$this->serviceIds = $saveSids;

			while( $rexDate )
			{
				$checkThis = TRUE;
				if( isset($this->filters['date']) )
				{
					if( $rexDate > max($this->filters['date']) )
					{
						$rexDate = 0;
						continue;
					}

					if( isset($this->filters['date']['from']) )
					{
						if( $rexDate < $this->filters['date']['from'] )
						{
							$checkThis = FALSE;
						}
					}
					else
					{
						if( ! in_array($rexDate, $this->filters['date']) )
						{
							$checkThis = FALSE;
						}
					}
				}

				if( $checkThis )
				{
					$this->companyT->setDateDb( $rexDate );
					$timesEndCheck = $this->companyT->getEndDay();
					$this->companyT->setDateDb( $rexDate );
					$timesStartCheck = $this->companyT->getStartDay();
					if( $timesStartCheck < $start ){
						$timesStartCheck = $start;
						}
					$times = $this->getAllTime( $timesStartCheck, $timesEndCheck );

					reset( $times );
					foreach( $times as $ts => $slots ){
						reset( $slots );
						foreach( $slots as $slot ){
							list( $lid, $rid, $sid, $seats ) = $slot;
							if( ! isset($return['location'][$lid]) ){
								$return['location'][$lid] = $ts;
								unset( $shouldFind['location'][$lid] );
								}
							}
						}
				}

				if( $shouldFind['location'] )
				{
					$this->companyT->setDateDb( $rexDate );
					$this->companyT->modify( '+1 day' );
					$rexDate = $this->companyT->formatDate_Db();
					$rexTs = $this->companyT->getTimestamp();
					$diffSec = $rexTs - $now;
					if( $diffSec < 0 )
						$diffSec = 0;

					$where = array();
					$where[] = array(
						array( 'location_id' => array( 'IN', array_keys($shouldFind['location']) ) ),
						array( 'location_id' => array( '=', 0 ) ),
						);
					if( $this->resourceIds ){
						$where[] = 'AND';
						$where[] = array(
							array( 'resource_id' => array( 'IN', $this->resourceIds) ),
							array( 'resource_id' => array( '=', 0 ) ),
							);
						}
					if( $this->serviceIds ){
						$where[] = 'AND';
						$where[] = array(
							array( 'service_id' => array( 'IN', $this->serviceIds) ),
							array( 'service_id' => array( '=', 0 ) ),
							);
						}
					$where[] = 'AND';
					$where[] = array( 'valid_to' => array('>=', $rexDate) );
					$where[] = 'AND';
					$where[] = array( 'max_from_now' => array('>=', $diffSec) );

					$result = $ntsdb->select( 'MIN(valid_from) AS min_valid_from', 'timeblocks', $where );
					$e = $result->fetch();
					if( $e && $e['min_valid_from'] ){
						if( $e['min_valid_from'] > $rexDate )
							$rexDate = $e['min_valid_from'];
						$this->setLocation( array_keys($shouldFind['location']) );
						}
					else {
						$shouldFind['location'] = array();
						}
				}
				if( ! $shouldFind['location'] )
					$rexDate = 0;
			}
		}

	/* go check - resource */
		if( $shouldFind['resource'] )
		{
			$rexDate = $startRexDate;
			$this->setResource( array_keys($shouldFind['resource']) );
			$this->locationIds = $saveLids;
			$this->serviceIds = $saveSids;

			while( $rexDate )
			{
				$checkThis = TRUE;
				if( isset($this->filters['date']) )
				{
					if( $rexDate > max($this->filters['date']) )
					{
						$rexDate = 0;
						continue;
					}

					if( isset($this->filters['date']['from']) )
					{
						if( $rexDate < $this->filters['date']['from'] )
						{
							$checkThis = FALSE;
						}
					}
					else
					{
						if( ! in_array($rexDate, $this->filters['date']) )
						{
							$checkThis = FALSE;
						}
					}
				}

				if( $checkThis )
				{
					$this->companyT->setDateDb( $rexDate );
					$timesEndCheck = $this->companyT->getEndDay();
					$this->companyT->setDateDb( $rexDate );
					$timesStartCheck = $this->companyT->getStartDay();
					if( $timesStartCheck < $start ){
						$timesStartCheck = $start;
						}
					$times = $this->getAllTime( $timesStartCheck, $timesEndCheck );

					reset( $times );
					foreach( $times as $ts => $slots ){
						reset( $slots );
						foreach( $slots as $slot ){
							list( $lid, $rid, $sid, $seats ) = $slot;
							if( ! isset($return['resource'][$rid]) ){
								$return['resource'][$rid] = $ts;
								unset( $shouldFind['resource'][$rid] );
								}
							}
						}
				}

				if( $shouldFind['resource'] )
				{
					$this->companyT->setDateDb( $rexDate );
					$this->companyT->modify( '+1 day' );
					$rexDate = $this->companyT->formatDate_Db();
					$rexTs = $this->companyT->getTimestamp();
					$diffSec = $rexTs - $now;
					if( $diffSec < 0 )
						$diffSec = 0;

					$where = array();
					$where[] = array(
						array( 'resource_id' => array( 'IN', array_keys($shouldFind['resource']) ) ),
						array( 'resource_id' => array( '=', 0 ) ),
						);
					if( $this->locationIds ){
						$where[] = 'AND';
						$where[] = array(
							array( 'location_id' => array( 'IN', $this->locationIds) ),
							array( 'location_id' => array( '=', 0 ) ),
							);
						}
					if( $this->serviceIds ){
						$where[] = 'AND';
						$where[] = array(
							array( 'service_id' => array( 'IN', $this->serviceIds) ),
							array( 'service_id' => array( '=', 0 ) ),
							);
						}
					$where[] = 'AND';
					$where[] = array( 'valid_to' => array('>=', $rexDate) );
					$where[] = 'AND';
					$where[] = array( 'max_from_now' => array('>=', $diffSec) );

					$result = $ntsdb->select( 'MIN(valid_from) AS min_valid_from', 'timeblocks', $where );
					$e = $result->fetch();
					if( $e && $e['min_valid_from'] ){
						if( $e['min_valid_from'] > $rexDate )
							$rexDate = $e['min_valid_from'];
						$this->setResource( array_keys($shouldFind['resource']) );
						}
					else {
						$shouldFind['resource'] = array();
						}
				}
				if( ! $shouldFind['resource'] )
					$rexDate = 0;
			}
		}

	/* go check - service */
		if( $shouldFind['service'] )
		{
			$rexDate = $startRexDate;
			$this->setService( array_keys($shouldFind['service']) );
			$this->locationIds = $saveLids;
			$this->resourceIds = $saveRids;

			while( $rexDate )
			{
				$checkThis = TRUE;
				if( isset($this->filters['date']) )
				{
					if( $rexDate > max($this->filters['date']) )
					{
						$rexDate = 0;
						continue;
					}

					if( isset($this->filters['date']['from']) )
					{
						if( $rexDate < $this->filters['date']['from'] )
						{
							$checkThis = FALSE;
						}
					}
					else
					{
						if( ! in_array($rexDate, $this->filters['date']) )
						{
							$checkThis = FALSE;
						}
					}
				}

				if( $checkThis )
				{
					$this->companyT->setDateDb( $rexDate );
					$timesEndCheck = $this->companyT->getEndDay();
					$this->companyT->setDateDb( $rexDate );
					$timesStartCheck = $this->companyT->getStartDay();
					if( $timesStartCheck < $start ){
						$timesStartCheck = $start;
						}
					$times = $this->getAllTime( $timesStartCheck, $timesEndCheck );

					reset( $times );
					foreach( $times as $ts => $slots ){
						reset( $slots );
						foreach( $slots as $slot ){
							list( $lid, $rid, $sid, $seats ) = $slot;
							if( ! isset($return['service'][$sid]) ){
								$return['service'][$sid] = $ts;
								unset( $shouldFind['service'][$sid] );
								}
							}
						}
				}

				if( $shouldFind['service'] )
				{
					$this->companyT->setDateDb( $rexDate );
					$this->companyT->modify( '+1 day' );
					$rexDate = $this->companyT->formatDate_Db();
					$rexTs = $this->companyT->getTimestamp();
					$diffSec = $rexTs - $now;
					if( $diffSec < 0 )
						$diffSec = 0;

					$where = array();
					$where[] = array(
						array( 'service_id' => array( 'IN', array_keys($shouldFind['service']) ) ),
						array( 'service_id' => array( '=', 0 ) ),
						);
					if( $this->locationIds ){
						$where[] = 'AND';
						$where[] = array(
							array( 'location_id' => array( 'IN', $this->locationIds) ),
							array( 'location_id' => array( '=', 0 ) ),
							);
						}
					if( $this->resourceIds ){
						$where[] = 'AND';
						$where[] = array(
							array( 'resource_id' => array( 'IN', $this->resourceIds) ),
							array( 'resource_id' => array( '=', 0 ) ),
							);
						}
					$where[] = 'AND';
					$where[] = array( 'valid_to' => array('>=', $rexDate) );
					$where[] = 'AND';
					$where[] = array( 'max_from_now' => array('>=', $diffSec) );

					$result = $ntsdb->select( 'MIN(valid_from) AS min_valid_from', 'timeblocks', $where );
					$e = $result->fetch();
					if( $e && $e['min_valid_from'] ){
						if( $e['min_valid_from'] > $rexDate )
							$rexDate = $e['min_valid_from'];
						$this->setService( array_keys($shouldFind['service']) );
						}
					else {
						$shouldFind['service'] = array();
						}
				}
				if( ! $shouldFind['service'] )
					$rexDate = 0;
			}
		}

		$this->setLocation( $saveLids );
		$this->setResource( $saveRids );
		$this->setService( $saveSids );
		return $return;
		}

	function getNextTimes( $start = 0, $chunkSize = 0, $isBundle = FALSE )
	{
		if( ! $start )
		{
			$start = time();
			if( isset($this->filters['date']) )
			{
				if( isset($this->filters['date']['from']) )
				{
					$this->companyT->setDateDb( $this->filters['date']['from'] );
				}
				else
				{
					$this->companyT->setDateDb( min($this->filters['date']) );
				}
				$thisStart = $this->companyT->getTimestamp();
				if( $thisStart > $start )
					$start = $thisStart;
			}
		}
		if( ! $chunkSize )
			$chunkSize = $this->chunkSize;

		$ntsdb =& dbWrapper::getInstance();
		$return = array();

		$lrsWhere = array();
		if( $this->locationIds )
			$lrsWhere[] = "(location_id IN (" . join(',', $this->locationIds) . ") OR location_id = 0)";
		if( $this->resourceIds )
			$lrsWhere[] = "resource_id IN (" . join(',', $this->resourceIds) . ")";
		if( $this->serviceIds )
			$lrsWhere[] = "(service_id IN (" . join(',', $this->serviceIds) . ") OR service_id = 0)";

		$this->companyT->setTimestamp( $start );
		$rexStart = $this->companyT->formatDate_Db();

		while( $rexStart ){
			$checkThis = TRUE;
			if( isset($this->filters['date']) )
			{
				if( isset($this->filters['date']['from']) )
				{
					if( $rexStart < $this->filters['date']['from'] )
					{
						$rexStart = $this->filters['date']['from'];
						continue;
					}
					if( $rexStart > $this->filters['date']['to'] )
					{
						$rexStart = 0;
						continue;
					}
				}
				else
				{
					if( $rexStart < min($this->filters['date']) )
					{
						$rexStart = min($this->filters['date']);
						continue;
					}
					if( $rexStart > max($this->filters['date']) )
					{
						$rexStart = 0;
						continue;
					}
				}
			}

			$this->companyT->setDateDb( $rexStart );
			$this->companyT->modify( '+' . ($chunkSize - 1) . ' days' );
			$timesEndCheck = $this->companyT->getEndDay();
			$this->companyT->setTimestamp( $timesEndCheck - 1 );
			$rexEnd = $this->companyT->formatDate_Db(); 

			$this->companyT->setDateDb( $rexStart );
			$timesStartCheck = $this->companyT->getStartDay();

			if( $timesStartCheck < $start ){
				$timesStartCheck = $start;
				}

			$times = $this->getAllTime( $timesStartCheck, $timesEndCheck );
			if( $times )
			{
				if( $isBundle ){
					$return = $times;
					}
				else {
					$return = array_keys($times);
					}
				$rexStart = 0;
			}
			else 
			{
			/* check next ones */
				$this->companyT->setDateDb( $rexEnd );
				$this->companyT->modify( '+1 day' );
				$rexStart = $this->companyT->formatDate_Db();
				$this->companyT->modify( '+' . ($chunkSize - 1) . ' days' );
				$rexEnd = $this->companyT->formatDate_Db(); 

				$lrsWhere2 = array();
				$where = array_merge( $lrsWhere, $lrsWhere2 );
				$where[] = "valid_to >= $rexStart";
				$where = join( ' AND ', $where );
				$sql =<<<EOT
				SELECT
					MIN(valid_from) AS min_valid_from
				FROM
					{PRFX}timeblocks
				WHERE
					$where
EOT;
				$result = $ntsdb->runQuery( $sql );
				$e = $result->fetch();
				if( $e['min_valid_from'] )
				{
					if( $e['min_valid_from'] > $rexStart )
						$rexStart = $e['min_valid_from'];
				}
				else
				{
					$rexStart = 0;
				}

				if( $rexStart && isset($this->filters['date']) )
				{
					if( $rexStart < min($this->filters['date']) )
						$rexStart = min($this->filters['date']);
					if( $rexStart > max($this->filters['date']) )
						$rexStart = 0;
				}
			}
		}
		return $return;
	}

/* REALIZATION SPECIFIC STUFF */
	function queryTimeoff( $where = array(), $addon = '' ){
		$ntsdb =& dbWrapper::getInstance();
		$keys = 'id, starts_at, ends_at, resource_id, location_id, description';

		$return = $ntsdb->select(
			$keys,
			'timeoffs',
			$where,
			$addon
			);
		return $return;
		}

	function getTimeoff( $rexDate = '', $endDate = '' ){
		$ntsdb =& dbWrapper::getInstance();

		$where = array();
		if( $rexDate ){
			$this->companyT->setDateDb( $rexDate );
			$startDay = $this->companyT->getStartDay();
			if( $endDate ){
				$this->companyT->setDateDb( $endDate );
				}
			$endDay = $this->companyT->getEndDay();

			$where['starts_at'] = array( '<', $endDay );
			$where['ends_at'] = array( '>', $startDay );
			}
		if( $this->resourceSet || $this->resourceIds ){
			$where['resource_id'] = array( 'IN', $this->resourceIds );
			}

		$return = array();
		if( isset($where['resource_id']) && ($where['resource_id'][0] == 'IN') && (! $where['resource_id'][1]) ){
			}
		else {
			$result = $this->queryTimeoff( $where );
			while( $e = $result->fetch() ){
				$return[] = $e;
				}
			}
		return $return;
		}

	function getBlocksByWhere( $where = array() ){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();

		$keys = 'location_id, resource_id, service_id, starts_at, ends_at, selectable_every, capacity, group_id';
		$keys .= ', valid_from, valid_to, applied_on';

		$result = $ntsdb->select($keys, 'timeblocks', $where );
		$byGroup = array();
		$thisIndex = 0;
		while( $e = $result->fetch() ){
			if( isset($byGroup[$e['group_id']]) ){
				$myIndex = $byGroup[$e['group_id']];
				if( ! in_array($e['location_id'], $return[$myIndex]['location_id']) )
					$return[$myIndex]['location_id'][] = $e['location_id'];
				if( ! in_array($e['resource_id'], $return[$myIndex]['resource_id']) )
					$return[$myIndex]['resource_id'][] = $e['resource_id'];
				if( ! in_array($e['service_id'], $return[$myIndex]['service_id']) )
					$return[$myIndex]['service_id'][] = $e['service_id'];
				if( ! in_array($e['applied_on'], $return[$myIndex]['applied_on']) )
					$return[$myIndex]['applied_on'][] = $e['applied_on'];
				}
			else {
				$e['location_id'] = array($e['location_id']);
				$e['resource_id'] = array($e['resource_id']);
				$e['service_id'] = array($e['service_id']);
				$e['applied_on'] = array($e['applied_on']);
				$return[ $thisIndex ] = $e;
				$byGroup[$e['group_id']] = $thisIndex;
				$thisIndex++;
				}
			}
		return $return;
		}

	function getBlocks( $rexDate = '', $extendedKeys = false ){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();

		$dateWhere = array();
		if( $rexDate ){
			$this->companyT->setDateDb( $rexDate );
			$weekday = $this->companyT->getWeekday();
			$dateWhere[] = "valid_from <= $rexDate AND valid_to >= $rexDate";
			$dateWhere[] = "applied_on = $weekday";
			}

		$lrsWhere = array();
		if( $this->locationIds ){
			$lrsWhere[] = "( location_id IN (" . join(',', $this->locationIds) . ") OR location_id=0 )";
			}
		if( $this->resourceIds ){
			$lrsWhere[] = "resource_id IN (" . join(',', $this->resourceIds) . ")";
			}
		if( $this->serviceIds ){
			$lrsWhere[] = "( service_id IN (" . join(',', $this->serviceIds) . ") OR service_id=0 )";
			}
		$where = array_merge( $dateWhere, $lrsWhere );
		$where = join( ' AND ', $where );

		$keys = 'location_id, resource_id, service_id, starts_at, ends_at, selectable_every, capacity, group_id';
		if( $extendedKeys )
			$keys .= ', id, valid_from, valid_to, applied_on';

		$sql =<<<EOT
		SELECT
			$keys
		FROM
			{PRFX}timeblocks
		WHERE
			$where
EOT;

		$result = $ntsdb->runQuery( $sql );
		while( $e = $result->fetch() ){
			$return[] = $e;
			}
		return $return;
		}

	function getAppointments( $where, $addon = '', $limit = array(), $fields = array() ){
		$return = array();
		$result = $this->queryAppointments( $where, $addon, $limit, $fields );
		if( $result ){
			while( $a = $result->fetch() ){
				$return[] = $a;
				}
			}
		return $return;
		}

	function getLrs( $flat = false ){
		$ntsdb =& dbWrapper::getInstance();

		$return = array();
		$lrs = array();

		$sql =<<<EOT
		SELECT
			DISTINCT( CONCAT(location_id, "-", resource_id, "-", service_id) ) AS lrs
		FROM
			{PRFX}timeblocks
EOT;

		$result = $ntsdb->runQuery( $sql );
		if( $result ){
			while( $e = $result->fetch() ){
				$lrs[] = $e['lrs'];
				}
			}
		$sql =<<<EOT
		SELECT
			DISTINCT( CONCAT(location_id, "-", resource_id, "-", service_id) ) AS lrs
		FROM
			{PRFX}appointments
EOT;

		$result = $ntsdb->runQuery( $sql );
		while( $e = $result->fetch() ){
			$lrs[] = $e['lrs'];
			}
		$lrs = array_unique( $lrs );
		if( $flat ){
			$return = $lrs;
			}
		else {
			reset( $lrs );
			foreach( $lrs as $e ){
				list( $lid, $rid, $sid ) = explode( '-', $e );
				if( $this->resourceIds && ! in_array($rid,$this->resourceIds) )
					continue;

				$lids = ( $lid == 0 ) ? $this->allLocationIds : array( $lid );
				$sids = ( $sid == 0 ) ? $this->allServiceIds : array( $sid );

				reset( $lids );
				foreach( $lids as $lid2 ){
					if( $this->locationIds && ! in_array($lid2,$this->locationIds) ){
						continue;
						}
					reset( $sids );
					foreach( $sids as $sid2 ){
						if( $this->serviceIds && ! in_array($sid2,$this->serviceIds) )
							continue;
						$return[] = array( $lid2, $rid, $sid2 );
						}
					}
				}
			}
		return $return;
		}

	function updateBlocks( $groupId, $params ){
		$toDelete = array();
		$toAdd = array();
		$toUpdate = array();

		$currentBlocks = $this->getBlocksByGroupId( $groupId );
		reset( $currentBlocks );

		$checkExist = array('location_id', 'resource_id', 'service_id', 'applied_on');
		$checkUpdate = array('capacity', 'valid_from', 'valid_to', 'starts_at', 'ends_at', 'selectable_every', 'min_from_now', 'max_from_now');

		$currentOptions = array();
		foreach( $currentBlocks as $cb ){
			$key = join( '-', array($cb['location_id'], $cb['resource_id'], $cb['service_id'], $cb['applied_on']) );
			$currentOptions[ $key ] = $cb;
			}

//		_print_r( $params );
		$newOptions = array();
		if( ! is_array($params['location_id']) ){
			$params['location_id'] = array( $params['location_id'] );
			}
		if( ! is_array($params['resource_id']) ){
			$params['resource_id'] = array( $params['resource_id'] );
			}
		if( ! is_array($params['service_id']) ){
			$params['service_id'] = array( $params['service_id'] );
			}
		reset( $params['location_id'] );
		foreach( $params['location_id'] as $id1 ){
			reset( $params['resource_id'] );
			foreach( $params['resource_id'] as $id2 ){
				reset( $params['service_id'] );
				foreach( $params['service_id'] as $id3 ){
					reset( $params['applied_on'] );
					foreach( $params['applied_on'] as $id4 ){
						$key = join( '-', array($id1, $id2, $id3, $id4) );
						$newOptions[ $key ] = 1;
						}
					}
				}
			}
	// which to delete
		$keys2delete = array_diff( array_keys($currentOptions), array_keys($newOptions) );
		reset( $keys2delete );
		foreach( $keys2delete as $k ){
			$toDelete[] = $currentOptions[$k]['id'];
			}

	// which to add
		$keys2add = array_diff( array_keys($newOptions), array_keys($currentOptions) );
		reset( $keys2add );
		foreach( $keys2add as $k ){
			$nb = array();
			list( $nb['location_id'], $nb['resource_id'], $nb['service_id'], $nb['applied_on'] ) = explode( '-', $k );
			
			reset( $checkUpdate );
			foreach( $checkUpdate as $k2 )
				$nb[$k2] = $params[$k2];
			$nb['group_id'] = $groupId;
			$toAdd[] = $nb;
			}

	// which to update
		$keys2update = array_intersect( array_keys($newOptions), array_keys($currentOptions) );
		reset( $keys2update );
		foreach( $keys2update as $k ){
			$ub = array();

			reset( $checkUpdate );
			foreach( $checkUpdate as $k2 ){
				if( $currentOptions[$k][$k2] != $params[$k2] ){
					$ub[$k2] = $params[$k2];
					}
				}

			if( $ub ){
				$ub['id'] = $currentOptions[$k]['id'];
				$toUpdate[] = $ub;
				}
			}
			
//		_print_r( $toDelete );
//		_print_r( $toUpdate );
//		_print_r( $toAdd );
//		exit;

		$ntsdb =& dbWrapper::getInstance();
//		$ntsdb->_debug = true;
		reset( $toDelete );
		foreach( $toDelete as $id ){
			$ntsdb->delete( 'timeblocks', array('id' => array('=', $id)) );
			}

		reset( $toUpdate );
		foreach( $toUpdate as $a ){
			$id = $a['id'];
			unset( $a['id'] );
			$ntsdb->update( 'timeblocks', $a, array('id' => array('=', $id)) );
			}

		reset( $toAdd );
		foreach( $toAdd as $a ){
			$ntsdb->insert( 'timeblocks', $a );
			}
//		$ntsdb->_debug = false;
		}

	function addBlock( $b ){
		$ntsdb =& dbWrapper::getInstance();
		$t = new ntsTime;

		if( ! isset($b['selectable_every']))
			$b['selectable_every'] = 0;
		if( ! isset($b['ends_at']))
			$b['ends_at'] = 0;

		/* get new group id */
		$newGroupId = 0;
		$result = $ntsdb->select( 'MAX(group_id) AS group_id', 'timeblocks' );
		if( $result && ($i = $result->fetch()) ){
			$newGroupId = $i['group_id'];
			}
		if( $newGroupId )
			$newGroupId = $newGroupId + 1;
		else
			$newGroupId = 1;

		if( ! is_array($b['location_id']) )
			$b['location_id'] = array( $b['location_id'] );
		if( ! is_array($b['resource_id']) )
			$b['resource_id'] = array( $b['resource_id'] );
		if( ! is_array($b['service_id']) )
			$b['service_id'] = array( $b['service_id'] );
		if( ! is_array($b['applied_on']) )
			$b['applied_on'] = array( $b['applied_on'] );

		$toAdd = array();
		reset( $b['location_id'] );
		foreach( $b['location_id'] as $lid ){
			reset( $b['resource_id'] );
			foreach( $b['resource_id'] as $rid ){
				reset( $b['service_id'] );
				foreach( $b['service_id'] as $sid ){
					reset( $b['applied_on'] );
					foreach( $b['applied_on'] as $aon ){
						$newB = $b;
						$newB['location_id'] = $lid;
						$newB['resource_id'] = $rid;
						$newB['service_id'] = $sid;
						$newB['applied_on'] = $aon;
						$newB['group_id'] = $newGroupId;
						$toAdd[] = $newB;
						}
					}
				}
			}
		reset( $toAdd );
		foreach( $toAdd as $a ){
			$ntsdb->insert( 'timeblocks', $a );
			}
		}

	function getBlocksByGroupId( $groupId ){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();
		$extendedKeys = true;

		$where = array();
		$where['group_id'] = array('=', $groupId);

		$what = array('location_id', 'resource_id', 'service_id', 'starts_at', 'ends_at', 'selectable_every', 'capacity', 'group_id', 'min_from_now', 'max_from_now' );
		if( $extendedKeys )
			$what = array_merge($what, array('id', 'valid_from', 'valid_to', 'applied_on'));

		$result = $ntsdb->select( $what, 'timeblocks', $where );
		while( $i = $result->fetch() ){
			$return[] = $i;
			}
		return $return;
		}

	function deleteBlocks( $groupId ){
		$ntsdb =& dbWrapper::getInstance();

		$where = array();
		$where['group_id'] = array('=', $groupId);

		$result = $ntsdb->delete( 'timeblocks', $where );
		}

	function deleteBlocksByWhere( $where ){
		$ntsdb =& dbWrapper::getInstance();
		$result = $ntsdb->delete( 'timeblocks', $where );
		}
	}
?>