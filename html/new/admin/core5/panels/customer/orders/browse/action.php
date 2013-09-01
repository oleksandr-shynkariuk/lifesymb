<?php
$ntsdb =& dbWrapper::getInstance();
$customerId = $NTS_CURRENT_USER->getId();

$where = array(
	'customer_id'	=> array( '=', $customerId ),
	);

$addOn = 'ORDER BY created_at DESC';

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
ntsLib::setVar( 'customer/orders/browse::entries', $entries );
?>