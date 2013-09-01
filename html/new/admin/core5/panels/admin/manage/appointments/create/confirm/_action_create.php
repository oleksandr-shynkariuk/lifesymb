<?php
$cm =& ntsCommandManager::getInstance();
reset( $apps );

for( $i = 0; $i < count($apps); $i++ ){
	$customerId = $apps[$i]->getProp('customer_id');
	}

$invoiceFor = array();
for( $i = 0; $i < count($apps); $i++ ){
	$invoiceFor[] = $i;
	}

$customer = new ntsUser;
$customer->setId( $customerId );

$invoiceFor = array();
$orderFor = array();

$ready = array();
for( $i = 0; $i < count($apps); $i++ ){
	$appInfo = array(
		'location_id'	=> $apps[$i]->getProp('location_id'),
		'resource_id'	=> $apps[$i]->getProp('resource_id'),
		'service_id'	=> $apps[$i]->getProp('service_id'),
		'seats'			=> $apps[$i]->getProp('seats')
		);
	$ready[] = $appInfo;
	}
$availableOrders = $customer->checkOrders( $ready );

for( $i = 0; $i < count($apps); $i++ ){
	$paymentOption = $paymentOptions[$i];
	switch( $paymentOption ){
		case 'no':
			// do nothing
			break;
		case 'order':
			for( $i = 0; $i < count($apps); $i++ ){
				if( $availableOrders[$i] ){
					$orderFor[ $i ] = $availableOrders[$i]->getId();
					}
				else {
					$invoiceFor[] = $i;
					}
				}
			break;

		case 'invoice':
			$invoiceFor[] = $i;
			break;
		}
	}

reset( $orderFor );
foreach( $orderFor as $appIndex => $ordId ){
	$apps[$appIndex]->setProp( '_order', $ordId );
	$cm->runCommand( $apps[$appIndex], 'update' );
	}

for( $i = 0; $i < count($apps); $i++ ){
	$cm->runCommand( $apps[$i], 'request' );
	$customerId = $apps[$i]->getProp('customer_id');
	}

$makeInvoices = array();
reset( $invoiceFor );
foreach( $invoiceFor as $i ){
	$makeInvoices[] = $apps[$i];
	}
if( $makeInvoices ){
	$pm =& ntsPaymentManager::getInstance();
	$pm->makeInvoices( $makeInvoices );
	}
?>