<?php
$alias = 'admin/manage/agenda';

$parseClasses = false;
ntsLib::setVar( 'admin/manage/agenda:parseClasses', $parseClasses );

$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$objId = $object->getId();
ntsView::setBack( ntsLink::makeLink('admin/customers/edit/appointments', '', array('_id' => $objId)) );

$period = 'all';
ntsLib::setVar( 'admin/manage/agenda:period', $period );

$cal = null;
ntsLib::setVar( 'admin/manage/agenda:cal', $cal );

$orderBy = 'ORDER BY starts_at DESC';
ntsLib::setVar( 'admin/manage/agenda:orderBy', $orderBy );

$filter = array(
	'customer_id'	=> array( '=', $objId ),
	'completed'		=> array( '>=', 0 ),
	);
ntsLib::setVar( 'admin/manage/agenda:filter', $filter );

$mainView = false;
ntsLib::setVar( 'admin/manage/agenda:mainView', $mainView );
?>