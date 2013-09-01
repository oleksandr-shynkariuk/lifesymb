<?php
$alias = 'admin/customers/browse';

$returnTo = null;
ntsLib::setVar('admin/customers/browse::returnTo', $returnTo);

$ids = array();
$ntsdb =& dbWrapper::getInstance();
$where = array(
	'obj_class'		=> array('=', 'user'),
	'meta_name'		=> array('=', '_restriction'),
	'meta_value'	=> array('IN', array('"suspended"')),
	);
$result = $ntsdb->select( 'obj_id', 'objectmeta', $where );
if( $result ){
	while( $i = $result->fetch() ){
		$ids[] = $i['obj_id'];
		}
	}
ntsLib::setVar('admin/customers/browse::ids', $ids);
?>