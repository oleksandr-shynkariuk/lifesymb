<?php
$ff =& ntsFormFactory::getInstance();
$pm =& ntsPaymentManager::getInstance();
$cm =& ntsCommandManager::getInstance();

$invoice = $_NTS['REQ']->getParam('invoice');
$default = $_NTS['REQ']->getParam('default');

$formParams = array('invoice' => $invoice, 'amount' => $default);

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

switch( $action ){
	case 'add':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();
			$amount = $formValues['amount'];
			$notes = $formValues['notes'];
			$pgateway = $formValues['type'];
//			$pgateway = 'offline';

			$paymentInfo = array(
				'pgateway'			=> $pgateway,
				'pgateway_response'	=> $notes
				);
			$transId = $pm->makeTransaction( -1, 0, $amount, $invoice, $paymentInfo );

			$amountFormatted = ntsCurrency::formatPrice( $amount );
			$msg = array( M('Payment'), $amountFormatted, M('Add'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );

			ntsView::getBack();
			exit;
			}
		break;
	default:
		break;
	}
?>