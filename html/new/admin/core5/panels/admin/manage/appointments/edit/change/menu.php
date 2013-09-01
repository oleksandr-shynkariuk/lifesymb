<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
if( ! is_array($object) ){
	$rid = $object->getProp( 'resource_id' );
	$t = $NTS_VIEW['t'];
	$startsAt = $object->getProp('starts_at');
	$t->setTimestamp( $startsAt );
	$cal = $t->formatDate_Db();

	if( ($startsAt > 0) && in_array($rid, $appEdit) ){
		$title = M('Change');
		$sequence = 40;
		$params = array(
			'resource_id'	=> $rid,
			'location_id'	=> $object->getProp( 'location_id' ),
			'service_id'	=> $object->getProp( 'service_id' ),
			'cal'			=> $cal,
			);
		}
	}
?>