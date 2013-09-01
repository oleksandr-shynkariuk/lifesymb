<?php
include_once( dirname(__FILE__) . '/ntsPackBase.php' );
class ntsPack extends ntsPackBase {
	function ntsPack(){
		parent::ntsObject( 'pack' );
		}

	function getTotalPrice()
	{
		$pm =& ntsPaymentManager::getInstance();
		$taxRate = $pm->getTaxRate( $this ); 

		$price = $this->getProp('price');
		$tax = ntsLib::calcTax( $price, $taxRate );
		$return = $price + $tax;
		return $return;
	}

	function getTaxAmount()
	{
		$pm =& ntsPaymentManager::getInstance();
		$taxRate = $pm->getTaxRate( $this ); 

		$subtotal = $this->getSubTotal(); 
		$return = ntsLib::calcTax( $subtotal, $taxRate );
		return $return;
	}

	function getSubTotal( $total = 0 )
	{
		if( $total )
		{
			$return = $total;

			$pm =& ntsPaymentManager::getInstance();
			$taxRate = $pm->getTaxRate( $this ); 

			if( $taxRate )
			{
				$return = ntsLib::removeTax( $total, $taxRate );
			}
		}
		else
		{
			$return = $this->getProp('price');
		}
		return $return;
	}

	function getServices(){
		$return = array();
		$serviceIds = $this->getServiceId();
		reset( $serviceIds );
		foreach( $serviceIds as $sid ){
			$service = ntsObjectFactory::get( 'service' );
			$service->setId( $sid );
			$return[] = $service;
			}
		return $return;
		}

	function getGroupedServices(){
		$return = array();
		$serviceIds = $this->getServiceId();

		$index = array();
		reset( $serviceIds );
		foreach( $serviceIds as $sid ){
			if( ! isset($index[$sid]) ){
				$index[$sid] = count($return);
				$service = ntsObjectFactory::get( 'service' );
				$service->setId( $sid );
				$return[] = array( $service, 0 );
				}
			$return[ $index[$sid] ][1]++;
			}
		return $return;
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

		$price = $this->getProp('price');
		$price = $price ? ntsCurrency::formatPrice( $price ) : M('Not For Sale');

		$return = ntsView::objectTitle($this) . ' [' . $this->getDetails() . ' ' . $serviceTitle . ']';
		$return .= ' ' . $price;
		return $return;
		}

	function getExpiresIn(){
		$return = $this->getProp('expires_in');
		return $return;
		}
}
?>