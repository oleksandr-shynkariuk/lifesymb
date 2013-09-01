<?php
$ntsdb =& dbWrapper::getInstance();
$t = $NTS_VIEW['t'];

$invoiceRefNo = $_NTS['REQ']->getParam( 'refno' );
if( ! $invoiceRefNo ){
	echo "invoiceRefNo required!";
	exit;
	}

/* invoice info */
$where = array(
	'refno'	=> array('=', $invoiceRefNo),
	);

$result = $ntsdb->select( 'id', 'invoices', $where );
if( $result && ($i = $result->fetch()) ){
	$invoice = ntsObjectFactory::get( 'invoice' );
	$invoice->setId( $i['id'] );
	}
else {
	echo "invoice '$invoiceRefNo' not found!";
	exit;
	}
$invoiceId = $invoice->getId();
$invoiceInfo = $invoice->getByArray();

$invoiceInfo['object'] = $invoice;

$totalAmount = $invoice->getProp( 'amount' );
$paidAmount = $invoice->getPaidAmount();

/* check if the invoice is already fully paid */
if( $paidAmount >= $totalAmount ){
	// redirect to 
	$paymentOkUrl = ntsLink::makeLink( 'customer/invoices/view', '', array('refno' => $invoiceRefNo, 'display' => 'ok') );
	ntsView::redirect( $paymentOkUrl );
	exit;
	}

/* payment manager */
$pgm =& ntsPaymentGatewaysManager::getInstance();
$allGateways = $pgm->getActiveGateways();

/* find dependants and item name */
$invoiceInfo['items'] = $invoice->getItems();

$invoiceInfo['customer'] = $invoice->getCustomer();

$NTS_VIEW['paymentGateways'] = $allGateways;
$NTS_VIEW['invoiceInfo'] = $invoiceInfo;
?>