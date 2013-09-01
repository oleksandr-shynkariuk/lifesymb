<?php
$paymentAmountGross = $_NTS['REQ']->getParam( 'total', FALSE );
$accountId = $paymentGatewaySettings['account_id'];
$paymentRef = $_NTS['REQ']->getParam( 'order_number', FALSE );
$orderNumber = $_NTS['REQ']->getParam( 'order_number', FALSE );

if( $paymentGatewaySettings['test'] )
{
	$orderNumber = 1;
}

$suppliedKey = $_NTS['REQ']->getParam( 'key', FALSE );

$secret = array(
	$paymentGatewaySettings['secret_word'],
	$accountId,
	$orderNumber,
	$paymentAmountGross
	);
$myKey = strtoupper( md5(join('', $secret)) );

$paymentResponse = "OK";
if( $myKey == $suppliedKey )
{
	$paymentOk = TRUE;
}
else
{
	$paymentOk = FALSE;
	$paymentResponse = "2CO Hash Mismatch: $myKey vs $suppliedKey";
}
$paymentAmountNet = $paymentAmountGross;
?>