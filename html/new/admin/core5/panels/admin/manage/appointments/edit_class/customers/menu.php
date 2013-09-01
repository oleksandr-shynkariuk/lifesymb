<?php
$objects = ntsLib::getVar( 'admin/manage/appointments/edit_class::objects' );

$count = 0;
/* don't count cancelled */
reset( $objects );
foreach( $objects as $a ){
	$statusCompleted = $a->getProp('completed');
	if( ! in_array($statusCompleted, array(HA_STATUS_CANCELLED, HA_STATUS_NOSHOW) ) )
		$count++;
	}

$title = M('Customers') . ' [' . $count . ']';
$sequence = 20;
?>