<?php
$id = $_NTS['REQ']->getParam( '_id' );
ntsView::setPersistentParams( array('_id' => $id), 'admin/manage/appointments/edit_class' );

$tm2 = ntsLib::getVar('admin::tm2');
list( $lid, $rid, $sid, $startsAt ) = explode( '-', $id );

$tm2->setLocation( $lid );
$tm2->setResource( $rid );
$tm2->setService( $sid );

$where = array(
	'location_id'	=> array( '=', $lid ),
	'resource_id'	=> array( '=', $rid ),
	'service_id'	=> array( '=', $sid ),
	'starts_at'		=> array( '=', $startsAt ),
	'completed'		=> array( '>=', 0 ),
	);
$orderBy = 'ORDER BY created_at ASC';
$apps = $tm2->getAppointments( $where, $orderBy, array(), array('id', 'customer_id') );
if( ! $apps ){
	ntsView::getBack( true );
	exit;
	}

ntsView::setBack( ntsLink::makeLink('admin/manage/appointments/edit_class/customers'), '', array('_id' => $id) );

$objects = array();
foreach( $apps as $app ){
	$obj = ntsObjectFactory::get( 'appointment' );
	$obj->setId( $app['id'] );
	$objects[] = $obj;
	}
ntsLib::setVar( 'admin/manage/appointments/edit_class::objects', $objects );
?>