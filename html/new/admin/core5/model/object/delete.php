<?php
$actionResult = 1;

$ntsdb =& dbWrapper::getInstance();
$om =& objectMapper::getInstance();

$className = $object->getClassName();
$metaClass = $object->getMetaClass();
$id = $object->getId();

/* MAIN TABLE */
if( (! isset($skipMainTable)) || (! $skipMainTable) ){
	$actionDescription = 'Delete object data from the database';

	$tblName = $om->getTableForClass( $object->getClassName() );
	$whereString = "id = $id";

	$result = $ntsdb->delete( 
		$tblName,
		array( 
			'id' => array( '=', $id ),
			)
		);

	if( $result ){
		$actionResult = 1;
		}
	else {
		$actionResult = 0;
		$actionError = $ntsdb->getError();
		}
	}
	
/* delete meta */
if( $metaClass ){
	$result = $ntsdb->delete(
		'objectmeta',
		array(
			'obj_id'	=> array('=', $id),
			'obj_class'	=> array('=', $metaClass),
			)
		);

	if( $result ){
		$actionResult = 1;
		}
	else {
		$actionResult = 0;
		$actionError = $ntsdb->getError();
		}
	}

/* delete meta as child */
$childMetaClass = '_' . strtolower($className);
$result = $ntsdb->delete( 
	'objectmeta',
	array( 
		'meta_name'		=> array('=', $childMetaClass),
		'meta_value'	=> array('=', $id),
		)
	);

if( $result ){
	$actionResult = 1;
	}
else {
	$actionResult = 0;
	$actionError = $ntsdb->getError();
	}
?>