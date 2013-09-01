<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$customerId = $object->getId();

$tm2 = ntsLib::getVar( 'admin::tm2' );
$where = array(
	'customer_id'	=> array( '=', $customerId ),
	'completed'		=> array( '>=', 0 ),
	);

/* addonWhere */
$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );
$addonWhere = array(
	'location_id'	=> array( 'IN', $locs ),
	'resource_id'	=> array( 'IN', $ress ),
	'service_id'	=> array( 'IN', $sers ),
	);
reset( $addonWhere );
foreach( $addonWhere as $k => $v ){
	if( (! isset($where[$k])) && (! isset($where['id'])) )
		$where[$k] = $v;
	}

$totalCount = $tm2->countAppointments( $where );

$title = M('Appointments') . ' [' . $totalCount . ']';
$sequence = 20;
?>