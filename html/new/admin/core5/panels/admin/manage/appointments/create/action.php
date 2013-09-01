<?php
$cal = ntsLib::getVar( 'admin/manage:cal' );
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$tm2 = ntsLib::getVar('admin::tm2');
$reschedule = ntsLib::getVar( 'admin/manage/appointments/create::reschedule' );
$ntsConf =& ntsConf::getInstance();
$sendCcForAppointment = $ntsConf->get('sendCcForAppointment');

$om =& objectMapper::getInstance();

if( $reschedule ){
	global $NTS_SKIP_APPOINTMENTS;
	$NTS_SKIP_APPOINTMENTS = array( $reschedule->getId() );
	}

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$fParams = array();

$from = $_NTS['REQ']->getParam( 'from' );
$to = $_NTS['REQ']->getParam( 'to' );
$showAll = $_NTS['REQ']->getParam( 'all' );
$time = $_NTS['REQ']->getParam( 'starts_at' );
$endTime = $_NTS['REQ']->getParam( 'ends_at' );

$t = $NTS_VIEW['t'];

if( $time OR $from ){
	$resetTime = $time ? $time : $from;
	$t->setTimestamp($resetTime);
	$cal = $t->formatDate_Db();
	ntsLib::setVar( 'admin/manage:cal', $cal );
	$saveOn['cal'] = $cal;
	ntsView::setPersistentParams( $saveOn, 'admin/manage' );
	}

$t->setDateDb( $cal );
$dayStart = $t->getStartDay();
$dayEnd = $t->getEndDay();

$fParams['dayStart'] = $dayStart;
$fParams['dayEnd'] = $dayEnd;
$fParams['from'] = $from;
$fParams['to'] = $to;
$fParams['showAll'] = $showAll;

$appsCount = 1;
if( $time && preg_match('/-/', $time) ){
	$time = explode( '-', $time );
	$appsCount = count($time);
	}
$fParams['starts_at'] = $time ? $time : 0;

if( $appsCount > 1 ){
	$available = array();
	reset( $time );
	foreach( $time as $testTime ){
		$times = $tm2->getAllTime( $testTime, $testTime );
		reset( $times );
		foreach( $times as $ts => $slots ){
			reset( $slots );
			foreach( $slots as $slot ){
				$key = join( '-', array(
					$slot[ $tm2->SLT_INDX['location_id'] ],
					$slot[ $tm2->SLT_INDX['resource_id'] ],
					$slot[ $tm2->SLT_INDX['service_id'] ],
					$ts
					)
					);
				$available[ $key ] = 1;
				}
			}
		}
	}
else {
	$t->setDateDb( $cal );
	$dayStart = $t->getStartDay();
	$dayEnd = $t->getEndDay();

	$times = $tm2->getAllTime( $dayStart, $dayEnd );
	$available = array();
	reset( $times );
	foreach( $times as $ts => $slots ){
		reset( $slots );
		foreach( $slots as $slot ){
			$key = join( '-', array(
				$slot[ $tm2->SLT_INDX['location_id'] ],
				$slot[ $tm2->SLT_INDX['resource_id'] ],
				$slot[ $tm2->SLT_INDX['service_id'] ],
				$ts
				)
				);
			$available[ $key ] = 1;
			}
		}
	}
$available = array_keys($available);
$fParams['available'] = $available;

$showFull = ntsLib::getVar( 'admin/manage/appointments/create::showFull' );

$locs = $showFull ? ntsLib::getVar( 'admin::locs' ) : ntsLib::getVar( 'admin::locs2' );
$ress = $showFull ? ntsLib::getVar( 'admin::ress' ) : ntsLib::getVar( 'admin::ress2' );
$sers = $showFull ? ntsLib::getVar( 'admin::sers' ) : ntsLib::getVar( 'admin::sers2' );

if( count($locs) == 1 ){
	if( $appsCount > 1 )
		$fParams['location_id'] = array_fill(0, $appsCount, $locs[0]);
	else
		$fParams['location_id'] = $locs[0];
	}
else {
	$lid = $_NTS['REQ']->getParam( 'location_id' );
	if( $appsCount > 1 )
		$fParams['location_id'] = explode('-', $lid);
	else
		$fParams['location_id'] = $lid ? $lid : 0;
	}

if( count($ress) == 1 ){
	if( $appsCount > 1 )
		$fParams['resource_id'] = array_fill(0, $appsCount, $ress[0]);
	else
		$fParams['resource_id'] = $ress[0];
	}
else {
	$rid = $_NTS['REQ']->getParam( 'resource_id' );
	if( $appsCount > 1 )
		$fParams['resource_id'] = explode('-', $rid);
	else
		$fParams['resource_id'] = $rid ? $rid : 0;
	}

if( count($sers) == 1 ){
	if( $appsCount > 1 )
		$fParams['service_id'] = array_fill(0, $appsCount, $sers[0]);
	else
		$fParams['service_id'] = $sers[0];
	}
else {
	$sid = $_NTS['REQ']->getParam( 'service_id' );
	if( $appsCount > 1 )
		$fParams['service_id'] = explode('-', $sid);
	else
		$fParams['service_id'] = $sid ? $sid : 0;
	}

$fixCustomer = ntsLib::getVar( 'admin/manage/appointments/create::fixCustomer' );
if( $fixCustomer ){
	$fParams['customer_id'] = $fixCustomer;
	}
else {
	$cid = $_NTS['REQ']->getParam( 'customer_id' );
	$fParams['customer_id'] = $cid ? $cid : 0;
	}

if( $reschedule ){
	if( $fParams['service_id'] ){
		$class = 'appointment';
		$otherDetails = array(
			'service_id'	=> $fParams['service_id'],
			);
		$customFields = $om->getFields( $class, 'internal', $otherDetails );
		reset( $customFields );
		foreach( $customFields as $cf ){
			$fParams[ $cf[0] ] = $reschedule->getProp( $cf[0] );
			}
		}
	}

if( isset($fParams['starts_at']) && $fParams['starts_at'] && is_array($fParams['starts_at']) ){
	$formFile = dirname( __FILE__ ) . '/formMultiple';
	}
else {
	$formFile = dirname( __FILE__ ) . '/form';
	}

$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $fParams );

switch( $action ){
	case 'create':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

			if( $sendCcForAppointment )
			{
				$cc = array();
				$keys = array_keys($formValues);
				reset( $keys );
				foreach( $keys as $k )
				{
					$pref = 'cc_';
					if( substr($k, 0, strlen($pref)) == $pref )
					{
						$cc_to = trim($formValues[$k]);
						if( $cc_to )
							$cc[] = $cc_to;
						unset( $formValues[$k] );
					}
				}
				if( $cc )
				{
					$formValues['_cc'] = $cc;
				}
			}

			$apps = array();
			$paymentOptions = array();
			if( preg_match('/-/', $formValues['starts_at']) ){
				$startsAt = explode( '-', $formValues['starts_at'] );
				$appsCount = count($startsAt);

				for( $ii = 0; $ii < $appsCount; $ii++ ){
					$newA = array();
					if( isset($formValues['_cc']) ){
						$newA['_cc'] = $formValues['_cc'];
						}

					$explode = array('location_id', 'resource_id', 'service_id', 'starts_at');
					foreach( $explode as $ex ){
						$values = explode( '-', $formValues[$ex] );
						$newA[$ex] = $values[$ii];
						}
					$newA['customer_id'] = $formValues['customer_id'];

					$class = 'appointment';
					$otherDetails = array(
						'service_id'	=> $newA['service_id'],
						);
					$thisCustomFields = $om->getFields( $class, 'internal', $otherDetails );
					reset( $thisCustomFields );
					foreach( $thisCustomFields as $cfi ){
						$newA[$cfi[0]] = $formValues[$cfi[0]];
						}

					if( isset($formValues['payment_option_' . $ii]) )
						$paymentOptions[] = $formValues['payment_option_' . $ii];
					else
						$paymentOptions[] = '';
					$apps[] = $newA;
					}
				}
			else {
				$newA = $formValues;
				if( isset($newA['payment_option']) )
					unset($newA['payment_option']);
				if( $reschedule ){
					$newA['id'] = $reschedule->getId();
					}
				$apps[] = $newA;
				if( isset($formValues['payment_option']) )
					$paymentOptions[] = $formValues['payment_option'];
				else
					$paymentOptions[] = '';
				}

			require( dirname(__FILE__) . '/confirm/_action_check.php' );

			if( (! $failed) ){
				require( dirname(__FILE__) . '/confirm/_action_init.php' );

				if( $reschedule ){
					require( dirname(__FILE__) . '/confirm/_action_change.php' );

					if( count($apps) == 1 )
						$msg = array( M('Appointment'), ntsView::objectTitle($apps[0]) );
					else
						$msg = array( $resultCount . ' ' . M('Appointments') );
					$msg[] = M('Change');
					$msg[] = M('OK');
					$msg = join( ': ' , $msg );
					ntsView::addAnnounce( $msg, 'ok' );
					}
				else {
					require( dirname(__FILE__) . '/confirm/_action_create.php' );

					if( count($apps) == 1 )
						$msg = array( M('Appointment'), ntsView::objectTitle($apps[0]) );
					else
						$msg = array( $resultCount . ' ' . M('Appointments') );
					$msg[] = M('Create');
					$msg[] = M('OK');
					$msg = join( ': ' , $msg );
					ntsView::addAnnounce( $msg, 'ok' );
					}

			/* get back */
				$service = ntsObjectFactory::get( 'service' );
				$service->setId( $apps[0]->getProp('service_id') );
				$serviceType = $service->getType();

				if( $serviceType == 'class' ){
					$idValue = join( '-', array($apps[0]->getProp('location_id'), $apps[0]->getProp('resource_id'), $apps[0]->getProp('service_id'), $apps[0]->getProp('starts_at')) );
				// other apps already here
					$thisIds = array();
					foreach( $apps as $app ){
						$thisIds[] = $app->getId();
						}

					$where = array(
						'id'			=> array('NOT IN', $thisIds),
						'starts_at'		=> array('=', $apps[0]->getProp('starts_at')),
						'location_id'	=> array('=', $apps[0]->getProp('location_id')),
						'resource_id'	=> array('=', $apps[0]->getProp('resource_id')),
						'service_id'	=> array('=', $apps[0]->getProp('service_id')),
						);
					$alreadyApps = $tm2->getAppointments( $where );
				
					if( $alreadyApps ){
						$forceRedirect = false;
						$backLink = ntsLink::makeLink('admin/manage/appointments/edit_class/customers', '', array('_id' => $idValue));
						ntsView::setBack( $backLink );
						}
				// if first one then redirect to calendar
					else {
						$forceRedirect = true;
						}
					}
				else {
					$forceRedirect = true;
					}
				$back = ntsView::getBackLink( $forceRedirect, true );

			/* check if the cal param is set in the back link */
				$startsAt = $apps[0]->getProp('starts_at'); 
				$t->setTimestamp( $startsAt );
				$newCal = $t->formatDate_Db();

				if( preg_match('/([\?|\&]cal=\d{8})/', $back, $ma) ){
					$search = $ma[1];
					$replace = substr($search, 0, -8) . $newCal;
					$back = str_replace( $search, $replace, $back );
					}
				else {
					$back .= '&nts-cal=' . $newCal;
					}
				ntsView::redirect( $back, $forceRedirect, true );
				exit;
				}
			else {
				$NTS_VIEW['form']->valid = false;

			/* not available, get back */
				$t->setTimestamp( $formValues['starts_at'] );
				$timeView = $t->formatFull();
				$errorText = $timeView . ': ' . '<b>' . M('Not Available') . '</b>';
				ntsView::addAnnounce( $errorText, 'error' );
				}
			}
		else {
				/* form not valid, get back */
			}
		break;
	}
?>