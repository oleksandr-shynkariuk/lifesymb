<?php
$cm =& ntsCommandManager::getInstance();
$totalPrice = 0;
reset( $apps );

for( $i = 0; $i < count($apps); $i++ ){
	$cm->runCommand( $apps[$i], 'request' );
	}
?>