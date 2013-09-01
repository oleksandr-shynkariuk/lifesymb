<?php
class ntsPackBase extends ntsObject {
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

	/* copy from ntsPromotion */
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

	function getServiceId(){
		$serviceId = $this->getProp('service_id');
		$serviceType = $this->getServiceType();
		switch( $serviceType ){
			case 'one':
				$serviceId = trim( $serviceId, ',' );
				$return = explode( ',', $serviceId );
//				$return = $serviceId;
				break;
			case 'fixed':
				$serviceId = trim( $serviceId, '-' );
				$return = explode( '-', $serviceId );
				break;
			}
		return $return;
		}

	function getServiceType(){
		$serviceId = $this->getProp('service_id');
		if( strpos($serviceId, '-') === FALSE )
			$return = 'one';
		else
			$return = 'fixed';
		return $return;
		}

	function getType(){
		$return = '';
		$qty = $this->getProp('qty');
		$amount = $this->getProp('amount'); 
		$duration = $this->getProp('duration'); 
		if( $qty ){
			$return = 'qty';
			}
		elseif( $amount ){
			$return = 'amount';
			}
		elseif( $duration ){
			$return = 'duration';
			}
		else {
			$return = 'unlimited';
			}
		return $return;
		}

	function getDetails(){
		$return = '';
		$type = $this->getType();
		$serviceType = $this->getServiceType();
		switch( $serviceType ){
			case 'fixed':
				$serviceId = $this->getServiceId();
				$qty = count($serviceId);
				$return = 'x' . $qty;
				break;
			case 'one':
				switch( $type ){
					case 'unlimited':
						$return = M('Unlimited');
						break;
					case 'qty':
						$qty = $this->getProp('qty');
						$return = 'x' . $qty;
						break;
					case 'amount':
						$amount = $this->getProp('amount');
						$return = ntsCurrency::formatPrice( $amount );
						break;
					case 'duration':
						$duration = $this->getProp('duration');
						$return = ntsTime::formatPeriod( $duration );
						break;
					}
				break;
			}
		return $return;
		}
	}
?>