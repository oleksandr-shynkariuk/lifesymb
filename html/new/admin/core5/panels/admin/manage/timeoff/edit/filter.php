<?php
$object = ntsLib::getVar( 'admin/manage/timeoff/edit::OBJECT' );
$schEdit = ntsLib::getVar( 'admin/manage:schEdit' );
$resId = $object->getProp( 'resource_id' );

$iCanEdit = in_array($resId, $schEdit );
if( ! $iCanEdit ){
	$msg = M('Timeoff') . ': ' . M('Edit') . ': ' . M('Permission Denied');
	ntsView::addAnnounce( $msg, 'error' );
	require( NTS_BASE_DIR . '/views/error.php' );
	exit;
	}
?>