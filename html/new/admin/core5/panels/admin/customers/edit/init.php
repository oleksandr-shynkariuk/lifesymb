<?php
$id = $_NTS['REQ']->getParam( '_id' );
ntsView::setPersistentParams( array('_id' => $id), 'admin/customers/edit' );

if( is_array($id) ){
	}
else {
	$object = new ntsUser();
	$object->setId( $id );
	ntsLib::setVar( 'admin/customers/edit::OBJECT', $object );
	}
?>