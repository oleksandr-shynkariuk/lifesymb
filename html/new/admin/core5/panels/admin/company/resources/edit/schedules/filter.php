<?php
$schView = ntsLib::getVar( 'admin/manage:schView' );
$object = ntsLib::getVar( 'admin/company/resources/edit::OBJECT' );
$resourceId = $object->getId();

if( ! in_array($resourceId, $schView) ){
	$msg = M('Schedules') . ': ' . M('View') . ': ' . M('Permission Denied');
	ntsView::addAnnounce( $msg, 'error' );

	/* continue */
	$forwardTo = ntsLink::makeLink( '-current-/..' );
	ntsView::redirect( $forwardTo );
	exit;
	}
?>