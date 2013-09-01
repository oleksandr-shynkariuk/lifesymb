<?php
/* receives $msg, $to */
/* returns $success, $response */

$statuses = array(
	'1'	=> 'Message sent',
	'-1'	=> 'Invalid user and/or password or API is not allowed for your account',
	'-2'	=> 'Credit limit reached',
	'-5'	=> 'Local opt out (the recipient/number is on your opt-out list.)',
	'-7'	=> 'Invalid message or subject (exceeds maximum number of characters and/or contains invalid characters - see a list of valid characters below)',
	'-104'	=> 'Globally opted out phone number (the phone number has been opted out from all messages sent from our short code)',
	'-106'	=> 'Incorrectly formatted phone number (number must be 10 digits)',
	'-10'	=> 'Unknown error (please contact our support dept.)',
	);

//$text = urlencode( $msg );
$msg = str_replace( array('[',']'), array('(',')'), $msg );
$text = rawurlencode( $msg );

$user = $plm->getPluginSetting( $plugin, 'username' );
$password = $plm->getPluginSetting( $plugin, 'password' );

$baseUrl = 'https://app.eztexting.com/api/sending/';
$url = $baseUrl . "?user=$user&pass=$password&phonenumber=$to&message=$text&subject=&express=1";

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
	if ($response == 1){
		$success = 1;
		$response = isset($statuses[$response]) ? $statuses[$response] : 'Unrecognized Response';
		}
	else {
		$success = 0;
		$response = isset($statuses[$response]) ? $statuses[$response] : 'Unrecognized Response';
		$this->setError( $response );
		}
	}
?>