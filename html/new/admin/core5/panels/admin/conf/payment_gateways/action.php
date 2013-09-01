<?php
$conf =& ntsConf::getInstance();
$gtm =& ntsPaymentGatewaysManager::getInstance();

switch( $action ){
	case 'prepay':
		$cm =& ntsCommandManager::getInstance();
		$prepay = $_NTS['REQ']->getParam( 'prepay' );
		$services = ntsObjectFactory::getAll( 'service' );
		reset( $services );
		foreach( $services as $service )
		{
			$service->setProp( 'prepay', $prepay );
			$cm->runCommand( $service, 'update' );
		}
		$msg = array( count($services) . ' ' . M('Services'), M('Update'), M('OK') );
		$msg = join( ': ', $msg );
		ntsView::addAnnounce( $msg, 'ok' );
		$forwardTo = ntsLink::makeLink( '-current-' );
		ntsView::redirect( $forwardTo );
		exit;
		break;

	case 'activate':
		$newGateway = $_NTS['REQ']->getParam( 'gateway' );
		$setting = $gtm->gatewayActivate( $newGateway );

		$conf->set( 'paymentGateways', $setting );

		if( ! ($error = $conf->getError()) ){
			ntsView::setAnnounce( M('Payment Gateway') . ": <b>$newGateway</b>: " . M('Activate') . ': ' . M('OK'), 'ok' );
		/* continue to this page */
			$forwardTo = ntsLink::makeLink( '-current-' );
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
			echo '<BR>Database error:<BR>' . $error . '<BR>';
			}
		break;

	case 'disable':
		$disableGateway = $_NTS['REQ']->getParam( 'gateway' );
		$setting = $gtm->gatewayDisable( $disableGateway );

		$conf->set( 'paymentGateways', $setting );

		if( ! ($error = $conf->getError()) ){
			ntsView::addAnnounce( M('Payment Gateway') .  ": <b>$disableGateway</b>: " . M('Disable') . ': ' . M('OK'), 'ok' );

		/* check if we have services that require prepayment and no online payment gateway */
			$cm =& ntsCommandManager::getInstance();

			$allowedCurrencies = $gtm->getActiveCurrencies();
			$currentCurrency = $conf->get('currency');
			if( ! in_array($currentCurrency, $allowedCurrencies) ){
				$result2 = $conf->reset( 'currency' );
				/* reset currency as well */
				ntsView::addAnnounce( 'Currency Reset To USD', 'ok' );
				$forwardTo = ntsLink::makeLink( '-current-/../currency' );
				}
			else {
			/* continue to this page */
				$forwardTo = ntsLink::makeLink( '-current-' );
				}
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
			echo '<BR>Database error:<BR>' . $error . '<BR>';
			}
		break;

	default:
		break;
	}
?>