<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
if( ! is_array($object) ){
	$rid = $object->getProp( 'resource_id' );
	$startsAt = $object->getProp('starts_at');

	if( ($startsAt == 0) && ((! $rid) || in_array($rid, $appEdit) ) ){
		$title = M('Schedule');
		$sequence = 45;
		$params = array();
		if( $object->getProp( 'location_id' ) ){
			$params[ 'location_id' ] = $object->getProp( 'location_id' );
			}
		if( $object->getProp( 'resource_id' ) ){
			$params[ 'resource_id' ] = $object->getProp( 'resource_id' );
			}
		if( $object->getProp( 'service_id' ) ){
			$params[ 'service_id' ] = $object->getProp( 'service_id' );
			}
		}
	}
?>