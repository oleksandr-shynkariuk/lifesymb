<?php
$ntsdb =& dbWrapper::getInstance();
$cm =& ntsCommandManager::getInstance();

$now = time();

/* find services that have auto reject */
$where = array(
	'obj_class'	=> array('=', 'service'),
	'meta_name'	=> array('=', '_auto_reject'),
	);

$services = array();
$result = $ntsdb->select( array('obj_id', 'meta_value'), 'objectmeta', $where );
if( $result ){
	while( $e = $result->fetch() ){
		$services[ $e['obj_id'] ] = unserialize( $e['meta_value'] );
		}
	}

$process = array();
reset( $services );
foreach( $services as $sid => $rejectInfo ){
	$rejectBefore = $rejectInfo['reject-before'];
	/* find apps that should be reminded at this run */
	$where = array(
		'completed'						=> array('=', 0),
		'service_id'					=> array('=', $sid),
		"(starts_at - $rejectBefore)"	=> array('<=', $now),
		'starts_at'						=> array('>', $now),
		);

	$result = $ntsdb->select( array('COUNT(id) AS count', 'CONCAT(location_id, "-", resource_id, "-", service_id, "-", starts_at) AS lrst', ), 'appointments', $where, 'GROUP BY lrst' );
	$lessThan = $rejectInfo['less-than'];
	if( $result ){
		while( $e = $result->fetch() ){
			if( $e['count'] < $lessThan ){
				$process[] = array( $e['lrst'], $rejectInfo['reason'] );
				}
			}
		}
	}
//_print_r( $process );

reset( $process );
foreach( $process as $pro ){
	list( $lid, $rid, $sid, $startsAt ) = explode( '-', $pro[0] );
	$commandParams = array(
		'reason' => $pro[1],
		);

	$where = array(
		'location_id'	=> array('=', $lid),
		'resource_id'	=> array('=', $rid),
		'service_id'	=> array('=', $sid),
		'starts_at'		=> array('=', $startsAt),
		);

	$result = $ntsdb->select( 'id', 'appointments', $where );
	if( $result ){
		while( $e = $result->fetch() ){
			$a = ntsObjectFactory::get( 'appointment' );
			$a->setId( $e['id'] );
			$cm->runCommand( $a, 'reject', $commandParams );
			}
		}
	}
?>