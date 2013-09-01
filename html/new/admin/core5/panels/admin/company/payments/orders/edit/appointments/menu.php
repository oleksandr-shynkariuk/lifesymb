<?php
$order = ntsLib::getVar( 'admin/company/payments/orders/edit::OBJECT' );
$deps = $order->getItems();

$count = 0;
reset( $deps );
foreach( $deps as $dep ){
	$className = $dep->getClassName();
	if( $className == 'appointment' ){
		$count++;
		}
	}

if( $count ){
	$title = ( $count > 1 ) ? M('Appointments') . ' [' . $count . ']' : M('Appointment');
	$sequence = 20;
	}
?>