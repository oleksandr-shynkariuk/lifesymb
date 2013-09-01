<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$totalAmount = $object->getTotalAmount();
$paidAmount = $object->getPaidAmount(); 

if( $totalAmount ){
	$sequence = 200;
	if( $paidAmount > 0 ){
		if( $paidAmount < $totalAmount ){
			$title = M('Partially Paid');
			$percent = floor( 100 * ($paidAmount / $totalAmount) );
			$title .= ' ' . $percent . '%';
			$alert = 1;
			}
		else {
			$title = M('Fully Paid');
			}
		}
	else {
		$title = M('Not Paid');
		$alert = 1;
		}
	}
?>