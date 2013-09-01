<?php
$ntsdb =& dbWrapper::getInstance();

/* prepare services table */
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `return_url` TEXT DEFAULT ''";
$result = $ntsdb->runQuery( $sql );
?>