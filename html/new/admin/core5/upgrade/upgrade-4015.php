<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'share_location' for service */
$sql = 'ALTER TABLE {PRFX}services ADD COLUMN share_location TINYINT DEFAULT 0';
$result = $ntsdb->runQuery( $sql );
?>