<?php
$NTS_VIEW[NTS_PARAM_VIEW_MODE] = 'print';

$ntsdb =& dbWrapper::getInstance();
$invoiceRefNo = $_NTS['REQ']->getParam( 'refno' );
$invoiceId = 0;
$result = $ntsdb->select( 'id', 'invoices', array('refno' => array('=', $invoiceRefNo)) );
if( $i = $result->fetch() ){
	$invoiceId = $i['id'];
	}

if( ! $invoiceId ){
	echo "invoice '$invoiceRefNo' not found!";
	exit;
	}

$invoice = ntsObjectFactory::get( 'invoice' );
$invoice->setId( $invoiceId );

$invoiceInfo = $invoice->getByArray();
$invoiceInfo['object'] = $invoice;
$invoiceInfo['customer'] = $invoice->getCustomer();

/* transactions */
$entries = array();
$transactionsAmount = 0;

$pm =& ntsPaymentManager::getInstance();
$entries = $pm->getTransactionsOfInvoice( $invoice->getId() );

reset( $entries );
foreach( $entries as $e ){
	$transactionsAmount += $e->getProp('amount');
	}

$t = new ntsTime;
$NTS_VIEW['t'] = $t;
	
ntsLib::setVar( 'system/invoice::OBJECT', $invoice );
ntsLib::setVar( 'system/invoice::entries', $entries );
ntsLib::setVar( 'system/invoice::transactionsAmount', $transactionsAmount );

/* payment manager */
$pgm =& ntsPaymentGatewaysManager::getInstance();
$allGateways = $pgm->getActiveGateways();

$paymentGateways = array();
reset( $allGateways );
foreach( $allGateways as $gtw )
{
	if( ! in_array($gtw, array('offline')) )
	{
		$paymentGateways[] = $gtw;
	}
}
ntsLib::setVar( 'system/invoice::paymentGateways', $paymentGateways );

$invoiceInfo['items'] = $invoice->getItems();
$NTS_VIEW['invoiceInfo'] = $invoiceInfo;
?>