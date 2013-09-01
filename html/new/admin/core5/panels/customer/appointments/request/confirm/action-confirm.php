<?php
require_once( dirname(__FILE__) . '/../common/grab.php' );

$customerId = 0;
$loginClicked = $_NTS['REQ']->getParam('login');
$registerClicked = $_NTS['REQ']->getParam('register');
$reguireLoginForm = false;
$reguireRegisterForm = false;

if( $loginClicked ){
	$reguireLoginForm = true;
	}
elseif( $registerClicked ){
	$reguireRegisterForm = true;
	}

$ff =& ntsFormFactory::getInstance();
$formFile = dirname(__FILE__) . '/form';
$formParams = array(
	'reguireLoginForm'		=> $reguireLoginForm,
	'reguireRegisterForm'	=> $reguireRegisterForm
	);
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

if( $loginClicked ){
	require( dirname(__FILE__) . '/action-login.php' );
	}
elseif( $registerClicked ){
	require( dirname(__FILE__) . '/action-register.php' );
	}

/* it may redirect and exit in this file */
require( dirname(__FILE__) . '/before-confirm.php' );

if( ! $customerId ){
	if( NTS_CURRENT_USERID ){
		$customerId = NTS_CURRENT_USERID;
		}
	elseif( isset($_SESSION['temp_customer_id']) ){
		$customerId = $_SESSION['temp_customer_id'];
		}
	elseif( $_NTS['REQ']->getParam('email') && $_NTS['REQ']->getParam('first_name') && $_NTS['REQ']->getParam('last_name') ){
		$uif =& ntsUserIntegratorFactory::getInstance();
		$integrator =& $uif->getIntegrator();

		$myWhere = array();
		$myWhere['email'] = array('=', $_NTS['REQ']->getParam('email'));
		$thisUsers = $integrator->getUsers( $myWhere );

		if( $thisUsers && count($thisUsers) > 0 ){
			$customerId = $thisUsers[0]['id'];
			}
		}
	}

if( ! $customerId && (! $registerClicked) && (! $loginClicked) ){
	// redirect to login & register
	$targetPanel = '-current-/register';
	$forwardTo = ntsLink::makeLink( $targetPanel );
	ntsView::redirect( $forwardTo );
	exit;
	}

$customer = new ntsUser;
$customer->setId( $customerId );

$ready = $NTS_AR->getReady();
$reschedule = $NTS_AR->getReschedule();
$suppliedCoupon = $NTS_AR->getCoupon();

$title = ( count($ready) > 1 ) ? M('Confirm Appointments') : M('Confirm Appointment');
ntsView::setTitle( $title );

$cm =& ntsCommandManager::getInstance();
$ntspm =& ntsPaymentManager::getInstance();

if( $NTS_VIEW['form']->validate(array(), $loginClicked) ){
	$ntspm->resetPromotions();

	$notAvailable = array();
	$formValues = $NTS_VIEW['form']->getValues();

	reset( $ready );
	$readyCount = count( $ready );
	for( $readyIndex = 1; $readyIndex <= $readyCount; $readyIndex++ ){
		$r = $ready[ $readyIndex - 1 ];

		if( $r['location'] == 'a' ){
			$availability = $tm2->getNearestTimes( $r['time'] );
			$availableLocations = array();
			reset( $availability['location'] );
			foreach( $availability['location'] as $lid2 => $ts2 ){
				if( $ts2 == $r['time'] )
					$availableLocations[] = $lid2;
				}
			if( $availableLocations ){
				$r['location'] = ntsLib::pickRandom( $availableLocations );
				$ready[ $readyIndex - 1 ]['location'] = $r['location'];
				}
			else {
				$notAvailable[] = $readyIndex;
				continue;
				}
			}
		$tm2->setLocation( $r['location'] );
			
		if( $r['resource'] == 'a' ){
			$availability = $tm2->getNearestTimes( $r['time'] );
			$availableResources = array();
			reset( $availability['resource'] );
			foreach( $availability['resource'] as $rid2 => $ts2 ){
				if( $ts2 == $r['time'] )
					$availableResources[] = $rid2;
				}
			if( $availableResources ){
				$r['resource'] = ntsLib::pickRandom( $availableResources );
				$ready[ $readyIndex - 1 ]['resource'] = $r['resource'];
				}
			else {
				$notAvailable[] = $readyIndex;
				continue;
				}
			}
		$tm2->setResource( $r['resource'] );

	/* final check */
		$tm2->setService( $r['service'] );
		$tm2->virtualIndex = $readyIndex;

		$nextTimes = $tm2->getNextTimes( $r['time'] );
		if( ! in_array($r['time'], $nextTimes) ){
			// not available
			$notAvailable[] = $readyIndex;
			}
		}

	if( $notAvailable ){
	/* continue to the list with anouncement */
		$forwardTo = ntsLink::makeLink( '-current-' );
		ntsView::redirect( $forwardTo );
		exit;
		}

	reset( $ready );
	foreach( $ready as $r ){
		if( $reschedule ){
			$oldStartsAt = $reschedule->getProp('starts_at');

			$reschedule->setProp( 'resource_id',	$r['resource'] );
			$reschedule->setProp( 'location_id',	$r['location'] );
			$reschedule->setProp( 'starts_at', $r['time'] );

			$cm->runCommand( $reschedule, 'change', array('oldStartsAt' => $oldStartsAt) );
			$appId = $reschedule->getId();
			ntsView::addAnnounce( M('Appointment') . ': ' . M('Change') . ': ' . M('OK'), 'ok' );
			$forwardTo = ntsLink::makeLink( 'customer/appointments/view', '', array('id' => $appId) );
			ntsView::redirect( $forwardTo );
			exit;
			}

		$object = ntsObjectFactory::get( 'appointment' );
		$object->setByArray( $formValues );
		$object->setProp( 'starts_at', $r['time'] );
		$object->setProp( 'customer_id', $customerId );

		$object->setProp( 'service_id',		$r['service'] );
		$object->setProp( 'resource_id',	$r['resource'] );
		$object->setProp( 'location_id',	$r['location'] );

		$service = ntsObjectFactory::get( 'service' );
		$service->setId( $r['service'] );
		$object->setProp( 'duration', 	$service->getProp('duration') );
		$object->setProp( 'lead_in',	$service->getProp('lead_in') );
		$object->setProp( 'lead_out',	$service->getProp('lead_out') );

	// set price
		$price = $ntspm->getPrice( $r, $suppliedCoupon );
		$promotions = $ntspm->getPromotions( $r, $suppliedCoupon );
		$object->setProp( 'price', $price );
		if( $promotions )
		{
			$proms = array();
			reset( $promotions );
			foreach( $promotions as $prom )
			{
				$coupon = $prom->getProp('coupon');
				if( ! $coupon )
					$coupon = 0;
				$proms[ $prom->getId() ] = $coupon;
			}
			$object->setProp( '_promotion', $proms );
		}

		$cm->runCommand( $object, 'init' );
		if( ! $cm->isOk() ){
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );

		/* continue to the list with anouncement */
			$forwardTo = ntsLink::makeLink( '-current-' );
			ntsView::redirect( $forwardTo );
			exit;
			}
		$allApps[ $object->getId() ] = $object;
		}

	$appsById = array();
	$ready = array();
	reset( $allApps );
	$ii = 0;
	foreach( $allApps as $objId => $app ){
		$appsById[ $objId ] = $ii;
		$ready[$ii] = $app->getByArray();
		$ii++;
		}
	$availableOrders = $customer->checkOrders( $ready, $NTS_AR->getOrder() );

	$dueOrders = array();
	$duePayments = array();
	$now = time();

	reset( $allApps );
	foreach( $allApps as $objId => $object ){
		$appIndex = $appsById[ $objId ];
		if( isset($availableOrders[$appIndex]) && $availableOrders[$appIndex] ){
			$dueOrders[ $object->getId() ] = $availableOrders[$appIndex]->getId();
			}
		else {
			$duePayments[] = $object;
			}
		}

/* process orders */
	if( $dueOrders ){
		reset( $dueOrders );
		foreach( $dueOrders as $appId => $orderId ){
			$orderProp = array( $orderId );
			$allApps[$appId]->setProp( '_order', $orderProp );
			$cm->runCommand( $allApps[$appId], 'update' );
			}
		}

/* service permissions will be checked in virtual _request command */
	reset( $allApps );
	foreach( $allApps as $objId => $object ){
		$cm->runCommand( $object, '_request' );
		}

/* process payments */
	if( $duePayments ){
		$pm =& ntsPaymentManager::getInstance();
		$now = time();
		$invoices = $pm->makeInvoices( $duePayments, 0, $now );

	/* build invoice(s) */
		$dueNow = 0;
		reset( $invoices );
		foreach( $invoices as $inv ){
			if( $inv->getProp('due_at') == $now ){
				$dueNow = $inv;
				break;
				}
			}

		$payOnline = $dueNow ? true : false;

	/* check which payments are configured for this */
		$pgm =& ntsPaymentGatewaysManager::getInstance();
		$paymentGateways = $pgm->getActiveGateways();
		if( (count($paymentGateways) == 1) && ($paymentGateways[0] == 'offline') )
			$payOnline = false;

	/* so if it is only offline, then don't forward to payments */		
		if( ! $payOnline ){
			$appIds = array_keys( $allApps );
			$appId = join( '-', $appIds );
			$forwardTo = ntsLink::makeLink( 'customer/appointments/view', '', array('id' => $appId, 'request' => 1) );
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
			$refno = $dueNow->getProp('refno');
			$forwardTo = ntsLink::makeLink( 'customer/invoices/pay', '', array('refno' => $refno) );
			ntsView::redirect( $forwardTo );
			exit;
			}
		}
	else {
		$appIds = array_keys( $allApps );
		$appId = join( '-', $appIds );
		$forwardTo = ntsLink::makeLink( 'customer/appointments/view', '', array('id' => $appId, 'request' => 1) );
		ntsView::redirect( $forwardTo );
		exit;
		}
	}
else {
/* form not valid, continue to create form */
	}
?>