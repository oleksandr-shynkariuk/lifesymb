<?php
$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$fParams = array();

$t = $NTS_VIEW['t'];
$t->setDateDb( $cal );
$dayStart = $t->getStartDay();
$dayEnd = $t->getEndDay();

$fixCustomer = ntsLib::getVar( 'admin/company/payments/orders/create::fixCustomer' );
if( $fixCustomer ){
	$fParams['customer_id'] = $fixCustomer;
	}
else {
	$cid = $_NTS['REQ']->getParam( 'customer_id' );
	$fParams['customer_id'] = $cid ? $cid : 0;
	}

$fixPack = ntsLib::getVar( 'admin/company/payments/orders/create::fixPack' );
if( $fixPack ){
	$fParams['pack_id'] = $fixPack;
	}
else {
	$pid = $_NTS['REQ']->getParam( 'pack_id' );
	$fParams['pack_id'] = $pid ? $pid : 0;
	}

if( $fParams['pack_id'] ){
	$pack = ntsObjectFactory::get( 'pack' );
	$pack->setId( $fParams['pack_id'] );
	$packPrice = $pack->getTotalPrice();

	$fParams['amount'] = $packPrice ? $packPrice : '';
	$fParams['add-payment'] = $packPrice ? 1 : 0;
	}

$cm =& ntsCommandManager::getInstance();
$pm =& ntsPaymentManager::getInstance();
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $fParams );

switch( $action ){
	case 'create':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

			$customerId = $formValues['customer_id'];
			$packId = $formValues['pack_id'];
			$pack = ntsObjectFactory::get( 'pack' );
			$pack->setId( $packId );

			$order = ntsObjectFactory::get( 'order' );
			$order->setProp( 'customer_id', $customerId );
			$order->setProp( 'pack_id', $packId );

		/* add payment */
			$amount = 0;
			if( isset($formValues['addPayment']) && $formValues['addPayment'] ){
				$amount = isset($formValues['amount']) ? $formValues['amount'] : 0;
				}
			$commandParams = array();
			if( $amount ){
				$commandParams['forceInvoice'] = $amount;
				}
			$cm->runCommand( $order, 'create', $commandParams );
			$orderId = $order->getId();

		/* add payment */
			$invoices = $order->getInvoices();
			if( $amount && $invoices ){
				reset( $invoices );
				$amountLeft = $amount;
				foreach( $invoices as $ia ){
					list( $invoiceId, $amountNeeded, $due ) = $ia;
					if( $amountNeeded ){
						$thisAmount = ($amountLeft > $amountNeeded) ? $amountNeeded : $amountLeft;
						}
					else
						$thisAmount = $amountLeft;
					$notes = $formValues['notes'];
					$paymentInfo = array(
						'pgateway'			=> 'offline',
						'pgateway_response'	=> $notes
						);
					$transId = $pm->makeTransaction( -1, 0, $thisAmount, $invoiceId, $paymentInfo );
					$amountLeft = $amountLeft - $thisAmount;
					}
			// something left
				if( $amountLeft ){
					$transId = $pm->makeTransaction( -1, 0, $amountLeft, $invoiceId, $paymentInfo );
					}

				$amountFormatted = ntsCurrency::formatPrice( $amount );
				$msg = array( M('Payment'), $amountFormatted, M('Add'), M('OK') );
				$msg = join( ': ', $msg );
				ntsView::addAnnounce( $msg, 'ok' );
				}
			else {
				$cm->runCommand( $order, 'request' );
				}

		/* package announcement */
			$packTitle = $pack->getFullTitle();
			$msg = array( $packTitle, M('Sell'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );

		/* forward to create appointment for this customer */
//			$forwardTo = ntsLink::makeLink( 'admin/customers/edit/create_appointment', '', array('_id' => $customerId, 'service_id' => $pack->getProp('service_id'))
			ntsView::getBack( true, true );
			exit;
			}
		else {
			/* form not valid, get back */
			}
		break;
	}
?>