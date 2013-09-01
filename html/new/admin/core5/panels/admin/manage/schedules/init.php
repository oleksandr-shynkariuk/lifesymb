<?php
/* redefine calendar */
$cal = $_NTS['REQ']->getParam( 'cal' );
ntsLib::setVar( 'admin/manage/schedules:cal', $cal );

ntsView::setBack( ntsLink::makeLink('admin/manage/schedules', '', array('cal' => $cal)) );
?>