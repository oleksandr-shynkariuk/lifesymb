<?php
$alias = 'admin/manage/agenda';

$parseClasses = true;
ntsLib::setVar( 'admin/manage/agenda:parseClasses', $parseClasses );

$object = ntsLib::getVar( 'admin/company/resources/edit::OBJECT' );
$objId = $object->getId();
ntsView::setBack( ntsLink::makeLink('admin/company/resources/edit/appointments', '', array('_id' => $objId)) );

$period = 'all';
ntsLib::setVar( 'admin/manage/agenda:period', $period );

$cal = null;
ntsLib::setVar( 'admin/manage/agenda:cal', $cal );

$orderBy = 'ORDER BY starts_at DESC';
ntsLib::setVar( 'admin/manage/agenda:orderBy', $orderBy );

$filter = array(
	'resource_id'	=> array( '=', $objId ),
	);
ntsLib::setVar( 'admin/manage/agenda:filter', $filter );

$mainView = false;
ntsLib::setVar( 'admin/manage/agenda:mainView', $mainView );
?>