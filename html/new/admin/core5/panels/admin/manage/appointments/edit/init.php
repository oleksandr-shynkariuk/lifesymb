<?php
$id = $_NTS['REQ']->getParam( '_id' );
$single = $_NTS['REQ']->getParam( 'single' );
$noheader = $_NTS['REQ']->getParam( 'noheader' );
$saveOn = array(
	'_id'		=> $id,
	'single'	=> $single,
	'noheader'	=> $noheader
	);
ntsView::setPersistentParams( $saveOn, 'admin/manage/appointments/edit' );

$object = ntsObjectFactory::get( 'appointment' );
$object->setId( $id );
ntsLib::setVar( 'admin/manage/appointments/edit::OBJECT', $object );

$notes = $object->getProp('_note');
?>