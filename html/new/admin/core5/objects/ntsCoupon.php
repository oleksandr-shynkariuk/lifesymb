<?php
class ntsCoupon extends ntsObject 
{
	function __construct()
	{
		parent::ntsObject( 'coupon' );
	}

	function getUseCount()
	{
		$ntsdb =& dbWrapper::getInstance();
		$code = $this->getProp('code');
		$where = array(
			'meta_name'	=> array('=', '_promotion'),
			'meta_data'	=> array('=', $code),
			);
		$return = $ntsdb->count( 'objectmeta', $where );
		return $return;
	}
}
?>