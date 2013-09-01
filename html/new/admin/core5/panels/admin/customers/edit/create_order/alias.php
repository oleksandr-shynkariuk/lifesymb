<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$customerId = $object->getId();

ntsView::setBack( ntsLink::makeLink('admin/customers/edit/orders', '', array('_id' => $customerId) ) );

$alias = 'admin/company/payments/orders/create';

ntsLib::setVar( 'admin/company/payments/orders/create::fixCustomer', $customerId );
ntsLib::setVar( 'admin/company/payments/orders/create::fixPack', 0 );

$capture = array( 'pack_id' );
reset( $capture );
foreach( $capture as $c ){
	$value = $_NTS['REQ']->getParam( $c );
	if( $value )
		$saveOn[$c] = $value;
	}
ntsView::setPersistentParams( $saveOn, 'admin/customers/edit/sell_pack' );
?>