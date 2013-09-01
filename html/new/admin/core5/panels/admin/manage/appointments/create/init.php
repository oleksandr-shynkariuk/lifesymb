<?php
$saveOn = array();

$showFull = $_NTS['REQ']->getParam('full');
ntsLib::setVar( 'admin/manage/appointments/create::showFull', $showFull );

$capture = array( 'location_id', 'resource_id', 'service_id', 'starts_at', 'reschedule', 'customer_id', 'no_customer', 'hidden', 'all', 'from', 'to');
reset( $capture );
foreach( $capture as $c ){
	$value = $_NTS['REQ']->getParam( $c );
	if( $value )
		$saveOn[$c] = $value;
	}
ntsView::setPersistentParams( $saveOn, 'admin/manage/appointments/create' );

$rescheduleId = $_NTS['REQ']->getParam('reschedule');
if( $rescheduleId ){
	$reschedule = ntsObjectFactory::get( 'appointment' );
	$reschedule->setId( $rescheduleId );
	}
else {
	$reschedule = null;
	}
ntsLib::setVar( 'admin/manage/appointments/create::reschedule', $reschedule );

$noCustomer = $_NTS['REQ']->getParam('no_customer');
ntsLib::setVar( 'admin/manage/appointments/create::noCustomer', $noCustomer );

$hidden = $_NTS['REQ']->getParam('hidden');
ntsLib::setVar( 'admin/manage/appointments/create::hidden', $hidden );

ntsLib::setVar( 'admin/manage/appointments/create::fixCustomer', 0 );
ntsLib::setVar( 'admin/manage/appointments/create::changeDate', FALSE );
?>