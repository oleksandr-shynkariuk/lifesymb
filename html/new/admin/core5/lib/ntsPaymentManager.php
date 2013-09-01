<?php
class ntsPaymentManager {
	var $promotions = array();
	var $couponCountLeft = array();
	var $gotPromotions = array();

	function __construct()
	{
		$om =& objectMapper::getInstance();
		if( $om->isClassRegistered('promotion') )
		{
			$this->promotions = ntsObjectFactory::getAll('promotion');
		}
	}

	function resetPromotions()
	{
		$this->couponCountLeft = array();
		$this->gotPromotions = array();
	}

	function getRKey( $r )
	{
		$return = array(
			'duration'		=> isset($r['duration']) ? $r['duration'] : 0,
			'location_id'	=> isset($r['location_id']) ? $r['location_id'] : 0,
			'resource_id'	=> isset($r['resource_id']) ? $r['resource_id'] : 0,
			'service_id'	=> isset($r['service_id']) ? $r['service_id'] : 0,
			'starts_at'		=> isset($r['starts_at']) ? $r['starts_at'] : 0,
			);
		return $return;
	}

	function getPromotions( $r = array(), $suppliedCoupon = '', $requireCoupon = FALSE )
	{
		$return = array();
		$r = $this->_parseReady( $r );

		$key = $this->getRKey($r);
		$key['suppliedCoupon'] = $suppliedCoupon;
		$key['requireCoupon'] = $requireCoupon;
		$rKey = serialize( $key );

		if( isset($this->gotPromotions[$rKey]) )
		{
			return $this->gotPromotions[$rKey];
		}

		reset( $this->promotions );
		foreach( $this->promotions as $cp )
		{
			$on = TRUE;
			$rule = $cp->getRule();
			if( ! $rule )
				$rule = array();
			foreach( $rule as $key => $options )
			{
				if( ! $on )
				{
					break;
				}

				$on = FALSE;
				switch( $key )
				{
					case 'location':
						if( isset($r['location_id']) && in_array($r['location_id'], $options) )
						{
							$on = TRUE;
						}
						break;

					case 'resource':
						if( isset($r['resource_id']) && in_array($r['resource_id'], $options) )
						{
							$on = TRUE;
						}
						break;

					case 'service':
						if( isset($r['service_id']) && in_array($r['service_id'], $options) )
						{
							$on = TRUE;
						}
						break;

					case 'weekday':
						if( isset($r['starts_at']) )
						{
							$t = new ntsTime;
							$t->setTimestamp( $r['starts_at'] );
							$weekday = $t->getWeekday();
							if( in_array($weekday, $options) )
							{
								$on = TRUE;
							}
						}
						break;

					case 'time':
						if( isset($r['starts_at']) )
						{
							$from = $options[0];
							$to = $options[1];

							$t = new ntsTime;
							$t->setTimestamp( $r['starts_at'] );
							$timeOfDay = $t->getTimeOfDay();
							if( ($timeOfDay >= $from) && ( ($timeOfDay + $r['duration']) <= $to) )
							{
								$on = TRUE;
							}
						}
						break;

					case 'date':
						if( isset($r['starts_at']) )
						{
							$t = new ntsTime;
							$t->setTimestamp( $r['starts_at'] );
							$thisDate = $t->formatDate_Db();
							if( isset($options['from']) )
							{
								if( ($thisDate >= $options['from']) && ($thisDate <= $options['to']) )
								{
									$on = TRUE;
								}
							}
							else
							{
								if( in_array($thisDate, $options) )
								{
									$on = TRUE;
								}
							}
						}
						break;
				}
			}
			if( ! $on )
			{
				continue;
			}

		/* check coupons */
			$codes = $cp->getCouponCodes();

			if( $suppliedCoupon )
			{
				if( $codes && (! in_array($suppliedCoupon, $codes)) )
				{
					continue;
				}
				if( $requireCoupon && (! $codes) )
				{
					continue;
				}
			}
			else
			{
				if( $requireCoupon )
				{
					if( ! $codes )
					{
						continue;
					}
				}
				else
				{
					if( $codes && (! in_array($suppliedCoupon, $codes)) )
					{
						continue;
					}
				}
			}

		if( $codes && $suppliedCoupon && (! $requireCoupon) )
		{
			if( ! isset($this->couponCountLeft[$suppliedCoupon]) )
			{
				$thisCoupon = NULL;
				$coupons = $cp->getCoupons();
				reset( $coupons );
				foreach( $coupons as $cpn )
				{
					if( $suppliedCoupon == $cpn->getProp('code') )
					{
						$thisCoupon = $cpn;
						break;
					}
				}
				if( ! $thisCoupon )
					continue;
				$useLimit = $thisCoupon->getProp('use_limit');
				if( $useLimit )
				{
					$this->couponCountLeft[$suppliedCoupon] = $useLimit;
					$alreadyUsed = $thisCoupon->getUseCount();
					$this->couponCountLeft[$suppliedCoupon] = $this->couponCountLeft[$suppliedCoupon] - $alreadyUsed;
				}
				else
				{
					$this->couponCountLeft[$suppliedCoupon] = -1;
				}
			}

			if( $this->couponCountLeft[$suppliedCoupon] > -1 )
			{
				if( $this->couponCountLeft[$suppliedCoupon] <= 0 )
					continue;
				$this->couponCountLeft[$suppliedCoupon]--;
			}
			$cp->setProp('coupon', $suppliedCoupon );
		}
		$return[] = $cp;
		}

	$this->gotPromotions[$rKey] = $return;
	return $this->gotPromotions[$rKey];
	}

	function getBasePrice( $r = array() )
	{
		$r = $this->_parseReady( $r );
		$return = '';
		if( ! isset($r['service_id']) )
		{
			return $return;
		}

		$service = ntsObjectFactory::get( 'service' );
		$service->setId( $r['service_id'] );
		$return = $service->getProp('price');
		if( ! strlen($return) )
		{
			return $return;
		}
		return $return;
	}

	function getPrice( $r, $coupon )
	{
		$r = $this->_parseReady( $r );

		$return = $this->getBasePrice( $r );
		if( ! strlen($return) )
		{
			return $return;
		}

	/* promotions */
	$promotions = $this->getPromotions( $r, $coupon );
	reset( $promotions );
	foreach( $promotions as $cp )
	{
		$sign = $cp->getSign();
		$measure = $cp->getMeasure();
		$amount = $cp->getAmount();

		if( $measure == '%' )
		{
			$amount = round( ($amount/100) * $return, 2 );
		}

		if( $sign == '-' )
		{
			$return = $return - $amount;
		}
		else
		{
			$return = $return + $amount;
		}	
	}

	/* plugins */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		$pluginFiles = array();
		reset( $activePlugins );
		foreach( $activePlugins as $plg ){
			$f = $plm->getPluginFolder( $plg ) . '/getPrice.php';
			if( file_exists($f) )
				$pluginFiles[] = $f;
			}
		reset( $pluginFiles );
		foreach( $pluginFiles as $f ){
			require( $f );
			}
		
		return $return;
	}

	function _parseReady( $r = array() )
	{
		$return = array();
		$process = array(
			'location'	=> 'location_id',
			'resource'	=> 'resource_id',
			'service'	=> 'service_id',
			'time'		=> 'starts_at',
			);

		foreach( array_keys($r) as $k )
		{
			if( isset($process[$k]) )
			{
				$return[$process[$k]] = $r[$k];
			}
			else
			{
				$return[$k] = $r[$k];
			}
		}
		
		if( (! isset($return['duration'])) && ( isset($return['starts_at']) && isset($return['service_id']) ) )
		{
			$service = ntsObjectFactory::get( 'service', $return['service_id'] );
			$duration = $service->getProp( 'duration' );
			$return['duration'] = $duration;
		}
		ksort( $return );
		return $return;
	}

	function getPrepayAmount( $r = array(), $coupon )
	{
		$r = $this->_parseReady( $r );

		$price = $this->getPrice( $r, $coupon );
		$service = ntsObjectFactory::get( 'service' );
		$service->setId( $r['service_id'] );
		$prepay = $service->getPrepay();
		$dueNow = 0;
		if( $price ){
			if( substr($prepay, -1) == '%' ){
				$percent = substr($prepay, 0, -1) / 100;
				$dueNow = $price * $percent;
				}
			else {
				$dueNow = $prepay;
				}
			}
		return $dueNow;
	}

	function getTaxRate( $item )
	{
		$return = 0;
		$ntsconf =& ntsConf::getInstance();
		$taxRate = $ntsconf->get('taxRate');

		$className = $item->getClassName();
		switch( $className )
		{
			case 'appointment':
			case 'order':
			case 'pack':
			case 'service':
			default:
				$return = $taxRate; 
				break;
		}
		return $return;
	}

	function getInvoicesOfCustomer( $customerId ){
		$ids = array();
		$where = array(
			array(
				'meta_name'		=> array( '=', '_appointment' ),
				'obj_class'		=> array( '=', 'invoice' ),
				'meta_value'	=> array( 'IN', "(SELECT id FROM {PRFX}appointments WHERE customer_id = $customerId)", 1 ),
				),
			array(
				'meta_name'		=> array( '=', '_order' ),
				'obj_class'		=> array( '=', 'invoice' ),
				'meta_value'	=> array( 'IN', "(SELECT id FROM {PRFX}orders WHERE customer_id = $customerId)", 1 ),
				)
			);

		$ntsdb =& dbWrapper::getInstance();
		$result = $ntsdb->select( 'obj_id', 'objectmeta', $where );
		while( $i = $result->fetch() ){
			$ids[ $i['obj_id'] ] = 1;
			}
		$return = array_keys($ids);
		return $return;
		}
	
	function makeInvoices( $items, $forceAmount = 0, $now = 0 ){
		$return = array();
		if( ! $now )
			$now = time();

		$makeInvoices = array(); // array( $item, $amount, array(taxrate, taxname) );

		$invoiceAmount = 0;
		reset( $items );
		foreach( $items as $item ){
			$className = $item->getClassName();
			switch( $className ){
				case 'appointment':
					$serviceId = $item->getProp( 'service_id' );
					$service = ntsObjectFactory::get( 'service' );
					$service->setId( $serviceId );
					$startsAt = $item->getProp( 'starts_at' );

					if( $forceAmount )
					{
						if( ! isset($makeInvoices[$startsAt]) )
							$makeInvoices[$startsAt] = array();
						$makeInvoices[$startsAt][] = array( $item, $forceAmount );
					}
					else
					{
						$couponCode = '';
						$promotions = $item->getProp('_promotion');
						if( $promotions )
						{
							reset( $promotions );
							foreach( $promotions as $promId => $couponCode )
							{
								if( $couponCode )
								{
									break;
								}
							}
						}

						$totalPrice = $this->getPrice( $item->getByArray(), $couponCode );
						$totalPriceReal = $this->getPrice( $item->getByArray(), '' );
						$totalDiscount = ( $totalPriceReal - $totalPrice );
						$prepayAmount = $this->getPrepayAmount( $item->getByArray(), $couponCode );
						$prepayDiscount = round( ($prepayAmount/$totalPrice)*$totalDiscount, 2 );

						$thisPayments = array();
						if( $prepayAmount ){
							if( ! isset($makeInvoices[$now]) )
								$makeInvoices[$now] = array();
							$makeInvoices[$now][] = array( $item, $prepayAmount, $prepayDiscount );
							}
						if( $totalPrice > $prepayAmount ){
							$startsAt = $item->getProp( 'starts_at' );
							if( ! isset($makeInvoices[$startsAt]) )
								$makeInvoices[$startsAt] = array();
							$makeInvoices[$startsAt][] = array( $item, ($totalPrice - $prepayAmount), ($totalDiscount - $prepayDiscount) );
							}
					}
					break;

				case 'order':
					$thisAmount = 0;
					if( $forceAmount ){
						$thisAmount = $forceAmount;
						}
					else {
						$packId = $item->getProp( 'pack_id' );
						$pack = ntsObjectFactory::get( 'pack' );
						$pack->setId( $packId );
						$thisAmount = $pack->getProp('price');
						}
					if( ! isset($makeInvoices[$now]) )
						$makeInvoices[$now] = array();
					$makeInvoices[$now][] = array( $item, $thisAmount, 0 );
					break;
				}
			}

		$cm =& ntsCommandManager::getInstance();

	/* check if we have specific Paypal emails for every resource */	
		$finalMakeInvoices = array();

		$pgm =& ntsPaymentGatewaysManager::getInstance();
		$gateways = $pgm->getActiveGateways();
		$isPaypal = in_array('paypal', $gateways);
		if( $isPaypal ){
			$paymentGatewaySettings = $pgm->getGatewaySettings( 'paypal' );
			$mainPaypal = $paymentGatewaySettings['email'];
			}

		reset( $makeInvoices );
		foreach( $makeInvoices as $due => $items ){
			if( $isPaypal ){
				$byPaypal = array();

				reset( $items );
				foreach( $items as $ia ){
					$item = $ia[0];
					$itemAmount = $ia[1];
					$itemDiscount = isset($ia[2]) ? $ia[2] : 0;

					$resourceId = $item->getProp('resource_id');
					$resource = ntsObjectFactory::get( 'resource' );
					$resource->setId( $resourceId );
					$thisPaypal = $resource->getProp( '_paypal' );
					if( $thisPaypal ){
						if( ! isset($byPaypal[$thisPaypal]) )
							$byPaypal[$thisPaypal] = array();
						$byPaypal[$thisPaypal][] = array( $item, $itemAmount, $itemDiscount );
						}
					else {
						if( ! isset($byPaypal[$mainPaypal]) )
							$byPaypal[$mainPaypal] = array();
						$byPaypal[ $mainPaypal ][] = array( $item, $itemAmount, $itemDiscount );
						}
					}
				reset( $byPaypal );
				foreach( $byPaypal as $paypal => $items ){
					$finalMakeInvoices[] = array( $due, $items );
					}
				}
			else {
				$finalMakeInvoices[] = array( $due, $items );
				}
			}

		for( $jj = 0; $jj < count($finalMakeInvoices); $jj++ ){
			list( $due, $items ) = $finalMakeInvoices[ $jj ];
			$params = array(
				'_appointment'	=> array(),
				'_order'		=> array(),
				'_item'			=> array(),
				);
			$invoiceAmount = 0;
			reset( $items );
			foreach( $items as $ia ){
				$item = $ia[0];
				$itemAmount = $ia[1];
				$itemDiscount = isset($ia[2]) ? $ia[2] : 0;

				$tax = $this->getTaxRate( $item );
				$className = $item->getClassName();
				switch( $className ){
					case 'appointment':
						$params['_appointment'][$item->getId()] = $itemAmount;
						break;
					case 'order':
						$params['_order'][$item->getId()] = $itemAmount;
						break;
					}
				if( $tax )
					$params['_tax'][ $className . ':' . $item->getId() ] = $tax;
				if( $itemDiscount )
					$params['_discount'][ $className . ':' . $item->getId() ] = $itemDiscount;

				$invoiceAmount += $itemAmount;
				}

			$invoice = ntsObjectFactory::get( 'invoice' );
			$invoice->setProp( 'amount', $invoiceAmount );
			$invoice->setProp( 'due_at', $due );

		/* invoice items */
			if( $params['_appointment'] )
				$invoice->setProp( '_appointment', $params['_appointment'] );
			if( $params['_order'] )
				$invoice->setProp( '_order', $params['_order'] );
			if( isset($params['_tax']) && $params['_tax'] )
				$invoice->setProp( '_tax', $params['_tax'] );
			if( isset($params['_discount']) && $params['_discount'] )
				$invoice->setProp( '_discount', $params['_discount'] );

			$cm->runCommand( $invoice, 'create' );
			$invoiceId = $invoice->getId();

			$return[] = $invoice;
			}

	/* plugins */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		$transactionFiles = array();
		reset( $activePlugins );
		foreach( $activePlugins as $plg ){
			$trf = $plm->getPluginFolder( $plg ) . '/makeInvoices.php';
			if( file_exists($trf) )
				$transactionFiles[] = $trf;
			}
		reset( $transactionFiles );
		foreach( $transactionFiles as $trf ){
			require( $trf );
			}

		return $return;
		}

	function updateInvoice( $invoice ){
		$return = true;
	/* plugins */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		$transactionFiles = array();
		reset( $activePlugins );
		foreach( $activePlugins as $plg ){
			$trf = $plm->getPluginFolder( $plg ) . '/updateInvoice.php';
			if( file_exists($trf) )
				$transactionFiles[] = $trf;
			}
		reset( $transactionFiles );
		foreach( $transactionFiles as $trf ){
			require( $trf );
			}

		return $return;
		}

	function getTransactionsOfInvoice( $invoiceId ){
		$invoice = ntsObjectFactory::get( 'invoice' );
		$invoice->setId( $invoiceId );

	/* plugins */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		$transactionFiles = array();
		reset( $activePlugins );
		foreach( $activePlugins as $plg ){
			$trf = $plm->getPluginFolder( $plg ) . '/getTransactionsOfInvoice.php';
			if( file_exists($trf) )
				$transactionFiles[] = $trf;
			}
		reset( $transactionFiles );
		foreach( $transactionFiles as $trf ){
			require( $trf );
			}

		if( $transactionFiles ){
			ntsObjectFactory::clearCache( 'invoice', $invoiceId );
			ntsObjectFactory::clearCache( 'transaction' );
			}

	/* now get them */
		$transactions = $invoice->getTransactions();

		return $transactions;
		}
		
	function deleteTransaction( $transId ){
		$tra = ntsObjectFactory::get('transaction');
		$tra->setId( $transId );
		$amount = $tra->getProp('amount');
		$invoiceId = $tra->getProp( 'invoice_id' );

		$cm =& ntsCommandManager::getInstance();
		$cm->runCommand( $tra, 'delete' );
		if( $cm->isOk() ){
			$return = true;
			}
		else {
			$return = false;
			}

		if( $return ){
		/* plugins */
			$plm =& ntsPluginManager::getInstance();
			$activePlugins = $plm->getActivePlugins();
			$transactionFiles = array();
			reset( $activePlugins );
			foreach( $activePlugins as $plg ){
				$trf = $plm->getPluginFolder( $plg ) . '/deleteTransaction.php';
				if( file_exists($trf) )
					$transactionFiles[] = $trf;
				}
			reset( $transactionFiles );
			foreach( $transactionFiles as $trf ){
				require( $trf );
				}
			}
		return $return;
		}

	function makeTransaction( $fromAccount, $toAccount, $amount, $invoiceId = 0, $paymentInfo = array() ){
		$return = 0;
		$now = time();
		$ntsdb =& dbWrapper::getInstance();

		$what = array(
			'from_account'	=> $fromAccount,
			'to_account'	=> $toAccount,
			'amount'		=> $amount,
			'created_at'	=> $now,
			'invoice_id'	=> $invoiceId,
			);
		$what = array_merge( $what, $paymentInfo );
		if( ! isset($what['amount_net']) )
			$what['amount_net'] = $what['amount'];

		$cm =& ntsCommandManager::getInstance();
		if( $amount ){
			$tra = ntsObjectFactory::get( 'transaction' );
			$tra->setByArray( $what );
			$cm->runCommand( $tra, 'create' );
			$return = $tra->getId();
			}

	/* send payments to interested objects */
		if( $invoiceId ){
			$cm =& ntsCommandManager::getInstance();

			$invoice = ntsObjectFactory::get( 'invoice' );
			$invoice->setId( $invoiceId );
			$totalAmount = $invoice->getTotalAmount();

			$items = $invoice->getItemsObjects();
			reset( $items );
			foreach( $items as $item ){
				$neededAmount = $invoice->getItemAmount( $item );
				$receivedAmount = $amount * ($neededAmount / $totalAmount);
				$cm->runCommand( $item, 'receive_payment', array('amount' => $receivedAmount) );
				}
			}
		
		if( $amount ){
		/* plugins */
			$plm =& ntsPluginManager::getInstance();
			$activePlugins = $plm->getActivePlugins();
			$transactionFiles = array();
			reset( $activePlugins );
			foreach( $activePlugins as $plg ){
				$trf = $plm->getPluginFolder( $plg ) . '/makeTransaction.php';
				if( file_exists($trf) )
					$transactionFiles[] = $trf;
				}
			reset( $transactionFiles );
			foreach( $transactionFiles as $trf ){
				require( $trf );
				}
			}
		return $return;
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsPaymentManager' );
		}
	}
?>