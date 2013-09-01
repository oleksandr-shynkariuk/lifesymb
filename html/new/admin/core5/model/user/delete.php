<?php
$userId = $object->getId();
if( $userId == NTS_CURRENT_USERID ){
	$actionResult = 0;
	$actionError = "You can't delete your own account";
	$actionStop = true;
	return;
	}

$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

$integrator->deleteUser( $userId );

$skipMainTable = true;
?>