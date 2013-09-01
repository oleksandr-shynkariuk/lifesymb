<?php
$tm2 = ntsLib::getVar('admin::tm2');
$cal = ntsLib::getVar( 'admin/manage/timeoff:cal' );

ntsView::setBack( ntsLink::makeLink('admin/manage/timeoff', '', array('cal' => $cal)) );

$entries = $tm2->getTimeoff( $cal );

uasort( $entries, create_function(
	'$a, $b',
	'
	if( $a["starts_at"] != $b["starts_at"] ){
		$return = ($b["starts_at"] - $a["starts_at"]);
		}
	else {
		$return = ($b["starts_at"] - $a["starts_at"]);
		}
	return $return;
	'
	)
);
ntsLib::setVar( 'admin/manage/timeoff:entries', $entries );
?>