<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$ress = ntsLib::getVar( 'admin::ress' );

if( ! ( $appEdit && array_intersect($ress, $appEdit) ) ){
	$msg = M('Appointment') . ': ' . M('Create') . ': ' . M('Permission Denied');
	ntsView::addAnnounce( $msg, 'error' );
	require( NTS_BASE_DIR . '/views/error.php' );
	exit;
	}
?>