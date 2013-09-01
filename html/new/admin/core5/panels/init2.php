<?php
$currentVersion = $conf->get('currentVersion');
if( ! $currentVersion ){
	$setupFile = NTS_BASE_DIR . '/setup/setup.php';
	if( ! file_exists($setupFile) )
		$setupFile = NTS_APP_DIR . '/setup/setup.php';
	require( $setupFile );
	exit;
	}

$installationId = $conf->get( 'installationId' );
if( ! $installationId ){
	$installationId = md5(rand());
	$conf->set( 'installationId', $installationId );
	}

if( ! NTS_APP_WHITELABEL )
	$_NTS['DOWNLOAD_URL'] = 'http://www.hitappoint.com/upgrade/';
else
	$_NTS['DOWNLOAD_URL'] = '';

$_NTS['CHECK_LICENSE_URL'] = 'http://www.hitcode.com/customers/lic.php';
//$_NTS['CHECK_LICENSE_URL'] = 'http://localhost/sld2/lic.php';

$_NTS['REQUESTED_PANEL'] = ( isset($_REQUEST[NTS_PARAM_PANEL]) ) ? $_REQUEST[NTS_PARAM_PANEL] : '';
$_NTS['WAS_REQUESTED_PANEL'] = $_NTS['REQUESTED_PANEL'];

if( isset($_REQUEST['nts-theme']) ){
	$theme = $_REQUEST['nts-theme'];
	global $NTS_PERSISTENT_PARAMS;
	$NTS_PERSISTENT_PARAMS[ '/' ][ 'nts-theme' ] = $theme;
	}

/* IF PULL JAVASCRIPT OR CSS */
if( $_NTS['REQUESTED_PANEL'] == 'system/pull' ){
	if( ob_get_length() ){
		ob_end_clean();
		}
	require( dirname(__FILE__) . '/pull.php' );
	exit;
	}

$thisPage = ntsLib::pureUrl( ntsLib::currentPageUrl() );
if( ! defined('NTS_ROOT_WEBPAGE') )
	define( 'NTS_ROOT_WEBPAGE',	$thisPage );

$thisWebDir = ntsLib::webDirName(NTS_ROOT_WEBPAGE);
define( 'NTS_ROOT_WEBDIR',	$thisWebDir );

if( ! defined('NTS_FRONTEND_WEBPAGE') ){
	define( 'NTS_FRONTEND_WEBPAGE',	NTS_ROOT_WEBPAGE );
	}

define( 'NTS_PAGE_PARAM',	'ntsp' );

/* session start */
if( ! defined('NTS_SESSION_NAME') ){
	define( 'NTS_SESSION_NAME', 'ntssess_' . $installationId );
	}

if( ! isset($_SESSION) ){
	session_name( NTS_SESSION_NAME );
	session_start();
	}

/* run other file */
if( isset($_GET['nts-run']) ){
	$rootDir = realpath(NTS_APP_DIR . '/../');
	$file = $rootDir . '/' . $_GET['nts-run'] . '.php';
	if( file_exists($file) )
		require( $file );
	exit;
	}

/* reminder code */
if( isset($_GET['nts-reminder']) || isset($_GET['nts-cron']) ){
	require( dirname(__FILE__) . '/cron.php' );
	exit;
	}

/* sos code */
if( isset($_GET['nts-send-sos']) ){
	require( dirname(__FILE__) . '/send-sos.php' );
	exit;
	}
if( isset($_GET['nts-sos']) ){
	$ntsSos = $_GET['nts-sos'];
	$sosSetting =  $conf->get( 'sosCode' );
	list( $sosCode, $sosCreated ) = explode( ':', $sosSetting );

	$now = time();
	if( $ntsSos == $sosCode  ){
		if( $now <= ($sosCreated + 24 * 60 * 60) ){
			ntsView::setAnnounce( 'SOS code ok', 'ok' );
			$_SESSION['nts_sos_user_id'] = -111;
			}
		else {
			ntsView::setAnnounce( 'SOS code expired', 'error' );
			if( isset($_SESSION['nts_sos_user_id']) )
				unset($_SESSION['nts_sos_user_id']);
			}
		}
	else {
		ntsView::setAnnounce( 'SOS code incorrect', 'error' );
		if( isset($_SESSION['nts_sos_user_id']) )
			unset($_SESSION['nts_sos_user_id']);
		}
	}
/* request */
$_NTS['REQ'] = new ntsRequest;

/* sanitize */
$_NTS['REQ']->addSanitizer( 'service', '/^[\d-]*$/' );
$_NTS['REQ']->addSanitizer( 'resource', '/^[\d-a]*$/' );
$_NTS['REQ']->addSanitizer( 'time', '/^[\d-]*$/' );
$_NTS['REQ']->addSanitizer( 'key', '/^[a-zA-Z\d_-]*$/' );

/* now check current user id and type */
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

if( ! defined('NTS_CURRENT_USERID') ){
	if( isset($_SESSION['nts_sos_user_id']) ){
		ini_set( 'display_errors', 'On' );
		error_reporting( E_ALL );
		$currentUserId = $_SESSION['nts_sos_user_id'];
		}
	else {
		$currentUserId = $integrator->currentUserId();
		}
	define( 'NTS_CURRENT_USERID', $currentUserId );
	}
global $NTS_CURRENT_USER;
if( ! ( isset($NTS_CURRENT_USER) && $NTS_CURRENT_USER ) ){
	$NTS_CURRENT_USER = new ntsUser();
	$NTS_CURRENT_USER->setId( NTS_CURRENT_USERID );
	}

/* language manager */
$lm =& ntsLanguageManager::getInstance(); 
$lm->setLanguage( $NTS_CURRENT_USER->getLanguage() );
$languageConf = $lm->getLanguageConf( $NTS_CURRENT_USER->getLanguage() );
if( isset($languageConf['charset']) ){
	if( ! headers_sent() )
		header( 'Content-Type: text/html; charset=' . $languageConf['charset'] );
	}

/* default panel */
if( ! $_NTS['REQUESTED_PANEL'] ){
	if( $NTS_CURRENT_USER->hasRole('admin') ){
		$_NTS['REQUESTED_PANEL'] = 'admin';
		}
	else{
		$_NTS['REQUESTED_PANEL'] = 'customer';
		}
	}

/* check current version */
require( dirname(__FILE__) . '/version-check.php' );

/* check current license */
require( dirname(__FILE__) . '/license-check.php' );
	
/****************************/
/* ACTIONS					*/
/****************************/
$saveRequestedPanel = $_NTS['REQUESTED_PANEL'];
$requestedAction = $_NTS['REQ']->getRequestedAction();
$_NTS['REQUESTED_ACTION'] = $requestedAction;

$apm =& ntsAdminPermissionsManager::getInstance();
$allPanels = $apm->getPanels();
while( $_NTS['REQUESTED_PANEL'] ){
	/* GET CURRENT PANEL */
	$currentPanel = '';
	$requestedAction = $_NTS['REQUESTED_ACTION'];

	$checkPanels = array();
	/* shortcut? */
	while( $shortcutFile = ntsLib::fileInCoreDirs('panels/' . $_NTS['REQUESTED_PANEL'] . '/shortcut.php') ){
		require( $shortcutFile );
		$_NTS['REQUESTED_PANEL'] .= '/' . $shortcut;
		}

	/* folder exists? */
	if( ntsLib::fileInCoreDirs('panels/' . $_NTS['REQUESTED_PANEL']) ){
		$checkPanels[] = $_NTS['REQUESTED_PANEL'];
		}
	else {
		$parent = ntsLib::getParentPath( $_NTS['REQUESTED_PANEL'] );
		while( ! ntsLib::fileInCoreDirs('panels/' . $parent) ){
			$parent = ntsLib::getParentPath( $parent );
			}
		if( $parent ){
			$checkPanels[] = $parent;
			}
		}

	/* PRE-ACTION FILES */
	$preActionFiles = array();
	reset( $checkPanels );
	foreach( $checkPanels as $checkPanel ){
		$rootInfo = ntsLib::findClosestFile( $checkPanel, 'root.php' );
		if( $rootInfo && ( ! in_array($rootInfo[0], $preActionFiles)) ){
			$preActionFiles[] = $rootInfo[0];
			}
		$initInfo = ntsLib::findAllFiles( $checkPanel, 'init.php' );
		foreach( $initInfo as $fi ){
			if( ! in_array($fi[0], $preActionFiles) ){
				$preActionFiles[] = $fi[0];
				}
			}
		$filterInfo = ntsLib::findAllFiles( $checkPanel, 'filter.php' );
		foreach( $filterInfo as $fi ){
			if( ! in_array($fi[0], $preActionFiles) ){
				$preActionFiles[] = $fi[0];
				}
			}
		}

	/* before action nullify the requested panel */
	$_NTS['CURRENT_PANEL'] = $_NTS['REQUESTED_PANEL'];

	$_NTS['CURRENT_ACTION'] = $requestedAction;
	$action = $_NTS['CURRENT_ACTION'];

	reset( $preActionFiles );
	foreach( $preActionFiles as $preActionFile ){
		if( file_exists($preActionFile) ){
			require_once( $preActionFile );
			}
		}

/* redefine checkPanels */		
	$checkPanels = array();
	/* shortcut? */
	while( $shortcutFile = ntsLib::fileInCoreDirs('panels/' . $_NTS['REQUESTED_PANEL'] . '/shortcut.php') ){
		require( $shortcutFile );
		$_NTS['REQUESTED_PANEL'] .= '/' . $shortcut;
		}

	/* folder exists? */
	if( ntsLib::fileInCoreDirs('panels/' . $_NTS['REQUESTED_PANEL']) ){
		$checkPanels[] = $_NTS['REQUESTED_PANEL'];
		}
	else {
		$parent = ntsLib::getParentPath( $_NTS['REQUESTED_PANEL'] );
		while( ! ntsLib::fileInCoreDirs('panels/' . $parent) ){
			$parent = ntsLib::getParentPath( $parent );
			}
		if( $parent ){
			$checkPanels[] = $parent;
			}
		}

	$preActionFiles2 = array();
	/* alias? */
	if( $aliasInfo = ntsLib::findClosestFile($_NTS['REQUESTED_PANEL'], 'alias.php')){
		list( $aliasFile, $aliasPath ) = $aliasInfo;
		$NTS_VIEW['aliasFile'] = $aliasFile;
		require( $aliasFile );
		$checkPanel = $alias . substr($_NTS['REQUESTED_PANEL'], strlen($aliasPath));

		/* check if it is also alias */
		if( $aliasInfo2 = ntsLib::findClosestFile($checkPanel, 'alias.php')){
			list( $aliasFile2, $aliasPath2 ) = $aliasInfo2;
			require( $aliasFile2 );
			$checkPanel = $alias . substr($checkPanel, strlen($aliasPath2));
			}

		$checkPanels[] = $checkPanel;

		/* pre-action for alias */
		$rootInfo = ntsLib::findClosestFile( $checkPanel, 'root.php' );
		if( $rootInfo && ( ! in_array($rootInfo[0], $preActionFiles)) ){
			$preActionFiles2[] = $rootInfo[0];
			}
		$filterInfo = ntsLib::findAllFiles( $checkPanel, 'filter.php' );
		foreach( $filterInfo as $fi ){
			if( ! in_array($fi[0], $preActionFiles) ){
				$preActionFiles2[] = $fi[0];
				}
			}

		reset( $preActionFiles2 );
		foreach( $preActionFiles2 as $preActionFile ){
			if( file_exists($preActionFile) ){
				require_once( $preActionFile );
				}
			}
		}

	/* first try without expand */
	$expandNeeded = true;
	reset( $checkPanels );
	foreach( $checkPanels as $checkPanel ){
		/* action or index files exist */
		$checkFiles = array( 'action.php', 'index.php' );
		if( $requestedAction ){
			$checkFiles[] = 'action-' . $requestedAction . '.php';
			}

		$stayHere = false;
		reset( $checkFiles );
		foreach( $checkFiles as $cf ){
			if( ntsLib::fileInCoreDirs('panels/' . $checkPanel . '/' . $cf) ){
				$expandNeeded = false;
				break;
				}
			}
		if( ! $expandNeeded ){
			$currentPanel = $_NTS['REQUESTED_PANEL'];
			break;
			}
		}

	/* expand needed */
	if( $expandNeeded ){
		reset( $checkPanels );
		foreach( $checkPanels as $checkPanel ){
			/* get subfolders for possible expand */
			$mySubFolders = ntsLib::subfoldersInCoreDirs('panels/' . $checkPanel);
			if( $mySubFolders ){
				/* find if any have menu */
				$expandTo = '';
				$lowestSequence = 1000;

				reset( $mySubFolders );
				foreach( $mySubFolders as $sf ){
					$menuFile = ntsLib::fileInCoreDirs('panels/' . $checkPanel . '/' . $sf . '/menu.php');
					if( $menuFile ){
						$checkExpandTo = $_NTS['REQUESTED_PANEL'] . '/' . $sf;
						$checkFiles2 = array( 'action.php', 'index.php', 'alias.php' );
						if( $requestedAction ){
							$checkFiles2[] = 'action-' . $requestedAction . '.php';
							}
						$stayHere = false;
						reset( $checkFiles2 );
						foreach( $checkFiles2 as $cf2 ){
							$check = 'panels/' . $checkPanel . '/' . $sf . '/' . $cf2;
							if( ntsLib::fileInCoreDirs($check) ){
								$stayHere = true;
								break;
								}
							}

						if( $stayHere ){
							// if it is alias
							list( $title, $sequence, $params, $directLink, $alert, $permissionsFor ) = ntsLib::requireMenuFile( $menuFile );
							$permissionsFor = $permissionsFor ? $permissionsFor : $checkExpandTo;
							if( $sequence < $lowestSequence ){
								if( ! $NTS_CURRENT_USER->isPanelDisabled($permissionsFor) ){
									$lowestSequence = $sequence;
									$expandTo = $sf;
									}
								}
							}
						else {
							list( $title, $sequence, $params, $directLink, $alert, $permissionsFor ) = ntsLib::requireMenuFile( $menuFile );
							if( ! ($sequence < $lowestSequence) ){
								continue;
								}
							$saveSequence = $sequence;

							// try to expand more
							$mySubFolders2 = ntsLib::subfoldersInCoreDirs('panels/' . $checkPanel . '/' . $sf);
							$expandTo = '';
							$lowestSequence2 = 1000;

							reset( $mySubFolders2 );
							foreach( $mySubFolders2 as $sf2 ){
								$menuFile = ntsLib::fileInCoreDirs('panels/' . $checkPanel . '/' . $sf . '/' . $sf2 . '/menu.php');
								if( $menuFile ){
									$checkFiles22 = array( 'action.php', 'index.php', 'alias.php' );
									if( $requestedAction ){
										$checkFiles22[] = 'action-' . $requestedAction . '.php';
										}
									$stayHere = false;
									reset( $checkFiles22 );
									foreach( $checkFiles22 as $cf22 ){
										$check = 'panels/' . $checkPanel . '/' . $sf . '/' . $sf2 . '/' . $cf22;
										if( ntsLib::fileInCoreDirs($check) ){
											$stayHere = true;
											break;
											}
										}

									if( $stayHere ){
										list( $title, $sequence, $params, $directLink, $alert, $permissionsFor ) = ntsLib::requireMenuFile( $menuFile );
										$permissionsFor = $permissionsFor ? $permissionsFor : $checkExpandTo;
										if( $sequence < $lowestSequence2 ){
											if( ! $NTS_CURRENT_USER->isPanelDisabled($permissionsFor) ){
												$lowestSequence2 = $sequence;
												$expandTo = $sf2;
												}
											}
										}
									}
								}
							$sequence = $saveSequence;

							if( $expandTo ){
								$lowestSequence = $sequence;
								$currentPanel = $_NTS['REQUESTED_PANEL'] . '/' . $sf . '/' . $expandTo;
//								break;
								}
							}
						}
					}
				if( ! $currentPanel && $expandTo ){
					$currentPanel = $_NTS['REQUESTED_PANEL'] . '/' . $expandTo;
					}
				}

			if( $currentPanel ){
				break;
				}
			}
		}

	if( ! $currentPanel ){
		$forwardTo = NTS_ROOT_WEBPAGE;
		ntsView::redirect( $forwardTo );
		exit;
		}
	if( $NTS_CURRENT_USER->isPanelDisabled($currentPanel) ){
		ntsView::setAnnounce( M('Permission Denied'), 'error' );
		$forwardTo = NTS_ROOT_WEBPAGE;
		ntsView::redirect( $forwardTo );
		exit;
		}

	/* FIND PRE-ACTION FILES AFTER EXPAND */
	$preActionFiles3 = array();
	$preActionFiles4 = array();
	if( $expandNeeded ){
		$checkPanels = array();
		/* folder exists? */
		if( ntsLib::fileInCoreDirs('panels/' . $currentPanel) ){
			$checkPanels[] = $currentPanel;
			}

		reset( $checkPanels );
		foreach( $checkPanels as $checkPanel ){
			$rootInfo = ntsLib::findClosestFile( $checkPanel, 'root.php' );
			if( $rootInfo ){
				if( ! in_array($rootInfo[0], $preActionFiles) )
					$preActionFiles3[] = $rootInfo[0];
				}
			$filterInfo = ntsLib::findAllFiles( $checkPanel, 'init.php' );
			foreach( $filterInfo as $fi ){
				if( ! in_array($fi[0], $preActionFiles) ){
					$preActionFiles3[] = $fi[0];
					}
				}
			$filterInfo = ntsLib::findAllFiles( $checkPanel, 'filter.php' );
			foreach( $filterInfo as $fi ){
				if( ! in_array($fi[0], $preActionFiles) ){
					$preActionFiles3[] = $fi[0];
					}
				}
			}
			
	/* before action nullify the requested panel */
		$_NTS['REQUESTED_PANEL'] = '';
		$_NTS['CURRENT_PANEL'] = $currentPanel;
		$_NTS['CURRENT_ACTION'] = $requestedAction;
		$action = $_NTS['CURRENT_ACTION'];

		reset( $preActionFiles3 );
		foreach( $preActionFiles3 as $preActionFile ){
			if( file_exists($preActionFile) ){
				require_once( $preActionFile );
				}
			}
			
		/* alias? */
		if( $aliasInfo = ntsLib::findClosestFile($currentPanel, 'alias.php')){
			list( $aliasFile, $aliasPath ) = $aliasInfo;
			require( $aliasFile );
			$checkPanel = $alias . substr($currentPanel, strlen($aliasPath));

			if( $aliasInfo7 = ntsLib::findClosestFile($checkPanel, 'alias.php')){
				$currentPanel = $alias . substr($currentPanel, strlen($aliasPath));
				list( $aliasFile7, $aliasPath7 ) = $aliasInfo7;
				require( $aliasFile7 );
				$checkPanel = $alias . substr($checkPanel, strlen($aliasPath7));
				}

			$checkPanels[] = $checkPanel;

			/* pre-action for alias */
			$rootInfo = ntsLib::findClosestFile( $checkPanel, 'root.php' );
			if( $rootInfo && ( ! in_array($rootInfo[0], $preActionFiles3)) ){
				$preActionFiles4[] = $rootInfo[0];
				}
			$filterInfo = ntsLib::findAllFiles( $checkPanel, 'filter.php' );
			foreach( $filterInfo as $fi ){
				if( ! in_array($fi[0], $preActionFiles3) ){
					$preActionFiles4[] = $fi[0];
					}
				}
			}
		}

	/* before action nullify the requested panel */
	$_NTS['REQUESTED_PANEL'] = '';
	$_NTS['CURRENT_PANEL'] = $currentPanel;

	$_NTS['CURRENT_ACTION'] = $requestedAction;
	$action = $_NTS['CURRENT_ACTION'];

	reset( $preActionFiles4 );
	foreach( $preActionFiles4 as $preActionFile ){
		if( file_exists($preActionFile) ){
			require_once( $preActionFile );
			}
		}

	/* FIND ACTION FILES */
	$actionFiles = array();
	reset( $checkPanels );
	$checkFiles = array();
	foreach( $checkPanels as $checkPanel ){
		if( $requestedAction )
			$checkFiles[] = $checkPanel . '/action-' . $requestedAction . '.php';
		$checkFiles[] = $checkPanel . '/action.php';
		}
	reset( $checkFiles );
	foreach( $checkFiles as $cf ){
		if( $actionFile = ntsLib::fileInCoreDirs('panels/' . $cf) ){
			$actionFiles[] = $actionFile;
			break;
			}
		}

	/* HANDLE ACTION */
	reset( $actionFiles );
	foreach( $actionFiles as $actionFile ){
		if( file_exists($actionFile) ){
			require( $actionFile );
			break;
			}
		}
	}

/****************************/
/* END OF ACTIONS			*/
/****************************/
/*
_print_r( $preActionFiles );
_print_r( $preActionFiles2 );
_print_r( $preActionFiles3 );
_print_r( $preActionFiles4 );
*/

/* FIND DISPLAY FILES */
$displayFiles = array();
reset( $checkPanels );
$checkFiles = array();
$customDisplay = $_NTS['REQ']->getParam('display');

foreach( $checkPanels as $checkPanel ){
	if( $customDisplay )
		$checkFiles[] = $checkPanel . '/' . $customDisplay . '.php';
	if( $requestedAction )
		$checkFiles[] = $checkPanel . '/index-' . $requestedAction . '.php';
	if( ! isset($NTS_VIEW['no-index']) )
		$checkFiles[] = $checkPanel . '/index.php';
	}

reset( $checkFiles );
foreach( $checkFiles as $cf ){
	if( $displayFile = ntsLib::fileInCoreDirs('panels/' . $cf) ){
		$displayFiles[] = $displayFile;
		break;
		}
	}
if( isset($NTS_VIEW['form']) ){
	$displayFiles[] = dirname(__FILE__) . '/index-form.php';
	}

$NTS_VIEW['displayFile'] = '';
reset( $displayFiles );
foreach( $displayFiles as $displayFile ){
	if( file_exists($displayFile) ){
		$NTS_VIEW['displayFile'] = $displayFile;
		break;
		}
	}

/* IF PULL ICAL */
if( $_NTS['CURRENT_PANEL'] == 'system/appointments/export' ){
	if( ob_get_length() ){
		ob_end_clean();
		}
	require( dirname(__FILE__) . '/../views/export.php' );
	exit;
	}

/* if no display file exists then it is an error, redirect to home page */
if( ! $NTS_VIEW['displayFile'] ){
	/* continue to home page */
	$forwardTo = NTS_ROOT_WEBPAGE;
	ntsView::redirect( $forwardTo );
	exit;
	}

/* BUILD MENUS */
/* FIRST LEVEL MENU */
$NTS_VIEW['menu1'] = array();
if( $rootInfo ){
	$rootPath = $rootInfo[1];
	$mySubFolders = ntsLib::subfoldersInCoreDirs('panels/' . $rootPath);

	reset( $mySubFolders );
	foreach( $mySubFolders as $sf ){
		$link2panel = $rootPath . '/' . $sf;
		$menuFile = ntsLib::fileInCoreDirs('panels/' . $link2panel . '/menu.php');
		if( $menuFile ){
			list( $title, $sequence, $params, $directLink, $alert, $permissionsFor ) = ntsLib::requireMenuFile( $menuFile );
			$permissionsFor = $permissionsFor ? $permissionsFor : $link2panel;

			if( ! $NTS_CURRENT_USER->isPanelDisabled($permissionsFor) ){
				if( in_array($link2panel, array('admin/manage')) )
					$showThisMenu = true;
				else
					$showThisMenu = false;
				if( ! $showThisMenu ){
					reset( $allPanels );
					foreach( $allPanels as $checkPanel ){
						if( substr($checkPanel, 0, strlen($link2panel)) == $link2panel ){
							if( ! $NTS_CURRENT_USER->isPanelDisabled($checkPanel) ){
								$showThisMenu = true;
								break;
								}
							}
						}
					}

				if( $showThisMenu ){
					$menuArray = array(
						'panel'		=> $link2panel,
						'title'		=> $title,
						'seq'		=> $sequence,
						'params'	=> $params,
						);
					if( $directLink ){
						$menuArray['directLink'] = $directLink;
						}
					$NTS_VIEW['menu1'][] = $menuArray;
					}
				}
			}
		}
	usort( $NTS_VIEW['menu1'], create_function('$a, $b', 'return ntsLib::numberCompare($a["seq"], $b["seq"]);') );
	}

/* SECOND LEVEL MENU */
$NTS_VIEW['menu2'] = array();
$NTS_VIEW['menu3'] = array();
if( $NTS_VIEW['menu1'] ){
	/* if subheader exists then parent of current, otherwise second level after root */
	$subheaderInfo = ntsLib::findClosestFile( $currentPanel, 'subheader.php', true );
	$secondLevelPath2 = '';
	if( $subheaderInfo )
	{
		$secondLevelPath2 = $subheaderInfo[1];
		if( ! array_key_exists('subHeaderFile', $NTS_VIEW) )
		{
			if( 
				( array_key_exists('skipSubHeaderFile', $NTS_VIEW) ) &&
				( isset($NTS_VIEW['skipSubHeaderFile'][ ntsLib::normalizePath($subheaderInfo[0]) ]) )
				)
			{
				// skip
			}
			else
			{
				$NTS_VIEW['subHeaderFile'] = $subheaderInfo[0];
			}
		}
	}

	$remain = substr( $currentPanel, strlen($rootPath) + 1 );
	$slashPos = strpos( $remain, '/' );
	if( $slashPos && ($slashPos > 0) )
		$secondLevel = substr( $remain, 0, $slashPos );
	else
		$secondLevel = $remain;
	$secondLevelPath = $rootPath . '/' . $secondLevel;
	if( $secondLevelPath == $secondLevelPath2 ){
		$secondLevelPath2 = '';
		}

	$mySubFolders = ntsLib::subfoldersInCoreDirs('panels/' . $secondLevelPath);
	reset( $mySubFolders );
	foreach( $mySubFolders as $sf ){
		$link2panel = $secondLevelPath . '/' . $sf;
		$menuFile = ntsLib::fileInCoreDirs('panels/' . $link2panel . '/menu.php');
		if( $menuFile ){
			list( $title, $sequence, $params, $directLink, $alert, $permissionsFor ) = ntsLib::requireMenuFile( $menuFile );
			$permissionsFor = $permissionsFor ? $permissionsFor : $link2panel;
			if( ! $NTS_CURRENT_USER->isPanelDisabled($permissionsFor) ){
				if( $title ){
					if( substr($link2panel, 0, strlen('admin/manage')) == 'admin/manage' )
						$showThisMenu = true;
					else
						$showThisMenu = false;

					if( ! $showThisMenu ){
						reset( $allPanels );
						foreach( $allPanels as $checkPanel ){
							if( substr($checkPanel, 0, strlen($permissionsFor)) == $permissionsFor ){
								if( ! $NTS_CURRENT_USER->isPanelDisabled($checkPanel) ){
									$showThisMenu = true;
									break;
									}
								}
							}
						}

					if( $showThisMenu ){
						$menuArray = array(
							'panel'		=> $link2panel,
							'title'		=> $title,
							'seq'		=> $sequence,
							'params'	=> $params,
							'alert'		=> $alert,
							);
						if( $directLink ){
							$menuArray['directLink'] = $directLink;
							}
						$NTS_VIEW['menu2'][] = $menuArray;
						}
					}
				}
			}
		}
	usort( $NTS_VIEW['menu2'], create_function('$a, $b', 'return ntsLib::numberCompare($a["seq"], $b["seq"]);') );

	if( $secondLevelPath2 ){
		if( substr_count($currentPanel, '/') > (substr_count($secondLevelPath2, '/') + 1) )
			$secondLevelPath2 = '';
		}

	if( $secondLevelPath2 ){
		$mySubFolders = ntsLib::subfoldersInCoreDirs('panels/' . $secondLevelPath2);
		reset( $mySubFolders );
		foreach( $mySubFolders as $sf ){
			$link2panel = $secondLevelPath2 . '/' . $sf;
			$menuFile = ntsLib::fileInCoreDirs('panels/' . $link2panel . '/menu.php');
			if( $menuFile ){
				list( $title, $sequence, $params, $directLink, $alert, $permissionsFor ) = ntsLib::requireMenuFile( $menuFile );
				$permissionsFor = $permissionsFor ? $permissionsFor : $link2panel;
				if( ! $NTS_CURRENT_USER->isPanelDisabled($permissionsFor) ){
					if( $title ){
						$menuArray = array(
							'panel'		=> $link2panel,
							'title'		=> $title,
							'seq'		=> $sequence,
							'params'	=> $params,
							'alert'		=> $alert,
							);
						if( $directLink ){
							$menuArray['directLink'] = $directLink;
							}
						$NTS_VIEW['menu3'][] = $menuArray;
						}
					}
				}
			}
		usort( $NTS_VIEW['menu3'], create_function('$a, $b', 'return ntsLib::numberCompare($a["seq"], $b["seq"]);') );
		}
	}

$headerFile = '';
$footerFile = '';
if( $rootInfo ){
	$headerFile = ntsLib::fileInCoreDirs( '/panels/' . $rootPath . '/header.php' );
	$footerFile = ntsLib::fileInCoreDirs( '/panels/' . $rootPath . '/footer.php' );
	}
if( $headerFile && $footerFile ){
	$NTS_VIEW['headerFile'] = $headerFile;
	$NTS_VIEW['footerFile'] = $footerFile;
	}
else {
/* for customer view */
	$defaultThemeFolder = NTS_APP_DIR . '/defaults/theme';
	$conf =& ntsConf::getInstance();
	$theme = $conf->get( 'theme' );
	$themeFolder = NTS_EXTENSIONS_DIR . '/themes/' . $theme;

	if( file_exists($themeFolder) ){
		$NTS_VIEW['headFile'] = $themeFolder . '/head.php';
		$NTS_VIEW['headerFile'] = $themeFolder . '/header.php';
		$NTS_VIEW['footerFile'] = $themeFolder . '/footer.php';
		}
	// default theme
	else {
		$NTS_VIEW['headerFile'] = $defaultThemeFolder . '/header.php';
		$NTS_VIEW['footerFile'] = $defaultThemeFolder . '/footer.php';
		}
	}

/* pull housekeeping */
require( dirname(__FILE__) . '/cron.php' );
?>