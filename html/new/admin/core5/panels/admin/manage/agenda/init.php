<?php
$period = $_NTS['REQ']->getParam( 'period' );
if( ! $period )
	$period = 'day';
$saveOn['period'] = $period;
ntsView::setPersistentParams( $saveOn, 'admin/manage/agenda' );
ntsLib::setVar( 'admin/manage/agenda:period', $period );

ntsView::setBack( ntsLink::makeLink('admin/manage/agenda', '', $saveOn) );

$cal = ntsLib::getVar( 'admin/manage:cal' );
ntsLib::setVar( 'admin/manage/agenda:cal', $cal );

$orderBy = 'ORDER BY starts_at ASC';
ntsLib::setVar( 'admin/manage/agenda:orderBy', $orderBy );

$filter = array(
	'completed'		=> array( '<>', HA_STATUS_CANCELLED ),
	'completed '	=> array( '<>', HA_STATUS_NOSHOW ),
	);
ntsLib::setVar( 'admin/manage/agenda:filter', $filter );

$parseClasses = true;
ntsLib::setVar( 'admin/manage/agenda:parseClasses', $parseClasses );

$mainView = true;
ntsLib::setVar( 'admin/manage/agenda:mainView', $mainView );

$returnTo = ntsLink::makeLink( '-current-' );
ntsLib::setVar('admin/manage/cal::returnTo', $returnTo);
?>