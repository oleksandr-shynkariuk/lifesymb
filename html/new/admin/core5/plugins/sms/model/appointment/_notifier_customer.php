<?php
$conf =& ntsConf::getInstance();

$customerId = $object->getProp( 'customer_id' );
$customer = new ntsUser();
$customer->setId( $customerId );

/* --- SEND MESSAGE IF MOBILE PHONE DEFINED --- */
$userMobile = trim( $customer->getProp('mobile_phone') );
if( ! $userMobile )
	return;

$userLang = $customer->getLanguage();
if( ! $userLang )
	$userLang = $defaultLanguage;

/* --- GET TEMPLATE --- */
$key = 'appointment-' . $mainActionName . '-customer';

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

$tags = $om->makeTags_Appointment( $object, 'external' );

if( ! isset($params['reason']) )
	$params['reason'] = '';

$tags[0][] = '{REJECT_REASON}';
$tags[1][] = $params['reason'];
$tags[0][] = '{CANCEL_REASON}';
$tags[1][] = $params['reason'];

$tags[0][] = '{APPOINTMENT.REJECT_REASON}';
$tags[1][] = $params['reason'];
$tags[0][] = '{APPOINTMENT.CANCEL_REASON}';
$tags[1][] = $params['reason'];

if( $mainActionName == 'reschedule' ){
	$oldts = $params['oldStartsAt'];
	$t = new ntsTime( $oldts, $customer->getProp('_timezone') );
	$timeFormatted = $t->formatWeekdayShort() . ', ' . $t->formatDate() . ' ' . $t->formatTime();
	$tags[0][] = '{OLD_APPOINTMENT.STARTS_AT}';
	$tags[1][] = $timeFormatted;
	}

$tags[0][] = '{APPOINTMENT.LINK_TO_ICAL}';
$tags[1][] = '';

/* replace tags */
$body = str_replace( $tags[0], $tags[1], $templateInfo['body'] );
$body = trim( $body );

/* --- SEND SMS --- */
$this->runCommand( $customer, 'sms', array('body' => $body) );
?>