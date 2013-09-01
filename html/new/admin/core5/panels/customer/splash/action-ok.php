<?php
setcookie( 'ntsFirstTimeSplash', 1, time() + 365*24*60*60 );
$forwardTo = ntsLink::makeLink( 'customer' );
ntsView::redirect( $forwardTo );
exit;
?>