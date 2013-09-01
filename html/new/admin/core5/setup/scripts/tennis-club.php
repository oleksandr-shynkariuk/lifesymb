<?php
/* create services */
$services = array();

$titles = array(
	array( 'Court Booking 30 min', 30 * 60, 25 ),
	array( 'Court Booking 60 min', 30 * 60, 40 ),
	);

foreach( $titles as $ta ){
	$object = ntsObjectFactory::get( 'service' );
	$object->setByArray( array(
		'title'			=> $ta[0],
		'description'	=> 'Description of ' . $ta[0],
		'min_cancel'	=> 1 * 24 * 60 * 60,
		'allow_queue'	=> 0,
		'recur_total'	=> 1,
		'duration'	 	=> $ta[1],
		'lead_in'		=> 0,
		'lead_out'		=> 0,
		'price'			=> $ta[2],
		)
		);
	$cm->runCommand( $object, 'create' );
	$serviceId = $object->getId();
	$services[] = $serviceId;
	}

/* create locations */
$locations = array();
$titles = array('St.Lucia School', 'White Mountain Resort', 'Sea Point' );
foreach( $titles as $t ){
	$object = ntsObjectFactory::get( 'location' );
	$object->setByArray( array(
		'title'			=> $t,
		'description'	=> 'Description of ' . $t,
		)
		);
	$cm->runCommand( $object, 'create' );
	$locationId = $object->getId();
	$locations[] = $locationId;
	}

$tm2 = new haTimeManager2();

/* create resources */
$titles = array( 
	'Court 1 (St.Lucia)', // 1
	'Court 2 (St.Lucia)', // 2
	'Court 1 (White Mountain)', // 3
	'Court 1 (Sea Point)', // 4
	'Court 2 (Sea Point)' // 5
	);
$resources = array();
foreach( $titles as $title ){
	$object = ntsObjectFactory::get('resource');
	$object->setByArray( 
		array(
			'title'		=> $title,
			)
		);

	$cm->runCommand( $object, 'create' );
	$resId = $object->getId();
	$resources[] = $resId;

	$resourceSchedules = $admin->getSchedulePermissions();
	$resourceApps = $admin->getAppointmentPermissions();
	$resourceSchedules[ $resId ] = array( 'view' => 1, 'edit' => 1 );
	$resourceApps[ $resId ] = array( 'view' => 1, 'edit' => 1, 'notified' => 1 );

	$admin->setSchedulePermissions( $resourceSchedules );
	$admin->setAppointmentPermissions( $resourceApps );

	$cm->runCommand( $admin, 'update' );
	}

/* schedules */
$t = new ntsTime;
$startSchedule = $t->formatDate_Db();
list( $year, $month, $day ) = ntsTime::splitDate( $startSchedule );
$t->setDateTime( $year + 1, $month, $day, 0, 0, 0 );
$endSchedule = $t->formatDate_Db();

$newBlock = array(
	'starts_at'			=> 10 * 60 * 60,
	'ends_at'			=> 21 * 60 * 60, 
	'selectable_every'	=> 30 * 60,
	'applied_on'		=> array( 1, 2, 3, 4, 5, 6, 7),
	'location_id'		=> 1,
	'resource_id'		=> 1,
	'service_id'		=> 0,
	'valid_from'		=> $startSchedule,
	'valid_to'			=> $endSchedule,
	'capacity'			=> 1,
	);
$tm2->addBlock( $newBlock );

$newBlock = array(
	'starts_at'			=> 10 * 60 * 60,
	'ends_at'			=> 21 * 60 * 60, 
	'selectable_every'	=> 30 * 60,
	'applied_on'		=> array( 1, 2, 3, 4, 5, 6, 7),
	'location_id'		=> 1,
	'resource_id'		=> 2,
	'service_id'		=> 0,
	'valid_from'		=> $startSchedule,
	'valid_to'			=> $endSchedule,
	'capacity'			=> 1,
	);
$tm2->addBlock( $newBlock );

$newBlock = array(
	'starts_at'			=> 11 * 60 * 60,
	'ends_at'			=> 19 * 60 * 60, 
	'selectable_every'	=> 30 * 60,
	'applied_on'		=> array( 3, 4, 5, 6, 7),
	'location_id'		=> 2,
	'resource_id'		=> 3,
	'service_id'		=> 1,
	'valid_from'		=> $startSchedule,
	'valid_to'			=> $endSchedule,
	'capacity'			=> 1,
	);
$tm2->addBlock( $newBlock );

$newBlock = array(
	'starts_at'			=> 8 * 60 * 60,
	'ends_at'			=> 17 * 60 * 60, 
	'selectable_every'	=> 30 * 60,
	'applied_on'		=> array( 1, 2, 4, 5, 6, 7),
	'location_id'		=> 3,
	'resource_id'		=> 4,
	'service_id'		=> 0,
	'valid_from'		=> $startSchedule,
	'valid_to'			=> $endSchedule,
	'capacity'			=> 1,
	);
$tm2->addBlock( $newBlock );

$newBlock = array(
	'starts_at'			=> 8 * 60 * 60,
	'ends_at'			=> 17 * 60 * 60, 
	'selectable_every'	=> 30 * 60,
	'applied_on'		=> array( 1, 2, 4, 5, 6, 7),
	'location_id'		=> 3,
	'resource_id'		=> 5,
	'service_id'		=> 0,
	'valid_from'		=> $startSchedule,
	'valid_to'			=> $endSchedule,
	'capacity'			=> 1,
	);
$tm2->addBlock( $newBlock );

// resources terminology
$resnameSing = 'Court';
$resnamePlu = 'Courts';
$conf->set( 'text-Bookable Resource', $resnameSing );
$conf->set( 'text-Bookable Resources', $resnamePlu );
$conf->set( 'htmlTitle', 'Tennis Court Booking' );
$conf->set( 'appointmentFlow', array(
	array('location', 'manual'),
	array('service', 'manual'),
	array('time', 'manual'),
	array('resource', 'auto')
	));

$conf->set( 'timeStarts', 28800 );
$conf->set( 'timeEnds', 75600 );
?>