<?php
// generate random code for superadmin access
$sosCode = ntsLib::generateRand( 8 );
$now = time();

$sosSetting = $sosCode . ':' . $now;
$conf =& ntsConf::getInstance();
$conf->set( 'sosCode', $sosSetting );

// send to support
include_once( NTS_BASE_DIR . '/lib/email/ntsEmail.php' );
$email = 'support@hitcode.com';
$mailer = new ntsEmail;
$mailer->setSubject( 'HitCode SOS Code: ' . NTS_ROOT_WEBDIR );

$url = NTS_ROOT_WEBDIR . '/?nts-sos=' . $sosCode;
$body = "<a href=\"$url\">$url</a>";
$mailer->setBody( $body );
$mailer->sendToOne( $email );
?>