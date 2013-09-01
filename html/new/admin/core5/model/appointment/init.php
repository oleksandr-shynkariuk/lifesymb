<?php
$now = time();
$object->setProp( 'created_at', $now );
$object->setProp( 'approved', 0 );
$object->setProp( 'completed', 0 );

/* reminder */
$object->setProp( 'need_reminder', 1 );

/* auth code */
$authCode = ntsLib::generateRand( 8 );
$object->setProp( 'auth_code', $authCode );

$this->runCommand( $object, 'create' );
$actionResult = 1;
?>
