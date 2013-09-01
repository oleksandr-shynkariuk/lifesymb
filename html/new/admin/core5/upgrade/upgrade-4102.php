<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'service_cats' table */
$sql =<<<EOT
CREATE TABLE IF NOT EXISTS {PRFX}service_cats (
	`id` int(11) NOT NULL auto_increment,

	`title` VARCHAR(255),
	`description` TEXT,

	`show_order` int(11) DEFAULT 1,
	PRIMARY KEY  (`id`)
	);
EOT;

$result = $ntsdb->runQuery( $sql );
?>