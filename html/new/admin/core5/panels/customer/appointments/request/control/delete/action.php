<?php
global $NTS_AR;
require_once( dirname(__FILE__) . '/../../common/grab.php' );

$what = $_NTS['REQ']->getParam( 'what' );
$NTS_AR->resetAll( $what );

/* forward to dispatcher to see what's next? */
$noForward = false;
require( dirname(__FILE__) . '/../../common/dispatcher.php' );
exit;
?>