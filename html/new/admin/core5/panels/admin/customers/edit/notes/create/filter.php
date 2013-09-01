<?php
$iCanEdit = ntsLib::getVar( 'admin/customers/edit/notes::iCanEdit' );

if( ! $iCanEdit ){
	$msg = M('Customer') . ': ' . M('Edit') . ': ' . M('Permission Denied');
	ntsView::addAnnounce( $msg, 'error' );

	/* continue */
	ntsView::getBack( true );
	exit;
	}
?>