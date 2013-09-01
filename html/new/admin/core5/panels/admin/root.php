<?php
ntsView::setTitle( M('Admin Area') );

/* this file is here to indicate that the menu hierarchy starts here */
/* check if a force url is required */
if( defined('NTS_FORCE_ADMIN_URL') ){
	$currentUrl = ntsLib::currentPageUrl();
	if( substr($currentUrl, 0, strlen(NTS_FORCE_ADMIN_URL)) != NTS_FORCE_ADMIN_URL ){
		// force redirect
		$paramsPart = ntsLib::urlParamsPart( $currentUrl );
		$forwardTo = NTS_FORCE_ADMIN_URL . $paramsPart;
		ntsView::redirect( $forwardTo );
		exit;
		}
	}

/* check permissions if admin */
if( ! ($NTS_CURRENT_USER->hasRole('admin')) ){
	$requestParams = $_NTS['REQ']->getGetParams();
	$returnPage = array(
		NTS_PARAM_PANEL		=> $_NTS['CURRENT_PANEL'],
		NTS_PARAM_ACTION	=> $requestParams,
		'params'	=> $requestParams,
		);
	$_SESSION['return_after_login'] = $returnPage;

	/* redirect to login page */
	$forwardTo = ntsLink::makeLink( 'anon/login', '', array('user' => 'admin') );
	ntsView::redirect( $forwardTo );
	exit;
	}

if( ! isset($_NTS['CURRENT_PANEL']) )
	$_NTS['CURRENT_PANEL'] = 'admin';

/* check if should run backup */
$conf =& ntsConf::getInstance();
$remindOfBackup = $conf->get('remindOfBackup');
$backupLastRun = $conf->get('backupLastRun'); 
$now = time();

if( $remindOfBackup ){
	if( (! $backupLastRun) || ( ($now - $backupLastRun) > $remindOfBackup ) ){
		if( $_NTS['CURRENT_PANEL'] != 'admin/conf/backup' ){
			$announceText = M("It seems that you have not made a backup for some time, it's highly recommended to do it now");
			$announceText .= '<br><a href="' . ntsLink::makeLink('admin/conf/backup') . '">' . M('Download Backup') . '</a>';
			ntsView::setAdminAnnounce( $announceText, 'alert' );
			}
		}
	}

if( $remindOfBackup ){
	if( (! $backupLastRun) || ( ($now - $backupLastRun) > $remindOfBackup ) ){
		if( $_NTS['CURRENT_PANEL'] != 'admin/conf/backup' ){
			$announceText = M("It seems that you have not made a backup for some time, it's highly recommended to do it now");
			$announceText .= '<br><a href="' . ntsLink::makeLink('admin/conf/backup') . '">' . M('Download Backup') . '</a>';
			ntsView::setAdminAnnounce( $announceText, 'alert' );
			}
		}
	}

/* CUSTOMER FIELDS */
$om2 =& objectMapper::getInstance();
$fields = $om2->getFields( 'customer', 'external' );
$customerFields = array();
$skip = array( 'first_name', 'last_name' );
foreach( $fields as $f ){
	if( ! in_array($f[0], $skip) )
		$customerFields[] = $f;
	}
$NTS_VIEW['CUSTOMER_FIELDS'] = $customerFields;

$t = new ntsTime();
$NTS_VIEW['t'] = $t;

/* CURRENT USER PERMISSIONS */
$NTS_VIEW['APP_EDIT'] = array();
$NTS_VIEW['APP_VIEW'] = array();
$NTS_VIEW['SCH_EDIT'] = array();
$NTS_VIEW['SCH_VIEW'] = array();
$NTS_VIEW['ALL_RESS'] = array();

$appPermissions = $NTS_CURRENT_USER->getAppointmentPermissions();
reset( $appPermissions );
foreach( $appPermissions as $rid => $pa ){
	if( $pa['view'] )
		$NTS_VIEW['APP_VIEW'][] = $rid;
	if( $pa['edit'] )
		$NTS_VIEW['APP_EDIT'][] = $rid;
	$NTS_VIEW['ALL_RESS'][] = $rid;
	}
$schPermissions = $NTS_CURRENT_USER->getSchedulePermissions();
reset( $schPermissions );
foreach( $schPermissions as $rid => $pa ){
	if( $pa['view'] )
		$NTS_VIEW['SCH_VIEW'][] = $rid;
	if( $pa['edit'] )
		$NTS_VIEW['SCH_EDIT'][] = $rid;
	$NTS_VIEW['ALL_RESS'][] = $rid;
	}

/* ALL LRS */
$loadRes = array_unique( array_merge($NTS_VIEW['APP_VIEW'], $NTS_VIEW['SCH_VIEW']) );
$locs = ntsObjectFactory::getAllIds( 'location' );
$sers = ntsObjectFactory::getAllIds( 'service' );
$sortRess = ntsObjectFactory::getAllIds( 'resource' );

$filterRes = 'ALL_RESS';
if( $_NTS['CURRENT_PANEL'] == 'admin/schedules/create' ){
	$filterRes = 'SCH_EDIT';
	}
elseif( $_NTS['CURRENT_PANEL'] == 'admin/appointments/create' ){
	$filterRes = 'APP_EDIT';
	}
elseif( preg_match('/^admin\/schedules/', $_NTS['CURRENT_PANEL']) ){
	$filterRes = 'SCH_VIEW';
	}
elseif( $_NTS['CURRENT_PANEL'] == 'admin/manage/agenda' ){
	$filterRes = 'APP_VIEW';
	}
elseif( preg_match('/^admin\/appointments/', $_NTS['CURRENT_PANEL']) ){
	$filterRes = 'APP_VIEW';
	$filterRes2 = 'SCH_VIEW';
	}
$NTS_VIEW['ALL_RESS'] = ( isset($filterRes2) && $filterRes2 ) ? array_merge($NTS_VIEW[$filterRes], $NTS_VIEW[$filterRes2]) : $NTS_VIEW[$filterRes];
$NTS_VIEW['ALL_RESS'] = array_unique( $NTS_VIEW['ALL_RESS'] );

/* INIT TIMEMANAGER */
ntsObjectFactory::preload( 'location', $locs );
ntsObjectFactory::preload( 'resource', $NTS_VIEW['ALL_RESS'] );
ntsObjectFactory::preload( 'service', $sers );

$returnTo = $_NTS['REQ']->getParam( NTS_PARAM_RETURN );
$saveOn = array();
if( $returnTo )
	$saveOn[ NTS_PARAM_RETURN ] = $returnTo;
ntsView::setPersistentParams( $saveOn, 'admin' );
ntsLib::setVar( 'admin:returnTo', $returnTo ); 

$ress = array();
$appEdit = array();
$appView = array();
$appPermissions = $NTS_CURRENT_USER->getAppointmentPermissions();
reset( $appPermissions );
foreach( $appPermissions as $rid => $pa ){
	if( $pa['view'] || $pa['edit'] )
		$ress[] = $rid; 
	if( $pa['edit'] )
		$appEdit[] = $rid;
	if( $pa['view'] )
		$appView[] = $rid;
	}

$schEdit = array();
$schView = array();
$schPermissions = $NTS_CURRENT_USER->getSchedulePermissions();
reset( $schPermissions );
foreach( $schPermissions as $rid => $pa ){
	if( $pa['view'] || $pa['edit'] )
		$ress[] = $rid; 
	if( $pa['edit'] )
		$schEdit[] = $rid;
	if( $pa['view'] )
		$schView[] = $rid;
	}
$ress = array_unique( $ress );

/* check filter */
$filterParam = $_NTS['REQ']->getParam( 'nts-filter' );
$allowedFilter = array('l', 'r', 's', 'c');
$filterParam = explode( '-', $filterParam );
$filter = array();
foreach( $filterParam as $fp ){
	$fclass = trim(substr( $fp, 0, 1 ));
	$fid = trim(substr( $fp, 1 ));
	if( ! in_array($fclass, $allowedFilter) )
		continue;
	if( ! preg_match('/^[\d]*$/', $fid) )
		continue;

	switch( $fclass ){
		case 'l':
			if( ! in_array($fid, $locs) )
				$fp = '';
			else
				$locs = array( $fid );
			break;
		case 'r':
			/* not allowed */
			if( ! in_array($fid, $ress) )
				$fp = '';
			else
				$ress = array( $fid );
			break;
		case 's':
			if( ! in_array($fid, $sers) )
				$fp = '';
			else
				$sers = array( $fid );
			break;
		}

	if( $fp )
		$filter[] = $fp;
	}
$filterParam = join( '-', $filter );
ntsLib::setVar( 'admin/manage:filter', $filter );

$saveOn = array();
$saveOn['nts-filter'] = $filterParam;

$tm2 = new haTimeManager2();
$tm2->checkNow = 0;
$tm2->setResource( $ress );
$tm2->setLocation( $locs );
$tm2->setService( $sers );

// which i can view
$locs2 = array();
$ress2 = array();
$sers2 = array();

if( $schView || $appView ){
	$lrss = $tm2->getLrs();
	reset( $lrss );

	foreach( $lrss as $lrs ){
		if( ! in_array($lrs[0], $locs2) )
			$locs2[] = $lrs[0];
		if( ! in_array($lrs[1], $ress2) )
			$ress2[] = $lrs[1];
		if( ! in_array($lrs[2], $sers2) )
			$sers2[] = $lrs[2];
		}
	}

/* sort ress2 */
$ress = ntsLib::sortArrayByArray( $ress, $sortRess );
$ress2 = ntsLib::sortArrayByArray( $ress2, $sortRess );

ntsLib::setVar( 'admin::tm2', $tm2 );
ntsLib::setVar( 'admin::ress', $ress );
ntsLib::setVar( 'admin::locs', $locs );
ntsLib::setVar( 'admin::sers', $sers );

ntsLib::setVar( 'admin::ress2', $ress2 );
ntsLib::setVar( 'admin::locs2', $locs2 );
ntsLib::setVar( 'admin::sers2', $sers2 );

ntsLib::setVar( 'admin/manage:appEdit', $appEdit );
ntsLib::setVar( 'admin/manage:schEdit', $schEdit );
ntsLib::setVar( 'admin/manage:appView', $appView );
ntsLib::setVar( 'admin/manage:schView', $schView );

/* calendar */
$cal = $_NTS['REQ']->getParam('cal');
if( $cal ){
	$saveOn['cal'] = $cal;
	$calSet = true;
	}
else {
	$t->setNow();
	$cal = $t->formatDate_Db();
	$calSet = false;
	}
ntsLib::setVar( 'admin/manage:cal', $cal );
ntsLib::setVar( 'admin/manage:calSet', $calSet );

ntsView::setPersistentParams( $saveOn, 'admin/manage' );
?>