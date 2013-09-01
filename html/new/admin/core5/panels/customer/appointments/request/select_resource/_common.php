<?php
$conf =& ntsConf::getInstance();
require_once( dirname(__FILE__) . '/../common/grab.php' );

$allValidIds = array();
$currentIndexes = $NTS_AR->getCurrentIndexes();
reset( $currentIndexes );

$ntsdb =& dbWrapper::getInstance();

foreach( $currentIndexes as $i ){
	$allValidIds[$i] = array();

	$service = $NTS_AR->getSelected( $i, 'service' );
	$tm2->setService( $service );

	$location = $NTS_AR->getSelected( $i, 'location' );
	$tm2->setLocation( $location );

	$thisTs = $NTS_AR->getSelected( $i, 'time' );
	$availability = $tm2->getNearestTimes( $thisTs );

	if( $thisTs ){
		$NTS_VIEW['availability'][$i] = array();
		reset( $availability['resource'] );
		foreach( $availability['resource'] as $rid => $availableTs ){
			if( $thisTs ==  $availableTs )
				$NTS_VIEW['availability'][$i][$rid] = $availableTs;
			}
		}
	else {
		$NTS_VIEW['availability'][$i] = $availability['resource'];
		}
	
	$allValidIds[$i] = array_keys( $NTS_VIEW['availability'][$i] );
	}
?>