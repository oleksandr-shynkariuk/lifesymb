<?php

/* --- RETURN IF EMAIL DISABLED --- */
$conf =& ntsConf::getInstance();
if( $conf->get('emailDisabled') )
	return;

$customerId = $object->getProp( 'customer_id' );
$customer = new ntsUser();
$customer->setId( $customerId );

/* --- SEND MESSAGE IF EMAIL DEFINED --- */
$userEmail = trim( $customer->getProp('email') );
if( ! $userEmail )
	return;

$userLang = $customer->getLanguage();
if( ! $userLang )
	$userLang = $defaultLanguage;

/* --- GET TEMPLATE --- */
$key = 'appointment-' . $mainActionName . '-customer';

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

/* add .ics attachement */
$attachements = array();
$fileAttachements = array();
if( in_array($key, $attachTo) ){
	include_once( NTS_APP_DIR . '/helpers/ical.php' );
	$ntsCal = new ntsIcal();
	$ntsCal->setTimezone( $customer->getTimezone() );
	$ntsCal->addAppointment( $object );
	$str = $ntsCal->printOut();

	$attachName = 'appointment-' . $object->getId() . '.ics';
	$attachements[] = array( $attachName, $str );

	$tags[0][] = '{APPOINTMENT.LINK_TO_ICAL}';
	$tags[1][] = 'cid:' . $attachName;

	// mod: add location dependent attachments
	$attachDir = NTS_EXTENSIONS_DIR . '/location-attach';
	if( file_exists($attachDir) ){
		$loc2attach = array();
		$files = ntsLib::listFiles( $attachDir );
		reset( $files );
		foreach( $files as $f ){
			if( preg_match('/\-(\d+)\./', $f, $ma) ){
				$loc2attach[$ma[1]] = $f;
				}
			}
		$locId = $object->getProp( 'location_id' );
		if( isset($loc2attach[$locId]) ){
			$fileAttachements[] = array( $loc2attach[$locId], $attachDir . '/' . $loc2attach[$locId] );
			}
		}
	}
else {
	$tags[0][] = '{APPOINTMENT.LINK_TO_ICAL}';
	$tags[1][] = '';
	}

/* replace tags */
$subject = str_replace( $tags[0], $tags[1], $templateInfo['subject'] );
$body = str_replace( $tags[0], $tags[1], $templateInfo['body'] );

/* --- SEND EMAIL --- */
$this->runCommand( $customer, 'email', array('body' => $body, 'subject' => $subject, 'attachements' => $attachements, 'fileAttachements' => $fileAttachements) );
?>