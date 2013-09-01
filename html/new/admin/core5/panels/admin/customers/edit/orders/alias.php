<?php
$alias = 'admin/company/payments/orders';

$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$customerId = $object->getId();
ntsView::setBack( ntsLink::makeLink('admin/customers/edit/orders', '', array('_id' => $customerId) ) );

$where = array();
ntsLib::setVar( 'admin/company/payments/orders::where', $where );
ntsLib::setVar( 'admin/company/payments/orders::customer', $customerId );
?>