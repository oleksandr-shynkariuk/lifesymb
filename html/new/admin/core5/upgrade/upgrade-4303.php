<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'auth_code' for appointment */
$sql = "ALTER TABLE {PRFX}appointments ADD COLUMN auth_code VARCHAR(32) NOT NULL DEFAULT ''";
$result = $ntsdb->runQuery( $sql );

/* add 'extra_price' for session */
$sql = "ALTER TABLE {PRFX}sessions ADD COLUMN extra_price VARCHAR(16) DEFAULT ''";
$result = $ntsdb->runQuery( $sql );

/* default extra_price */
$sql = "UPDATE {PRFX}sessions SET extra_price = price";
$result = $ntsdb->runQuery( $sql );

/* add 'price' for appointments */
$sql = "ALTER TABLE {PRFX}appointments ADD COLUMN price VARCHAR(16) DEFAULT ''";
$result = $ntsdb->runQuery( $sql );

/* price for appointments */
$sql = "UPDATE {PRFX}appointments SET price = (SELECT price FROM {PRFX}sessions WHERE {PRFX}sessions.id = session_id)";
$result = $ntsdb->runQuery( $sql );
?>