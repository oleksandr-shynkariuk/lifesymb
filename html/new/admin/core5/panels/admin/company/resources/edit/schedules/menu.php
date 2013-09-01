<?php
$object = ntsLib::getVar( 'admin/company/resources/edit::OBJECT' );
$resourceId = $object->getId();

$schView = ntsLib::getVar( 'admin/manage:schView' );
if( in_array($resourceId, $schView) ){
	$title = M('Schedules');
	$sequence = 35;
	}
?>