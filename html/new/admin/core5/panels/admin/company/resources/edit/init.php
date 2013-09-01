<?php
$id = $_NTS['REQ']->getParam( '_id' );
ntsView::setPersistentParams( array('_id' => $id), 'admin/company/resources/edit' );

$object = ntsObjectFactory::get( 'resource' );
$object->setId( $id );
ntsLib::setVar( 'admin/company/resources/edit::OBJECT', $object );
?>