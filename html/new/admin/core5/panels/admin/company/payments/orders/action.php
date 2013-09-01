<?php
$ntsdb =& dbWrapper::getInstance();

$customerId = ntsLib::getVar( 'admin/company/payments/orders::customer' );
$where = ntsLib::getVar( 'admin/company/payments/orders::where' );

if( $customerId ){
	$where['customer_id'] = array('=', $customerId);
	}

$ff =& ntsFormFactory::getInstance();
$searchFormParams = array();
if( $search = $_NTS['REQ']->getParam('search') ){
	$searchFormParams['search'] = $search;
	}
$NTS_VIEW['search'] = $search;
$formFile = dirname( __FILE__ ) . '/searchForm';
$NTS_VIEW['searchForm'] =& $ff->makeForm( $formFile, $searchFormParams );
if( $search ){
	$where['refno'] = array( 'LIKE', '%' . $search . '%' );
	}

$showPerPage = 20;
$currentPage = $_NTS['REQ']->getParam('p');
if( ! $currentPage )
	$currentPage = 1;
$limit = ( ($currentPage - 1) * $showPerPage ) . ',' . $showPerPage;

$addOn = '';
$addOn .= 'ORDER BY created_at DESC';
if( $limit )
	$addOn .= " LIMIT $limit";

$ids = array();
$result = $ntsdb->select( 'id', 'orders', $where, $addOn );
while( $i = $result->fetch() ){
	$ids[] = $i['id'];
	}
	
$entries = array();
ntsObjectFactory::preload( 'order', $ids );
reset( $ids );
foreach( $ids as $id ){
	$e = ntsObjectFactory::get( 'order' );
	$e->setId( $id );
	$entries[] = $e;
	}

$totalCount = $ntsdb->count( 'orders', $where );
if( $totalCount > 0 ){
	$showFrom = 1 + ($currentPage - 1) * $showPerPage;
	$showTo = $showFrom + $showPerPage - 1;
	if( $showTo > $totalCount )
		$showTo = $totalCount;
	}
else {
	$showFrom = 0;
	$showTo = 0;
	}

ntsLib::setVar( 'admin/company/payments/orders::search', $search );
ntsLib::setVar( 'admin/company/payments/orders::entries', $entries );
ntsLib::setVar( 'admin/company/payments/orders::currentPage', $currentPage );
ntsLib::setVar( 'admin/company/payments/orders::showFrom', $showFrom );
ntsLib::setVar( 'admin/company/payments/orders::showTo', $showTo );
ntsLib::setVar( 'admin/company/payments/orders::totalCount', $totalCount );
ntsLib::setVar( 'admin/company/payments/orders::showPerPage', $showPerPage );
?>