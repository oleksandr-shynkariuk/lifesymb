<?php
global $NTS_CORE_DIRS;

$what = isset( $_GET['nts-what'] ) ? $_GET['nts-what'] : 'css';
$finalFiles = array();
switch( $what ){
	case 'font':
		$contentType = 'application/octet-stream';
		$file = $_GET['nts-file'];
		$thisFolder = NTS_BASE_DIR . '/defaults/theme/admin/fonts';
		array_unshift( $finalFiles, $thisFolder . '/' . $file );
		break;

	case 'css':
		$contentType = 'text/css';
		$panel = isset( $_GET['nts-side'] ) ? $_GET['nts-side'] : '';
		switch( $panel ){
			case 'admin':
				reset( $NTS_CORE_DIRS );
				foreach( $NTS_CORE_DIRS as $rcd ){
					$thisFolder = $rcd . '/defaults/theme/admin';
					$subFiles = ntsLib::listFiles( $thisFolder, '.css' );
					reset( $subFiles );
					foreach( $subFiles as $sf ){
						array_unshift( $finalFiles, $thisFolder . '/' . $sf );
						}
					}
				break;

			default:
				$theme = isset( $_GET['nts-theme'] ) ? $_GET['nts-theme'] : '';

				reset( $NTS_CORE_DIRS );
				foreach( $NTS_CORE_DIRS as $rcd ){
					$thisFolder = $rcd . '/defaults/theme';
					$subFiles = ntsLib::listFiles( $thisFolder, '.css' );
					reset( $subFiles );
					foreach( $subFiles as $sf ){
						array_unshift( $finalFiles, $thisFolder . '/' . $sf );
						}

					if( $theme ){
						$thisFolder = NTS_EXTENSIONS_DIR . '/themes/' . $theme;
						$subFiles = ntsLib::listFiles( $thisFolder, '.css' );
						reset( $subFiles );
						foreach( $subFiles as $sf ){
							$finalFiles[] = $thisFolder . '/' . $sf;
							}
						}
					}
				break;
			}
		break;

	case 'js':
		$contentType = 'text/javascript';
		$files = $_GET['nts-files'];
		$files = trim( $files );
		$files = explode( '|', $files );

		foreach( $files as $f ){
			$f = trim( $f );
			if( ! $f )
				continue;
			$fullPath = ntsLib::fileInCoreDirs( 'lib/js/' . $f );
			if( $fullPath )
				$finalFiles[] = $fullPath;
			}
		break;
	}

$finalFiles = array_unique( $finalFiles );
reset( $finalFiles );

header("Content-type: $contentType");

foreach( $finalFiles as $f ){
	if( file_exists($f) ){
		readfile( $f );
		}
	}
exit;
?>