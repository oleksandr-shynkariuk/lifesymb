<?php
$invoice = ntsLib::getVar( 'admin/company/payments/transactions::invoice' );
$ntsdb =& dbWrapper::getInstance();

$ff =& ntsFormFactory::getInstance();

$t = $NTS_VIEW['t'];

$dateFormParams = array();
$gateway = $_NTS['REQ']->getParam('gateway');
$from = $_NTS['REQ']->getParam('from');
$to = $_NTS['REQ']->getParam('to');

if( ! ($from && $to) ){
	$t->setNow();
	$to = $t->formatDate_Db();
	$t->modify( '-2 weeks' );
	$from = $t->formatDate_Db();
	}

$t->setDateDb( $from );
$fromTs = $t->getStartDay();
$t->setDateDb( $to );
$toTs = $t->getEndDay();

$dateFormParams = array(
	'gateway'	=> $gateway,
	'from'	=> $from,
	'to'	=> $to,
	);

$formFile = dirname( __FILE__ ) . '/dateForm';
$NTS_VIEW['dateForm'] =& $ff->makeForm( $formFile, $dateFormParams );

$addon = 'ORDER BY created_at DESC';
$where = array();

$entries = array();
$transactionsAmount = 0;

if( $invoice ){
	$limit = 0;
	$pm =& ntsPaymentManager::getInstance();
	$entries = $pm->getTransactionsOfInvoice( $invoice->getId() );
	$count = count($entries);
	}
else {
	$where['created_at'] = array('>=', $fromTs);
	$where[' created_at'] = array('<=', $toTs);
	if( $gateway ){
		$where['pgateway'] = array('=', $gateway);
		}

	$limit = 0;
	$count = $ntsdb->count( 'transactions', $where );
	if( $limit && ($count > $limit) ){
		$addon .= ' LIMIT ' . $limit;
		}

	$result = $ntsdb->select( 'id', 'transactions', $where, $addon );
	$ids = array();
	while( $i = $result->fetch() ){
		$ids[] = $i['id'];
		}
	ntsObjectFactory::preload( 'transaction', $ids );
	reset( $ids );
	foreach( $ids as $id ){
		$e = ntsObjectFactory::get( 'transaction' );
		$e->setId( $id );
		$entries[] = $e;
		}
	}

reset( $entries );
foreach( $entries as $e ){
	$transactionsAmount += $e->getProp('amount');
	}

ntsLib::setVar( 'admin/company/payments/transactions::totalCount', $count );
ntsLib::setVar( 'admin/company/payments/transactions::limit', $limit );

ntsLib::setVar( 'admin/company/payments/transactions::entries', $entries );
ntsLib::setVar( 'admin/company/payments/transactions::transactionsAmount', $transactionsAmount );

switch( $action ){
	case 'export':
		$fileName = 'transactions-' . $t->formatDate_Db() . '.csv';
		ntsLib::startPushDownloadContent( $fileName );
		require( dirname(__FILE__) . '/excel.php' );
		exit;
		break;
	default:
		break;
	}
?>