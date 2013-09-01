<?php
$userId = $object->getId();
if( $userId == NTS_CURRENT_USERID ){
	$actionResult = 0;
	$actionError = "You can't suspend your own account";
	$actionStop = true;
	return;
	}

/* add user restriction */
$object->setProp( '_restriction', 'suspended' );
$this->runCommand( $object, 'update' );
$actionResult = 1;
?>