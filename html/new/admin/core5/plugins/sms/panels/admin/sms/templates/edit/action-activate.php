<?php
$plm =& ntsPluginManager::getInstance();
$plugin = 'sms';

$key = $_NTS['REQ']->getParam( 'key' );
$currentlyDisabled = $plm->getPluginSetting($plugin, 'disabledNotifications');
if( ! $currentlyDisabled )
	$currentlyDisabled = array();
if( ! is_array($currentlyDisabled) )
	$currentlyDisabled = array( $currentlyDisabled );

$newDisabled = array();
reset( $currentlyDisabled );
foreach( $currentlyDisabled as $d ){
	if( $d == $key )
		continue;
	$newDisabled[] = $d;
	}

$plm->savePluginSetting( $plugin, 'disabledNotifications', $newDisabled );

ntsView::setAnnounce( M('Notification') . ': ' . M('Activate') . ': ' . M('OK'), 'ok' );

/* continue  */
$forwardTo = ntsLink::makeLink( '-current-/..' );
ntsView::redirect( $forwardTo );
exit;
?>