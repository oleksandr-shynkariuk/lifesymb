<?php
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

$userId = $object->getId();
$userPassword = $object->getProp('password');

$integrator->login( $userId, $userPassword );
unset( $_SESSION['temp_customer_id'] );
?>