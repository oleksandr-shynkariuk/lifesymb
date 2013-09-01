<?php
$permissionsFor = 'admin/customers/browse';

$where = array(
	'_restriction'	=> array('IN', array('"suspended"')),
//	'_role'			=> array('=', 'customer'),
	);
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();
$notApprovedCount = $integrator->countUsers( $where );

if( $notApprovedCount > 0 ){
	$title = M('Suspended') . ' [' . $notApprovedCount . ']';
	$sequence = 40;
	}
?>