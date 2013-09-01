<?php
$alias = 'admin/customers/edit';

$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$customerId = $object->getProp( 'customer_id' );

$customer = new ntsUser();
$customer->setId( $customerId );
ntsLib::setVar( 'admin/customers/edit::OBJECT', $customer );
?>