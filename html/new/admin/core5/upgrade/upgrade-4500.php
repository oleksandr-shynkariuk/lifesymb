<?php
$ntsdb =& dbWrapper::getInstance();
$cm =& ntsCommandManager::getInstance();
set_time_limit( 120 );
error_reporting( E_ALL );


/* prepare services table */
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `duration` int(11) DEFAULT 1800";
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `price` VARCHAR(16) DEFAULT ''";
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `lead_in` int(11) DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `lead_out` int(11) DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `pack_only` TINYINT DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `class_type` TINYINT DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}services ADD COLUMN `until_closed` TINYINT DEFAULT 0";
$result = $ntsdb->runQuery( $sql );
$sql = "UPDATE {PRFX}services SET show_order = 10*show_order";
$result = $ntsdb->runQuery( $sql );

/* upgrade meta table */
$sql = "ALTER TABLE {PRFX}objectmeta ADD COLUMN `meta_data` TEXT";
$result = $ntsdb->runQuery( $sql );

/* move sessions to services */
$sql = "SELECT id FROM {PRFX}services ORDER BY show_order";
$result = $ntsdb->runQuery( $sql );
$session2service = array();
$service2service = array();
$allSessions = array();

while( $i = $result->fetch() ){
	$serviceId = $i['id'];
	$oldServiceId = $i['id'];
	$service2service[ $oldServiceId ] = array();

	$service = ntsObjectFactory::get( 'service' );
	$service->setId( $serviceId );
	$serviceProps = $service->getByArray();
	unset( $serviceProps['id'] );
	$serviceTitle = $service->getProp( 'title' );
	$showOrder = $service->getProp( 'show_order' ); 

	/* get sessions */
	$sql2 = "SELECT * FROM {PRFX}sessions WHERE service_id = $serviceId ORDER BY show_order";
	$result2 = $ntsdb->runQuery( $sql2 );

	$i2s = array();
	while( $i2 = $result2->fetch() ){
		$allSessions[] = $i2['id'];
		$i2s[] = $i2;
		}

	reset( $i2s );
	$count = 0;
	foreach( $i2s as $i2 ){
		$sessionId = $i2['id'];

		if( count($i2s) > 1 )
			$newServiceTitle = $serviceTitle . ' - ' . $i2['title'];
		else
			$newServiceTitle = $serviceTitle;

		if( $count ){
			$newService = ntsObjectFactory::get( 'service' );

			$i2 = array_merge( $serviceProps, $i2 );
			unset( $i2['id'] );

			$newService->setByArray( $i2 );
			$newService->setProp( 'title', $newServiceTitle );

			$cm->runCommand( $newService, 'create' );

			$newService->setProp( 'show_order', $showOrder );
			$cm->runCommand( $newService, 'update' );

			$serviceId = $newService->getId();
			}
		else {
			unset( $i2['id'] );
			$service->setByArray( $i2 );
			$service->setProp( 'title', $newServiceTitle );
			$service->setProp( 'show_order', $showOrder );

			$cm->runCommand( $service, 'update' );
			$serviceId = $service->getId();
			}

		$session2service[ $sessionId ] = $serviceId;
		$service2service[ $oldServiceId ][] = $serviceId;

		$count++;
		$showOrder++;
		}
	}

$allSessionsString = join( ',', $allSessions );
$sql =<<<EOT
DELETE FROM 
	{PRFX}appointments
WHERE 
	session_id NOT IN ($allSessionsString)
EOT;
$result = $ntsdb->runQuery( $sql );

/* change session ids in appointments */
$case = "CASE\n";
reset( $session2service );

foreach( $session2service as $sessionId => $serviceId ){
	$case .= "WHEN session_id = $sessionId THEN $serviceId\n";
	}
$case .= "END\n";

$sql =<<<EOT
UPDATE 
	{PRFX}appointments 
SET
	session_id = 
	$case
EOT;
$result = $ntsdb->runQuery( $sql );

$sql = "DELETE FROM {PRFX}appointments WHERE session_id IS NULL";
$result = $ntsdb->runQuery( $sql );

$sql = "DELETE FROM {PRFX}appointments WHERE provider_id IS NULL";
$result = $ntsdb->runQuery( $sql );

/* alter column name */
$sql = "ALTER TABLE {PRFX}appointments CHANGE COLUMN `session_id` `service_id` int(11) NOT NULL";
$result = $ntsdb->runQuery( $sql );

/* packs */
$sql = "SELECT id, sessions FROM {PRFX}packs";
$result = $ntsdb->runQuery( $sql );
while( $i = $result->fetch() ){
	$sessions = explode( '|', $i['sessions'] );
	$packId = $i['id'];
	$services = array();
	reset( $sessions );
	foreach( $sessions as $s ){
		$services[] = $session2service[ $s ];
		}
	$newValue = join( '|', $services );
	$sql2 = "UPDATE {PRFX}packs SET sessions = '$newValue' WHERE id = $packId";
	$result2 = $ntsdb->runQuery( $sql2 );
	}

/* alter column name */
$sql = "ALTER TABLE {PRFX}packs CHANGE COLUMN `sessions` `services` TEXT";
$result = $ntsdb->runQuery( $sql );

/* delete seats in services */
$sql = "ALTER TABLE {PRFX}services DROP COLUMN `total_seats`, DROP COLUMN `min_seats`, DROP COLUMN `max_seats`, DROP COLUMN `share_location`, DROP COLUMN `need_provider`";
$result = $ntsdb->runQuery( $sql );

/* for schedules, set locations and services */
$sql = "SELECT id FROM {PRFX}schedules";
$result = $ntsdb->runQuery( $sql );
while( $i = $result->fetch() ){
	$scheduleId = $i['id'];
	$schedule = ntsObjectFactory::get( 'schedule' );
	$schedule->setId( $scheduleId );

// find services
	$sql2 =<<<EOT
	SELECT 
		meta_value 
	FROM 
		{PRFX}objectmeta 
	WHERE 
		obj_class = "timeblock" AND
		meta_name = "_service" AND
		obj_id IN (SELECT id FROM {PRFX}timeblocks WHERE schedule_id = $scheduleId)
EOT;

	$result2 = $ntsdb->runQuery( $sql2 );
	$serviceIds = array();
	while( $i2 = $result2->fetch() ){
		$serviceIds[] = $i2['meta_value'];
		}
	$serviceIds = array_unique( $serviceIds );

	global $NTS_OBJECT_CACHE;
	reset( $serviceIds );
	foreach( $serviceIds as $sid ){
		$expandTo = $service2service[ $sid ];
		reset( $expandTo );
		foreach( $expandTo as $expSid ){
			unset( $NTS_OBJECT_CACHE['service'][$expSid] );
			$service = ntsObjectFactory::get('service');
			$service->setId( $expSid );
			}
		}
	$schedule->setProp( '_service', $serviceIds );

// find locations
	$sql2 =<<<EOT
	SELECT 
		meta_value 
	FROM 
		{PRFX}objectmeta 
	WHERE 
		obj_class = "timeblock" AND
		meta_name = "_location" AND
		obj_id IN (SELECT id FROM {PRFX}timeblocks WHERE schedule_id = $scheduleId)
EOT;

	$result2 = $ntsdb->runQuery( $sql2 );
	$locationIds = array();
	while( $i2 = $result2->fetch() ){
		$locationIds[] = $i2['meta_value'];
		}
	$locationIds = array_unique( $locationIds );
	$locationsProp = array();
//	reset( $locationIds );
//	foreach( $locationIds as $lid ){
//		$locationsProp[ $lid ] = array( 'seats'	=> 1 );
//		}
	$locationsProp = $locationIds;
	$schedule->setProp( '_location', $locationsProp );

	$cm->runCommand( $schedule, 'update' );
	}

/* delete from objectmeta services and locations tied to timeblocks */
$sql = "DELETE FROM {PRFX}objectmeta WHERE obj_class = \"timeblock\" AND (meta_name = \"_service\" OR meta_name = \"_location\")";
$result = $ntsdb->runQuery( $sql );

/* create resources table */
$sql =<<<EOT
CREATE TABLE IF NOT EXISTS `{PRFX}resources` (
	`id` int(11) NOT NULL auto_increment,

	`title` VARCHAR(255),
	`description` TEXT,
	`show_order` int(11) DEFAULT 1,

	PRIMARY KEY  (`id`)
	);
EOT;
$result = $ntsdb->runQuery( $sql );

/* move providers to resources */
$sql =<<<EOT
SELECT 
	DISTINCT(obj_id)
FROM 
	{PRFX}objectmeta 
WHERE 
	obj_class = "user" AND
	meta_name = "_role" AND
	meta_value = "provider"
EOT;

$allResources = array();
$allProviders = array();
$pro2res = array();
$result = $ntsdb->runQuery( $sql );
while( $i = $result->fetch() ){
	$pro = new ntsUser();
	$pro->setId( $i['obj_id'] );

	$resource = ntsObjectFactory::get( 'resource' );
	$resource->setProp( 'title', $pro->getProp('first_name') . ' ' . $pro->getProp('last_name') );
	$cm->runCommand( $resource, 'create' );
	$resId = $resource->getId();
	$pro2res[ $i['obj_id'] ] = $resId;
	$allResources[] = $resId; 
	$allProviders[] = $i['obj_id'];
	}

$allProvidersString = join( ',', $allProviders );
$sql =<<<EOT
DELETE FROM 
	{PRFX}appointments
WHERE 
	provider_id NOT IN ($allProvidersString)
EOT;
$result = $ntsdb->runQuery( $sql );
	
reset( $pro2res );
$case = "CASE\n";
foreach( $pro2res as $proId => $resId ){
	$case .= "WHEN provider_id = $proId THEN $resId\n";
	}
$case .= "END\n";

$sql =<<<EOT
UPDATE 
	{PRFX}schedules 
SET
	provider_id = 
	$case
EOT;
$result = $ntsdb->runQuery( $sql );

$sql = "ALTER TABLE {PRFX}schedules CHANGE COLUMN `provider_id` `resource_id` int(11) NOT NULL";
$result = $ntsdb->runQuery( $sql );

$sql = "ALTER TABLE {PRFX}schedules ADD COLUMN `capacity` int(11) DEFAULT 1";
$result = $ntsdb->runQuery( $sql );

$sql =<<<EOT
UPDATE 
	{PRFX}timeoffs 
SET
	provider_id = 
	$case
EOT;
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}timeoffs CHANGE COLUMN `provider_id` `resource_id` int(11) NOT NULL";
$result = $ntsdb->runQuery( $sql );

$sql =<<<EOT
UPDATE 
	{PRFX}appointments 
SET
	provider_id = 
	$case
EOT;
$result = $ntsdb->runQuery( $sql );
$sql = "ALTER TABLE {PRFX}appointments CHANGE COLUMN `provider_id` `resource_id` int(11) NOT NULL";
$result = $ntsdb->runQuery( $sql );

$sql = "ALTER TABLE {PRFX}appointments ADD COLUMN `until_closed` TINYINT DEFAULT 0";
$result = $ntsdb->runQuery( $sql );

/* fix appointment flow */
$sql =<<<EOT
UPDATE 
	{PRFX}conf 
SET
	value  = REPLACE( value, "provider", "resource" )
WHERE
	name = "appointmentFlow"
EOT;
$result = $ntsdb->runQuery( $sql );

/* add selectable fixed */
$sql = "ALTER TABLE {PRFX}timeblocks ADD COLUMN `selectable_fixed` TEXT";
$result = $ntsdb->runQuery( $sql );

/* drop sessions table */
$sql = "DROP TABLE {PRFX}sessions";
$result = $ntsdb->runQuery( $sql );

/* make every provider as admin then assign permissions */
$adminsIds = array();
$sql = "SELECT DISTINCT(obj_id) FROM {PRFX}objectmeta WHERE meta_name = '_role' AND meta_value = 'admin'";
$result = $ntsdb->runQuery( $sql );
while( $l = $result->fetch() ){
	$adminsIds[] = $l['obj_id'];
	}

/* make every provider as admin then assign permissions */
$sql = "SELECT DISTINCT(obj_id) FROM {PRFX}objectmeta WHERE meta_name = '_role' AND meta_value = 'provider'";
$result = $ntsdb->runQuery( $sql );
while( $l = $result->fetch() ){
	$proId = $l['obj_id'];
	$sql2 = "DELETE FROM {PRFX}objectmeta WHERE meta_name = '_role' AND meta_value = 'provider' AND obj_id = $proId";
	$result2 = $ntsdb->runQuery( $sql2 );

	// if already admin then skip
	$sql3 = "SELECT * FROM {PRFX}objectmeta WHERE meta_name = '_role' AND meta_value = 'admin' AND obj_id = $proId";
	$result3 = $ntsdb->runQuery( $sql );
	if( $l = $result3->fetch() ){
		}
	// otherwise set conservative permissions
	else {
		$sql4 = "INSERT INTO {PRFX}objectmeta (meta_name, meta_value, obj_id, obj_class) VAlUES ('_role', 'admin', $proId, 'user')";
		$result4 = $ntsdb->runQuery( $sql4 );
		}
	}

// assign every provider rights to manage own resource
$apm =& ntsAdminPermissionsManager::getInstance();
$allPanels = $apm->getPanels();

reset( $pro2res );
foreach( $pro2res as $proId => $resId ){
	$pro = new ntsUser;
	$pro->setId( $proId );
	$resourceSchedules = array( $resId => 'edit' );
	$resourceApps = array( $resId => 'manage' );
	$pro->setProp( '_resource_schedules', $resourceSchedules );
	$pro->setProp( '_resource_apps', $resourceApps );
	if( ! in_array($proId, $adminsIds) )
		$pro->setProp( '_disabled_panels', $allPanels );
	$cm->runCommand( $pro, 'update' );
	}

// assign the admins rights to manage all resources
$resourceSchedules = array();
$resourceApps = array();
reset( $allResources );
foreach( $allResources as $resId ){
	$resourceSchedules[ $resId ] = 'edit';
	$resourceApps[ $resId ] = 'manage';
	}

reset( $adminsIds );
foreach( $adminsIds as $admId ){
	$adm = new ntsUser;
	$adm->setId( $admId );
	$adm->setProp( '_resource_schedules', $resourceSchedules );
	$adm->setProp( '_resource_apps', $resourceApps );
	$cm->runCommand( $adm, 'update' );
	}

/* delete cc to admin */
$sql = "DELETE FROM {PRFX}objectmeta WHERE meta_name = '_cc_admin'";
$result = $ntsdb->runQuery( $sql );
?>