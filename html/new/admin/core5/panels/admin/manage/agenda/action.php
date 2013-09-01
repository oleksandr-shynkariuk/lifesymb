<?php
$ff =& ntsFormFactory::getInstance();
$cm =& ntsCommandManager::getInstance();

$t = $NTS_VIEW['t'];
$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

$cal = ntsLib::getVar( 'admin/manage/agenda:cal' );
$period = ntsLib::getVar( 'admin/manage/agenda:period' );
$orderBy = ntsLib::getVar( 'admin/manage/agenda:orderBy' );

$parseClasses = ntsLib::getVar( 'admin/manage/agenda:parseClasses' );

$where = array();
$filter = ntsLib::getVar( 'admin/manage/agenda:filter' );
if( 
	(! isset($filter['completed'])) OR
	in_array($period, array('month', 'all'))
	)
{
	$filter['completed'] = array( '>=', 0 );
	unset( $filter['completed '] );
}

$ntsdb =& dbWrapper::getInstance();

switch( $period ){
	case 'day':
		$t->setDateDb( $cal );
		$dayStart = $t->getStartDay();
		break;
	case 'month':
		$t->setDateDb( $cal );
		$t->setStartMonth();
		$dayStart = $t->getStartDay();
		$t->setEndMonth();
		break;
	case 'upcoming':
		$t->setNow();
		$dayStart = $t->getStartDay();

		$max = 0;
		$result = $ntsdb->select( 'MAX(starts_at+duration+lead_out) AS maxtime', 'appointments' );
		if( $result && ($i = $result->fetch()) ){
			$max = $i['maxtime'];
			}
			
		$result = $ntsdb->select( 'MAX(ends_at) AS maxtime', 'timeoffs' );
		if( $result && ($i = $result->fetch()) ){
			if( $i['maxtime'] > $max )
				$max = $i['maxtime'];
			}

		if( $max ){
			$t->setTimestamp( $max );
			}
		else {
			$t->modify( '+1 year' );
			}
		break;
	case 'all':
	case 'pending':
		$min = 0;
		$max = 0;

		$result = $ntsdb->select( array('MAX(starts_at+duration+lead_out) AS maxtime', 'MIN(starts_at-lead_in) AS mintime'), 'appointments' );
		if( $result && ($i = $result->fetch()) ){
			$min = $i['mintime'];
			$max = $i['maxtime'];
			}

		$result = $ntsdb->select( array('MAX(ends_at) AS maxtime', 'MIN(starts_at) AS mintime'), 'timeoffs' );
		if( $result && ($i = $result->fetch()) ){
			if( $i['mintime'] ){
				if( (! $min) OR ($i['mintime'] < $min) )
					$min = $i['mintime'];
				}
			if( $i['maxtime'] ){
				if( (! $max) OR ($i['maxtime'] > $max) )
					$max = $i['maxtime'];
				}
			}

		if( $min && $max ){
			$t->setTimestamp( $min );
			$dayStart = $t->getStartDay();
			$t->setTimestamp( $max );
			}
		else {
			$t->setNow();
			$t->modify( '-1 year' );
			$dayStart = $t->getStartDay();
			$t->setNow();
			$t->modify( '+1 year' );
			}
		break;
	}

switch( $period ){
	case 'pending':
		$filter['approved'] = array( '=', 0 );
		break;
	}

$daySlotsStart = $dayStart + NTS_TIME_STARTS;
$dayEnd = $t->getEndDay();

$daySlotsEnd = $dayEnd - (24*60*60 - NTS_TIME_ENDS);
ntsLib::setVar( 'admin/manage/agenda::daySlotsStart', $daySlotsStart );
ntsLib::setVar( 'admin/manage/agenda::daySlotsEnd', $daySlotsEnd );

/* TIME MANAGER */
$tm2 = ntsLib::getVar( 'admin::tm2' );

$index = -1;

if( $filter ){
	reset( $filter );
	foreach( $filter as $filterParam => $filterValue ){
		$where[ $filterParam ] = $filterValue;
		}
	}

/* appointments */
$addonWhere = array(
	'(starts_at + duration + lead_out)'	=> array('>', $dayStart),
	'starts_at'							=> array('<', $dayEnd),
	'location_id'						=> array( 'IN', $locs ),
	'resource_id'						=> array( 'IN', $ress ),
	'service_id'						=> array( 'IN', $sers ),
	);
reset( $addonWhere );
foreach( $addonWhere as $k => $v ){
	if( (! isset($where[$k])) && (! isset($where['id'])) )
		$where[$k] = $v;
	}

$apps = $tm2->getAppointments( $where, $orderBy, array(), array('id', 'customer_id') );

$appIds = array();
$custIds = array();
reset( $apps );
foreach( $apps as $app ){
	$appIds[] = $app['id'];
	$custIds[] = $app['customer_id']; 
	}

ntsObjectFactory::preload( 'appointment', $appIds );
$custIds = array_unique( $custIds );
ntsObjectFactory::preload( 'user', $custIds );

ntsLib::setVar( 'admin/manage/agenda::apps', $appIds );

/* if I get one of the classes, then I also show the slots even if there're no appointments */
$slots = array();
$tslots = array();

if( $parseClasses ){
	// get ids of classes
	$classServices = array();
	reset( $tm2->services );
	foreach( $tm2->services as $sid2 => $sa ){
		if( $sa['class_type'] )
			$classServices[] = $sid2;
		}

	if( $classServices ){
		$saveSids = $tm2->getService();
		$tm2->setService( $classServices );
		$times = $tm2->getAllTime( $dayStart, $dayEnd, true );
		reset( $times );
		$classSlotsLrst = array();

		foreach( $times as $ts => $slts ){
			reset( $slts );
			foreach( $slts as $sl ){
				$serviceId = $sl[ $tm2->SLT_INDX['service_id'] ];
				if( ! $tm2->services[$serviceId]['class_type'] )
					continue;

				if( ! in_array($serviceId, $sers) )
					continue;

				$lrst = join( '-', array($sl[ $tm2->SLT_INDX['location_id'] ], $sl[ $tm2->SLT_INDX['resource_id'] ], $sl[ $tm2->SLT_INDX['service_id'] ], $ts) );
				if( isset($classSlotsLrst[$lrst]) )
					continue;
				$classSlotsLrst[$lrst] = 1;

				$slot = array(
					'lrst'			=> $lrst,
					'service_id'	=> $serviceId,
					'starts_at'		=> $ts,
					'duration'		=> $tm2->services[$serviceId]['duration'],
					'seats'			=> $sl[ $tm2->SLT_INDX['seats'] ],
					'type'			=> 'slot',
					);
				$slots[] = $slot;
				}
			}
		$tm2->setService( $saveSids );
		}

	if( ! in_array($period, array('upcoming', 'all', 'pending') ) ){
		/* working times */
		$times = $tm2->getAllTime( $dayStart, $dayEnd - 1 );

		$index = -1;
		reset( $times );
		foreach( $times as $ts => $slts ){
			reset( $slts );
			foreach( $slts as $sl ){
				$lid = $sl[ $tm2->SLT_INDX['location_id'] ];
				$rid = $sl[ $tm2->SLT_INDX['resource_id'] ];
				$sid = $sl[ $tm2->SLT_INDX['service_id'] ];
				$type = $sl[ $tm2->SLT_INDX['type'] ];
				$duration = $tm2->services[$sid]['duration'];

				if( ! (in_array($lid, $locs) && in_array($rid, $ress) && in_array($sid, $sers)) ){
					continue;
					}

				$lrst = join( '-', array($lid, $rid, $sid, $ts) );
			// skip if we already have class with this slot
				if( isset($classSlotsLrst[$lrst]) )
					continue;

				$glue = false;
				if( isset($tslots[$index]) )
				{
					if( $type == haTimeManager2::SLOT_TYPE_SELECTABLE )
					{
						if( $ts <= $tslots[$index][1] )
							$glue = true;
					}
					else
					{
						if( $ts == $tslots[$index][0] )
							$glue = true;
					}
				}

				if( $glue ){
					if( $type == haTimeManager2::SLOT_TYPE_SELECTABLE ){
						if( $ts < $tslots[$index][0] )
							$tslots[$index][0] = $ts;
						if( ($ts + $duration) > $tslots[$index][1] )
							$tslots[$index][1] = ($ts + $duration);
						}
					if( ! in_array($sid, $tslots[$index][3]) )
						$tslots[$index][3][] = $sid;
					}
				else {
					$index++;
					
					if( $type == haTimeManager2::SLOT_TYPE_SELECTABLE )
						$end_slot = ($ts + $duration);
					else
						$end_slot = 0;
					$tslots[ $index ] = array( $ts, $end_slot, HA_SLOT_TYPE_WO, array($sid) );
					}
				}
			}

		reset( $tslots );
		foreach( $tslots as $tslot ){
			$lrst = join( '-', array(0, 0, 0, $tslot[0]) );
			$duration = $tslot[1] ? ($tslot[1] - $tslot[0]) : 0;
			$slot = array(
				'lrst'			=> $lrst,
				'service_id'	=> 0,
				'starts_at'		=> $tslot[0],
				'duration'		=> $duration,
				'seats'			=> -1,
				'type'			=> 'tslot',
				);
			$slots[] = $slot;
			}
		}

	/* timeoffs */
	$t->setTimestamp( $dayStart );
	$dateStart = $t->formatDate_Db();
	$t->setTimestamp( ($dayEnd - 1) );
	$dateEnd = $t->formatDate_Db();

	$toffs = array();
	if( ! in_array($period, array('pending')) )
	{
		$toffs = $tm2->getTimeoff( $dateStart, $dateEnd );
	}
	reset( $toffs );

	foreach( $toffs as $toff ){
		if( ! in_array($toff['resource_id'], $ress) )
			continue;

		$noteResource = ntsObjectFactory::get('resource');
		$noteResource->setId( $toff['resource_id'] );
		$noteView = ntsView::objectTitle( $noteResource ) . ': ' . $toff['description'];

	/* check if this timeoff in multiple days */
		$t->setTimestamp( $toff['starts_at'] );
		$toStartDate = $t->formatDate_Db();

		$t->setTimestamp( $toff['ends_at'] );
		$toEndDate = $t->formatDate_Db();

		$toRexDate = $toStartDate;
		while( $toRexDate <= $toEndDate ){
			$t->setDateDb( $toRexDate );
			$thisDayStart = $t->getStartDay();

			if( $toff['starts_at'] > ($thisDayStart + NTS_TIME_STARTS) )
				$thisStart = $toff['starts_at'];
			else
				$thisStart = ($thisDayStart + NTS_TIME_STARTS);

			if( $toff['ends_at'] > ($thisDayStart + NTS_TIME_ENDS) )
				$thisEnd = ($thisDayStart + NTS_TIME_ENDS);
			else
				$thisEnd = $toff['ends_at'];

			if( 
				($thisStart < $thisEnd) && 
				($thisEnd > $daySlotsStart) &&
				($thisStart < $dayEnd)
				)
			{
				$lrst = join( '-', array(0, $toff['resource_id'], 0, $thisStart) );
				$slot = array(
					'lrst'			=> $lrst,
					'service_id'	=> 0,
					'starts_at'		=> $thisStart,
					'duration'		=> ($thisEnd - $thisStart),
					'seats'			=> -1,
					'type'			=> 'toff',
					'id'			=> $toff['id'],
					'_note'			=> $noteView
					);
				$slots[] = $slot;
			}

		// next date
			$t->setDateDb( $toRexDate );
			$t->modify( '+1 day' );
			$toRexDate = $t->formatDate_Db();
			}
		}
	}

ntsLib::setVar( 'admin/manage/agenda:slots', $slots );

switch( $action ){
	case 'export':
		$display = $_NTS['REQ']->getParam( 'display' );
	
		$t = new ntsTime;
		switch( $display ){
			case 'excel':
				$fileName = 'appointments-' . $t->formatDate_Db() . '.csv';
				ntsLib::startPushDownloadContent( $fileName );
				require( dirname(__FILE__) . '/excel.php' );
				exit;
				break;
				
			}
		break;
	default:
		break;

	}
?>