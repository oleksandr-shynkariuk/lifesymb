<?php
$skipVersionCheck = false;
$skipPanels = array('admin/conf/upgrade', 'anon/login', 'admin/conf/backup' );

reset( $skipPanels );
foreach( $skipPanels as $sp ){
	if( substr($_NTS['REQUESTED_PANEL'], 0, strlen($sp)) == $sp ){
		$skipVersionCheck = true;
		break;
		}
	}

global $NTS_CURRENT_VERSION;
$conf =& ntsConf::getInstance();
$NTS_CURRENT_VERSION = $conf->get('currentVersion');
if( ! $NTS_CURRENT_VERSION )
	$NTS_CURRENT_VERSION = NTS_APP_VERSION;

list( $v1, $v2, $v3 ) = explode( '.', $NTS_CURRENT_VERSION );
$currentVersion = $v1 . $v2 . sprintf('%02d', $v3 );

if( ! $skipVersionCheck ){
	$fileVersion = NTS_APP_VERSION;
	list( $v1, $v2, $v3 ) = explode( '.', $fileVersion );
	$fileVersion = $v1 . $v2 . sprintf('%02d', $v3 );

	if( $fileVersion > $currentVersion ){
		/* check if there are upgrade files to run */
		$runFiles = array();
		$upgradeDir = NTS_APP_DIR . '/upgrade';
		$upgradeFiles = ntsLib::listFiles( $upgradeDir, '.php' );
		foreach( $upgradeFiles as $uf ){
			$ver = substr( $uf, strlen('upgrade-'), 4 );
			if( $ver > $currentVersion ){
				$runFiles[] = $uf;
				}
			}

		// upgrade scripts run required 
		if( $runFiles ){
			ntsView::setAnnounce( M('New Version Files Uploaded, Upgrade Procedure Required'), 'ok' );

			/* redirect to upgrade screeen */
			$forwardTo = ntsLink::makeLink( 'admin/conf/upgrade' );
			ntsView::redirect( $forwardTo );
			exit;
			}
	// just update the installed version in the database
		else {
			$conf->set('currentVersion', NTS_APP_VERSION );
			}
		}
	}
?>