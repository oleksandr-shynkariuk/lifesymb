<?php
/* --- RETURN IF EMAIL DISABLED --- */
$conf =& ntsConf::getInstance();
if( $conf->get('emailDisabled') )
	return;

$userLang = $customer->getLanguage();
if( ! $userLang )
	$userLang = $defaultLanguage;

/* --- GET TEMPLATE --- */
$key = 'order-' . $mainActionName . '-admin';

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

/* --- FIND PROVIDERS --- */
$providers = array();
$resourceId = $object->getProp( 'resource_id' );
if( $resourceId ){
	$resource = ntsObjectFactory::get( 'resource' );
	$resource->setId( $resourceId );

	list( $appsAdmins, $scheduleAdmins ) = $resource->getAdmins();
	reset( $appsAdmins );
	foreach( $appsAdmins as $admId => $access ){
		if( $access['notified'] ){
			$provider = new ntsUser;
			$provider->setId( $admId );
			$providers[] = $provider;
			}
		}
	}
else {
	$allResources = ntsObjectFactory::getAll( 'resource' );
	reset( $allResources );
	$alreadyIds = array();
	foreach( $allResources as $resource ){
		list( $appsAdmins, $scheduleAdmins ) = $resource->getAdmins();
		reset( $appsAdmins );
		foreach( $appsAdmins as $admId => $access ){
			if( $access['notified'] ){
				if( ! isset($alreadyIds[$admId]) ){
					$provider = new ntsUser;
					$provider->setId( $admId );
					$providers[] = $provider;
					$alreadyIds[$admId] = 1;
					}
				}
			}
		}
	}

if( ! $providers )
	return;

/* --- PREPARE MESSAGE --- */
/* build tags */
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
reset( $providers );
foreach( $providers as $provider ){
	$this->runCommand( $provider, 'email', array('body' => $body, 'subject' => $subject) );
	}
?>