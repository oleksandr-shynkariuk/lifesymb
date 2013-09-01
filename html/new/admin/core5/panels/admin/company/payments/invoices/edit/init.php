<?php
$id = $_NTS['REQ']->getParam( '_id' );
ntsView::setPersistentParams( array('_id' => $id), 'admin/company/payments/invoices/edit' );

$object = ntsObjectFactory::get( 'invoice' );
$object->setId( $id );
ntsLib::setVar( 'admin/company/payments/invoices/edit::OBJECT', $object );
ntsLib::setVar( 'admin/company/payments/transactions::invoice', $object );

ntsView::setBack( ntsLink::makeLink('admin/company/payments/invoices/edit', '', array('_id' => $id)), true );
?>