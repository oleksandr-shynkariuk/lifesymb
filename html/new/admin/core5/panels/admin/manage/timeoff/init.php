<?php
/* redefine calendar */
$cal = $_NTS['REQ']->getParam( 'cal' );
ntsLib::setVar( 'admin/manage/timeoff:cal', $cal );
?>