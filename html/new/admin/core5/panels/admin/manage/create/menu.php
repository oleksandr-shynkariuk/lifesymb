<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$ress = ntsLib::getVar( 'admin::ress' );

if( $appEdit && array_intersect($ress, $appEdit) ){
	$title = '<i class="icon-plus"></i> ' . M('Create Appointment');
	$sequence = 50;
	}
?>