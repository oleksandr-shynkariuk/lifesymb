<?php
$appView = ntsLib::getVar( 'admin/manage:appView' );
$object = ntsLib::getVar( 'admin/company/resources/edit::OBJECT' );
$resourceId = $object->getId();

if( ! in_array($resourceId, $appView) ){
	$msg = M('Appointments') . ': ' . M('View') . ': ' . M('Permission Denied');
	ntsView::addAnnounce( $msg, 'error' );

	/* continue */
	$forwardTo = ntsLink::makeLink( '-current-/..' );
	ntsView::redirect( $forwardTo );
	exit;
	}
?>