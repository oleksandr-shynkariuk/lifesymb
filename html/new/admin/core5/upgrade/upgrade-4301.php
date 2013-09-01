<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'show_order' for location */
$sql = "ALTER TABLE {PRFX}locations ADD COLUMN show_order int(11) DEFAULT 1";
$result = $ntsdb->runQuery( $sql );

/* default show order */
$sql = "UPDATE {PRFX}locations SET show_order = id";
$result = $ntsdb->runQuery( $sql );

/* default show order */
$sql = "UPDATE {PRFX}sessions SET show_order = id";
$result = $ntsdb->runQuery( $sql );
?>