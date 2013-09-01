<?php
$title = M('Payments');
$sequence = 40;

$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$objId = $object->getId();
?>