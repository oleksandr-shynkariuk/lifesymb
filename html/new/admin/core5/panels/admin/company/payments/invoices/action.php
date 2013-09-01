<?php
$ntsdb =& dbWrapper::getInstance();

$customerId = ntsLib::getVar( 'admin/company/payments/invoices::customer' );
$where = ntsLib::getVar( 'admin/company/payments/invoices::where' );

if( $customerId ){
	$pm =& ntsPaymentManager::getInstance();
	$ids = $pm->getInvoicesOfCustomer( $customerId );
	if( $ids ){
		$where['id'] = array('IN', $ids);
		}
	else {
		$where['id'] = array('=', 0);
		}
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

if( ! isset($where['id']) ){
	$showPerPage = 10;
	$currentPage = $_NTS['REQ']->getParam('p');
	if( ! $currentPage )
		$currentPage = 1;
	$limit = ( ($currentPage - 1) * $showPerPage ) . ',' . $showPerPage;
	}
else {
	$limit = '';
	$showPerPage = 'all';
	$currentPage = 1;
	}

$addOn = '';
$addOn .= 'ORDER BY created_at DESC';
if( $limit )
	$addOn .= " LIMIT $limit";

$ids = array();
$result = $ntsdb->select( 'id', 'invoices', $where, $addOn );
while( $i = $result->fetch() ){
	$ids[] = $i['id'];
	}

$entries = array();
ntsObjectFactory::preload( 'invoice', $ids );
reset( $ids );
foreach( $ids as $id ){
	$e = ntsObjectFactory::get( 'invoice' );
	$e->setId( $id );
	$entries[] = $e;
	}

$totalCount = $ntsdb->count( 'invoices', $where );
if( $totalCount > 0 ){
	if( $showPerPage == 'all' ){
		$showFrom = 1;
		$showTo = $totalCount;
		}
	else {
		$showFrom = 1 + ($currentPage - 1) * $showPerPage;
		$showTo = $showFrom + $showPerPage - 1;
		if( $showTo > $totalCount )
			$showTo = $totalCount;
		}
	}
else {
	$showFrom = 0;
	$showTo = 0;
	}

ntsLib::setVar( 'admin/company/payments/invoices::search', $search );
ntsLib::setVar( 'admin/company/payments/invoices::entries', $entries );
ntsLib::setVar( 'admin/company/payments/invoices::currentPage', $currentPage );
ntsLib::setVar( 'admin/company/payments/invoices::showFrom', $showFrom );
ntsLib::setVar( 'admin/company/payments/invoices::showTo', $showTo );
ntsLib::setVar( 'admin/company/payments/invoices::totalCount', $totalCount );
ntsLib::setVar( 'admin/company/payments/invoices::showPerPage', $showPerPage );
?>