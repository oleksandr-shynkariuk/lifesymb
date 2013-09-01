<?php
$gateway = $_NTS['REQ']->getParam('gateway');
$from = $_NTS['REQ']->getParam('from');
$to = $_NTS['REQ']->getParam('to');

if( $to < $from )
	$to = $from;
$params = array(
	'from' => $from,
	'to' => $to,
	);
if( $gateway )
	$params['gateway'] = $gateway;

$forwardTo = ntsLink::makeLink( '-current-', '', $params );
ntsView::redirect( $forwardTo );
exit;
?>