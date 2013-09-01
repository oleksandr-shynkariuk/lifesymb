<?php
$alias = 'admin/manage/appointments/create';

$capture = array( 'location_id', 'resource_id', 'service_id', 'starts_at' );
reset( $capture );
foreach( $capture as $c ){
	$value = $_NTS['REQ']->getParam( $c );
	if( $value )
		$saveOn[$c] = $value;
	}
ntsView::setPersistentParams( $saveOn, 'admin/manage/appointments/edit/schedule' );

$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );

$customerId = $object->getProp( 'customer_id' );
ntsLib::setVar( 'admin/manage/appointments/create::fixCustomer', $customerId );

$ress = array();
$appPermissions = $NTS_CURRENT_USER->getAppointmentPermissions();
reset( $appPermissions );
foreach( $appPermissions as $rid => $pa ){
	if( $pa['edit'] )
		$ress[] = $rid;
	}

$ress = array_unique( $ress );
$rid = $object->getProp( 'resource_id' );
if( $rid )
	$ress = array($rid);

$lid = $object->getProp( 'location_id' );
$locs = $lid ? array($lid) : ntsObjectFactory::getAllIds( 'location' );

$sid = $object->getProp( 'service_id' );
$sers = $sid ? array($sid) : ntsObjectFactory::getAllIds( 'service' );

ntsLib::setVar( 'admin::locs', $locs );
ntsLib::setVar( 'admin::ress', $ress );
ntsLib::setVar( 'admin::sers', $sers );

$noCustomer = 0;
ntsLib::setVar( 'admin/manage/appointments/create::noCustomer', $noCustomer );

$hidden = 0;
ntsLib::setVar( 'admin/manage/appointments/create::hidden', $hidden );

$showFull = 1;
ntsLib::setVar( 'admin/manage/appointments/create::showFull', $showFull );

$reschedule = null;
ntsLib::setVar( 'admin/manage/appointments/create::reschedule', $reschedule );
?>