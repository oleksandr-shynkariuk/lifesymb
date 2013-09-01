<?php
$ff =& ntsFormFactory::getInstance();
$conf =& ntsConf::getInstance();
$currentLicense = $conf->get('licenseCode');
$installationId = $conf->get( 'installationId' );

$formParams = array(
	'licenseCode' => $currentLicense,
	);
$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

switch( $action ){
	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();
			$enteredLicense = $formValues['licenseCode'];
			$conf->set( 'licenseCode', $enteredLicense );

		/* continue to the list with anouncement */
			$forwardTo = ntsLink::makeLink( '-current-' );
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
		/* form not valid, continue to create form */
			}

		break;

	case 'upgrade':
		$currentVersion = $conf->get('currentVersion');
		if( ! $currentVersion )
			$currentVersion = NTS_APP_VERSION;
		list( $v1, $v2, $v3 ) = explode( '.', $currentVersion );
		$dgtCurrentVersion = $v1 . $v2 . sprintf('%02d', $v3 );

		$fileVersion = NTS_APP_VERSION;
		list( $v1, $v2, $v3 ) = explode( '.', $fileVersion );
		$dgtFileVersion = $v1 . $v2 . sprintf('%02d', $v3 );

		if( $dgtFileVersion > $dgtCurrentVersion ){
		/* get upgrade script files */
			$runFiles = array();
			$upgradeDir = NTS_APP_DIR . '/upgrade';
			$upgradeFiles = ntsLib::listFiles( $upgradeDir, '.php' );
			foreach( $upgradeFiles as $uf ){
				$ver = substr( $uf, strlen('upgrade-'), 4 );
				if( $ver > $dgtCurrentVersion ){
					$runFiles[] = $uf;
					}
				}
		/* run upgrade files */
			foreach( $runFiles as $rf ){
				require( $upgradeDir . '/' . $rf );
				}

			$conf->set('currentVersion', NTS_APP_VERSION );

			$NTS_VIEW['newVersion'] = NTS_APP_VERSION;
			$NTS_VIEW['display'] = dirname(__FILE__) . '/upgraded.php';
			$NTS_VIEW['runFiles'] = $runFiles;
			break;
			}

	default:
		break;
	}
?>