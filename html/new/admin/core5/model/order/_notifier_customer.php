<?php
/* --- RETURN IF EMAIL DISABLED --- */
$conf =& ntsConf::getInstance();
if( $conf->get('emailDisabled') )
	return;

/* --- SEND MESSAGE IF EMAIL DEFINED --- */
$userEmail = trim( $customer->getProp('email') );
if( ! $userEmail )
	return;

$userLang = $customer->getLanguage();
if( ! $userLang )
	$userLang = $defaultLanguage;

/* --- GET TEMPLATE --- */
$key = 'order-' . $mainActionName . '-customer';

/* --- SKIP IF THIS NOTIFICATION DISABLED --- */
$currentlyDisabled = $conf->get( 'disabledNotifications' );
if( in_array($key, $currentlyDisabled) ){
	return;
	}

$templateInfo = $etm->getTemplate( $userLang, $key );

/* --- SKIP IF NO TEMPLATE --- */
if( ! $templateInfo ){
	return;
	}

$tags = array();

$orderTitle = $object->getFullTitle();
$tags[0][] = '{ORDER.TITLE}';
$tags[1][] = $orderTitle;

/* customer fields */
$om =& objectMapper::getInstance();
$fields = $om->getFields( 'customer', 'external' );
$allCustomerInfo = '';
foreach( $fields as $f ){
	$value = $customer->getProp( $f[0] );
	if( $f[2] == 'checkbox' ){
		$value = $value ? M('Yes') : M('No');
		}

	$tags[0][] = '{ORDER.CUSTOMER.' . strtoupper($f[0]) . '}';
	$tags[1][] = $value;

	$allCustomerInfo .= M($f[1]) . ': ' . $value . "\n";
	}
$tags[0][] = '{ORDER.CUSTOMER.-ALL-}';
$tags[1][] = $allCustomerInfo;

/* replace tags */
$subject = str_replace( $tags[0], $tags[1], $templateInfo['subject'] );
$body = str_replace( $tags[0], $tags[1], $templateInfo['body'] );

/* --- SEND EMAIL --- */
$this->runCommand( $customer, 'email', array('body' => $body, 'subject' => $subject) );
?>