<?php
$ntsdb =& dbWrapper::getInstance();
$promId = $object->getId();

/* delete coupons */
$result = $ntsdb->delete(
	'coupons',
	array(
		'promotion_id' => array( '=', $promId ),
		)
	);
?>