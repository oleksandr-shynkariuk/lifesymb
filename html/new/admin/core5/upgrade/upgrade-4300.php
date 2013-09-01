<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'packs' table */
$sql =<<<EOT
CREATE TABLE IF NOT EXISTS {PRFX}packs (
	`id` int(11) NOT NULL auto_increment,

	`title` VARCHAR(255),
	`description` TEXT,

	`discount` TEXT,
	`sessions` TEXT,

	`show_order` int(11) DEFAULT 1,
	PRIMARY KEY  (`id`)
	);
EOT;
$result = $ntsdb->runQuery( $sql );

/* add 'pack_only' for session */
$sql = "ALTER TABLE {PRFX}sessions ADD COLUMN pack_only TINYINT DEFAULT 0";
$result = $ntsdb->runQuery( $sql );

/* add 'ghost_last_access' for appointment */
$sql = "ALTER TABLE {PRFX}appointments ADD COLUMN ghost_last_access int(11) NOT NULL DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
?>