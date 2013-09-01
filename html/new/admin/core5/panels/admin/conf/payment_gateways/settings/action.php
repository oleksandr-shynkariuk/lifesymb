<?php
$conf =& ntsConf::getInstance();

/* payment manager */
$pgm =& ntsPaymentGatewaysManager::getInstance();
$gateway = $_NTS['REQ']->getParam( 'gateway' );

$new = $_NTS['REQ']->getParam( 'new' );
if( ! $new )
	$new = 0;

$NTS_VIEW['gateway'] = $gateway;
$NTS_VIEW['new'] = $new;

$defaults = $pgm->getGatewaySettings( $gateway );
$defaults['gateway'] = $gateway;
$defaults['new'] = $new;

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile, $defaults );

$confPrefix = 'payment-gateway-' . $gateway . '-';

switch( $action ){
	case 'update':
	case 'activate':
		$ff =& ntsFormFactory::getInstance();
		$formFile = dirname( __FILE__ ) . '/form';
		$form =& $ff->makeForm( $formFile, array('gateway' => $gateway, 'new' => $new) );
		if( $form->validate() ){
			$formValues = $form->getValues();
		/* add theese settings to the database */
			$result = true;
			reset( $formValues );
			foreach( $formValues as $pName => $pValue ){
				$pName = $confPrefix . $pName;
				$conf->set( $pName, $pValue );
				}
			$actionError = $conf->getError() ? true : false;
			}
		else {
		/* form not valid, continue to edit form */
			$actionError = TRUE;
			}
		break;

	default:
		break;
	}

switch( $action ){
	case 'update':
		if( ! $actionError ){
			ntsView::setAnnounce( M('Payment Gateway')  . ': ' . M('Update') . ': ' . M('OK'), 'ok' );

		/* continue to the list with anouncement */
			$forwardTo = ntsLink::makeLink( '-current-', '', array('gateway' => $gateway) );
			ntsView::redirect( $forwardTo );
			exit;
			}
		break;

	case 'activate':
		if( ! $actionError ){
		/* continue to really activate */
			$forwardTo = ntsLink::makeLink( '-current-/..', 'activate', array('gateway' => $gateway) );
			ntsView::redirect( $forwardTo );
			exit;
			}
		break;

	default:
		break;
	}
?>