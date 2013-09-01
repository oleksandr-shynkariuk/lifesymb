<?php
require_once( dirname(__FILE__) . '/_common.php' );

$currentIndexes = $NTS_AR->getCurrentIndexes();
foreach( $currentIndexes as $i ){
	$selectedId = $_NTS['REQ']->getParam( 'id_' . $i );
	if( $selectedId == 'auto' ){
//		$selectedId = ntsLib::pickRandom( $allValidIds[$i] );
		$selectedId = 'a';
		}
	$NTS_AR->setSelected( $i, 'location', $selectedId );
	}

/* forward to dispatcher to see what's next? */
$noForward = true;
require( dirname(__FILE__) . '/../common/dispatcher.php' );
return;
?>