<?php
class ntsInvoice extends ntsObject {
	function ntsInvoice(){
		parent::ntsObject( 'invoice' );
		}

	function updateItemCost( $itemClass, $itemId, $newCost )
	{
		$ntsdb =& dbWrapper::getInstance();
		$itemClass = strtolower($itemClass);
		$where = array(
			'obj_class'		=> array('=', 'invoice'),
			'obj_id'		=> array('=', $this->getId()),
			'meta_name'		=> array('=', '_' . $itemClass),
			'meta_value'	=> array('=', $itemId),
			);
		$what = array(
			'meta_data'	=> $newCost
			);
		$ntsdb->update( 'objectmeta', $what, $where );

		$pm =& ntsPaymentManager::getInstance();
		$pm->updateInvoice( $this );
	}

	function getItemDiscount( $item )
	{
		$discounts = $this->getProp('_discount');
		$key = $item->getClassName() . ':' . $item->getId();
		$return = isset($discounts[$key]) ? $discounts[$key] : 0;
		return $return;
	}

	function updateItemDiscount( $itemClass, $itemId, $newDiscount )
	{
		$item = ntsObjectFactory::get( $itemClass );
		$item->setId( $itemId );
		$currentCost = $this->getItemAmount( $item );
		$currentDiscount = $this->getItemDiscount( $item );
		if( $newDiscount != $currentDiscount )
		{
			$discounts = $this->getProp('_discount');
			$key = $item->getClassName() . ':' . $item->getId();
			$discounts[ $key ] = $newDiscount;
			$this->setProp( '_discount', $discounts );

			$cm =& ntsCommandManager::getInstance();
			$cm->runCommand( $this, 'update' );

			$newCost = $currentCost + $currentDiscount - $newDiscount;
			$this->updateItemCost( $itemClass, $itemId, $newCost );
		}
	}

	function getItemTax( $item )
	{
		$return = 0;
		$myTaxRate = $this->getItemTaxRate( $item );
		if( $myTaxRate )
		{
			$amount = $this->getItemAmount( $item );
			$return = ntsLib::calcTax( $amount, $myTaxRate );
		}
		return $return;
	}

	function getItemTaxRate( $item )
	{
		$return = 0;

		$className = $item->getClassName();
		$myKey = $item->getClassName() . ':' . $item->getId();
		$myTaxRate = 0;
		$taxes = $this->getProp('_tax');
		if( $taxes && is_array($taxes) )
		{
			reset( $taxes );
			foreach( $taxes as $key => $taxRate )
			{
				if( $key == $myKey )
				{
					$return = $taxRate;
					break;
				}
			}
		}
		return $return;
	}

	function getItemsObjects(){
		$apps = $this->getProp( '_appointment' );
		$orders = $this->getProp( '_order' );
		$items = $this->getProp( '_item' );

		$return = array();

		foreach( $apps as $itemId => $amount ){
			$item = ntsObjectFactory::get( 'appointment' );
			$item->setId( $itemId );
			$return[] = $item;
			}

		foreach( $orders as $itemId => $amount ){
			$item = ntsObjectFactory::get( 'order' );
			$item->setId( $itemId );
			$return[] = $item;
			}
		return $return;
		}

	function getItems(){
		$invoiceId = $this->getId();
		$apps = $this->getProp( '_appointment' );
		$orders = $this->getProp( '_order' );
		$items = $this->getProp( '_item' );

		$return = array();

		foreach( $apps as $itemId => $amount ){
			$item = ntsObjectFactory::get( 'appointment' );
			$item->setId( $itemId );
			
		/* check which number of payments */
			$appInvoices = $item->getInvoices();
			$totalCount = count($appInvoices);
			$mySeq = 0;
			if( $totalCount > 1 ){
				for( $ii = 1; $ii <= $totalCount; $ii++ ){
					if( $appInvoices[$ii - 1][0] == $invoiceId ){
						$mySeq = $ii;
						break;
						}
					}
				}

			$itemName = M('Appointment') . ' #' . $item->getId();
			$itemDescription = ntsView::objectTitle($item);

			$completed = $item->getProp('completed');
			switch( $completed ){
				case HA_STATUS_CANCELLED:
					$itemDescription = '[' . M('Cancelled') . '] ' . $itemDescription;
					break;
				case HA_STATUS_NOSHOW:
					$itemDescription = '[' . M('No Show') . '] ' . $itemDescription;
					break;
				}

			if( $mySeq ){
				$itemDescription .= ' [' . M('Payment') . ' ' . $mySeq . '/' . $totalCount . ']';
				}

			$seats = $item->getProp('seats');
			$thisItem = array(
				'name'			=> $itemName, 
				'description'	=> $itemDescription,
				'unitCost' 		=> ($amount / $seats),
				'unitTax' 		=> $this->getItemTax($item),
				'unitTaxRate' 	=> $this->getItemTaxRate($item),
				'quantity'		=> $seats,
				'object'		=> $item,
				);
			$return[] = $thisItem;
			}

		foreach( $orders as $itemId => $amount ){
			$item = ntsObjectFactory::get( 'order' );
			$item->setId( $itemId );
			$thisItem = array(
				'name'			=> M('Package Order'),
				'description'	=> $item->getFullTitle(),
				'unitCost' 		=> $amount,
				'unitTax' 		=> $this->getItemTax($item),
				'unitTaxRate' 	=> $this->getItemTaxRate($item),
				'quantity'		=> 1,
				'object'		=> $item,
				);
			$return[] = $thisItem;
			}

		return $return;
		}

	function getFullTitle(){
		$depDetails = array(
			'appointment'	=> '',
			'order'	=> '',
			);
		$items = $this->getItemsObjects();
		reset( $items );
		foreach( $items as $dep ){
			$className = $dep->getClassName();
			switch( $className ){
				case 'appointment':
					$depDetails['appointments'][] = $dep->getId();
					break;
				case 'order':
					$depDetails['order'] = $dep;
					break;
				}
			}

		if( $depDetails['order'] ){
			$return = $depDetails['order']->getFullTitle();
			}
		elseif( $depDetails['appointments'] ){
			$return = count($depDetails['appointments']) . ' ' . ( (count($depDetails['appointments'])>1) ? M('Appointments') : M('Appointment') );
			}
		return $return;
		}

	function getItemAmount( $item ){
		$apps = $this->getProp( '_appointment' );
		$orders = $this->getProp( '_order' );

		$return = 0;
		switch( $item->getClassName() ){
			case 'appointment':
				if( isset($apps[$item->getId()]) )
					$return = $apps[$item->getId()];
				break;
			case 'order':
				if( isset($orders[$item->getId()]) )
					$return = $orders[$item->getId()];
				break;
			}
		return $return;
		}

	function getTotalAmount(){
		$subtotal = $this->getSubTotal();
		$tax = $this->getTaxAmount();
		$return = $subtotal + $tax;
		return $return;
		}

	function getSubTotal()
	{
		$return = 0;

		$apps = $this->getProp( '_appointment' );
		$orders = $this->getProp( '_order' );
		$items = $this->getProp( '_item' );

		foreach( $apps as $itemId => $amount )
		{
			$return += $amount;
		}
		foreach( $orders as $itemId => $amount )
		{
			$return += $amount;
		}
		foreach( $items as $itemId => $amount )
		{
			$return += $amount;
		}
		return $return;
	}

	function getTaxAmount()
	{
		$return = 0;
		$items = $this->getItemsObjects();
		reset( $items );
		foreach( $items as $item )
		{
			$return += $this->getItemTax($item);
		}
		return $return;
	}

	function getPaidAmount(){
		$invoicePaidAmount = 0;

		$trs = $this->getTransactions();
		reset( $trs );
		foreach( $trs as $tr ){
			$trAmount = $tr->getProp( 'amount' );
			$invoicePaidAmount += $trAmount;
			}
		return $invoicePaidAmount;
		}

	function getTransactions(){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();
		$where = array(
			'invoice_id' => array('=', $this->getId()),
			);
		$result = $ntsdb->select( 'id', 'transactions', $where, 'ORDER BY created_at ASC' );

		if( $result ){
			$ids = array();
			while( $i = $result->fetch() ){
				$ids[] = $i['id'];
				}
			ntsObjectFactory::preload( 'transaction', $ids );

			reset( $ids );
			foreach( $ids as $id ){
				$e = ntsObjectFactory::get( 'transaction' );
				$e->setId( $id );
				$return[] = $e;
				}
			}
		return $return;
		}

	function getCustomer(){
		$return = NULL;

		$apps = $this->getProp( '_appointment' );
		$orders = $this->getProp( '_order' );
		$customerId = 0;
		reset( $apps );
		foreach( $apps as $itemId => $amount ){
			$item = ntsObjectFactory::get( 'appointment' );
			$item->setId( $itemId );
			$customerId = $item->getProp('customer_id');
			break;
			}

		if( ! $customerId ){
			reset( $orders );
			foreach( $orders as $itemId => $amount ){
				$item = ntsObjectFactory::get( 'order' );
				$item->setId( $itemId );
				$customerId = $item->getProp('customer_id');
				break;
				}
			}
		
		$return = new ntsUser;
		if( $customerId ){
			$return->setId( $customerId );
			}
		return $return;
		}
	}
?>