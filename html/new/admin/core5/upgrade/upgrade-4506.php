<?php
$ntsdb =& dbWrapper::getInstance();

/* update configuration for flow */
$sql = "SELECT value FROM {PRFX}conf WHERE name = 'appointmentFlow'";
$result = $ntsdb->runQuery( $sql );
if( $i = $result->fetch() ){
	$rawValue = $i['value'];

	$raw = explode( '|', $rawValue );
	reset( $raw );
	$currentFlow = array();
	foreach( $raw as $rr ){
		$r = array( $rr, 'manual'); 
		$currentFlow[] = $r;
		}

	$conf =& ntsConf::getInstance();
	$conf->set( 'appointmentFlow', $currentFlow );
	}
?>