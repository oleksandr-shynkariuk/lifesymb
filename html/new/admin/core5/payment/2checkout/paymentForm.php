<?php
$testMode = $paymentGatewaySettings['test'];
$paymentUrl = "https://www.2checkout.com/checkout/spurchase";

$accountId = $paymentGatewaySettings['account_id'];
$customerEmail = $invoiceInfo['customer']->getProp( 'email' );
$customerName = $invoiceInfo['customer']->getProp( 'first_name' ) . ' ' . $invoiceInfo['customer']->getProp( 'last_name' );

//_print_r( $invoiceInfo );

srand(time());
$sequence = rand(1, 1000);

$hidden = array(
	'sid'					=> $accountId,
	'id_type'				=> 1,
	'total' 				=> $paymentAmount,
	'email' 				=> $customerEmail,
	'cart_order_id'			=> $paymentOrderRefNo,
	'card_holder_name' 		=> $customerName,
	'return_url' 			=> $paymentNotifyUrl,
	'x_receipt_link_url' 	=> $paymentNotifyUrl,
	'currency_code'			=> strtoupper($paymentCurrency),
	'merchant_order_id'		=> $paymentOrderRefNo,
	);

	reset( $invoiceInfo['items'] );
	for( $ii = 1; $ii <= count($invoiceInfo['items']); $ii++ )
	{
		$fullId = $invoiceInfo['items'][$ii-1]['object']->getClassName() . '_' . $invoiceInfo['items'][$ii-1]['object']->getId();
		$hidden['c_prod_' . $ii]		= $fullId . ',' . $invoiceInfo['items'][$ii-1]['quantity'];
		$hidden['c_name_' . $ii]		= $invoiceInfo['items'][$ii-1]['name'];
		$hidden['c_description_' . $ii]	= $invoiceInfo['items'][$ii-1]['description'];
		$hidden['c_description_' . $ii]	= 'Test one';
		$hidden['c_tangible_' . $ii]	= 'N';
		$hidden['c_price_' . $ii]		= $invoiceInfo['items'][$ii-1]['unitCost'] + $invoiceInfo['items'][$ii-1]['unitTax'];
	}

	if( $testMode )
	{
		$hidden['demo'] = 'Y';	
	}
?>
<FORM ACTION="<?php echo $paymentUrl; ?>" METHOD=POST>

<?php foreach( $hidden as $hidK => $hidV ) : ?>
<input type="hidden" name="<?php echo $hidK; ?>" value="<?php echo $hidV; ?>">
<?php endforeach; ?>

<input type="submit" value="<?php echo $paymentGatewaySettings['label']; ?>">
</FORM>