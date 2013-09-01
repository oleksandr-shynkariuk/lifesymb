<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$totalAmount = $object->getTotalAmount();

if( $totalAmount <= 0 )
{
	$sequence = 210;
	$title = M('Add Invoice');
}
?>