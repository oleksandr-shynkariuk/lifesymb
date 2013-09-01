<?php
$conf =& ntsConf::getInstance();

/* init some params */
$now = time();
$createdAt = $object->getProp( 'created_at' );
if( ! $createdAt )
	$object->setProp( 'created_at', $now );

$validFrom = $object->getProp( 'valid_from' );
if( ! $validFrom )
	$object->setProp( 'valid_from', $now );

/* pack */
$packId = $object->getProp( 'pack_id' );
$pack = ntsObjectFactory::get( 'pack' );
$pack->setId( $packId );

/* copy props from pack */
$copyProps = array('qty', 'amount', 'duration', 'rule');
reset( $copyProps );
foreach( $copyProps as $copyProp )
{
	$object->setProp( $copyProp, $pack->getProp($copyProp) );
}

$now = time();
$object->setProp( 'valid_from', $now );

$validTo = 0;
$t = new ntsTime;
$t->setTimestamp( $now );
$expiresIn = $pack->getExpiresIn();
if( $expiresIn ){
	$t->modify( '+' . $expiresIn );
	$validTo = $t->getTimestamp();
	}
$object->setProp( 'valid_to', $validTo );

$locationId = $pack->getProp('location_id');
$object->setProp( 'service_id', $locationId );
$resourceId = $pack->getProp('resource_id');
$object->setProp( 'resource_id', $resourceId );
$serviceId = $pack->getProp('service_id');
$object->setProp( 'service_id', $serviceId );
?>