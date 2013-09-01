<?php
$conf =& ntsConf::getInstance();
$pgm =& ntsPaymentGatewaysManager::getInstance();
$ntsdb =& dbWrapper::getInstance();

$taxTitle = $conf->get('taxTitle');

$invoiceInfo = $NTS_VIEW['invoiceInfo'];
$paymentGateways = $NTS_VIEW['paymentGateways'];

$object = $NTS_VIEW['invoiceInfo']['object'];

$subTotal = $object->getSubTotal();
$taxAmount = $object->getTaxAmount();
$total = $subTotal + $taxAmount;
$paidAmount = $object->getPaidAmount();
$totalDue = $total - $paidAmount;
$paymentAmount = $totalDue;

/* prepare some common data for payment forms */
$paymentCurrency = $conf->get( 'currency' );

$items = $invoiceInfo['items']; 
$invoiceRefNo = $invoiceInfo['refno'];
$paymentOrderRefNo = $invoiceRefNo;

$paymentItemName = array();
reset( $items );
foreach( $items as $item ){
	if( count($items) > 1 )
		$paymentItemName[] = $item['name'];
	else
		$paymentItemName[] = $item['description'];
	}
$paymentItemName = join( '<br>', $paymentItemName );

reset( $paymentGateways );
reset( $items );
?>
<H2><?php echo M('Payment Required'); ?></H2>
<h3 style="margin: 0 0 0 0;"><?php echo M('Invoice'); ?> #<?php echo $invoiceRefNo; ?></h3>

<p>
<ol>
<?php foreach( $items as $item ) : ?>
<li>
	<ul>
		<li><?php echo $item['name']; ?></li>
		<li><?php echo $item['description']; ?></li>
	</ul>
</li>
<?php endforeach; ?>
</ol>

<?php if( $taxAmount ) : ?>
	<h3 style="margin: 0.5em 0 0 0;"><?php echo M('Subtotal'); ?>: <?php echo ntsCurrency::formatPrice($subTotal); ?></h3>
	<?php echo $taxTitle; ?>: <b><?php echo ntsCurrency::formatPrice($taxAmount); ?></b>
<?php endif; ?>

<h3 style="margin: 0.5em 0 0 0;"><?php echo M('Total Due'); ?>: <?php echo ntsCurrency::formatPrice($totalDue); ?></h3>

<p>
<table>
<tr>
<?php foreach( $paymentGateways as $gateway ) : ?>
<?php
	$gatewayFolder = $pgm->getGatewayFolder( $gateway );
	$gatewayFile = $gatewayFolder . '/paymentForm.php';

	$paymentGatewaySettings = $pgm->getGatewaySettings( $gateway );

	if( $gateway == 'paypal' ){
		$objects = $invoiceInfo['object']->getItemsObjects();
		reset( $objects );
		foreach( $objects as $object ){
			$resourceId = $object->getProp( 'resource_id' );
			$resource = ntsObjectFactory::get( 'resource' );
			$resource->setId( $resourceId );
			$myPaypal = $resource->getProp( '_paypal' );
			if( $myPaypal )
				$paymentGatewaySettings['email'] = $myPaypal;
			}
		}

	/* some links */
	if( defined('NTS_PAYMENT_LINK') )
	{
		$paymentNotifyUrl = ntsLink::makeLinkFull( NTS_PAYMENT_LINK, '', '', array('gateway' => $gateway) ) . '&nts-refno=' . $invoiceRefNo;
	}
	else
	{
		$paymentNotifyUrl = ntsLink::makeLink( 'system/payment', '', array('gateway' => $gateway) ) . '&nts-refno=' . $invoiceRefNo;
	}
	$paymentOkUrl = ntsLink::makeLink( 'customer/invoices/view', '', array('refno' => $invoiceRefNo, 'display' => 'ok') );
	$paymentFailedUrl = ntsLink::makeLink( 'customer/invoices/view', '', array('refno' => $invoiceRefNo, 'display' => 'fail') );
?>
<td style="padding: 0 0.5em;">
<?php	require( $gatewayFile ); ?>
</td>
<?php endforeach; ?>
</tr>
</table>