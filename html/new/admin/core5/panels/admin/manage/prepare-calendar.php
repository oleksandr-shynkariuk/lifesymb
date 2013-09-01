<?php
$cacheTime = array();

$tm2->setLocation( $locs );
$tm2->setResource( $ress );
$tm2->setService( $sers );

$t = $NTS_VIEW['t'];
$t->setDateDb( $cal );
$t->setEndMonth();
$fullEnd = $t->getTimestamp();
$t->setStartMonth();
$fullStart = $t->getStartDay();

$currentTs = $fullStart;
$dates = array();
while( $currentTs <= $fullEnd ){
	$startDay = $t->getStartDay();
	$thisDate = $t->formatDate_Db();
	$weekDay = $t->getWeekday();
	$t->modify( '+1 day' );
	$endDay = $t->getTimestamp();
	
	$countApps = 0;
	$selectable = 0;
	$dates[ $thisDate ] = array( $startDay, $endDay, $weekDay, $countApps, $selectable );
	$currentTs = $endDay;
	}

ntsLib::setVar( 'admin/manage:dates', $dates );

$selectedDate = $cal;

// ok now check my appointments
$dateStatus = array();
$dates2process = array();
reset( $dates );
foreach( $dates as $date => $da ){
	$dateStatus[ $date ] = 0;
	$dates2process[ $date ] = 1;
	}

$conf =& ntsConf::getInstance();
$showCompleted = $conf->get( 'showCompletedAppsAdmin' );

// ok now check my appointments
if( $locs && $sers && $ress ){
	$where = array(
		'(starts_at + duration + lead_out)'	=> array( '>', $fullStart ),
		'starts_at'							=> array( '<', $fullEnd ),
		'location_id'						=> array( 'IN', $locs ),
		'service_id'						=> array( 'IN', $sers ),
		'resource_id'						=> array( 'IN', $ress ),
//		'completed'							=> array( 'NOT IN', array(HA_STATUS_CANCELLED, HA_STATUS_NOSHOW) ),
		);
	
	if( $showCompleted ){
		$where['completed'] = array( 'NOT IN', array(HA_STATUS_CANCELLED, HA_STATUS_NOSHOW) );
		}
	$totalCount = $tm2->countAppointments( $where );
	}
else {
	$totalCount = 0;
	}

$perQuery = 100;
$startOne = 0;
$lastOne = $startOne + $perQuery;
$checkStart = $fullStart;

$processDatesWithApps = array();
while( $startOne < $totalCount ){
	$apps = $tm2->getAppointments( $where, "ORDER BY starts_at ASC LIMIT $startOne, $perQuery" );

	reset( $apps );
	foreach( $apps as $a ){
		if( ! $dates2process ){
			break;
			}
		if( ($a['starts_at'] + $a['duration'] + $a['lead_out']) < $checkStart )
			continue;

		if( ! in_array($a['location_id'], $locs) )
			continue;
		if( ! in_array($a['resource_id'], $ress) )
			continue;
		if( ! in_array($a['service_id'], $sers) )
			continue;

		reset( $dates2process );
		foreach( array_keys($dates2process) as $date ){
			$da = $dates[$date];
			if( $da[0] >= ($a['starts_at'] + $a['duration'] + $a['lead_in']) )
				continue;
			if( $da[1] <= ($a['starts_at'] - $a['lead_in']) )
				continue;
			$dateStatus[$date] = 2;
			$processDatesWithApps[$date] = 1;
			unset( $dates2process[$date] );
			$checkStart = $da[1];
			break;
			}
		}
	if( ! $dates2process ){
		break;
		}
	$startOne += $perQuery;
	$lastOne = $startOne + $perQuery;
	}

/* check dates which have apps if we have free time */
if( $processDatesWithApps )
{
	foreach( array_keys($processDatesWithApps) as $checkDate )
	{
		$da = $dates[$checkDate];
		$times = $tm2->getAllTime( $da[0], $da[1] );
		if( ! $times ){
			$dateStatus[$checkDate] = 4;
			}
	}
}

/* check timeoffs */
if( $dates2process ){
	$remainDates = array_keys($dates2process);
	$firstDate = $remainDates[0];
	$lastDate = $remainDates[ count($remainDates) - 1 ];

	$tm2->resourceSet = TRUE;
	$toffs = $tm2->getTimeoff( $firstDate, $lastDate );
	if( $toffs ){
		$blocksWhere = array();
		if( $locs ){
			$blocksWhere[] = 'AND';
			$blocksWhere[] = array(
				array( 'location_id' => array('IN', $locs) ),
				array( 'location_id' => array('=', 0) ),
				);
			}
		if( $ress ){
			$blocksWhere[] = 'AND';
			$blocksWhere[] = array(
				array( 'resource_id' => array('IN', $ress) ),
				array( 'resource_id' => array('=', 0) ),
				);
			}
		if( $sers ){
			$blocksWhere[] = 'AND';
			$blocksWhere[] = array(
				array( 'service_id' => array('IN', $sers) ),
				array( 'service_id' => array('=', 0) ),
				);
			}
		$blocksWhere[] = 'AND';
		$blocksWhere[] = array(
			'valid_from' => array( '<=', $lastDate ),
			'valid_to' => array( '>=', $firstDate ),
			);
		$blocks = $tm2->getBlocksByWhere( $blocksWhere );

		reset( $dates2process );
		foreach( $dates2process as $checkDate => $dd ){
			$cacheTime = FALSE;
			$da = $dates[$checkDate];
			reset( $toffs );
			foreach( $toffs as $to ){
				if( $da[0] >= $to['ends_at'] )
					continue;
				if( $da[1] <= $to['starts_at'] )
					continue;		

				if( 
					! (
					( ! $to['location_id'] ) OR
					( $locs && in_array($to['location_id'], $locs) )
					)
					){
						continue;
					}
			
				if( 
					! (
					( ! $to['resource_id'] ) OR
					( $ress && in_array($to['resource_id'], $ress) )
					)
					){
						continue;
					}

				// ok check if we have blocks here
				$gotBlocks = FALSE;
				reset( $blocks );
				foreach( $blocks as $block ){
					if( ! in_array($da[2], $block['applied_on']) ){
						continue;
						}
					if( $block['valid_from'] > $checkDate ){
						continue;
						}
					if( $block['valid_to'] < $checkDate ){
						continue;
						}
					$gotBlocks = TRUE;
					break;
					}

				// if not then its time off
				if( ! $gotBlocks ){
					$dateStatus[$checkDate] = 3;
					unset( $dates2process[$checkDate] );
					break;
					}

				// finally check time, if not then it's timeoff				
				if( is_array($cacheTime) )
				{
					$times = $cacheTime;
				}
				else
				{
					$times = $tm2->getAllTime( $da[0], $da[1] );
					$cacheTime = $times;
				}
				if( ! $times ){
					$dateStatus[$checkDate] = 3;
					unset( $dates2process[$checkDate] );
					break;
					}
				}
			}
		}
	}

/* now see if I have dates without appointments and timeoffs so we check if timeblocks are defined */
if( $dates2process ){
	$remainDates = array_keys($dates2process);
	$firstDate = $remainDates[0];
	$lastDate = $remainDates[ count($remainDates) - 1 ];

	$blocksWhere = array();
	if( $locs ){
		$blocksWhere[] = 'AND';
		$blocksWhere[] = array(
			array( 'location_id' => array('IN', $locs) ),
			array( 'location_id' => array('=', 0) ),
			);
		}
	if( $ress ){
		$blocksWhere[] = 'AND';
		$blocksWhere[] = array(
			array( 'resource_id' => array('IN', $ress) ),
			array( 'resource_id' => array('=', 0) ),
			);
		}
	if( $sers ){
		$blocksWhere[] = 'AND';
		$blocksWhere[] = array(
			array( 'service_id' => array('IN', $sers) ),
			array( 'service_id' => array('=', 0) ),
			);
		}
	$blocksWhere[] = 'AND';
	$blocksWhere[] = array(
		'valid_from' => array( '<=', $lastDate ),
		'valid_to' => array( '>=', $firstDate ),
		);
	if( $ress )
		$blocks = $tm2->getBlocksByWhere( $blocksWhere );
	else
		$blocks = array();

	reset( $dates2process );
	foreach( $dates2process as $checkDate => $dd ){
		$da = $dates[$checkDate];
		reset( $blocks );
		foreach( $blocks as $block ){
			if( ! in_array($da[2], $block['applied_on']) ){
				continue;
				}
			if( $block['valid_from'] > $checkDate ){
				continue;
				}
			if( $block['valid_to'] < $checkDate ){
				continue;
				}

			$dateStatus[$checkDate] = 1;
			unset( $dates2process[$checkDate] );
			break;
			}
		}
	}

$cssDates = array();
$okDates = array();
$linkedDates = array();
$labelDates = array();

reset( $dateStatus );
foreach( $dateStatus as $date => $status ){
	$linkedDates[] = $date;
	$dayClass = array();
	$dayLabel = '';
	switch( $status ){
		case 0:
			$dayClass[] = 'ntsNotWorking';
			$dayLabel = M('Not Available');
			break;
		case 1:
			$dayClass[] = 'ntsWorking';
			$dayLabel = M('Available');
			break;
		case 2:
			$dayClass[] = 'ntsApproved';
			$dayLabel = M('Appointments');
			break;
		case 3:
			$dayClass[] = 'ntsTimeoff';
			$dayLabel = M('Timeoff');
			break;
		case 4:
			$dayClass[] = 'ntsFullyBooked';
			$dayLabel = M('Fully Booked');
			break;
		}
	$cssDates[ $date ] = $dayClass;
	$labelDates[ $date ] = $dayLabel;
	}
?>