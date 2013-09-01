<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
if( ! is_array($object) ){
	$rid = $object->getProp( 'resource_id' );
	if( in_array($rid, $appEdit) ){
		$title = M('Delete');
		$sequence = 300;
		$alert = 1;
		}
	}
?>