<?php
$skipCheck = false;
$skipPanels = array('customer', 'admin/conf/upgrade', 'user', 'anon', 'admin/conf/backup' );

reset( $skipPanels );
foreach( $skipPanels as $sp ){
	if( substr($_NTS['REQUESTED_PANEL'], 0, strlen($sp)) == $sp ){
		$skipCheck = true;
		break;
		}
	if( $_SERVER['SERVER_NAME'] == 'localhost' ){
		$skipCheck = true;
		}
	}

$currentLicense = $conf->get('licenseCode');
if( (! $skipCheck) && (! $currentLicense) && (NTS_APP_LEVEL != 'lite') ){
	ntsView::setAnnounce( M('Please Enter Your License Code'), 'ok' );

	/* redirect to license screeen */
	$forwardTo = ntsLink::makeLink( 'admin/conf/upgrade' );
	ntsView::redirect( $forwardTo );
	exit;
	}
?>