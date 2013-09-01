<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'share_location' for service */
$sql = 'ALTER TABLE {PRFX}services ADD COLUMN recur_total int(11) DEFAULT 1';
$result = $ntsdb->runQuery( $sql );
?>