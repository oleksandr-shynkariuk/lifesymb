<?php
global $NTS_AR;
require_once( dirname(__FILE__) . '/../../common/grab.php' );

$ready = $NTS_AR->getReady();
if( count($ready) ){
	$NTS_AR->add();
	}

/* forward to dispatcher to see what's next? */
$noForward = false;
require( dirname(__FILE__) . '/../../common/dispatcher.php' );
exit;
?>