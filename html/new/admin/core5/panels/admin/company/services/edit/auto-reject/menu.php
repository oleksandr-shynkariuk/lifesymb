<?php
$object = ntsLib::getVar( 'admin/company/services/edit::OBJECT' );
if( $object->getType() == 'class' ){
	$title = M('Automatic Reject');
	$title .= ': ';
	$thisProp = $object->getProp( '_auto_reject' );

	$conf =& ntsConf::getInstance();
	$cronEnabled = $conf->get( 'cronEnabled' );

	$title .= ($thisProp && $cronEnabled) ? M('On') : M('Off');
	$sequence = 60;
	}
?>