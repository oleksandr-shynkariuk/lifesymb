<?php
$amount = isset($params['amount']) ? $params['amount'] : 0;
if( $amount < 0 ){
	$commandParams = array(
		'reason' => 'Refund',
		);
//	$this->runCommand( $object, 'cancel', $commandParams );
	}
else {
	$this->runCommand( $object, 'request', array('amount' => $amount) );
	}
?>