<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'need_reminder' for appointment */
$sql = "ALTER TABLE {PRFX}appointments ADD COLUMN `need_reminder` tinyint NOT NULL DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
?>