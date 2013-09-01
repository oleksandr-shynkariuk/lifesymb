<?php
/* receives $msg, $to */
/* returns $success, $response */

//$text = urlencode( $msg );
$text = rawurlencode( $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$password = $plm->getPluginSetting( $plugin, 'password' );
$from = $plm->getPluginSetting( $plugin, 'from' );

$baseUrl = 'http://www.smsglobal.com/http-api.php';
$url = $baseUrl . "?action=sendsms&user=$user&password=$password&from=$from&to=$to&text=$text&maxsplit=5";

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