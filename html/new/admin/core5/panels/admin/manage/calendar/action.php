<?php
$cm =& ntsCommandManager::getInstance();

$t = $NTS_VIEW['t'];
$cal = ntsLib::getVar( 'admin/manage:cal' );

global $NTS_CURRENT_USER;
$calendarField = $NTS_CURRENT_USER->getProp('_calendar_field');
ntsLib::setVar( 'admin/manage/calendar:calendarField', $calendarField );

$conf =& ntsConf::getInstance();
$showCompleted = $conf->get( 'showCompletedAppsAdmin' );

ntsView::setBack( ntsLink::makeLink('admin/manage/calendar', '', array('cal' => $cal)) );

$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

$locs2 = ntsLib::getVar( 'admin::locs2' );
$ress2 = ntsLib::getVar( 'admin::ress2' );
$sers2 = ntsLib::getVar( 'admin::sers2' );

$filter = ntsLib::getVar( 'admin/manage:filter' );

/* COMPILE LIST OF DISPLAYS */
$list = array();
$displayBy = '';
if( count($ress) > 1 OR ( (count($ress) == 1) && (count($locs) == 1) ) ){
	$displayBy = 'resource';
	reset( $ress );
	foreach( $ress2 as $objId ){
		$obj = ntsObjectFactory::get( 'resource' );
		$obj->setId( $objId );
		$list[] = array( $obj, array($locs, array($obj->getId()), $sers) );
		}
	}
else {
	if( count($locs) > 1 ){
		reset( $locs );
		foreach( $locs2 as $objId ){
			$obj = ntsObjectFactory::get( 'location' );
			$obj->setId( $objId );
			if( in_array('l'.$obj->getId(),$filter) ){
				$displayBy = 'resource';
				$listedObj = ntsObjectFactory::get( 'resource' );
				$listedObj->setId( $ress[0] );
				$list[] = array( $listedObj, array(array($obj->getId()), $ress, $sers) );
				}
			else {
				$displayBy = 'location';
				$list[] = array( $obj, array(array($obj->getId()), $ress, $sers) );
				}
			}
		}
	else {
		reset( $sers );
		foreach( $sers2 as $objId ){
			$obj = ntsObjectFactory::get( 'service' );
			$obj->setId( $objId );
			if( in_array('s'.$obj->getId(),$filter) ){
				if( in_array('l'.$locs[0],$filter) ){
					$displayBy = 'resource';
					$listedObj = ntsObjectFactory::get( 'resource' );
					$listedObj->setId( $ress[0] );
					$list[] = array( $listedObj, array($locs, $ress, array($obj->getId())) );
					}
				else {
					$displayBy = 'location';
					$listedObj = ntsObjectFactory::get( 'location' );
					$listedObj->setId( $locs[0] );
					$list[] = array( $listedObj, array($locs, $ress, array($obj->getId())) );
					}
				}
			else {
				$displayBy = 'service';
				$list[] = array( $obj, array($locs, $ress, array($obj->getId())) );
				}
			}
		}
	}
ntsLib::setVar( 'admin/manage/calendar::list', $list );

/* TIME MANAGER */
$tm2 = ntsLib::getVar( 'admin::tm2' );
if( $showCompleted )
	$tm2->processCompleted = TRUE;

/* check how much days i can show */
$totalMax = 6;
$howManyDates = $list ? ceil( $totalMax / count($list) ) : 0;
$cals = $tm2->getDatesWithSomething( $cal, $howManyDates );

ntsLib::setVar( 'admin/manage:cals', $cals );

$slotsArray = array();
foreach( $cals as $cal ){
	/* build lrs */
	$t->setDateDb( $cal );
	$dayStart = $t->getStartDay();
	$dayEnd = $t->getEndDay();

	$daySlotsStart = $dayStart + NTS_TIME_STARTS;
	$daySlotsEnd = $dayStart + NTS_TIME_ENDS;

	$slots = array();
	$index = array();
	for( $li = 0; $li < count($list); $li++ ){
		$slots[$li] = array( array() );
		$index[$li] = -1;
		}

	/* working times */
	$times = $tm2->getAllTime( $dayStart, $dayEnd - 1 );

	reset( $times );
	foreach( $times as $ts => $slts ){
		reset( $slts );
		foreach( $slts as $sl ){
			$lid = $sl[ $tm2->SLT_INDX['location_id'] ];
			$rid = $sl[ $tm2->SLT_INDX['resource_id'] ];
			$sid = $sl[ $tm2->SLT_INDX['service_id'] ];
			$duration = $tm2->services[$sid]['duration'];

			for( $li = 0; $li < count($list); $li++ ){
				$thisLocs = $list[$li][1][0];
				$thisRess = $list[$li][1][1];
				$thisSers = $list[$li][1][2];
				if( ! (in_array($lid, $thisLocs) && in_array($rid, $thisRess) && in_array($sid, $thisSers)) ){
					continue;
					}

				if( isset($slots[$li][0][$index[$li]]) && ($ts <= $slots[$li][0][$index[$li]][1]) )
					$glue = true;
				else
					$glue = false;

				if( $glue ){
					if( $ts < $slots[$li][0][$index[$li]][0] )
						$slots[$li][0][$index[$li]][0] = $ts;
					if( ($ts + $duration) > $slots[$li][0][$index[$li]][1] )
						$slots[$li][0][$index[$li]][1] = ($ts + $duration);
					if( ! in_array($sid, $slots[$li][0][$index[$li]][3]) )
						$slots[$li][0][$index[$li]][3][] = $sid;
					}
				else {
					$index[$li]++;
					$slots[$li][0][ $index[$li] ] = array( $ts, $ts + $duration, HA_SLOT_TYPE_WO, array($sid) );
					}
				}
			}
		}

	/* appointments */
	$where = array(
		'(starts_at + duration + lead_out)'	=> array('>', $dayStart),
		'starts_at'							=> array('<', $dayEnd)
		);

	if( $showCompleted ){
		$where['completed'] = array( '<>', HA_STATUS_CANCELLED );
		$where['completed '] = array( '<>', HA_STATUS_NOSHOW );
		}

	$apps = $tm2->getAppointments( $where, 'ORDER BY starts_at ASC' );
	reset( $apps );

	$slotApps = array();
	$index = array();
	for( $li = 0; $li < count($list); $li++ ){
		$slotApps[$li] = array();
		$lrst[$li] = array();
		}

	/* timeoffs */	
	$toffs = $tm2->getTimeoff( $cal );
	reset( $toffs );

	foreach( $toffs as $toff ){
		if( ! in_array($toff['resource_id'], $ress) )
			continue;

		for( $li = 0; $li < count($list); $li++ ){
			$thisLocs = $list[$li][1][0];
			$thisRess = $list[$li][1][1];
			$thisSers = $list[$li][1][2];
			if( ! (in_array($toff['resource_id'], $thisRess)) ){
				continue;
				}

			$thisStart = ( $toff['starts_at'] > $daySlotsStart ) ? $toff['starts_at'] : $daySlotsStart;
			$thisEnd = ( $toff['ends_at'] < $daySlotsEnd ) ? $toff['ends_at'] : $daySlotsEnd;
			$slotApps[$li][] = array( $thisStart, $thisEnd, HA_SLOT_TYPE_TOFF, $toff['id'] );
			}
		}

	foreach( $apps as $app ){
		if( ! in_array($app['location_id'], $locs) )
			continue;
		if( ! in_array($app['resource_id'], $ress) )
			continue;
		if( ! in_array($app['service_id'], $sers) )
			continue;

		$classType = $tm2->services[$app['service_id']]['class_type'];
		for( $li = 0; $li < count($list); $li++ ){
			$thisLocs = $list[$li][1][0];
			$thisRess = $list[$li][1][1];
			$thisSers = $list[$li][1][2];
			if( ! (in_array($app['location_id'], $thisLocs) && in_array($app['resource_id'], $thisRess) && in_array($app['service_id'], $thisSers)) ){
				continue;
				}

			if( $classType ){ // class
				$thisLrst = join( '-', array($app['location_id'], $app['resource_id'], $app['service_id'], $app['starts_at']) );
				if( isset($lrst[$li][$thisLrst]) ){ // glue with existing
					$targetIndex = $lrst[$li][$thisLrst];
	//				$slotApps[$li][ $targetIndex ][3][] = $app['id'];
					$slotApps[$li][ $targetIndex ][3][] = $app;
					}
				else { // start new
					$thisStart = ( $app['starts_at'] > $daySlotsStart ) ? $app['starts_at'] : $daySlotsStart;
					$thisEnd = ( ($app['starts_at'] + $app['duration']) < $daySlotsEnd ) ? ($app['starts_at'] + $app['duration']) : $daySlotsEnd;
	//				$slotApps[$li][] = array( $thisStart, $thisEnd, HA_SLOT_TYPE_APP_BODY, array($app['id']) );
					$slotApps[$li][] = array( $thisStart, $thisEnd, HA_SLOT_TYPE_APP_BODY, array($app) );
					$lrst[$li][$thisLrst] = count($slotApps[$li]) - 1;
					}
				}
			else {
				$thisStart = ( $app['starts_at'] > $daySlotsStart ) ? $app['starts_at'] : $daySlotsStart;
				$thisEnd = ( ($app['starts_at'] + $app['duration']) < $daySlotsEnd ) ? ($app['starts_at'] + $app['duration']) : $daySlotsEnd;
				$slotApps[$li][] = array( $thisStart, $thisEnd, HA_SLOT_TYPE_APP_BODY, $app['id'] );
				}
			}
		}

	$sortFunc = create_function('$a, $b', 'return ($a[0] - $b[0]);');

	//for( $li = 0; $li < count($list); $li++ ){
	//	usort( $slotApps[$li], $sortFunc );
	//	}

	/* now add appointments to slots checking overlap */
	for( $li = 0; $li < count($list); $li++ ){
		reset( $slotApps[$li] );
		foreach( $slotApps[$li] as $sa ){
			// check by rows
			$foundRow = -1;
			$addSlot = true;

			for( $row = 0; $row < count($slots[$li]); $row++ ){
				$ok = true;
				reset($slots[$li][$row]);
				for( $si = 0; $si < count($slots[$li][$row]); $si++ ){
					$slot = $slots[$li][$row][$si];

					if( ($sa[0] == $slot[0]) && ($sa[1] == $slot[1]) && is_array($sa[3]) ){
						$thisAppSid = $sa[3][0]['service_id'];
						if( 
							(count($slot[3]) == 1) && 
							($thisAppSid == $slot[3][0]) &&
							$tm2->services[$thisAppSid]['class_type']
							)
							{ // only this service and it's class
							// delete the availability slot
							$slots[$li][$row][$si] = $sa;
							$addSlot = false;
							$ok = false;
							break;
							}
						}

					if( ($sa[0] < $slot[1]) && ($sa[1] > $slot[0]) ){
						// overlaps
						$ok = false;
						break;
						}
	//				elseif( ($slot[2] == HA_SLOT_TYPE_WO) && is_array($sa[3]) && (count($slot[3]) < 2) ){
	//					$ok = false;
	//					break;
	//					}
					}
				if( $ok ){
					$foundRow = $row;
					break;
					}
				}

			if( $addSlot ){
				if( $foundRow >= 0 ){
					$slots[$li][ $foundRow ][] = $sa;
					}
				else {
					$slots[$li][] = array( $sa );
					}
				}
			}
		}

	for( $li = 0; $li < count($list); $li++ ){
		for	($r = 0; $r < count($slots[$li]); $r++ ){
			usort( $slots[$li][$r], $sortFunc );
			}
		}

	/* ok now add slots to fill unavailable time */
	for( $li = 0; $li < count($list); $li++ ){
		$oldSlots = $slots[$li];
		$slots[$li] = array( array() );

		for( $r = 0; $r < count($oldSlots); $r++ ){
			$slots[$li][$r] = array();
			$slotCount = count($oldSlots[$r]);
			if( $slotCount ){
				for( $ii = 0; $ii < $slotCount; $ii++ ){
					$checkStart = ( $ii == 0 ) ? $daySlotsStart : $oldSlots[$r][$ii-1][1];
					$checkEnd = ( $ii == ($slotCount-1) ) ? $daySlotsEnd : $oldSlots[$r][$ii+1][0];

					if( $oldSlots[$r][$ii][0] > $checkStart ){
						$slots[$li][$r][] = array($checkStart, $oldSlots[$r][$ii][0], HA_SLOT_TYPE_NA);
						}

					$slots[$li][$r][] = $oldSlots[$r][$ii];
					$oldSlots[$r][$ii][0] = $checkStart;

					if( $oldSlots[$r][$ii][1] < $checkEnd ){
						$slots[$li][$r][] = array($oldSlots[$r][$ii][1], $checkEnd, HA_SLOT_TYPE_NA);
						}
					$oldSlots[$r][$ii][1] = $checkEnd;
					}
				}
			else {
				$slots[$li][$r][] = array($daySlotsStart, $daySlotsEnd, HA_SLOT_TYPE_NA);
				}
			}
		}

	/* add width property */
	$totalDuration = ($daySlotsEnd - $daySlotsStart);
	for( $li = 0; $li < count($list); $li++ ){
		for( $r = 0; $r < count($slots[$li]); $r++ ){
			$alreadyWidth = 0;
			$slotCount = count($slots[$li][$r]);
			for( $ss = 0; $ss < $slotCount; $ss++ ){
				if( $ss < ($slotCount-1) ){
					$width = ( $totalDuration > 0 ) ? floor(99 * 100 * (($slots[$li][$r][$ss][1] - $slots[$li][$r][$ss][0])/$totalDuration ))/100 : 0;
					}
				else {
					$width = floor(100 * (99 - $alreadyWidth)) / 100;
					}
				$slots[$li][$r][$ss][4] = $width;
				$alreadyWidth += $width;
				}
			}
		}

	$slotsArray[ $cal ] = $slots;
	}

ntsLib::setVar( 'admin/manage/calendar:slots', $slotsArray );
?>