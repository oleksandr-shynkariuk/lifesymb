<?php
$permissionsFor = 'admin/customers/browse';

$where = array(
	'_restriction'	=> array('IN', array('"not_approved"', '"email_not_confirmed"')),
//	'_role'			=> array('=', 'customer'),
	);
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

/* clean up our object meta if wordpress */
$integrator->cleanUp();

$notApprovedCount = $integrator->countUsers( $where );

if( $notApprovedCount > 0 ){
	$title = M('Pending Approval') . ' [' . $notApprovedCount . ']';
	$sequence = 30;
	}
?>