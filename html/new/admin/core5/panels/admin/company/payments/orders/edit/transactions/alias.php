<?php
$alias = 'admin/company/payments/orders/transactions';

$object = ntsLib::getVar( 'admin/company/payments/orders/edit::OBJECT' );
$objId = $object->getId();

ntsLib::setVar( 'admin/company/payments/orders/transactions::order', $object );

ntsView::setBack( ntsLink::makeLink('admin/company/payments/orders/edit/transactions', '', array('_id' => $objId)), true );
?>