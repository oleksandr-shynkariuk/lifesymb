<?php
class ntsUser extends ntsObject {
	function ntsUser(){
		parent::ntsObject( 'user' );
		}

	function setLanguage( $lng )
	{
		$lm =& ntsLanguageManager::getInstance();
		$activeLanguages = $lm->getActiveLanguages();
		reset( $activeLanguages );
		$languageExists = false;
		foreach( $activeLanguages as $l )
		{
			if( $l == $lng )
			{
				$languageExists = true;
				break;
			}
		}
		if( $languageExists)
		{
			$expireIn = time() + 30 * 24 * 60 * 60;
			setcookie( NTS_LANGUAGE_COOKIE_NAME, $lng, $expireIn );

			$this->setProp( '_lang', $lng );
			$cm =& ntsCommandManager::getInstance();
			$cm->runCommand( $this, 'update' );
		}
	}

	function getLanguage()
	{
		$lng = '';
		if( isset($_COOKIE[NTS_LANGUAGE_COOKIE_NAME]) )
		{
			$lng = $_COOKIE[NTS_LANGUAGE_COOKIE_NAME];
		}
		$savedLng = $this->getProp( '_lang' );
		if( $savedLng )
		{
			$lng = $savedLng;
		}

		$lm =& ntsLanguageManager::getInstance(); 
		$activeLanguages = $lm->getActiveLanguages();
		if( ! $activeLanguages )
			$activeLanguages = array( 'en-builtin' );

		if( $lng )
		{
			reset( $activeLanguages );
			$languageExists = false;
			foreach( $activeLanguages as $l )
			{
				if( $l == $lng )
				{
					$languageExists = true;
					break;
				}
			}
			if( ! $languageExists )
				$lng = $activeLanguages[0];
		}
		else
		{
			$lng = $activeLanguages[0];
		}
		return $lng;
	}

/* returns an array of orders that fit to this one */
	function checkOrders( $ready, $suppliedOrder = 0 ){
		if( is_object($suppliedOrder) )
		{
			$suppliedOrder = $suppliedOrder->getId();
		}

		$t = new ntsTime;

		$return = array();
		$pm =& ntsPaymentManager::getInstance();

		for( $ii = 0; $ii < count($ready); $ii++ ){
			$return[ $ii ] = 0;
			}

		$now = time();
		$customerId = $this->getId();
		$where = array();
		$where[] = array(
			'customer_id'	=> array( '=', $customerId ),
			);
		$where[] = 'AND';
		$where[] = array(
			array('valid_to'	=> array( '>', $now )),
			array('valid_to'	=> array( '=', 0 )),
			);
		$where[] = 'AND';
		$where[] = array('is_active' => array( '=', 1 )); 

		$addon = 'ORDER BY (amount+duration+qty) ASC';
		$orders = ntsObjectFactory::find( 'order', $where, $addon );

		reset( $orders );
		$capacity = array();
		foreach( $orders as $order ){
			$capacityInfo = $order->getByArray();
			$capacityInfo['service_type'] = $order->getServiceType();
			$capacityInfo['service_id'] = $order->getServiceId();
			$capacityInfo['type'] = $order->getType();
			$capacityInfo['left'] = $order->getLeft();
			$capacityInfo['rule'] = $order->getRule();
			$capacity[] = $capacityInfo;
			}

		/* ok now check our apps against capacity */
		for( $ii = 0; $ii < count($ready); $ii++ ){
			if( ! isset($ready[$ii]['location_id']) )
				$ready[$ii]['location_id'] = $ready[$ii]['location'];
			if( ! isset($ready[$ii]['resource_id']) )
				$ready[$ii]['resource_id'] = $ready[$ii]['resource'];
			if( ! isset($ready[$ii]['service_id']) )
				$ready[$ii]['service_id'] = $ready[$ii]['service'];

			$service = ntsObjectFactory::get('service');
			$service->setId( $ready[$ii]['service_id'] );
			$duration = $service->getProp('duration');

			$price = $pm->getPrice( $ready[$ii], '' );

			$okOrder = 0;
			for( $jj = 0; $jj < count($capacity); $jj++ ){
			/* location */
				if( $capacity[$jj]['location_id'] && ($capacity[$jj]['location_id'] != $ready[$ii]['location_id']) )
					continue;
				if( 
					$capacity[$jj]['resource_id'] && 
					$ready[$ii]['resource_id'] && 
					($capacity[$jj]['resource_id'] != $ready[$ii]['resource_id'])
					){
					continue;
					}
				
			/* service */
				if( $capacity[$jj]['service_type'] == 'fixed' ){
					if( ! in_array($ready[$ii]['service_id'], $capacity[$jj]['left']) )
						continue;
					}
				else {
//					if( $capacity[$jj]['service_id'] && ($capacity[$jj]['service_id'] != $ready[$ii]['service_id']) )
					if( ( ! in_array(0, $capacity[$jj]['service_id'])) && ( ! in_array($ready[$ii]['service_id'], $capacity[$jj]['service_id'])) )
						continue;
					}

			/* weekday */
				if( isset($capacity[$jj]['rule']['weekday']) )
				{
					$checkThis = TRUE;
					if( isset($ready[$ii]['time']) && $ready[$ii]['time'] )
					{
						$t->setTimestamp($ready[$ii]['time']);
					}
					elseif( isset($ready[$ii]['date']) && $ready[$ii]['date'] )
					{
						$t->setDateDb( $ready[$ii]['date'] );
					}
					else
					{
						$checkThis = FALSE;
					}

					if( $checkThis )
					{
						$weekDay = $t->getWeekday();
						if( ! in_array($weekDay, $capacity[$jj]['rule']['weekday']) )
						{
							continue;
						}
					}
				}

			/* time */
				if( isset($capacity[$jj]['rule']['time']) )
				{
					$checkThis = TRUE;
					if( isset($ready[$ii]['time']) && $ready[$ii]['time'] )
					{
						$t->setTimestamp($ready[$ii]['time']);
					}
					elseif( isset($ready[$ii]['date']) && $ready[$ii]['date'] )
					{
						$t->setDateDb( $ready[$ii]['date'] );
					}
					else
					{
						$checkThis = FALSE;
					}

					if( $checkThis )
					{
						$timeOfDay = $t->getTimeOfDay();
						if( 
							($timeOfDay < $capacity[$jj]['rule']['time'][0]) OR
							( ($timeOfDay + $duration) > $capacity[$jj]['rule']['time'][1] )
							)
						{
							continue;
						}
					}
				}

			/* date */
				if( isset($capacity[$jj]['rule']['date']) )
				{
					$checkThis = TRUE;
					if( isset($ready[$ii]['time']) && $ready[$ii]['time'] )
					{
						$t->setTimestamp($ready[$ii]['time']);
					}
					else
					{
						$checkThis = FALSE;
					}

					if( $checkThis )
					{
						$thisDate = $t->formatDate_Db();
						if( isset($capacity[$jj]['rule']['date']['from']) )
						{
							if( 
								( $thisDate < $capacity[$jj]['rule']['date']['from'] ) OR
								( $thisDate > $capacity[$jj]['rule']['date']['to'] )
								)
							{
								continue;
							}
						}
						else
						{
							if( ! in_array($thisDate, $capacity[$jj]['rule']['date']) )
							{
								continue;
							}
						}
					}
				}

				$okOrder = 0;
				if( $capacity[$jj]['type'] == 'unlimited' ){
					$okOrder = $capacity[$jj]['id'];
					}
				else {
					switch( $capacity[$jj]['service_type'] ){
						case 'fixed':
							for( $kk = 0; $kk < count($capacity[$jj]['left']); $kk++ ){
								$breakThis = FALSE;
								if( $capacity[$jj]['left'][$kk] == $ready[$ii]['service_id'] ){
									$okOrder = $capacity[$jj]['id'];
									array_splice( $capacity[$jj]['left'], $kk, 1 );
									$breakThis = TRUE;
									break;
									}
								if( $breakThis )
									true;
								}
							break;
						case 'one':
							$left = $capacity[$jj]['left'];
							switch( $capacity[$jj]['type'] ){
								case 'qty':
									if( $left >= $ready[$ii]['seats'] ){
										$okOrder = $capacity[$jj]['id'];
										$capacity[$jj]['left'] = $capacity[$jj]['left'] - $ready[$ii]['seats'];
										}
									break;
								case 'duration':
									if( $left >= $duration ){
										$okOrder = $capacity[$jj]['id'];
										$capacity[$jj]['left'] = $capacity[$jj]['left'] - $duration;
										}
									break;
								case 'amount':
									if( $left >= $price ){
										$okOrder = $capacity[$jj]['id'];
										$capacity[$jj]['left'] = $capacity[$jj]['left'] - $price;
										}
									break;
								}
							break;
						}
					}

				if( $okOrder && $suppliedOrder && ($suppliedOrder != $okOrder) )
				{
					$okOrder = 0;
				}

				if( $okOrder ){
					break;
					}
				}
			if( $okOrder ){
				$returnOrder = ntsObjectFactory::get( 'order' );
				$returnOrder->setId( $okOrder );
				$return[ $ii ] = $returnOrder;
				}
			}
		return $return;
		}

	function getStatus(){
		$alert = 0;
		$cssClass = '';
		$message = '';
		$return = array( $alert, $cssClass, $message );

		$restrictions = $this->getProp( '_restriction' );		

		if( in_array('email_not_confirmed', $restrictions) ){
			$alert = 1;
			$cssClass = 'alert';
			$message = M('Email Not Confirmed');
			}
		elseif( in_array('not_approved', $restrictions) ){
			$alert = 1;
			$cssClass = 'alert';
			$message = M('Not Approved');
			}
		elseif( in_array('suspended', $restrictions) ){
			$alert = 1;
			$cssClass = 'alert';
			$message = M('Suspended');
			}
		else {
			$alert = 0;
			$cssClass = 'ok';
			$message = M('Active');
			}

		$return = array( $alert, $cssClass, $message );
		return $return;
		}
		
	function setId( $id, $load = true ){
		if( $id == -111 ){
			$this->id = $id;
			$this->setProp( '_role', array('admin') );
			$this->setProp( 'username', '-superadmin-' );
			$this->setProp( 'first_name', '-superadmin-' );

			global $NTS_CURRENT_VERSION_NUMBER;
			if( $NTS_CURRENT_VERSION_NUMBER >= 4500 ){
			// resource schedules
				$resApps = array();
				$resSchedules = array();
				$allResourcesIds = ntsObjectFactory::getAllIds( 'resource' );
				reset( $allResourcesIds );
				foreach( $allResourcesIds as $resId ){
					$resApps[ $resId ] = array( 'view' => 1, 'edit' => 1, 'modify' => 1 );
					$resSchedules[ $resId ] = array( 'view' => 1, 'edit' => 1 );
					}
				$this->setAppointmentPermissions( $resApps );
				$this->setSchedulePermissions( $resSchedules );
				}
			return;
			}
		parent::setId( $id, $load );
		}

	function getAppointmentPermissions(){
		$return = array();
		$raw = $this->getProp( '_resource_apps' );
		reset( $raw );
		foreach( $raw as $resId => $accLevel ){
			$perm = array( 'view' => 0, 'edit' => 0, 'notified' => 0 );
			if( $accLevel & 1 ){
				$perm['view'] = 1;
				}
			if( $accLevel & 2 ){
				$perm['edit'] = 1;
				}
			if( $accLevel & 4 ){
				$perm['notified'] = 1;
				}
			$return[ $resId ] = $perm;
			}
		return $return;
		}

	function setAppointmentPermissions( $pa ){
		$return = array();
		reset( $pa );
		foreach( $pa as $resId => $perm ){
			if( ! $perm )
				continue;
			$final = 0;
			if( isset($perm['view']) && $perm['view'] ){
				$final += 1;
				}
			if( isset($perm['edit']) && $perm['edit'] ){
				$final += 2;
				if( ! (isset($perm['view']) && $perm['view']) ){
					$final += 1; // also set view
					}
				}
			if( isset($perm['notified']) && $perm['notified'] ){
				$final += 4;
				if( ! (isset($perm['view']) && $perm['view']) ){
					if( ! (isset($perm['edit']) && $perm['edit']) ){
						$final += 1; // also set view
						}
					}
				}
			$return[ $resId ] = $final;
			}
		$this->setProp( '_resource_apps', $return );
		}

	function getSchedulePermissions(){
		$return = array();
		$raw = $this->getProp( '_resource_schedules' );
		reset( $raw );
		foreach( $raw as $resId => $accLevel ){
			$perm = array( 'view' => 0, 'edit' => 0 );
			if( $accLevel & 1 ){
				$perm['view'] = 1;
				}
			if( $accLevel & 2 ){
				$perm['edit'] = 1;
				}
			$return[ $resId ] = $perm;
			}
		return $return;
		}

	function setSchedulePermissions( $pa ){
		$return = array();
		reset( $pa );
		foreach( $pa as $resId => $perm ){
			if( ! $perm )
				continue;
			$final = 0;
			if( isset($perm['view']) && $perm['view'] ){
				$final += 1;
				}
			if( isset($perm['edit']) && $perm['edit'] ){
				$final += 2;
				if( ! (isset($perm['view']) && $perm['view']) ){
					$final += 1; // also set view
					}
				}
			$return[ $resId ] = $final;
			}
		$this->setProp( '_resource_schedules', $return );
		}

	function getProp( $pName, $unserialize = FALSE ){
		$return = parent::getProp( $pName, $unserialize );

		switch( $pName ){
			case '_agenda_fields':
				if( isset($_COOKIE['nts_agenda_fields']) ){
					$return = unserialize(stripslashes($_COOKIE['nts_agenda_fields'])); 
					}
				break;
			case '_calendar_field':
				if( isset($_COOKIE['nts_calendar_field']) ){
					$return = $_COOKIE['nts_calendar_field']; 
					}
				break;
			case '_default_calendar':
				if( isset($_COOKIE['nts_default_calendar']) ){
					$return = $_COOKIE['nts_default_calendar']; 
					}
				break;
			case '_default_apps_view':
				if( isset($_COOKIE['nts_default_apps_view']) ){
					$return = $_COOKIE['nts_default_apps_view']; 
					}
				break;
			case '_resource_apps':
			case '_resource_schedules':
				if( ! is_array($return) )
					$return = array();
				foreach( $return as $resId => $accLevel ){
					if( ! $accLevel ){
						unset( $return[$resId] );
						}
					}
			break;
			}
		return $return;
		}

	function hasRole( $role ){
		if( ! is_array($role) )
			$role = array( $role );
		$myRoles = $this->getProp( '_role' );
		$return = array_intersect( $myRoles, $role ) ? true : false;
		return $return;
		}

	function getTimezone(){
		$return = $this->getProp('_timezone');
		if( $this->getId() == 0 ){
			if( isset($_SESSION['nts_timezone']) ){
				if( NTS_ENABLE_TIMEZONES > 0 )
					$return = $_SESSION['nts_timezone'];
				else
					unset( $_SESSION['nts_timezone'] );
				}
			}
		return $return;
		}

	function getPanelPermissions(){
		$return = array();
		$apn =& ntsAdminPermissionsManager::getInstance();
		$allPanels = $apn->getPanels();

		$disabledPanels = $this->getProp( '_disabled_panels' );
		foreach( $allPanels as $p ){
			if( ! in_array($p, $disabledPanels) )
				$return[] = $p;
			}
		return $return;
		}

	function isPanelDisabled( $checkPanel ){
		$return = false;
		$disabledPanels = $this->getProp( '_disabled_panels' );

		global $NTS_SKIP_PANELS;
		if( $NTS_SKIP_PANELS ){
			$disabledPanels = array_merge( $disabledPanels, $NTS_SKIP_PANELS );
			}

		reset( $disabledPanels );
		foreach( $disabledPanels as $dp ){
			if( substr($checkPanel, 0, strlen($dp)) == $dp ){
				// not allowed
				$return = true;
				break;
				}
			}
		return $return;
		}
	}
?>