<?php
$alias = 'admin/company/payments/invoices';

$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$customerId = $object->getId();
ntsView::setBack( ntsLink::makeLink('admin/customers/edit/payments', '', array('_id' => $customerId) ) );

$where = array();
ntsLib::setVar( 'admin/company/payments/invoices::where', $where );
ntsLib::setVar( 'admin/company/payments/invoices::customer', $customerId );
?>