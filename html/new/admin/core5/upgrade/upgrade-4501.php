<?php
$ntsdb =& dbWrapper::getInstance();

/* prepare services table */
$sql = "ALTER TABLE {PRFX}form_controls ADD COLUMN `description` TEXT";
$result = $ntsdb->runQuery( $sql );
?>