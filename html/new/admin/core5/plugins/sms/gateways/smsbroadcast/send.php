<?php
/* receives $msg, $to */
/* returns $success, $response */

//$text = urlencode( $msg );
$text = rawurlencode( $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$password = $plm->getPluginSetting( $plugin, 'password' );
$from = $plm->getPluginSetting( $plugin, 'from' );

$baseUrl = 'https://www.smsbroadcast.com.au/api-adv.php';
$url = $baseUrl . "?username=$user&password=$password&to=$to&from=$from&message=$text";

$http = new ntsHttpClient;
$response = $http->get( $url );

/*
$response =<<<EOT
<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><soapenv:Body><soapenv:Fault><faultcode>soapenv:Server.userException</faultcode><faultstring>java.rmi.RemoteException: Invalid Credentials</faultstring><detail><ns1:hostname xmlns:ns1="http://xml.apache.org/axis/">penny1</ns1:hostname></detail></soapenv:Fault></soapenv:Body></soapenv:Envelope>
EOT;
*/

if( $http->isError() )
{
	$success = 0;
	$error = $http->getError();
	$this->setError( $error );
	$response = $error;
}
else
{
	// parse response
	$smsbroadcast_response = trim( $response );

	$response_lines = explode("\n", $smsbroadcast_response);
    
	foreach( $response_lines as $data_line)
	{
		$message_data = "";
		$message_data = explode(':',$data_line);
		if($message_data[0] == "OK")
		{
			$success = 1;
		}
		elseif( $message_data[0] == "BAD" )
		{
			$errorMsg = $message_data[2];
			$this->setError( $errorMsg );
		}
		elseif( $message_data[0] == "ERROR" )
		{
			$errorMsg = $message_data[1];
			$this->setError( $errorMsg );
		}
	}
}
?>