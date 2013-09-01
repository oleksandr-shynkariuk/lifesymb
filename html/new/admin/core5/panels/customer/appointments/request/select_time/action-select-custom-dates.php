<?php
global $NTS_AR;
require_once( dirname(__FILE__) . '/../common/grab.php' );

$currentIndexes = $NTS_AR->getCurrentIndexes();

$customDates = $_NTS['REQ']->getParam('custom-dates');
$preferredTime = $_NTS['REQ']->getParam('preferred-time');

if( $customDates )
	$customDates = explode( '-', $customDates );
else 
	$customDates = array();
$customDates = array_unique( $customDates );
sort( $customDates );

$customDatesCount = count( $customDates );
$startI = $currentIndexes[0];
$endI = $currentIndexes[0] + $customDatesCount - 1;
for( $i = $startI; $i <= $endI; $i++ ){
	if( $i > $startI ){
		$NTS_AR->duplicate( $startI, $i );
		}
	$NTS_AR->setSelected( $i, 'date', $customDates[$i-$startI] );
	}
$NTS_AR->resetSelected( 0, 'cal' ); // all

// reset custom-dates, cal, recurring params
$NTS_AR->resetOther( 'custom-dates' );
$NTS_AR->resetOther( 'recurring' );

$NTS_AR->setOther( 'preferred-time', $preferredTime );

/* forward to dispatcher to see what's next? */
$noForward = false;
require( dirname(__FILE__) . '/../common/dispatcher.php' );
exit;
?>