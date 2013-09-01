<?php
global $NTS_CURRENT_USER, $_NTS;

if( 
	preg_match('/manage$/', $_NTS['WAS_REQUESTED_PANEL']) OR
	preg_match('/admin$/', $_NTS['WAS_REQUESTED_PANEL']) OR
	(! $_NTS['WAS_REQUESTED_PANEL'])
	)
{
	$defaultAppsView = $NTS_CURRENT_USER->getProp('_default_apps_view');
	$redirectTo = 'admin/manage/' . $defaultAppsView;
	$forwardTo = ntsLink::makeLink( $redirectTo );
	ntsView::redirect( $forwardTo );
	exit;
}
?>