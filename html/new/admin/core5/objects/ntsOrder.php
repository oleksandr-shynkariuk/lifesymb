<?php
include_once( dirname(__FILE__) . '/ntsPackBase.php' );
class ntsOrder extends ntsPackBase {
	function ntsOrder(){
		parent::ntsObject( 'order' );
		}

	function getFilter( $what )
	{
		$return = array();
		$rule = $this->getRule();
		switch( $what )
		{
			case 'resource':
				$orderResourceId = $this->getProp('resource_id');
				$return = $orderResourceId ? array($orderResourceId) : array();
				break;

			case 'service':
				$packType = $this->getServiceType();
				switch( $packType )
				{
					case 'fixed':
						$return = $this->getLeft();
						break;
					case 'one':
						$return = $this->getServiceId();
						break;
				}
				break;

			default:
				if( isset($rule[$what]) )
				{
					$return = $rule[$what];
				}
				break;
		}
		return $return;
	}

	function getItems(){
		return $this->getParents();
		}

	function getInvoices()
	{
		$ntsdb =& dbWrapper::getInstance();
		$objId = $this->getId();

		$invoices = array();
		$where1 = array(
			'obj_class'		=> array('=', 'invoice'),
			'meta_name'		=> array('=', '_order'),
			'meta_value'	=> array('=', $objId),
			);
		$where2 = array(
			'obj_class'		=> array('=', 'invoice'),
			'meta_name'		=> array('=', '_tax'),
			'meta_value'	=> array('=', 'order:' . $objId),
			);
		$where = array( $where1, $where2 );

		$result = $ntsdb->select( array('meta_name', 'meta_data', 'obj_id'), 'objectmeta', $where );

		$invoiceIds = array();
		$invoiceData = array(
			'amount'	=> 0,
			'taxrate'	=> 0,
			'due_at'	=> 0
			);

		while( $i = $result->fetch() )
		{
			if( ! in_array($i['obj_id'], $invoiceIds) )
			{
				$invoiceIds[] = $i['obj_id'];
				$invoiceData[ $i['obj_id'] ] = array(
					'amount'	=> 0,
					'taxrate'	=> 0,
					'due_at'	=> 0
					);
			}
			switch( $i['meta_name'] )
			{
				case '_tax':
					$invoiceData[ $i['obj_id'] ]['taxrate'] = $i['meta_data'];
					break;
				default:
					$invoiceData[ $i['obj_id'] ]['amount'] = $i['meta_data'];
					break;
			}

			reset( $invoiceIds );
			foreach( $invoiceIds as $iid )
			{
				$amount = $invoiceData[$iid]['amount'];
				$tax = ntsLib::calcTax( $amount, $invoiceData[$iid]['taxrate'] );
				$total = $amount + $tax;
				$invoices[] = array( $iid, $total, $invoiceData[$iid]['due_at'] );
			}
		}
		return $invoices;
	}

	function getCost(){
		$packId = $this->getProp( 'pack_id' );
		$pack = ntsObjectFactory::get( 'pack' );
		$pack->setId( $packId );

		$price = $pack->getProp('price');
		return $price;
		}

	function getFullTitle(){
		$packType = $this->getServiceType(); 
		$serviceId = $this->getServiceId();
		if( $packType == 'fixed' )
		{
			$serviceTitle = ' - ' . M('Fixed Services') . ' - ';
		}
		else
		{
			if( in_array(0, $serviceId) )
			{
				$serviceTitle = ' - ' . M('Any Service') . ' - ';
			}
			else
			{
				if( count($serviceId) == 1 )
				{
					$service = ntsObjectFactory::get('service');
					$service->setId( $serviceId[0] );
					$serviceTitle = ntsView::objectTitle($service);
				}
				else
				{
					$serviceTitle = count($serviceId) . ' ' . M('Services');
				}
			}
		}

//		$return = $this->getDetails() . ' ' . $serviceTitle ;
		$packId = $this->getProp( 'pack_id' );
		$pack = ntsObjectFactory::get( 'pack' );
		$pack->setId( $packId );
		$packTitle = ntsView::objectTitle($pack);

		$return = $packTitle . ' [' . $this->getDetails() . ' ' . $serviceTitle . ']';
		return $return;
		}

	function getUsageText(){
		$return = '';

		$left = $this->getLeft();
		$usage = $this->getUsage();

		$qty = $this->getProp( 'qty' );
		$amount = $this->getProp( 'amount' );
		$duration = $this->getProp( 'duration' );

		$serviceType = $this->getServiceType();
		$type = $this->getType();
		switch( $serviceType ){
			case 'fixed':
				$serviceId = $this->getServiceId();
				$qty = count($serviceId);
				$usage = count( $usage );
				$left = count( $left );
				break;
			case 'one':
				break;
			}

		if( $usage > 0 )
			$usageText = M('Used') . ': ';
		else
			$usageText = M('Not Used');
		$leftText = M('Remain') . ': ';

		if( $left == -1 ){
			if( $usage > 0 )
				$usageText .= $usage;
			$leftText .= M('Unlimited');
			}
		elseif( $left > 0 ){
			if( $duration ){
				if( $usage > 0 )
					$usageText .= ntsTime::formatPeriod($usage);
				$leftText .= ntsTime::formatPeriod($left);
				}
			elseif( $amount ){
				if( $usage > 0 )
					$usageText .= ntsCurrency::formatPrice($usage);
				$leftText .= ntsCurrency::formatPrice($left);
				}
			elseif( $qty ){
				if( $usage > 0 )
					$usageText .= $usage;
				$leftText .= $left;
				}
			}
		else {
			$usageText .= M('Full');
			}

		if( $left )
			$return = $usageText . ', ' . $leftText;
		else
			$return = $usageText;
		return $return;
		}

	function getLeft(){
		$return = 0;

		$serviceType = $this->getServiceType();
		$type = $this->getType();
		switch( $serviceType ){
			case 'fixed':
				$return = $this->getServiceId();
				$usage = $this->getUsage();
				for( $ii = 0; $ii < count($usage); $ii++ ){
					for( $jj = 0; $jj < count($return); $jj++ ){
						if( $usage[$ii] == $return[$jj] ){
							array_splice( $return, $jj, 1 );
							break;
							}
						}
					}
				break;
			case 'one':
				switch( $type ){
					case 'unlimited':
						$return = -1;
						break;
					case 'qty':
						$qty = $this->getProp('qty');
						$return = $qty;
						break;
					case 'amount':
						$amount = $this->getProp('amount');
						$return = $amount;
						break;
					case 'duration':
						$duration = $this->getProp('duration');
						$return = $duration;
						break;
					}
				break;
			}

		if( $return == -1 ){
			}
		elseif( is_array($return) ){
			}
		else {
			$used = $this->getUsage();
			$return = $return - $used;
			if( $return < 0 )
				$return = 0;
			}

		return $return;
		}

	function getUsage(){
		$return = 0;

		$serviceType = $this->getServiceType();
		$type = $this->getType();
		switch( $serviceType ){
			case 'fixed':
				$return = array();
				$what = 'combo';
				break;
			case 'one':
				$qty = $this->getProp( 'qty' );
				$amount = $this->getProp( 'amount' );
				$duration = $this->getProp( 'duration' );

				if( $duration ){
					$what = 'duration';
					}
				elseif( $amount ){
					$what = 'amount';
					}
				else{
					$what = 'qty';
					}
				break;
			}

		$items = $this->getItems();
		foreach( $items as $item ){
			$className = $item->getClassName();
			if( $className != 'appointment' ){
				continue;
				}
			$completeStatus = $item->getProp('completed');
			if( in_array($completeStatus, array(HA_STATUS_CANCELLED, HA_STATUS_NOSHOW)) ){
				continue;
				}

			switch( $what ){
				case 'combo':
					$return[] = $item->getProp('service_id');
					break;
				case 'duration':
					$return += $item->getProp('duration');
					break;
				case 'amount':
					$return += $item->getProp('price');
					break;
				case 'qty':
				case 'unlimited':
					$return += $item->getProp('seats');
					break;
				}
			}
		return $return;
		}

	function getCustomer(){
		$customerId = $this->getProp( 'customer_id' );
		$return = new ntsUser;
		$return->setId( $customerId );
		return $return;
		}

	function isAvailable()
	{
		$return = FALSE;
	/* is not disabled */
		$isActive = $this->getProp( 'is_active' );
		if( ! $isActive )
		{
			return $return;
		}

	/* is expired */
		$expired = FALSE;
		$validTo = $this->getProp('valid_to');
		if( $validTo > 0 )
		{
			$t = new ntsTime;
			$t->setNow();
			$today = $t->formatDate_Db();

			$t->setTimestamp( $validTo );
			$validToDate = $t->formatDate_Db();
			if( $today > $validToDate )
			{
				$expired = TRUE;
			}
		}
		if( $expired )
		{
			return $return;
		}

	/* is anything left */
		$left = $this->getLeft();
		if( ! $left )
		{
			return $return;
		}

		$return = TRUE;
		return $return;
	}
}
?>