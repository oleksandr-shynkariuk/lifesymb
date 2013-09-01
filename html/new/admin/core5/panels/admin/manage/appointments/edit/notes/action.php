<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$objId = $object->getId();

$ntsdb =& dbWrapper::getInstance();
$where = array(
	'obj_class'	=> array('=', 'appointment'),
	'obj_id'	=> array('=', $objId),
	'meta_name'	=> array('=', '_note'),
	);

$result = $ntsdb->select( array('id', 'meta_value', 'meta_data'), 'objectmeta', $where );
$entries = array();
while( $e = $result->fetch() ){
	$entries[] = $e;
	}
ntsLib::setVar( 'admin/manage/appointments/edit/notes::entries', $entries );
?>