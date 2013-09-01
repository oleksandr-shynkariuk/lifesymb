<?php
/* receives $msg, $to */
/* returns $success, $response */

//$text = urlencode( $msg );
$text = rawurlencode( $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$password = $plm->getPluginSetting( $plugin, 'password' );

$baseUrl = 'http://pennytel.com/pennytelapi/services/PennyTelAPI';
$url = $baseUrl . "?Method=sendSMS&ID=$user&Password=$password&Type=0&To=$to&Message=$text";

$http = new ntsHttpClient;
$response = $http->get( $url );

/*
$response =<<<EOT
<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><soapenv:Body><soapenv:Fault><faultcode>soapenv:Server.userException</faultcode><faultstring>java.rmi.RemoteException: Invalid Credentials</faultstring><detail><ns1:hostname xmlns:ns1="http://xml.apache.org/axis/">penny1</ns1:hostname></detail></soapenv:Fault></soapenv:Body></soapenv:Envelope>
EOT;
*/

if( $http->isError() ){
	$success = 0;
	$error = $http->getError();
	$this->setError( $error );
	$response = $error;
	}
else {
	// parse response
	$response = trim( $response );
	
//	$response = preg_replace('|&lt;([/\w]+)(:)|m','&lt;$1',$response);
//	$response = preg_replace('|(\w+)(:)(\w+=\&quot;)|m','$1$3',$response);

	$parser = new xml_simple( 'utf-8' );
	$re = $parser->parse( $response );
	
	if( isset($re['soapenv:Body']['soapenv:Fault']['faultstring']) ){
		$success = 0;
		$errorMsg = $re['soapenv:Body']['soapenv:Fault']['faultstring'];
		$this->setError( $errorMsg );
		}
	else {
		$success = 1;
		}
	}
?>