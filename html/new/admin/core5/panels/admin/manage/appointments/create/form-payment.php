<?php if( ! $reschedule ) : ?>
<?php
$ntspm =& ntsPaymentManager::getInstance();

$service = ntsObjectFactory::get('service');
$service->setId( $sid );
$packOnly = $service->getProp( 'pack_only' );
$duration = $service->getProp('duration');

$appInfo = array(
	'location_id'	=> $lid,
	'resource_id'	=> $rid,
	'service_id'	=> $sid,
	'seats'			=> $seats,
	'time'			=> $time,
	'duration'		=> $duration,
	);

$price = $ntspm->getPrice( $appInfo, '' );
$basePrice = $ntspm->getBasePrice( $appInfo );
$promotions = $ntspm->getPromotions( $appInfo );

$customer = new ntsUser;
$customer->setId( $cid );

/* check if customer has enough balance for this */
if( $packOnly || $price ){
	$ready = array();
	$ready[] = $appInfo;
	$availableOrders = $customer->checkOrders( $ready );
	if( $availableOrders[0] )
		$paymentOptions[] = array( 'order', 1 );
	else
		$paymentOptions[] = array( 'order', 0 );
	}

if( $price )
	$paymentOptions[] = array( 'invoice', 1 );

$paymentOptions[] = array( 'no', 1 );


if( $price )
{
	$priceView = ntsCurrency::formatPrice($price);
	if( $promotions )
	{
		$basePrice = '<span style="text-decoration: line-through;">' . ntsCurrency::formatServicePrice($basePrice) . '</span>'; 
		$priceView = $basePrice . ' ' . $priceView;
		$tooltipInfo = array();
		reset( $promotions );
		foreach( $promotions as $pro )
		{
			$tooltipInfo[] = $pro->getModificationView() . ': ' . $pro->getTitle();
		}
		$tooltipInfo = join( '; ', $tooltipInfo );
		$priceView .= ' <a class="nts-tooltip" title="' . $tooltipInfo . '"><span title="?"> ? </span></a>';
	}
	$priceView = ' [' . $priceView . ']';
}
else
{
	$priceView = '';
}

$paymentOptionsTitles = array(
	'order'		=> M('Use Customer Packages'),
	'invoice'	=> M('Create Invoice') . $priceView,
	'no'		=> M('No Payment'),
	);

if( $packOnly && (! $availableOrders) )
	$paymentOptionsTitles['order'] = '<span class="nts-alert">' . M('No Packages Available') . '</span>';

$defaultPaymentOption = '';
if( $paymentOptions ){
	reset( $paymentOptions );
	foreach( $paymentOptions as $po ){
		if( $po[1] ){
			$defaultPaymentOption = $po[0];
			break;
			}
		}
	}
?>
<?php if( count($paymentOptions) > 1 ) : ?>
<tr>
<td class="ntsFormLabel">&nbsp;</td>
<td class="ntsFormValue">

<?php 	reset( $paymentOptions ); ?>
<?php 	foreach( $paymentOptions as $po ) : ?>
<?php
			$readonly = ! $po[1];
			echo $this->makeInput (
			/* type */
				'radio',
			/* attributes */
				array(
					'id'		=> 'payment_option',
					'value'		=> $po[0],
					'default'	=> $defaultPaymentOption,
					'readonly'	=> $readonly
					)
				);
?> <?php echo $paymentOptionsTitles[$po[0]]; ?>
<?php	endforeach; ?>
</td>
</tr>
<?php elseif( count($paymentOptions) == 1 ) : ?>
<?php 	if( $packOnly && (! $availableOrders) ) : ?>
<span class="nts-alert"><?php echo M('No Packages Available'); ?></span>
<?php 	endif; ?>
<?php endif; ?>

<?php endif; ?>