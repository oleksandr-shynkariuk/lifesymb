<?php
$object = ntsLib::getVar( 'admin/company/resources/edit::OBJECT' );
$resourceId = $object->getId();

$appView = ntsLib::getVar( 'admin/manage:appView' );
if( in_array($resourceId, $appView) ){
	$tm2 = ntsLib::getVar( 'admin::tm2' );

	$where = array(
		'resource_id'	=> array( '=', $resourceId )
		);
	$totalCount = $tm2->countAppointments( $where );

	$title = M('Appointments') . ' [' . $totalCount . ']';
	$sequence = 30;
	}
?>