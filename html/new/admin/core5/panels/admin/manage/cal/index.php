<?php
$ntsConf =& ntsConf::getInstance();
$cal2show = $ntsConf->get('monthsToShowAdmin');

$returnTo = ntsLib::getVar('admin/manage/cal::returnTo');

$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

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
?>

<p>
<ul class="nts-hori-list">
<?php 
/* prepare calendar */
$cal = ntsLib::getVar( 'admin/manage:cal' );
$highlightDay = $cal;
$t = $NTS_VIEW['t'];
$t->setDateDb( $cal );

for( $ii = 1; $ii <= $cal2show; $ii++ )
{
	echo '<li class="nts-hori-list-item">';
	require( dirname(__FILE__) . '/../prepare-calendar.php' );

	$skipNextLink = TRUE;
	$skipPrevLink = TRUE;
	if( $ii == 1 )
		$skipPrevLink = FALSE;
	if( $ii == $cal2show )
		$skipNextLink = FALSE;

	$calendarReturnTo = $returnTo;
	$calendarMonths = $cal2show;

	require( NTS_APP_DIR . '/helpers/calendar.php' );
	if( $ii != $cal2show )
	{
		$t->setDateDb( $cal );
		$t->setStartMonth();
		$t->modify( '+1 month' );
		$cal = $t->formatDate_Db();
	}
	echo '</li>';
}
$cal = ntsLib::getVar( 'admin/manage:cal' );
?>

</ul>