<?php
ntsLib::setVar( 'admin/company/payments/orders/create::fixCustomer', 0 );

$id = $_NTS['REQ']->getParam( '_id' );
ntsView::setPersistentParams( array('_id' => $id), 'admin/company/payments/orders/create' );

$object = ntsObjectFactory::get( 'pack' );
$object->setId( $id );
ntsLib::setVar( 'admin/company/payments/orders/create::fixPack', $object->getId() );
?>