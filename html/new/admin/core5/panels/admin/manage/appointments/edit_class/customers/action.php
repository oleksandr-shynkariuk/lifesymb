<?php
$objects = ntsLib::getVar( 'admin/manage/appointments/edit_class::objects' );

$tm2 = ntsLib::getVar('admin::tm2');

$startsAt = $objects[0]->getProp('starts_at');
$duration = $objects[0]->getProp('duration'); 

$times = $tm2->getAllTime( $startsAt, $startsAt + 1 );
$seatsLeft = isset($times[$startsAt][0]) ? $times[$startsAt][0][ $tm2->SLT_INDX['seats'] ] : 0;
ntsLib::setVar( 'admin/manage/appointments/edit_class::seatsLeft', $seatsLeft );
?>