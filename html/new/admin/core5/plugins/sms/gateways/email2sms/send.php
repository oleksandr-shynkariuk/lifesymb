<?php
include_once( NTS_BASE_DIR . '/lib/email/ntsEmail.php' );

/* receives $msg, $to */
/* returns $success, $response */

// we should find out the sms to email relation
$carrier = $this->getParam('carrier');

$from = $plm->getPluginSetting( $plugin, 'from' );

require( dirname(__FILE__) . '/phone2email.php' );
if( $email_to )
{
	$mailer = new ntsEmail;
	$mailer->setSubject( $from );
	$mailer->isHtml = FALSE;
	$mailer->setBody( $msg );
	$mailer->sendToOne( $email_to );
	$success = 1;
	$response = "Sent email to '$email_to'";
}
else
{
	$success = 0;
	$response = "Can't convert phone no to email for '$carrier' carrier";
}
?>