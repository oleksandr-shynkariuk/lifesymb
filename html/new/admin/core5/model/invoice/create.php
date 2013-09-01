<?php
$conf =& ntsConf::getInstance();

/* init some params */
$now = time();
$createdAt = $object->getProp( 'created_at' );
if( ! $createdAt )
	$object->setProp( 'created_at', $now );

$dueAt = $object->getProp( 'due_at' );
if( ! $dueAt )
	$object->setProp( 'due_at', $now );

$object->setProp( 'currency', $conf->get('currency') );

/* generate refno */
$refNoParts = array();
$refNoParts[] = ntsLib::generateRand( 3, array('letters' => false, 'caps' => true, 'digits' => false) );
$refNoParts[] = ntsLib::generateRand( 3, array('letters' => false, 'caps' => false, 'digits' => true) );
$refNo = join( '-', $refNoParts );
$object->setProp( 'refno', $refNo );
?>