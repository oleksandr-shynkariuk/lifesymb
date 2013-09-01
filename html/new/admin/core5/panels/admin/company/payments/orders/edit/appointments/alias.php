<?php
$order = ntsLib::getVar( 'admin/company/payments/orders/edit::OBJECT' );
$orderId = $order->getId();
$deps = $order->getItems();

$ids = array();
reset( $deps );
foreach( $deps as $dep ){
	$className = $dep->getClassName();
	if( $className == 'appointment' ){
		$ids[] = $dep->getId();
		}
	}
if( $ids ){
	$alias = 'admin/manage/agenda';
	ntsView::setBack( ntsLink::makeLink('admin/company/payments/orders/edit/appointments', '', array('_id' => $orderId)) );

	$parseClasses = false;
	ntsLib::setVar( 'admin/manage/agenda:parseClasses', $parseClasses );

	$period = 'all';
	ntsLib::setVar( 'admin/manage/agenda:period', $period );

	$cal = null;
	ntsLib::setVar( 'admin/manage/agenda:cal', $cal );

	$orderBy = 'ORDER BY starts_at DESC';
	ntsLib::setVar( 'admin/manage/agenda:orderBy', $orderBy );

	$filter = array(
		'id'	=> array( 'IN', $ids ),
		);
	ntsLib::setVar( 'admin/manage/agenda:filter', $filter );

	$mainView = false;
	ntsLib::setVar( 'admin/manage/agenda:mainView', $mainView );
	}
?>