<?php
$userMobile = trim( $object->getProp('mobile_phone') );
if( ! $userMobile )
	return;

$mobileCarrier = trim( $object->getProp('mobile_carrier') );

include_once( dirname(__FILE__) . '/../../lib/ntsSms.php' );
$body = $params['body'];

/* --- ADD RECIPIENT TAGS --- */
$om =& objectMapper::getInstance();
$fields = $om->getFields( 'user' );
$tags = array( array(), array() );

$allInfo = '';
foreach( $fields as $f ){
	$value = $object->getProp( $f[0] );
	if( isset($f[2]) && $f[2] == 'checkbox' ){
		$value = $value ? M('Yes') : M('No');
		}

	$tags[0][] = '{RECIPIENT.' . strtoupper($f[0]) . '}';
	$tags[1][] = $value;

/* build the -ALL- tag */
	$allInfo .= M($f[1]) . ': ' . $value . "\n";
	}
$tags[0][] = '{RECIPIENT.-ALL-}';
$tags[1][] = $allInfo;

/* --- PARSE RECIPIENT TAGS --- */
$body = str_replace( $tags[0], $tags[1], $body );

/* --- FINALLY SEND SMS --- */
$mailer = new ntsSms;
$mailer->setBody( $body );
$mailer->setParam( 'carrier', $mobileCarrier );

$mailer->sendToOne( $userMobile );
if( $mailer->isError() ){
	$mailerError = $mailer->getError();
	ntsView::setAnnounce( 'SMS sending error, see log for more info', 'error' );
	}
?>