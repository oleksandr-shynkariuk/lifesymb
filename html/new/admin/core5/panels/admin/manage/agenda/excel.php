<?php
require( dirname(__FILE__) . '/prepareView.php' );

$skipFields = array('lrst', 'type', 'time_end', 'status_approved', 'status_completed', 'starts_at');

$myShowFields = array();
reset( $showFields );
foreach( $showFields as $f ){
	if( in_array($f, $skipFields) )
		continue;
	$myShowFields[] = $f;
	}
$myShowFields[] = 'notes';

$ve = array();
reset( $myShowFields );
foreach( $myShowFields as $f ){
	$ve[] = str_replace( '<br>', ':', $allFields[$f] );
	}
echo ntsLib::buildCsv( $ve );
echo "\n";

reset( $viewEntries );
foreach( $viewEntries as $e ){
	if( $e['type'] == 'timeoff' )
		continue;
	$ve = array();
	reset( $myShowFields );
	foreach( $myShowFields as $f ){
		$ve[] = $e[$f];
		}
	echo ntsLib::buildCsv( $ve );
	echo "\n";
	}
?>