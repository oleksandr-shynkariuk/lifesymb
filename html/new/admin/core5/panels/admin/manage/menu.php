<?php
$title = M('Appointments');
$sequence = 10;

/* check if show subheader */
$locs2 = ntsLib::getVar( 'admin::locs2' );
$ress2 = ntsLib::getVar( 'admin::ress2' );
$sers2 = ntsLib::getVar( 'admin::sers2' );

$filter = ntsLib::getVar( 'admin/manage:filter' );
$tm2 = ntsLib::getVar( 'admin::tm2' );
if( count($filter) || (count($locs2) > 1) || (count($ress2) > 1) || (count($sers2) > 1) ){
	$showFilter = true;
	}
else {
	$showFilter = false;
	}
if( ! $showFilter )
{
	global $NTS_VIEW;
	$NTS_VIEW['skipSubHeaderFile'][ ntsLib::normalizePath(dirname(__FILE__) . '/subheader.php') ] = TRUE;
}

global $NTS_CURRENT_USER;
$defaultCalendar = $NTS_CURRENT_USER->getProp('_default_calendar');
ntsLib::setVar( 'admin/manage:defaultCalendar', $defaultCalendar );
?>