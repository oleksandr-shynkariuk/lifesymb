<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'share_location' for service */
$sql = "ALTER TABLE {PRFX}services ADD COLUMN recur_options VARCHAR(64) DEFAULT 'd-2d-w'";
$result = $ntsdb->runQuery( $sql );
?>