<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$notes = $object->getProp('_note');

$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$resourceId = $object->getProp('resource_id');
$iCanEdit = in_array($resourceId, $appEdit) ? true : false;

if( count($notes) || $iCanEdit ){
	$title = M('Appointment Notes') . ' [' . count($notes) . ']';
	$sequence = 50;
	}
?>