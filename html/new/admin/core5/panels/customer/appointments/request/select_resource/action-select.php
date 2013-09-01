<?php
require_once( dirname(__FILE__) . '/_common.php' );

$currentIndexes = $NTS_AR->getCurrentIndexes();
$deleteOnes = array();

foreach( $currentIndexes as $i ){
	$selectedId = $_NTS['REQ']->getParam( 'id_' . $i );
	if( $selectedId == 'auto' ){
//		$selectedId = ntsLib::pickRandom( $allValidIds[$i] );
		$selectedId = 'a';
		}
	if( ! $selectedId ){
		$deleteOnes[] = $i;
		}

	reset( $deleteOnes );
	while( $j = array_pop($deleteOnes) ){
		$NTS_AR->resetAll( $j );
		}
	$NTS_AR->setSelected( $i, 'resource', $selectedId );
	}

/* forward to dispatcher to see what's next? */
$noForward = true;
require( dirname(__FILE__) . '/../common/dispatcher.php' );
return;
?>