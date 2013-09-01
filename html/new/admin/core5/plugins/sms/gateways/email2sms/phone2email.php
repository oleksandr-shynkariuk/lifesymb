<?php
// http://en.wikipedia.org/wiki/List_of_SMS_gateways
// http://www.wikihow.com/Text-Message-Online
$phone2email = array(
	'verizon'	=> '_NUMBER_@vtext.com',
	'att'		=> '_NUMBER_@txt.att.net',
	'sprint'	=> '_NUMBER_@messaging.sprintpcs.com',
	'tmobile'	=> '_NUMBER_@tmomail.net',
	'cricket'	=> '_NUMBER_@sms.mycricket.com',
	'metropcs'	=> '_NUMBER_@mymetropcs.com',
	'us-cellular'	=> '_NUMBER_@email.uscc.net ',
	'rogers'	=> '_NUMBER_@pcs.rogers.com',
	);

if( isset($no_send) )
	return;

if( isset($phone2email[$carrier]) )
{
	$email_to = $phone2email[$carrier];
	$email_to = str_replace( '_NUMBER_', $to, $email_to );
}
else
{
	$error = "Sorry, your $carrier carrier is not supported<br>";
	$this->setError( $error );
}
?>