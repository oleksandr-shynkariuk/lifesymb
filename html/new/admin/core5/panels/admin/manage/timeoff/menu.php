<?php
$schView = ntsLib::getVar( 'admin/manage:schView' );
$ress = ntsLib::getVar( 'admin::ress' );

if( $schView && array_intersect($ress, $schView) ){
	//$title = '<i class="icon-coffee"></i> ' . M('Timeoff');
	$sequence = 40;
	}
?>