<?php
$count = ntsLib::getVar( 'admin/company/payments/transactions::totalCount' );
$limit = ntsLib::getVar( 'admin/company/payments/transactions::limit' );
$invoice = ntsLib::getVar( 'admin/company/payments/transactions::invoice' );
$entries = ntsLib::getVar( 'admin/company/payments/transactions::entries' );
$transactionsAmount = ntsLib::getVar( 'admin/company/payments/transactions::transactionsAmount' );

$fields = array(
	array('id', '#'),
	array('created_at', M('Date')),
	array('amount', M('Amount')),
	array('invoice', M('Invoice')),
	array('paid_through', M('Paid Through')),
	array('notes', M('Notes')),
	);

$headers = array();
reset( $fields );
foreach( $fields as $f )
	$headers[] = $f[1];
echo ntsLib::buildCsv( array_values($headers) );
echo "\n";

$t = $NTS_VIEW['t'];

reset( $entries );
foreach( $entries as $tra ){
	$objId = $tra->getId();
	$output = array();
	$output['id'] = '#' . $objId;

	$t->setTimestamp( $tra->getProp('created_at') );
	$output['created_at'] = $t->formatFull();
	$output['amount'] = ntsCurrency::formatPrice($tra->getProp('amount'));
	
	$thisInvoiceId = $tra->getProp('invoice_id');
	if( $thisInvoiceId ){
		$thisInvoice = ntsObjectFactory::get('invoice');
		$thisInvoice->setId( $thisInvoiceId );
		$output['invoice'] = $thisInvoice->getProp('refno');
		$output['paid_through'] = $tra->getProp('pgateway');

		if( $tra->getProp('pgateway_ref') ){
			$output['notes'] = $tra->getProp('pgateway_ref') . '<br>' . $tra->getProp('pgateway_response');
			}
		else {
			$output['notes'] = $tra->getProp('pgateway_response');
			}
		}
	else {
		$output['invoice'] = M('N/A');
		$output['paid_through'] = '';
		$output['notes'] = '';
		}
	
	$outLines = array();
	reset( $fields );
	foreach( $fields as $f ){
		$outLines[] = $output[ $f[0] ];
		}
	echo ntsLib::buildCsv( $outLines );
	echo "\n";
	}
?>