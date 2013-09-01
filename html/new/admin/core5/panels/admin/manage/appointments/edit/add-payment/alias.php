<?php
$alias = 'admin/company/payments/transactions/add';

$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$objId = $object->getId();

ntsView::setBack( ntsLink::makeLink('admin/manage/appointments/edit/invoice', '', array('_id' => $objId)) );
?>