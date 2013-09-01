<?php
require_once( dirname(__FILE__) . '/../common/grab.php' );
$ready = $NTS_AR->getReady();

$currentIndexes = $NTS_AR->getCurrentIndexes();
$NTS_AR->delete( $currentIndexes );

$title = ( count($ready) > 1 ) ? M('Confirm Appointments') : M('Confirm Appointment');
ntsView::setTitle( $title );
?>