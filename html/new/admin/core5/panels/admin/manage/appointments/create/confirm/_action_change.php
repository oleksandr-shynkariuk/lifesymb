<?php
$cm =& ntsCommandManager::getInstance();
$totalPrice = 0;
reset( $apps );

for( $i = 0; $i < count($apps); $i++ ){
	$completed = $apps[$i]->getProp( 'completed' );
	if( $completed && ($completed != HA_STATUS_COMPLETED) ){
		$apps[$i]->setProp( 'completed', 0 );
		}

	$cm->runCommand( $apps[$i], 'change' );
	}
?>