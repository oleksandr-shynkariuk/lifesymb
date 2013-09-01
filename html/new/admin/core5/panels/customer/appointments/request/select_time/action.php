<?php
ntsView::setTitle( M('Date and Time') );
$ntsConf =& ntsConf::getInstance();
$showMonths = $ntsConf->get('monthsToShow');
$daysToShow = $ntsConf->get('daysToShowCustomer');
$seats = 1;

global $NTS_AR, $NTS_CALENDAR_STOP;
$NTS_CALENDAR_STOP = false;
require_once( dirname(__FILE__) . '/../common/grab.php' );
$current = $NTS_AR->getCurrent();
$currentIndexes = $NTS_AR->getCurrentIndexes();

$today = $t->formatDate_Db();

$NTS_VIEW['cal'] = array();
$NTS_VIEW['dates'] = array();
$NTS_VIEW['times'] = array();

$recurring = $_NTS['REQ']->getParam('recurring');
if( ! $recurring )
	$recurring = 'single';
$NTS_VIEW['recurring'] = $recurring;

$preferredTime = $_NTS['REQ']->getParam('preferred-time');
$NTS_VIEW['preferredTime'] = $preferredTime;
if( strlen($preferredTime) ){
	$NTS_AR->setOther( 'preferred-time', $preferredTime );
	}

/* custom dates options */
$customDates = $_NTS['REQ']->getParam('custom-dates');
if( $customDates )
	$customDates = explode( '-', $customDates );
else {
	$customDates = array();
	}
$customDates = array_unique( $customDates );
sort( $customDates );
$NTS_VIEW['custom-dates'] = $customDates;

if( count($customDates) ){
	$service = $NTS_AR->getSelected( $currentIndexes[0], 'service' );
	$myRecurTotal = $service->getProp( 'recur_total' );
	if( count($customDates) >= $myRecurTotal ){
		$NTS_CALENDAR_STOP = true;
		}
	}

/* recurring options */
$recurringDates = array();
$allowedDates = array();

$recurEvery = $_NTS['REQ']->getParam('recur-every');
$NTS_VIEW['recur-every'] = $recurEvery;
$recurFrom = $_NTS['REQ']->getParam('recur-from');
$NTS_VIEW['recur-from'] = $recurFrom;
$recurTo = $_NTS['REQ']->getParam('recur-to');
$NTS_VIEW['recur-to'] = $recurTo;

if( $recurFrom ){
	if( in_array($recurEvery, array('3w', 'm', '6w', 'custom')) ){
		if( $showMonths < 3 )
			$showMonths = 3;
		}
	if( in_array($recurEvery, array('w', '2w')) ){
		if( $showMonths < 2 )
			$showMonths = 2;
		}
	}

$NTS_VIEW['showMonths'] = $showMonths;

if( $recurEvery && $recurFrom && $recurTo ){
	$NTS_CALENDAR_STOP = true;

	$recurEveryString = ntsTime::expandPeriodString( $recurEvery );
	$recurringDates = array();
	$nextDate = $recurFrom;
	$t->setDateDb( $nextDate );
	while( $nextDate <= $recurTo ){
		$recurringDates[] = $nextDate;
		$t->modify( $recurEveryString );
		$nextDate = $t->formatDate_Db();
		}
	}
if( $recurEvery && $recurFrom ){
	$maxRecurApps = 0;
	reset( $currentIndexes );
	foreach( $currentIndexes as $i ){
		$service = $NTS_AR->getSelected( $i, 'service' );
		$thisRecurApps = $service->getProp( 'recur_total' );
		if( $thisRecurApps > $maxRecurApps ){
			$maxRecurApps = $thisRecurApps;
			}
		}

	$recurEveryString = ntsTime::expandPeriodString( $recurEvery );
	$nextDate = $recurFrom;
	$t->setDateDb( $nextDate );
	for( $r = 1; $r <= $maxRecurApps; $r++ ){
		$allowedDates[] = $nextDate;
		$t->modify( $recurEveryString );
		$nextDate = $t->formatDate_Db();
		}
	}
//_print_r( $allowedDates );
$NTS_VIEW['recurring-dates'] = $recurringDates;

$preferredTimeFound = array();
$currentIndexes = $NTS_AR->getCurrentIndexes();

/* if bundle */
$isBundle = false;
if( count($currentIndexes) > 1 ){
	$isBundle = true;
	foreach( $currentIndexes as $i ){
		$selectedDate = $NTS_AR->getSelected( $i, 'date' );
		if( $selectedDate ){
			$isBundle = false;
			break;
			}
		}
	}

if( $isBundle ){
	$tm2->isBundle = true;

	$bundleServices = array();
	reset( $currentIndexes );
	foreach( $currentIndexes as $i ){
		$service = $NTS_AR->getSelected( $i, 'service' );
		$bundleServices[] = $service->getId();
		}
/* get this bundle */
	$bundleGap = 0;
	$bundleWhere = array(
		'services'	=> array( '=', join( '-', $bundleServices ) )
		);
	$findBundles = ntsObjectFactory::find( 'bundle', $bundleWhere );
	if( $findBundles && isset($findBundles[0]) ){
		$bundleGap = $findBundles[0]->getProp('gap');
		}
	$tm2->bundleGap = $bundleGap;
		
	$tm2->setService( $bundleServices );

	$resource = $NTS_AR->getSelected( $currentIndexes[0], 'resource' );
	$tm2->setResource( $resource );

	$location = $NTS_AR->getSelected( $currentIndexes[0], 'location' );
	$tm2->setLocation( $location );

/* FIND DATES */
	// if date is already selected, no need to search for dates
	$selectedDate = $NTS_AR->getSelected( $currentIndexes[0], 'date' );
	if( ! $selectedDate ){
		$START_CHECK_DATE = 0; $END_CHECK_DATE = 0; $END_CALENDAR = 0;

/* IF CALENDAR IS SUPPLIED THEN START CHECK AT THIS DATE MONTH'S */
		$requestedCal = $NTS_AR->getSelected( $currentIndexes[0], 'cal' );
	/* not recurring */
		if( $requestedCal ){
			$t->setDateDb( $requestedCal );
			$thisDay = $t->getDay();
			$t->setStartMonth();
			$START_CHECK_DATE = $t->formatDate_Db();

			$t->setDateDb( $START_CHECK_DATE );
			$t->modify( '+' . ($showMonths - 1) . ' months' );
			$t->setEndMonth();
			$END_CHECK_DATE = $t->formatDate_Db();
			$END_CALENDAR = $t->formatDate_Db();
			}

		$tm2->customerT = $t;

	/* no start/end check? */
		if( ! $START_CHECK_DATE ){
			$nextTimes = $tm2->getNextTimes();
			if( $nextTimes ){
				$time = $nextTimes[0];
				$t->setTimestamp( $time );
				$START_CHECK_DATE = $t->formatDate_Db();
				$t->modify( '+' . ($showMonths - 1) . ' months' );
				$t->setEndMonth();
				$END_CHECK_DATE = $t->formatDate_Db();
				}
			}

		$dates = array();
		$t->setDateDb( $START_CHECK_DATE );
		$time = $t->getTimestamp();
		$shownDays = 0;
		$breakThis = false;

		while( $nextTimes = $tm2->getNextTimes($time) ){
			reset( $nextTimes );
			$checkAfter = $nextTimes[0];
			foreach( $nextTimes as $time ){
				if( $time < $checkAfter ){
					continue;
					}
				$t->setTimestamp( $time );
				$date = $t->formatDate_Db();
				if( ! $requestedCal ){
					$requestedCal = $date;
					}

				if( $recurFrom && $recurEvery ){
					if( $END_CALENDAR && ($date > $END_CALENDAR) ){
						$breakThis = true;
						break;
						}
					}
				else {
					if( ($date > $END_CHECK_DATE) && ( $shownDays >= $daysToShow ) ){
						$breakThis = true;
						break;
						}
					
					}

			/* no recurring */
				if( (! $allowedDates) ){
					$dates[] = $date;
					if( $date >= $requestedCal ){
						$shownDays++;
						}
					$checkAfter = $t->getEndDay();
					}
			/* check only recurring dates */
				else {
					if( in_array($date, $allowedDates) ){
						$dates[] = $date;
						if( $date >= $requestedCal ){
							$shownDays++;
							}
						}

					// find next allowed date
					$nextAllowedDate = 0;
					reset( $allowedDates );
					foreach( $allowedDates as $ad ){
						if( $ad > $date ){
							$nextAllowedDate = $ad;
							break;
							}
						}

					if( $nextAllowedDate ){
						$t->setDateDb( $nextAllowedDate );
						$checkAfter = $t->getStartDay();
						}
					else {
						$breakThis = true;
						break;
						}
					}
				}
			if( $breakThis )
				break;
			$time = $checkAfter;
			}
		if( ! $requestedCal )
			$requestedCal = $today;
		$cal = $requestedCal;

		$NTS_AR->setSelected( $currentIndexes[0], 'cal', $cal );

		$NTS_VIEW['cal'][$currentIndexes[0]] = $cal;
		$NTS_VIEW['dates'][$currentIndexes[0]] = $dates;
		}

/* FIND TIMES */
	$timesStartCheck = 0;
	$timesEndCheck = 0;

	if( $selectedDate ){
		$t->setDateDb( $selectedDate );
		$timesStartCheck = $t->getStartDay();
		$timesEndCheck = $t->getEndDay();
		}
	else {
		$timeStartDate = '';
		$timeEndDate = '';

		$shown = 0;
		reset( $dates );
		foreach( $dates as $date ){
			if( $requestedCal > $date ){
				continue;
				}
			if( ! $shown ){
				$timeStartDate = $date;
				}

			$timeEndDate = $date;
			$shown++;
			if( $shown >= $daysToShow ){
				break;
				}
			}

		if( $timeStartDate && $timeEndDate ){
			$t->setDateDb( $timeStartDate );
			$timesStartCheck = $t->getTimestamp();
			$t->setDateDb( $timeEndDate );
			$timesEndCheck = $t->getEndDay();
			}
		}

	$bundleDurations = array();
	for( $jj = 0; $jj < count($currentIndexes); $jj++ ){
		$service = $NTS_AR->getSelected( $jj, 'service' );
		$duration = $service->getProp('duration');
		$bundleDurations[] = $duration;
		}

	$times = array();
	if( $timesStartCheck && $timesEndCheck ){
		$times = array();
		$nextTimes = $tm2->getNextTimes( $timesStartCheck, 0, TRUE );

		while( $nextTimes ){
			foreach( $nextTimes as $nextTime => $finalNextTime ){
				if( $nextTime >= $timesEndCheck )
					break;
				$times[] = $finalNextTime;
				}

			if( $nextTime >= $timesEndCheck ){
				$nextTimes = array();
				}
			else {
				$t->setTimestamp( $nextTime );
				$nextTime = $t->getEndDay();
				$nextTimes = $tm2->getNextTimes( $nextTime );
				}
			}
		}

	/* if preferred time is here we may forward then */
	if( strlen($preferredTime) && $selectedDate ){
		$thisDayPreferredTime = $timesStartCheck + $preferredTime;
		if( in_array($thisDayPreferredTime, $times) ){
			$preferredTimeFound[ $i ] = $thisDayPreferredTime;
			}
		}
//	$NTS_VIEW['times'][$currentIndexes[0]] = $times;
	$NTS_VIEW['bundleTimes'] = $times;
	
	if( (! $selectedDate) && (! $dates) ){
		reset( $currentIndexes );
		foreach( $currentIndexes as $ci ){
			$NTS_AR->resetSelected( $ci, 'service' );
			$NTS_AR->resetSelected( $ci, 'seats' );
			$NTS_AR->resetSelected( $ci, 'cal' );
			}

		ntsView::setAnnounce( M('Bundle') . ': ' . M('Not Available'), 'error' );
		$forwardTo = ntsLink::makeLink( '-current-/../select_service' );
		ntsView::redirect( $forwardTo );
		exit;
		}
	}
else {
	reset( $currentIndexes );
	foreach( $currentIndexes as $i ){
		$service = $NTS_AR->getSelected( $i, 'service' );
		$tm2->setService( $service );

		$resource = $NTS_AR->getSelected( $i, 'resource' );
		$tm2->setResource( $resource );

		$location = $NTS_AR->getSelected( $i, 'location' );
		$tm2->setLocation( $location );

	/* FIND DATES */
		// if date is already selected, no need to search for dates
		$selectedDate = $NTS_AR->getSelected( $i, 'date' );
		if( ! $selectedDate ){
			$START_CHECK_DATE = 0; $END_CHECK_DATE = 0; $END_CALENDAR = 0;

	/* IF CALENDAR IS SUPPLIED THEN START CHECK AT THIS DATE MONTH'S */
			$requestedCal = $NTS_AR->getSelected( $i, 'cal' );
		/* recurring */
			if( $recurFrom && ! ($NTS_CALENDAR_STOP)){
				$t->setDateDb( $recurFrom );
				$thisDay = $t->getDay();
				$t->modify( '+1 day' );
				$START_CHECK_DATE = $t->formatDate_Db();
				if( $requestedCal && ($requestedCal > $START_CHECK_DATE) )
					$START_CHECK_DATE = $requestedCal;

				$t->setDateDb( $requestedCal );
				$t->modify( '+' . ($showMonths - 1) . ' months' );
				$t->setEndMonth();
				$END_CALENDAR = $t->formatDate_Db();

				if( $recurTo ){
					$END_CHECK_DATE = $recurTo;
					}
				else {
				// check if within allowed number of apps
					$service = $NTS_AR->getSelected( $i, 'service' );
					$maxRecurApps = $service->getProp( 'recur_total' );
					$t->setDateDb( $recurFrom );
					$recurString = ntsTime::expandPeriodString( $recurEvery, $maxRecurApps );
					$t->modify( $recurString );
					$END_CHECK_DATE = $t->formatDate_Db();
					}
				}
		/* not recurring */
			else {
				if( $requestedCal ){
					$t->setDateDb( $requestedCal );
					$thisDay = $t->getDay();
					$t->setStartMonth();
					$START_CHECK_DATE = $t->formatDate_Db();

					$t->setDateDb( $START_CHECK_DATE );
					$t->modify( '+' . ($showMonths - 1) . ' months' );
					$t->setEndMonth();
					$END_CHECK_DATE = $t->formatDate_Db();
					$END_CALENDAR = $t->formatDate_Db();
					}
				}

			$tm2->customerT = $t;

		/* no start/end check? */
			if( ! $START_CHECK_DATE ){
				$nextTimes = $tm2->getNextTimes();
				if( $nextTimes ){
					$time = $nextTimes[0];
					$t->setTimestamp( $time );
					$START_CHECK_DATE = $t->formatDate_Db();
					$t->modify( '+' . ($showMonths - 1) . ' months' );
					$t->setEndMonth();
					$END_CHECK_DATE = $t->formatDate_Db();
					}
				}

			$dates = array();
			$t->setDateDb( $START_CHECK_DATE );
			$time = $t->getTimestamp();
			$shownDays = 0;
			$breakThis = false;

			while( $nextTimes = $tm2->getNextTimes($time) ){
				reset( $nextTimes );
				$checkAfter = $nextTimes[0];
				foreach( $nextTimes as $time ){
					if( $time < $checkAfter ){
						continue;
						}
					$t->setTimestamp( $time );
					$date = $t->formatDate_Db();
					if( ! $requestedCal ){
						$requestedCal = $date;
						}

					if( $recurFrom && $recurEvery ){
						if( $END_CALENDAR && ($date > $END_CALENDAR) ){
							$breakThis = true;
							break;
							}
						}
					else {
						if( ($date > $END_CHECK_DATE) && ( $shownDays >= $daysToShow ) ){
							$breakThis = true;
							break;
							}
						
						}

				/* no recurring */
					if( (! $allowedDates) ){
						$dates[] = $date;
						if( $date >= $requestedCal ){
							$shownDays++;
							}
						$checkAfter = $t->getEndDay();
						}
				/* check only recurring dates */
					else {
						if( in_array($date, $allowedDates) ){
							$dates[] = $date;
							if( $date >= $requestedCal ){
								$shownDays++;
								}
							}

						// find next allowed date
						$nextAllowedDate = 0;
						reset( $allowedDates );
						foreach( $allowedDates as $ad ){
							if( $ad > $date ){
								$nextAllowedDate = $ad;
								break;
								}
							}

						if( $nextAllowedDate ){
							$t->setDateDb( $nextAllowedDate );
							$checkAfter = $t->getStartDay();
							}
						else {
							$breakThis = true;
							break;
							}
						}
					}
				if( $breakThis )
					break;
				$time = $checkAfter;
				}
			if( ! $requestedCal )
				$requestedCal = $today;
			$cal = $requestedCal;

			$NTS_AR->setSelected( $i, 'cal', $cal );

			$NTS_VIEW['cal'][$i] = $cal;
			$NTS_VIEW['dates'][$i] = $dates;
			}

	/* FIND TIMES */
		$timesStartCheck = 0;
		$timesEndCheck = 0;

		if( $selectedDate ){
			$t->setDateDb( $selectedDate );
			$timesStartCheck = $t->getStartDay();
			$timesEndCheck = $t->getEndDay();
			}
		else {
			$timeStartDate = '';
			$timeEndDate = '';

			$shown = 0;
			reset( $dates );
			foreach( $dates as $date ){
				if( $requestedCal > $date ){
					continue;
					}
				if( ! $shown ){
					$timeStartDate = $date;
					}

				$timeEndDate = $date;
				$shown++;
				if( $shown >= $daysToShow ){
					break;
					}
				}

			if( $timeStartDate && $timeEndDate ){
				$t->setDateDb( $timeStartDate );
				$timesStartCheck = $t->getTimestamp();
				$t->setDateDb( $timeEndDate );
				$timesEndCheck = $t->getEndDay();
				}
			}

		$times = array();
		if( $timesStartCheck && $timesEndCheck ){
			$times = array();
			$nextTimes = $tm2->getNextTimes( $timesStartCheck );
			while( $nextTimes ){
				foreach( $nextTimes as $nextTime ){
					if( $nextTime >= $timesEndCheck )
						break;
					$times[] = $nextTime;
					}

				if( $nextTime >= $timesEndCheck ){
					$nextTimes = array();
					}
				else {
					$t->setTimestamp( $nextTime );
					$nextTime = $t->getEndDay();
					$nextTimes = $tm2->getNextTimes( $nextTime );
					}
				}
			}

		/* if preferred time is here we may forward then */
		if( strlen($preferredTime) && $selectedDate ){
			$thisDayPreferredTime = $timesStartCheck + $preferredTime;
			if( in_array($thisDayPreferredTime, $times) ){
				$preferredTimeFound[ $i ] = $thisDayPreferredTime;
				}
			}
		$NTS_VIEW['times'][$i] = $times;
		}

	/* preferred time found for all options */
	if( count($preferredTimeFound) == count($currentIndexes) ){
		reset( $currentIndexes );
		foreach( $preferredTimeFound as $i => $thisDayPreferredTime ){
			$NTS_AR->setSelected( $i, 'time', $thisDayPreferredTime );
			$NTS_AR->resetSelected( $i, 'date' );
			$NTS_AR->resetSelected( $i, 'cal' );
			}
		$NTS_AR->resetOther( 'preferred-time' );

		/* forward to dispatcher to see what's next? */
		$noForward = false;
		require( dirname(__FILE__) . '/../common/dispatcher.php' );
		exit;
		}
	}

$NTS_VIEW['isBundle'] = $isBundle;
	
$NTS_VIEW['t'] = $t;

/* check times in selected dates if any */
$preferredTimes = array();
$finalDates = array();

$checkDates = array();
if( $recurringDates )
	$checkDates = $recurringDates;
elseif( $customDates )
	$checkDates = $customDates;

if( $checkDates ){
	$finalDates = array();

	$service = $NTS_AR->getSelected( $currentIndexes[0], 'service' );
	$tm2->setService( $service );
	$resource = $NTS_AR->getSelected( $currentIndexes[0], 'resource' );
	$tm2->setResource( $resource );
	$location = $NTS_AR->getSelected( $currentIndexes[0], 'location' );
	$tm2->setLocation( $location );

	reset( $checkDates );
	foreach( $checkDates as $rd ){
		$t->setDateDb( $rd );
		$timesStartCheck = $t->getStartDay();
		$timesEndCheck = $t->getEndDay();

		$times = array();
		$nextTimes = $tm2->getNextTimes( $timesStartCheck );
		while( $nextTimes ){
			foreach( $nextTimes as $nextTime ){
				if( $nextTime >= $timesEndCheck )
					break;
				$times[] = $nextTime;
				}
			$nextTimes = ( $nextTime >= $timesEndCheck ) ? array() : $tm2->getNextTimes( $nextTime + 1 );
			}

		if( $times ){
			$finalDates[] = $rd;
			reset( $times );
			foreach( $times as $time ){
				$timeInDay = $time - $timesStartCheck;
				if( ! in_array($timeInDay, $preferredTimes) )
					$preferredTimes[] = $timeInDay;
				}
			}
		}
	if( $recurringDates )
		$NTS_VIEW['recurring-dates'] = $finalDates;
	elseif( $customDates )
		$NTS_VIEW['custom-dates'] = $finalDates;
	sort( $preferredTimes );
	}
$NTS_VIEW['preferred-times'] = $preferredTimes;
?>