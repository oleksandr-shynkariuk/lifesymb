<?php
$cm =& ntsCommandManager::getInstance();
$ntspm =& ntsPaymentManager::getInstance();

$failed = array();
$ok = array();
reset( $apps );
$index = 0;

foreach( $apps as $a ){
	$object = ntsObjectFactory::get( 'appointment' );

	if( isset($a['id']) && $a['id'] ){
		$object->setId( $a['id'] );
		$object->setByArray( $a );
		}
	else
		$object->setByArray( $a );

	$thisSeats = 1;
	$object->setProp( 'seats', $thisSeats );

	$service = ntsObjectFactory::get( 'service' );
	$service->setId( $a['service_id'] );
	$thisPrice = $ntspm->getPrice( $a, '' );

	$object->setProp( 'price',		$thisPrice );
	$object->setProp( 'duration',	$service->getProp('duration') );
	$object->setProp( 'lead_in',	$service->getProp('lead_in') );
	$object->setProp( 'lead_out',	$service->getProp('lead_out') );

	if( isset($a['id']) && $a['id'] ){
		$ok[] = $object;
		}
	else {
		$cm->runCommand( $object, 'init' );
		if( $cm->isOk() ){
			// ok
			$ok[] = $object;
			}
		else {
			// failed
			$failed[] = $index;
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );
			}
		}
	$index++;
	}
$apps = $ok;
?>