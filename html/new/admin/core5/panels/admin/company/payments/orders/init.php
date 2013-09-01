<?php
$where = array();
ntsLib::setVar( 'admin/company/payments/orders::where', $where );

$customer = null;
ntsLib::setVar( 'admin/company/payments/orders::customer', $customer );

ntsView::setBack( ntsLink::makeLink('admin/company/payments/orders') );
?>