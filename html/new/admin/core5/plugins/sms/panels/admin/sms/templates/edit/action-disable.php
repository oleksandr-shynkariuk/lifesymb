<?php
$plm =& ntsPluginManager::getInstance();
$plugin = 'sms';

$key = $_NTS['REQ']->getParam( 'key' );
$currentlyDisabled = $plm->getPluginSetting($plugin, 'disabledNotifications');
if( ! $currentlyDisabled )
	$currentlyDisabled = array();
if( ! is_array($currentlyDisabled) )
	$currentlyDisabled = array( $currentlyDisabled );

$currentlyDisabled[] = $key;

$plm->savePluginSetting( $plugin, 'disabledNotifications', $currentlyDisabled );

ntsView::setAnnounce( M('Notification') . ': ' . M('Disable') . ': ' . M('OK'), 'ok' );

/* continue  */
$forwardTo = ntsLink::makeLink( '-current-/..' );
ntsView::redirect( $forwardTo );
exit;
?>