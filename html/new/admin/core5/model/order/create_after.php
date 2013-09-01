<?php
/* check if we need to create invoice */
$price = $pack->getProp('price');
if( $price || (isset($params['forceInvoice']) && $params['forceInvoice']) ){
	$forceAmount = (isset($params['forceInvoice']) && $params['forceInvoice']) ? $params['forceInvoice'] : 0;
	if( $forceAmount )
	{
		// remove tax
		$forceAmount = $pack->getSubTotal( $forceAmount );
	}

	$pm =& ntsPaymentManager::getInstance();
	$invoice = $pm->makeInvoices( array($object), $forceAmount );
	}
?>