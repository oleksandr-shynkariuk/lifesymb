<?php
/* receives $msg, $to */
/* returns $success, $response */

$text = urlencode( $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$apiId = $plm->getPluginSetting( $plugin, 'apiid' );
$password = $plm->getPluginSetting( $plugin, 'password' );
$from = $plm->getPluginSetting( $plugin, 'from' );

$baseUrl = 'http://api.clickatell.com';
$url = $baseUrl . "/http/sendmsg?user=$user&password=$password&api_id=$apiId&to=$to&text=$text";
if( $from )
	$url .= "&from=$from&mo=1";

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
	if ($send[0] == "ID")
		$success = 1;
	else {
		$success = 0;
		$this->setError( $response );
		}
	}
?>