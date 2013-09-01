<?php
$conf =& ntsConf::getInstance();
require_once( dirname(__FILE__) . '/../common/grab.php' );

$allValidIds = array();
$currentIndexes = $NTS_AR->getCurrentIndexes();
reset( $currentIndexes );

foreach( $currentIndexes as $i ){
	$allValidIds[$i] = array();

	$service = $NTS_AR->getSelected( $i, 'service' );
	$tm2->setService( $service );

	$resource = $NTS_AR->getSelected( $i, 'resource' );
	$tm2->setResource( $resource );

	$thisTs = $NTS_AR->getSelected( $i, 'time' );
	$availability = $tm2->getNearestTimes( $thisTs );

	if( $thisTs ){
		$NTS_VIEW['availability'][$i] = array();
		reset( $availability['location'] );
		foreach( $availability['location'] as $lid => $availableTs ){
			if( $thisTs ==  $availableTs )
				$NTS_VIEW['availability'][$i][$lid] = $availableTs;
			}
		}
	else {
		$NTS_VIEW['availability'][$i] = $availability['location'];
		}
	
	$allValidIds[$i] = array_keys( $NTS_VIEW['availability'][$i] );
	}
?>