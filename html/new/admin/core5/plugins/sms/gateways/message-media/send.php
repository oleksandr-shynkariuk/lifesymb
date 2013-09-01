<?php
/* receives $msg, $to */
/* returns $success, $response */

include_once( dirname(__FILE__) . '/SmsInterface.inc' );
$si = new SmsInterface( true, false );
$si->addMessage( $to, $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$password = $plm->getPluginSetting( $plugin, 'password' );

if( ! $si->connect($user, $password, true, false) ){
	$success = 0;
	$error = 'Could not contact Message Media server!';
	$this->setError( $error );
	$response = $error;
	}
elseif( ! $si->sendMessages() ){
	$success = 0;
	$error = $si->getResponseMessage();
	$this->setError( $error );
	$response = $error;
	}
else {
	$success = 1;
	}
?>