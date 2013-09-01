<?php
class ntsPromotion extends ntsObject 
{
	/*
	location
	resource
	service
	weekday
	time
	*/
	var $_coupons = array();

	function ntsPromotion()
	{
		parent::ntsObject( 'promotion' );
	}

	function getRule()
	{
		$return = $this->getProp( 'rule', TRUE );
		if( ! $return )
		{
			$return = array();
		}
		return $return;
	}

	function setRule( $rule )
	{
		if( isset($rule['date'][0]) )
		{
			sort( $rule['date'] );
		}
		$this->setProp( 'rule', $rule );
	}

	function load()
	{
		parent::load();
	/* load coupons as well */
		$where = array(
			'promotion_id'	=> array('=', $this->getId()),
			);
		$this->_coupons = ntsObjectFactory::find( 'coupon', $where );
	}

	function getTitle()
	{
		return $this->getProp('title');
	}

	function getCoupons()
	{
		return $this->_coupons;
	}

	function getCouponCodes()
	{
		$currentCoupons = $this->getCoupons();
		$currentCodes = array_map( create_function('$a', 'return $a->getProp("code");'), $currentCoupons );
		return $currentCodes;
	}

	function getRuleView()
	{
		$return = array();
		$rule = $this->getRule();

		$objects = array( 
			array('location',	M('Location')),
			array('resource',	M('Bookable Resource')),
			array('service',	M('Service')),
			);
		
		foreach( $objects as $o )
		{
			if( isset($rule[$o[0]]) )
			{
				$view = array();
				$view[] = $o[1];

				$view2 = array();
				reset( $rule[$o[0]] );
				foreach( $rule[$o[0]] as $oid )
				{
					$obj = ntsObjectFactory::get($o[0], $oid);
					$view2[] = ntsView::objectTitle( $obj );
				}
				$view[] = $view2;
				$return[] = $view;
			}
		}

		if( isset($rule['date']) )
		{
			$view = array();
			$view[] = M('Dates');

			$t = new ntsTime();
			if( isset($rule['date']['from']) )
			{
				$t->setDateDb( $rule['date']['from'] );
				$fromView = $t->formatDate();
				$t->setDateDb( $rule['date']['to'] );
				$toView = $t->formatDate();
				$view2 = array( join( ' - ', array($fromView, $toView) ) );
			}
			else
			{
				$view2 = array();
				foreach( $rule['date'] as $date )
				{
					$t->setDateDb( $date );
					$dateView = $t->formatDate();
					$view2[] = $dateView;
				}
			}

			$view[] = $view2;
			$return[] = $view;
		}

		if( isset($rule['weekday']) )
		{
			$view = array();
			$view[] = M('Weekday');

			$view2 = array();
			reset( $rule['weekday'] );
			foreach( $rule['weekday'] as $wdi )
			{
				$view2[] = ntsTime::weekdayLabelShort($wdi);
			}
			$view[] = $view2;
			$return[] = $view;
		}

		if( isset($rule['time']) )
		{
			$view = array();
			$view[] = M('Time');

			$t = new ntsTime();
			$view2 = array( join( ' - ', array($t->formatTimeOfDay($rule['time'][0]), $t->formatTimeOfDay($rule['time'][1])) ) );

			$view[] = $view2;
			$return[] = $view;
		}

		if( ! $rule )
		{
			$return[] = M('Always');
		}
		return $return;
	}

	function getSign()
	{
		$price = $this->getProp('price');
		if( substr($price, 0, 1) == '-' )
			$return = '-';
		else
			$return = '+';
		return $return;
	}

	function getMeasure()
	{
		$price = $this->getProp('price');
		$price = trim( $price );
		if( substr($price, -1) == '%' )
			$return = '%';
		else
			$return = '';
		return $return;
	}

	function getAmount()
	{
		$return = $this->getProp('price');
		$return = trim( $return );
		if( substr($return, 0, 1) == '-' )
			$return = substr($return, 1);
		if( substr($return, -1) == '%' )
			$return = substr($return, 0, -1);
		return $return;
	}

	function getModificationView()
	{
		$thisView = array();
		$sign = $this->getSign();
		if( $this->getMeasure() == '%' )
		{
			$priceView = $this->getAmount() . '%';
		}
		else
		{
			$priceView = ntsCurrency::formatPrice($this->getAmount());
		}
		$thisView[] = $priceView;
		$thisView = $sign . join( ' ', $thisView );
		return $thisView;
	}

	function getUseCount()
	{
		$ntsdb =& dbWrapper::getInstance();
		$code = $this->getProp('code');
		$where = array(
			'meta_name'		=> array('=', '_promotion'),
			'meta_value'	=> array('=', $this->getId()),
			);
		$return = $ntsdb->count( 'objectmeta', $where );
		return $return;
	}
}
?>