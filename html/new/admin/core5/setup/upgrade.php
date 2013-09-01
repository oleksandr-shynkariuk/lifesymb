<?php
set_time_limit( 300 );
ini_set( 'memory_limit', '256M' );

global $table_prefix;
if( isset($table_prefix) && $table_prefix ){
	$oldPrefix = $table_prefix . 'ha45_';
	$wordpress = true;
	}
else {
	$oldPrefix = NTS_DB_TABLES_PREFIX;
	$wordpress = false;
	}

$oldWrapper = new ntsMysqlWrapper( NTS_DB_HOST, NTS_DB_USER, NTS_DB_PASS, NTS_DB_NAME, $oldPrefix );
$oldWrapper->init();
$oldTables = $oldWrapper->getTablesInDatabase();
$currentVersion = 0;
if( in_array('conf', $oldTables) ){
	// conf table exists, search installed version
	$sql = 'SELECT value FROM {PRFX}conf WHERE NAME="currentVersion"';
	$result = $oldWrapper->runQuery($sql);
	if( $i = $result->fetch() ){
		$currentVersion = $currentVersion = $i['value'];
		}
	}
if( ! $currentVersion )
	return;

$out = '';
$tables = $oldWrapper->getTablesInDatabase();
reset( $tables );
foreach( $tables as $t ){
	// skip users table, let the user integrator manage
	if( $t == 'users' )
		continue;
	if( $t == 'emaillog' )
		continue;
	$out .= $oldWrapper->dumpTable( $t, true ) . "\n";
	}
// add users
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

if( ! $wordpress ){
	$integrator->db = $oldWrapper;
	$out .= $integrator->dumpUsers() . "\n";
	}

$out = explode( "\n", $out );

reset( $out );
$newPrefix = NTS_DB_TABLES_PREFIX . 'v5_';
$newWrapper = new ntsMysqlWrapper( NTS_DB_HOST, NTS_DB_USER, NTS_DB_PASS, NTS_DB_NAME, $newPrefix );
$newWrapper->init();

foreach( $out as $line ){
	$line = trim( $line );
	if( $line )
		$line = trim( $line );

	// change prefix
	$re = '/[INTO|EXISTS]\s+' . $oldPrefix . '([^\s]+)\b/U';
	if( preg_match($re, $line, $ma) ){
		$tbl = $ma[1];
		if( $tbl == 'emaillog' ){
			$line = '';
			}
		else {
			$search = $oldPrefix . $tbl;
			$replace = '{PRFX}' . $tbl;
			$line = str_replace( $search, $replace, $line );
			}
		}

	if( $line ){
		$newWrapper->runQuery( $line );
		}
	}

list( $v1, $v2, $v3 ) = explode( '.', $currentVersion );
$dgtCurrentVersion = $v1 . $v2 . sprintf('%02d', $v3 );

$fileVersion = NTS_APP_VERSION;
list( $v1, $v2, $v3 ) = explode( '.', $fileVersion );
$dgtFileVersion = $v1 . $v2 . sprintf('%02d', $v3 );

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

$ntsdb = $newWrapper;
/* run upgrade files */
foreach( $runFiles as $rf ){
	require( $upgradeDir . '/' . $rf );
	}

$sql = 'UPDATE {PRFX}conf SET value="' . NTS_APP_VERSION . '" WHERE name="currentVersion"';
$newWrapper->runQuery( $sql );
?>
