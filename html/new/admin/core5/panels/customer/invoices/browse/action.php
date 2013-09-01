<?php
$ntsdb =& dbWrapper::getInstance();
$customerId = $NTS_CURRENT_USER->getId();

$ids = array();

$pm =& ntsPaymentManager::getInstance();
$ids = $pm->getInvoicesOfCustomer( $customerId );
if( $ids )
{
	$where['id'] = array('IN', $ids);
	$addOn = 'ORDER BY created_at DESC';
	$ids = array();
	$result = $ntsdb->select( 'id', 'invoices', $where, $addOn );
	if( $result ){
		while( $i = $result->fetch() ){
			$ids[] = $i['id'];
			}
		}
}

$entries = array();
ntsObjectFactory::preload( 'invoice', $ids );
reset( $ids );
foreach( $ids as $id ){
	$e = ntsObjectFactory::get( 'invoice' );
	$e->setId( $id );
	$entries[] = $e;
	}
ntsLib::setVar( 'customer/invoices/browse::entries', $entries );
?>