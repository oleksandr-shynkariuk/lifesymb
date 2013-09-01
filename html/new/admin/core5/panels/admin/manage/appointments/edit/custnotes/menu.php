<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
if( ! is_array($object) ){
	$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
	$customerId = $object->getProp( 'customer_id' );

	$customer = new ntsUser();
	$customer->setId( $customerId );
	$notes = $customer->getProp('_note');

	$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
	$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
	$resourceId = $object->getProp('resource_id');
	$iCanEdit = in_array($resourceId, $appEdit) ? true : false;
	
	if( count($notes) || $iCanEdit ){
		$title = M('Customer Notes') . ' [' . count($notes) . ']';
		$sequence = 120;
		}
	}
?>