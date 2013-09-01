<?php
/* this is a not an actual command, it checks the customer and sees what's next */
// service
$service = ntsObjectFactory::get( 'service' ); 
$service->setId( $object->getProp( 'service_id' ) );

$amount = isset($params['amount']) ? $params['amount'] : 0;

$customerId = $object->getProp( 'customer_id' );
$approvalRequired = $service->checkApproval( $customerId, $amount );

if( $approvalRequired ){
	$this->runCommand( $object, 'require_approval' );
	}
else {
	// check if already approved
	$approved = $object->getProp( 'approved' );
	if( ! $approved ){
		$this->runCommand( $object, 'request' );
		}
	}
?>