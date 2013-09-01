<?php
global $object;
$cm =& ntsCommandManager::getInstance();
$ff =& ntsFormFactory::getInstance();
$ntsdb =& dbWrapper::getInstance();

$isRequest = $_NTS['REQ']->getParam( 'request' );
$NTS_VIEW['isRequest'] = $isRequest;

if( $isRequest ){
	$idString = $_NTS['REQ']->getParam( 'id' );
	$id = explode( '-', $idString );
	$NTS_VIEW['id'] = $id;

	$object = array();
	reset( $id );
	foreach( $id as $idd ){
		$obj = ntsObjectFactory::get( 'appointment' );
		$obj->setId( $idd );
		$object[] = $obj;
		}
	$NTS_VIEW['object'] = $object;
	}
else {
	$id = $_NTS['REQ']->getParam( 'id' );
	$NTS_VIEW['id'] = $id;

	$object = ntsObjectFactory::get( 'appointment' );
	$object->setId( $id );
	$NTS_VIEW['object'] = $object;

	$service = ntsObjectFactory::get( 'service' );
	$service->setId( $object->getProp('service_id') );
	$NTS_VIEW['service'] = $service;

	$location = new ntsObject( 'location' );
	$location->setId( $object->getProp('location_id') );
	$NTS_VIEW['location'] = $location;

	$resource = ntsObjectFactory::get( 'resource' );
	$resource->setId( $object->getProp('resource_id') );
	$NTS_VIEW['resource'] = $resource;

	$customer = new ntsUser();
	$customer->setId( $object->getProp('customer_id') );
	$NTS_VIEW['customer'] = $customer;
	}
?>