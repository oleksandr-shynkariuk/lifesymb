<?php
$alias = 'admin/company/payments/invoices';

$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$objId = $object->getId();

$invoices = $object->getInvoices();
$invoiceIds = array();
reset( $invoices );
foreach( $invoices as $ia ){
	list( $invId, $amount, $due ) = $ia;
	$invoiceIds[] = $invId;
	}

$where = array(
	'id'	=> array('IN', $invoiceIds)
	);
ntsLib::setVar( 'admin/company/payments/invoices::where', $where );

$customer = null;
ntsLib::setVar( 'admin/company/payments/invoices::customer', $customer );

ntsView::setBack( ntsLink::makeLink('admin/manage/appointments/edit/invoice', '', array('_id' => $objId)) );
?>