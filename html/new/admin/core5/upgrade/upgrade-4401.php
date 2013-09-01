<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'need_reminder' for appointment */
$sql = "ALTER TABLE {PRFX}appointments ADD COLUMN `cancelled` tinyint NOT NULL DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
?>