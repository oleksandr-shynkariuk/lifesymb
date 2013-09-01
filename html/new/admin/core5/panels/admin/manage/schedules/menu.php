<?php
$schView = ntsLib::getVar( 'admin/manage:schView' );
$ress = ntsLib::getVar( 'admin::ress' );

if( $schView && array_intersect($ress, $schView) ){
	$title = '<i class="icon-bar-chart"></i> ' . M('Schedules');
	$sequence = 30;
	}
?>