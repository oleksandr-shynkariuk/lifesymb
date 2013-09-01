<?php
global $NTS_AR, $NTS_VIEW;
$NTS_AR = new ntsAppointmentRequest();

if( $NTS_CURRENT_USER->getId() > 0 )
	$currentCustomerId = $NTS_CURRENT_USER->getId();
elseif( isset($_SESSION['temp_customer_id']) )
	$currentCustomerId = $_SESSION['temp_customer_id'];
else
	$currentCustomerId = 0;

/* CHECK IF RESCHEDULE */
$reschedule = $NTS_AR->getReschedule();
if( $reschedule )
{
	$reschId = $reschedule->getId();
	global $NTS_SKIP_APPOINTMENTS;
	$NTS_SKIP_APPOINTMENTS = array( $reschId );
}

$order = $NTS_AR->getOrder();
$checkCustomerId = 0;
if( $reschedule )
{
	$reschId = $reschedule->getId();
	$checkCustomerId = $reschedule->getProp('customer_id');
}
elseif( $order )
{
	$checkCustomerId = $order->getProp('customer_id');
}

if( $checkCustomerId && ($checkCustomerId != $currentCustomerId) ){
	ntsView::setAnnounce( M('Access Denied'), 'error' );
	$forwardTo = ntsLink::makeLink();
	ntsView::redirect( $forwardTo );
	exit;
	}

$t = $NTS_VIEW['t'];
$tm2 = new haTimeManager2();
$tm2->customerT = $t;
$tm2->customerSide = true;
if( isset($GLOBALS['NTS_FIX_RESOURCE']) ){
	$tm2->addFilter( 'resource', $GLOBALS['NTS_FIX_RESOURCE'] );
	}
if( isset($GLOBALS['NTS_FIX_SERVICE']) ){
	$tm2->addFilter( 'service', $GLOBALS['NTS_FIX_SERVICE'] );
	}

if( $order )
{
/* set filters for time manager */
	/* services */
	$filter = $order->getFilter( 'service' );
	if( $filter )
	{
		$tm2->addFilter( 'service', $filter );
	}

	/* resources */
	$filter = $order->getFilter( 'resource' );
	if( $filter )
	{
		$tm2->addFilter( 'resource', $filter );
	}

	/* weekdays */
	$filter = $order->getFilter( 'weekday' );
	if( $filter )
	{
		$tm2->addFilter( 'weekday', $filter );
	}

	/* time */
	$filter = $order->getFilter( 'time' );
	if( $filter )
	{
		$tm2->addFilter( 'time', $filter );
	}

	/* date */
	$filter = $order->getFilter( 'date' );
	if( $filter )
	{
		$tm2->addFilter( 'date', $filter );
	}
}

$NTS_VIEW['tm2'] = $tm2;

class ntsAppointmentRequest {
	var $current;
	var $params = array();
	var $recurring = '';
	var $requiredFields;
	var $panel;

	function ntsAppointmentRequest(){
		$this->panel = 'customer/appointments/request';

		// array( default, required )
		$this->params = array(
			'location'	=> array( 0, 1),
			'resource'	=> array( 0, 1),
			'service'	=> array( 0, 1),
			'date'		=> array( 0, 0),
			'time'		=> array( 0, 1),
			'seats'		=> array( 1, 1),
			'cal'		=> array( '', 0),
			);

		$this->other = array(
			'recurring'			=> '',
			'custom-dates'		=> '',
			'recur-every'		=> '',
			'recur-from'		=> '',
			'recur-to'			=> '',
			'preferred-time'	=> '',
			'reschedule'		=> '',
			'order'				=> '',
			'coupon'			=> '',
			);

		$this->requiredFields = array();
		reset( $this->params );
		foreach( $this->params as $pk => $pa ){
			if( $pa[1] )
				$this->requiredFields[] = $pk;
			}

		$this->init(1);
		}

	function getCoupon()
	{
		global $_NTS;
		$suppliedCoupon = $_NTS['REQ']->getParam( 'coupon' );
		return $suppliedCoupon;
	}

	function getPrices(){
	// return = array( array(justPrice, dueTotal, dueNow, fullPrice, promotions) );
		global $NTS_CURRENT_USER, $_NTS;
		$suppliedCoupon = $this->getCoupon();
		$ntspm =& ntsPaymentManager::getInstance();
		if( ($NTS_CURRENT_USER->getId() > 0) && (! ($NTS_CURRENT_USER->hasRole('admin'))) ){
			$checkBalance = true;
			}
		else {
			$checkBalance = false;
			}

		$showPrice = false;
		$return = array();
		$ready = $this->getReady();

		$availableOrders = array();
		if( $checkBalance ){
			$availableOrders = $NTS_CURRENT_USER->checkOrders( $ready, $this->getOrder() );
			}

		reset( $ready );
		for( $ii = 0; $ii < count($ready); $ii++ ){
			$r = $ready[$ii];

			$justPrice = $ntspm->getPrice( $r, $suppliedCoupon );
			$basePrice = $ntspm->getBasePrice( $r );
			$dueNow = $ntspm->getPrepayAmount( $r, $suppliedCoupon );
			$dueTotal = $justPrice;
			$promotions = $ntspm->getPromotions( $r, $suppliedCoupon );

			if( strlen($justPrice) )
				$showPrice = true;

			if( isset($availableOrders[$ii]) && $availableOrders[$ii] ){
				$return[] = array( $justPrice, 0, 0, $basePrice, $promotions );
				}
			else {
				$return[] = array( $justPrice, $dueTotal, $dueNow, $basePrice, $promotions );
				}
			}
		if( ! $showPrice )
			$return = array();
		return $return;
		}

	function getPanel(){
		return $this->panel;
		}

	function getReschedule(){
		$return = null;
		$reschId = $this->getOther( 'reschedule' );
		if( $reschId ){
			$return = ntsObjectFactory::get( 'appointment' );
			$return->setId( $reschId );
			}
		return $return;
		}

	function getOrder(){
		$return = null;
		$orderId = $this->getOther( 'order' );
		if( $orderId ){
			$return = ntsObjectFactory::get( 'order' );
			$return->setId( $orderId );
			}
		return $return;
		}

	function getParams(){
		$joins = array();

		$skipJoins = array();
	/* get all keys first */
		reset( $this->current );
		foreach( $this->current as $ca ){
			reset( $ca );
			foreach( $ca as $k => $v ){
				if( ! isset($joins[$k]) ){
					$joins[$k] = array();
					$skipJoins[$k] = 1;
					}
				}
			}

	/* populate keys */
		$allKeys = array_keys( $joins );
		reset( $this->current );
		foreach( $this->current as $ca ){
			reset( $allKeys );
			foreach( $allKeys as $k ){
				switch( $k ){
					case 'seats':
						$thisVal = isset($ca[$k]) ? $ca[$k] : $this->current[0][$k];
						break;
					default:
						$thisVal = isset($ca[$k]) ? $ca[$k] : 0;
						break;
					}

				$joins[$k][] = $thisVal;
				if( $thisVal )
					unset( $skipJoins[$k] );
				}
			}

	/* unset joins if all are zero */
		$skipKeys = array_keys( $skipJoins );
		foreach( $skipKeys as $k ){
			unset( $joins[$k] );
			}

		if( isset($joins['time']) ){
			// reset date if all times are set and not zero
			if( count($joins['time']) == count($this->current) ){
				reset( $joins['time'] );
				$unsetDate = true;
				foreach( $joins['time'] as $t ){
					if( ! $t ){
						$unsetDate = false;
						break;
						}
					}
				if( $unsetDate ){
					if( isset($joins['date']) ){
						unset($joins['date']);
						}
					}
				}
			}

		reset( $joins );
		$saveOn = array();
		foreach( $joins as $key => $values ){
			$value = join( '-', $values );
			$saveOn[ $key ] = $value;
			}

		reset( $this->other );
		foreach( $this->other as $k => $v ){
			if( strlen($v) ){
				$saveOn[ $k ] = $v;
				}
			}
		return $saveOn;
		}

	function sort(){
		usort(
			$this->current,
			create_function(
				'$a, $b',
				'
				if( isset($a["time"]) && isset($b["time"]) ){
					return ($a["time"] - $b["time"]);
					}
				elseif( isset($a["date"]) && isset($b["date"]) ){
					return ($a["date"] - $b["date"]);
					}
				else {
					return 0;
					}
				'
				)
			);
		$this->save();
		}

	function save(){
	/* sort by time */
		reset( $this->current );

		$saveOn = $this->getParams();
		ntsView::resetPersistentParams( $this->getPanel() );
		ntsView::setPersistentParams( $saveOn, $this->getPanel() );

	/* populate virtual apps */
		global $NTS_VIRTUAL_APPOINTMENTS;
		$ready = $this->getReady();
		$NTS_VIRTUAL_APPOINTMENTS = array();

		foreach( $ready as $r ){
			$va = array(
				'location_id'	=> $r['location'],
				'resource_id'	=> $r['resource'],
				'service_id'	=> $r['service'],
				'seats'			=> $r['seats'],
				'starts_at'		=> $r['time'],
				);
			$NTS_VIRTUAL_APPOINTMENTS[] = $va;
			}
		}

	function add(){
		$newIndex = count( $this->current );
		$this->current[$newIndex] = array();
		reset( $this->params );
		foreach( $this->params as $pn => $pa ){
			$this->current[$newIndex][$pn] = $pa[0];
			}
		$this->save();
		}

	function duplicate( $fromIndex = 1, $newIndex = 0 ){
		if( ! $newIndex )
			$newIndex = count( $this->current ) + 1;
		$this->current[$newIndex - 1] = $this->current[ $fromIndex - 1 ];
		$this->save();
		}

	function init( $index = 1 ){
		global $_NTS;
		$this->current = array();

		if( ! isset($this->current[$index-1]) ){
			$this->current[$index-1] = array();
			reset( $this->params );
			foreach( $this->params as $pn => $pa ){
				$this->current[$index-1][$pn] = $pa[0];
				}
			}

		reset( $this->params );
		$noSave = true;
		foreach( $this->params as $pn => $pv ){
			$suppliedValue = $_NTS['REQ']->getParam( $pn );
			if( strlen($suppliedValue) ){
				$suppliedValue = explode( '-', $suppliedValue );
				$count = count($suppliedValue);
				for( $i = 1; $i <= $count; $i++ ){
					$this->setSelected( $i, $pn, $suppliedValue[$i - 1], $noSave );
					}
				}
			}

		reset( $this->other );
		foreach( $this->other as $k => $v ){
			$this->setOther( $k, $_NTS['REQ']->getParam($k) ); 
			}

		$reschedule = $this->getReschedule();
		if( $reschedule ){
			$this->setSelected( 1, 'service', $reschedule->getProp('service_id') );
			}
		$this->save();
		}

	function setSelected( $index, $what, $value, $noSave = false ){
		if( ! isset($this->current[$index-1]) ){
			$this->current[$index-1] = array();
			}
		$this->current[$index-1][$what] = $value;

		if( ! $noSave ){
			if( $what == 'resource' ){
				$this->save(1);
				}
			else {
				$this->save();
				}
			}
		}

	function resetSelected( $index, $what ){
		if( $index ){
			unset( $this->current[$index-1][$what] );
			}
		else {
			$count = count($this->current);
			for( $i = 0; $i < $count; $i++ ){
				unset( $this->current[$i][$what] );
				}
			}
		$this->save();
		}

	function getKeys(){
		$return = array();
		for( $i = 0; $i < count($this->current); $i++ ){
			$return = array_merge($return, array_keys( $this->current[$i] ) );
			}
		$return = array_unique( $return );
		return $return;
		}
	
	function resetAll( $index ){
		if( $index == 1 ){
			// check if it's only one
			if( count($this->current) > 1 ){
				$keys = $this->getKeys();
				reset( $keys );
				foreach( $keys as $k ){
					if( ! isset($this->current[1][$k]) )
						$this->current[1][$k] = $this->current[0][$k];
					}
				}
			}
		array_splice( $this->current, $index-1, 1 );
		$this->save();
		}

	function setOther( $what, $value ){
		$this->other[$what] = $value;
		$this->save();
		}
	function resetOther( $what ){
		unset( $this->other[$what] );
		$this->save();
		}
	function getOther( $what ){
		$return = '';
		$return = ( isset($this->other[$what]) ) ? $this->other[$what] : '';
		return $return;
		}

	function getAllOptionsFor( $what ){
		$return = array();
		$current = $this->getCurrent();
		$appsCount = count( $current );
		for( $i = 1; $i <= $appsCount; $i++ ){
			$option = $this->getSelectedValue( $i, $what );
			if( $option && (! in_array($option, $return) ) ){
				$return[] = $option;
				}
			}
		return $return;
		}

	function getSelectedValue( $index, $what ){
		if( $index != -1 ){
			if( isset($this->current[$index-1][$what]) ){
				$return = $this->current[$index-1][$what];
				}
			elseif(isset($this->current[0][$what])){
				$return = $this->current[0][$what];
				}
			else {
				$return = null;
				}

			if( (! $return) && ($what == 'date') && isset($this->current[$index-1]['time']) ){
				if( $this->current[$index-1]['time'] ){
					global $NTS_VIEW;
					$t = $NTS_VIEW['t'];
					$t->setTimestamp( $this->current[$index-1]['time'] );
					$return = $t->formatDate_Db();
					}
				}
			}
		else { // reschedule
			$reschedule = $this->getReschedule();
			switch( $what ){
				case 'location':
					$return = $reschedule->getProp( 'location_id' );
					break;
				case 'service':
					$return = $reschedule->getProp( 'service_id' );
					break;
				case 'resource':
					$return = $reschedule->getProp( 'resource_id' );
					break;
				case 'time':
					$return = $reschedule->getProp( 'starts_at' );
					break;
				case 'price':
					$return = $reschedule->getProp( 'price' );
					break;
				case 'date':
					global $NTS_VIEW;
					$t = $NTS_VIEW['t'];
					$ts = $reschedule->getProp( 'starts_at' );
					$t->setTimestamp( $ts );
					$return = $t->formatDate_Db();
					break;
				default:
					$return = null;
					break;
				}
			}
		return $return;
		}

	function getSelected( $index, $what ){
		$return = null;
		$objId = $this->getSelectedValue( $index, $what );
		if( $objId && ($objId != 'a') ){
			switch( $what ){
				case 'location':
				case 'resource':
				case 'service':
					$return = ntsObjectFactory::get( $what );
					$return->setId( $objId );
					break;
				default:
					$return = $objId;
					break;
				}
			}
		return $return;
		}

	function whatNext(){
		$conf =& ntsConf::getInstance();
		$flow = $conf->get('appointmentFlow');

		$what = '';
		$current = $this->getCurrent();
		if( $current ){
			$foundEmpty = false;
			reset( $current );
			foreach( $current as $cr ){
				reset( $flow );
				foreach( $flow as $ff ){
					if( isset($cr[$ff[0]]) && (! $cr[$ff[0]]) ){
//					if( ! isset($cr[$ff[0]]) || (! $cr[$ff[0]]) ){
						$what = $ff[0];
						$foundEmpty = true;
						break;
						}
					}
				if( $foundEmpty )
					break;
				}
			}
		else {
			$ready = $this->getReady();
			if( $ready ){
				$what = 'confirm';
				}
			else {
				$what = $flow[0][0];
				}
			}

		if( ! $what ){
			$what = 'confirm';
			}

		return $what;
		}

	function getCurrentIndexes(){
		$return = array();
		$appsCount = count( $this->current );

		for( $i = 1; $i <= $appsCount; $i++ ){
			$takeThis = false;
			reset( $this->requiredFields );
			foreach( $this->requiredFields as $rf ){
				$thisValue = $this->getSelectedValue( $i, $rf );
				if( ! $thisValue ){
					$takeThis = true;
					break;
					}
				}
			if( $takeThis )
				$return[] = $i;
			}
		return $return;
		}

	function getReadyIndexes(){
		$return = array();
		$currentIndexes = $this->getCurrentIndexes();

		$allCount = count( $this->current );
		for( $i = 1; $i <= $allCount; $i++ ){
			if( ! in_array($i, $currentIndexes) )
				$return[] = $i;
			}

		return $return;
		}

	function getCurrent(){
		$return = array();
		$indexes = $this->getCurrentIndexes();
		foreach( $indexes as $i ){
			$return[] = $this->getOne( $i );
			}
		return $return;
		}

	function getReady(){
		$return = array();
		$indexes = $this->getReadyIndexes();
		foreach( $indexes as $i ){
			$return[] = $this->getOne( $i );
			}
		return $return;
		}

	function getOne( $index ){
		$keys = $this->getKeys();
		$return = array();
		foreach( $keys as $what ){
			$option = $this->getSelectedValue( $index, $what );
			$return[ $what ] = $option;
			}
		return $return;
		}

	function getCount( $what, $which = 'current' ){
		$current = ( $which == 'current' ) ? $this->getCurrent() : $this->getReady();

		$return = 0;
		$allCount = count( $current );
		$options = array();
		for( $i = 0; $i < $allCount; $i++ ){
			if( isset($current[$i][$what]) ){
				if( ! in_array($current[$i][$what], $options) )
					$options[] = $current[$i][$what];
				}
			}
		$return = count( $options );
		return $return;
		}

	function delete( $index ){
		if( ! is_array($index) )
			$index = array( $index );
		rsort( $index );
		reset( $index );
		foreach( $index as $i )
			array_splice( $this->current, $i-1, 1 );
		$this->save();
		}
	}
?>