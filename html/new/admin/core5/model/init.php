<?php
if( substr(str_replace('.', '', PHP_VERSION), 0, 2) < 52 ){
	echo "This software requires PHP version 5.2 at least, yours is " . PHP_VERSION;
	exit;
	}

ini_set( 'track_errors', 'On' );
define( 'NTS_APP_DIR', realpath(dirname(__FILE__) . '/../')  );
define( 'NTS_BASE_DIR', NTS_APP_DIR );
if( ! defined('NTS_EXTENSIONS_DIR') )
	define( 'NTS_EXTENSIONS_DIR', realpath(dirname(__FILE__) . '/../../extensions')  );
include_once( NTS_APP_DIR . '/lib/ntsLib.php' );

global $NTS_EXECUTION_START;
$NTS_EXECUTION_START = ntsLib::utime();

/* database */
if( ! (defined('NTS_DB_HOST') && defined('NTS_DB_USER') && defined('NTS_DB_PASS') && defined('NTS_DB_NAME')) ){
	if( file_exists(NTS_APP_DIR . '/../db.php') )
		include_once( NTS_APP_DIR . '/../db.php' );
	else {
		echo "<p><b>db.php</b> file doesn't exist! Please rename the sample <b>db.rename_it.php</b> to <b>db.php</b>, then edit your MySQL database information there.";
		exit;
		}
	}

include_once( dirname(__FILE__) . '/../app/constants.php' );	

/* load base code files */
include_once( NTS_BASE_DIR . '/lib/ntsRequest.php' );
include_once( NTS_BASE_DIR . '/lib/ntsView.php' );
include_once( NTS_BASE_DIR . '/lib/ntsObject.php' );
include_once( NTS_BASE_DIR . '/lib/ntsMysqlWrapper.php' );
include_once( NTS_BASE_DIR . '/lib/ntsUser.php' );
include_once( NTS_BASE_DIR . '/lib/ntsCommandManager.php' );
include_once( NTS_BASE_DIR . '/lib/ntsLanguageManager.php' );
include_once( NTS_BASE_DIR . '/lib/ntsPaymentGatewaysManager.php' );
include_once( NTS_BASE_DIR . '/lib/ntsPaymentManager.php' );
include_once( NTS_BASE_DIR . '/lib/ntsPluginManager.php' );
include_once( NTS_BASE_DIR . '/lib/ntsEmailTemplateManager.php' );
include_once( NTS_BASE_DIR . '/lib/ntsUserIntegratorFactory.php' );
include_once( NTS_BASE_DIR . '/lib/ntsAdminPermissionsManager.php' );
include_once( NTS_BASE_DIR . '/lib/form/ntsForm.php' );
include_once( NTS_BASE_DIR . '/lib/form/ntsValidator.php' );
include_once( NTS_BASE_DIR . '/lib/ntsConf.php' );

$versionFile1 = NTS_APP_DIR . '/version.php';
$versionFile2 = NTS_BASE_DIR . '/version.php';
if( file_exists($versionFile1) )
	include_once($versionFile1);
else
	include_once($versionFile2);

/* define param names */
define( 'NTS_PARAM_ACTION', 'nts-action' );
define( 'NTS_PARAM_PANEL', 'nts-panel' );
define( 'NTS_PARAM_RETURN', 'nts-return' );
define( 'NTS_PARAM_VIEW_MODE', 'nts-view-mode' );

$ntsdb =& dbWrapper::getInstance();

global $NTS_CURRENT_VERSION, $NTS_CURRENT_VERSION_NUMBER;

$conf =& ntsConf::getInstance();

/* some essential configs */
/* if registration enabled */
$enableRegistration = $conf->get('enableRegistration');
define( 'NTS_ENABLE_REGISTRATION', $enableRegistration );

$timeUnit = $conf->get('timeUnit');
define( 'NTS_TIME_UNIT', $timeUnit );
$timeStarts = $conf->get('timeStarts');
define( 'NTS_TIME_STARTS', $timeStarts );
$timeEnds = $conf->get('timeEnds');
define( 'NTS_TIME_ENDS', $timeEnds );

/*
1, 'Allow To Set Own Timezone'
0, 'Only View The Timezone'
-1, 'Do Not Show The Timezone'
*/
$enableTimezones = $conf->get('enableTimezones');
define( 'NTS_ENABLE_TIMEZONES', $enableTimezones );

$allowNoEmail = $conf->get('allowNoEmail');
define( 'NTS_ALLOW_NO_EMAIL', $allowNoEmail );

define( 'NTS_TIME_FORMAT',		$conf->get('timeFormat') );
define( 'NTS_DATE_FORMAT', 		$conf->get('dateFormat') );
define( 'NTS_COMPANY_TIMEZONE', $conf->get('companyTimezone') );
date_default_timezone_set( NTS_COMPANY_TIMEZONE );

/* if email as username */
$emailAsUsername = defined('NTS_REMOTE_INTEGRATION') ? 0 : $conf->get('emailAsUsername');
define( 'NTS_EMAIL_AS_USERNAME', $emailAsUsername );

/* if duplicate emails allowed */
$allowDuplicateEmails = defined('NTS_REMOTE_INTEGRATION') ? 0 : $conf->get('allowDuplicateEmails');
define( 'NTS_ALLOW_DUPLICATE_EMAILS', $allowDuplicateEmails );

$NTS_CURRENT_VERSION = $conf->get('currentVersion');
$NTS_CURRENT_VERSION_NUMBER = ntsLib::parseVersion( $NTS_CURRENT_VERSION );

$objectMapperFile1 = NTS_APP_DIR . '/model/objectMapper.php';
$objectMapperFile2 = NTS_BASE_DIR . '/model/objectMapper.php';
if( file_exists($objectMapperFile1) )
	include_once($objectMapperFile1);
else
	include_once($objectMapperFile2);

include_once( NTS_BASE_DIR . '/lib/datetime/ntsTime.php' );
include_once( NTS_APP_DIR . '/helpers/currency.php' );
include_once( NTS_APP_DIR . '/helpers/timeManager2.php' );

if( $NTS_CURRENT_VERSION_NUMBER >= 4500 ){
	/* check how many locations do we have */
	$locations = ntsObjectFactory::getAllIds( 'location' );
	if( count( $locations ) == 1 ){
		define( 'NTS_SINGLE_LOCATION', $locations[0] );
		}
	else {
		define( 'NTS_SINGLE_LOCATION', 0 );
		}

	/* check how many resources do we have */
	$resources = ntsObjectFactory::getAllIds( 'resource' );
	if( count( $resources ) == 1 ){
		define( 'NTS_SINGLE_RESOURCE', $resources[0] );
		}
	else {
		define( 'NTS_SINGLE_RESOURCE', 0 );
		}
	}

/* run mods init scripts */
$plm =& ntsPluginManager::getInstance();
$activePlugins = $plm->getActivePlugins();
reset( $activePlugins );
foreach( $activePlugins as $plg ){
	$plgInitFile = $plm->getPluginFolder( $plg ) . '/init.php';
	if( file_exists($plgInitFile) )
		require( $plgInitFile );
	}

/* init folders */
global $NTS_CORE_DIRS, $NTS_FILE_LOOKUP_CACHE;
$NTS_CORE_DIRS = array();
$NTS_FILE_LOOKUP_CACHE = array();
/* plugins */
reset( $activePlugins );
foreach( $activePlugins as $plg ){
	$NTS_CORE_DIRS[] = $plm->getPluginFolder( $plg );
	}
/* normal */
$NTS_CORE_DIRS[] = NTS_APP_DIR;
/* base dir */
if( NTS_BASE_DIR != NTS_APP_DIR )
	$NTS_CORE_DIRS[] = NTS_BASE_DIR;
?>