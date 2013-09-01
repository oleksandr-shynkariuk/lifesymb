<?php
global $NTS_CURRENT_USER;
require_once( dirname(__FILE__) . '/../common/grab.php' );

$timezoneSelected = $_NTS['REQ']->getParam( 'tz' );

if( $NTS_CURRENT_USER->getId() > 0 ){
	$NTS_CURRENT_USER->setProp('_timezone', $timezoneSelected );
	$cm =& ntsCommandManager::getInstance();
	$cm->runCommand( $NTS_CURRENT_USER, 'update' );
	}
else {
	$_SESSION['nts_timezone'] = $timezoneSelected;
	}

/* get back to me */
$forwardTo = ntsLink::makeLink( '-current-' );
ntsView::redirect( $forwardTo );
exit;
?>