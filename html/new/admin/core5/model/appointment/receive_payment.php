<?php
$amount = isset($params['amount']) ? $params['amount'] : 0;
if( $amount < 0 ){
	$commandParams = array(
		'reason' => 'Refund',
		);
//	$this->runCommand( $object, 'cancel', $commandParams );
	}
else {
	$service = ntsObjectFactory::get( 'service' ); 
	$service->setId( $object->getProp( 'service_id' ) );
	$customerId = $object->getProp( 'customer_id' );
	$approvalRequired = $service->checkApproval( $customerId, $amount );

	// check if already approved
	$approved = $object->getProp( 'approved' );

	if( ($amount > 0) && (! $approved) && (! $approvalRequired) ){
		$this->runCommand( $object, 'request' );
		}
	}
?>