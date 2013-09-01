<?php
/* receives $msg, $to */
/* returns $success, $response */

//$text = urlencode( $msg );
$text = rawurlencode( $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$password = $plm->getPluginSetting( $plugin, 'password' );
$from = $plm->getPluginSetting( $plugin, 'from' );

$baseUrl = 'http://api.mVaayoo.com/mvaayooapi/MessageCompose';
$url = $baseUrl . "?user=$user:$password&senderID=$from&receipientno=$to&dcs=0&msgtxt=$text&state=4";

$http = new ntsHttpClient;
$response = $http->get( $url );

if( $http->isError() ){
	$success = 0;
	$error = $http->getError();
	$this->setError( $error );
	$response = $error;
	}
else {
	// parse response
	$response = trim( $response );
	$send = explode(":", $response);
	if ($send[0] == "OK")
		$success = 1;
	else {
		$success = 0;
		$this->setError( $response );
		}
	}
?>