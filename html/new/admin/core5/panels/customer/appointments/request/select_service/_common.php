<?php
$conf =& ntsConf::getInstance();
require_once( dirname(__FILE__) . '/../common/grab.php' );

$allValidIds = array();
$currentIndexes = $NTS_AR->getCurrentIndexes();
reset( $currentIndexes );

foreach( $currentIndexes as $i ){
	$allValidIds[$i] = array();

	$location = $NTS_AR->getSelected( $i, 'location' );
	$tm2->setLocation( $location );
	$resource = $NTS_AR->getSelected( $i, 'resource' );
	$tm2->setResource( $resource );

	$thisTs = $NTS_AR->getSelected( $i, 'time' );
	$availability = $tm2->getNearestTimes( $thisTs );

	if( $thisTs ){
		$NTS_VIEW['availability'][$i] = array();
		reset( $availability['service'] );
		foreach( $availability['service'] as $sid => $availableTs ){
			if( $thisTs ==  $availableTs )
				$NTS_VIEW['availability'][$i][$sid] = $availableTs;
			}
		}
	else {
		$NTS_VIEW['availability'][$i] = $availability['service'];
		}
	$allValidIds[$i] = array_keys( $NTS_VIEW['availability'][$i] );
	}
?>