<?php
$packs = ntsObjectFactory::getAllIds( 'pack' );
if( count($packs) > 0 ){
	$title = M('Package Orders');
	$sequence = 32;
	
	$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
	$objId = $object->getId();
	$where = array(
		'customer_id'	=> array('=', $objId),
		);
	$ntsdb =& dbWrapper::getInstance();
	$count = $ntsdb->count( 'orders', $where );
	$title .= ' [' . $count . ']';
	}
?>