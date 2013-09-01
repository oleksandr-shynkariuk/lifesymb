<?php
$conf =& ntsConf::getInstance();

$userLang = $object->getLanguage();
if( ! $userLang )
	$userLang = $defaultLanguage;

/* --- GET TEMPLATE --- */
$key = 'user-' . $mainActionName . '-user';

/* --- SKIP IF THIS NOTIFICATION DISABLED --- */
$currentlyDisabled = $plm->getPluginSetting($plugin, 'disabledNotifications');
if( ! $currentlyDisabled )
	$currentlyDisabled = array();
if( ! is_array($currentlyDisabled) )
	$currentlyDisabled = array( $currentlyDisabled );

if( in_array($key, $currentlyDisabled) ){
	return;
	}

$templateInfo = $stm->getTemplate( $userLang, $key );

/* --- SKIP IF NO TEMPLATE --- */
if( ! $templateInfo ){
	return;
	}

/* --- PREPARE MESSAGE --- */
/* build tags */
$tags = $om->makeTags_Customer( $object, 'external' );

$confirmKey = $object->getProp( '_confirmKey' );
$confirmLink = ntsLink::makeLink( 'anon/register/confirm_email', '', array('key' => $confirmKey) );
$confirmLink = '<a href="' . $confirmLink . '">' . M('Click here to confirm your email') . '</a>';

$tags[0][] = '{USER.CONFIRMATION_LINK}';
$tags[1][] = $confirmLink;

/* replace tags */
$body = str_replace( $tags[0], $tags[1], $templateInfo['body'] );
$body = trim( $body );

/* --- SEND SMS --- */
$this->runCommand( $object, 'sms', array('body' => $body) );
?>