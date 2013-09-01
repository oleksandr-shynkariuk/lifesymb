<?php
switch( $action ){
	case 'export':
		ini_set( 'memory_limit', '64M' );
		break;
	default:
		break;
	}

$ntsdb =& dbWrapper::getInstance();
$saveOn = array();
$skipParam = $_NTS['REQ']->getParam('skip');
$skip = $skipParam ? explode( '-', $skipParam ) : array();
if( $skip )
	$saveOn['skip'] = $skip;
ntsView::setPersistentParams( $saveOn, 'admin/customers/browse' );

$ff =& ntsFormFactory::getInstance();

$searchFormParams = array();
if( $search = $_NTS['REQ']->getParam('search') ){
	$searchFormParams['search'] = $search;
	}
$search = strtolower( $search );
$NTS_VIEW['search'] = $search;
$formFile = dirname( __FILE__ ) . '/searchForm';
$NTS_VIEW['searchForm'] =& $ff->makeForm( $formFile, $searchFormParams );

$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();
$showAllDisplays = array(
	'print',
	'excel'
	);

$mainWhere['_role'] = array( '=', 'customer' );
if( $skip ){
	$mainWhere['id'] = array( 'NOT IN', $skip );
	}

$ids = ntsLib::getVar('admin/customers/browse::ids');
if( $ids !== null ){
	$mainWhere['id'] = array( 'IN', $ids );
	}

if( $NTS_VIEW['search'] ){
	$where = array();
	
	$searchIn = array();
	$om =& objectMapper::getInstance();
	$fields = $om->getFields( 'customer', 'external' );
	reset( $fields );
	foreach( $fields as $f ){
		$searchIn[] = $f[0];
		}
	
	reset( $searchIn );
	foreach( $searchIn as $sin ){
		$thisWhere = $mainWhere;
		$thisWhere[ $sin ] = array( 'LIKE', '%' . $NTS_VIEW['search'] . '%' );
		$where[] = $thisWhere;
		}
	}
else {
	$where = $mainWhere;
	}

if( NTS_EMAIL_AS_USERNAME ){
	$order = array(
		array( 'email', 'ASC' )
		);
	}
else {
	$order = array(
		array( 'username', 'ASC' ),
		);
	}

$display = $_NTS['REQ']->getParam( 'display' );
if( $NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax' ){
	$display = 'ajax';
	}
$limit = '';

switch( $action ){
	case 'export':
		$display = 'excel';
		break;
	default:
		break;
	}

if( in_array($display, $showAllDisplays) ){
	$limit = '';
	$NTS_VIEW['showPerPage'] = 'all';
	$NTS_VIEW['currentPage'] = 1;
	}
else {
	if( $NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax' ){
		$NTS_VIEW['showPerPage'] = 10;
		}
	else {
		$NTS_VIEW['showPerPage'] = 20;
		}
	$NTS_VIEW['currentPage'] = $_NTS['REQ']->getParam('p');
	if( ! $NTS_VIEW['currentPage'] )
		$NTS_VIEW['currentPage'] = 1;
	$limit = ( ($NTS_VIEW['currentPage'] - 1) * $NTS_VIEW['showPerPage'] ) . ',' . $NTS_VIEW['showPerPage'];
	}

/* load users */
$users = $integrator->getUsers(
	$where,
	$order,
	$limit
	);
$entries = array();
reset( $users );
$userIds = array();
foreach( $users as $u ){
	$user = new ntsUser();
	$user->setId( $u['id'] );
	$entries[] = $user;
	$userIds[] = $u['id'];
	}

$NTS_VIEW['totalCount'] = $integrator->countUsers( $where );

$display = $_NTS['REQ']->getParam( 'display' );
if( in_array($display, $showAllDisplays) ){
	$NTS_VIEW['showFrom'] = 1;
	$NTS_VIEW['showTo'] = $NTS_VIEW['totalCount'];
	}
else {
	if($NTS_VIEW['totalCount'] > 0){
		$NTS_VIEW['showFrom'] = 1 + ($NTS_VIEW['currentPage'] - 1) * $NTS_VIEW['showPerPage'];
		$NTS_VIEW['showTo'] = $NTS_VIEW['showFrom'] + $NTS_VIEW['showPerPage'] - 1;
		if( $NTS_VIEW['showTo'] > $NTS_VIEW['totalCount'] )
			$NTS_VIEW['showTo'] = $NTS_VIEW['totalCount'];
		}
	else {
		$NTS_VIEW['showFrom'] = 0;
		$NTS_VIEW['showTo'] = 0;
		}
	}
	
ntsLib::setVar( 'admin/customers/browse:entries', $entries );

/* count apps */
$upcomingCount = array();
$oldCount = array();
$grandTotal = 0;
$totalAmount = 0;
$paidAmount = 0;
$orderCount = array();

if( $userIds ){
	$NTS_VIEW['t']->setNow();
	$NTS_VIEW['t']->setStartDay();
	$fromNow = $NTS_VIEW['t']->getTimestamp();

	/* addonWhere */
	$locs = ntsLib::getVar( 'admin::locs' );
	$ress = ntsLib::getVar( 'admin::ress' );
	$sers = ntsLib::getVar( 'admin::sers' );
	$addonWhere = array(
		'location_id'	=> array( 'IN', $locs ),
		'resource_id'	=> array( 'IN', $ress ),
		'service_id'	=> array( 'IN', $sers ),
		);

	$where = array(
		'starts_at'		=> array( '>=', $fromNow ),
		'customer_id'	=> array( 'IN', $userIds ),
		'completed'		=> array( '>=', 0 ),
		);
	reset( $addonWhere );
	foreach( $addonWhere as $k => $v ){
		if( (! isset($where[$k])) && (! isset($where['id'])) )
			$where[$k] = $v;
		}
	$upcomingCount = $tm2->countAppointments( $where, 'customer_id' );

	$where = array(
		'starts_at'		=> array( '<', $fromNow ),
		'customer_id'	=> array( 'IN', $userIds ),
		'completed'		=> array( '>=', 0 ),
		);
	reset( $addonWhere );
	foreach( $addonWhere as $k => $v ){
		if( (! isset($where[$k])) && (! isset($where['id'])) )
			$where[$k] = $v;
		}
	$oldCount = $tm2->countAppointments( $where, 'customer_id' );

	$where = array(
		'customer_id'	=> array( 'IN', $userIds ),
		);
	$result = $ntsdb->select( 'COUNT(id) AS count, customer_id', 'orders', $where, 'GROUP BY customer_id' );
	if( $result ){
		while( $i = $result->fetch() ){
			$orderCount[ $i['customer_id'] ] = $i['count'];
			}
		}
	}

ntsLib::setVar( 'admin/customers::upcomingCount', $upcomingCount );
ntsLib::setVar( 'admin/customers::oldCount', $oldCount );
ntsLib::setVar( 'admin/customers::orderCount', $orderCount );

switch( $action ){
	case 'export':
		$fileName = 'customers-' . $t->formatDate_Db() . '.csv';
		ntsLib::startPushDownloadContent( $fileName );
		require( dirname(__FILE__) . '/excel.php' );
		exit;
		break;
	default:
		break;
	}
?>