<?php
$alreadyApproved = $object->getProp( 'approved' );
if( $alreadyApproved ){
	$actionResult = 0;
	$actionError = M('Appointment') . ' (id=' . $object->getId() .  '): ' . M('Already Approved');
	$actionStop = true;
	return;
	}
else {
	$object->setProp( 'approved', 1 );
	$this->runCommand( $object, 'update' );
	$actionResult = 1;
	}
?>