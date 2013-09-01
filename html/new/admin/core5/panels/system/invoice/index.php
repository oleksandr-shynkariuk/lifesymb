<?php
$invoice = ntsLib::getVar( 'system/invoice::OBJECT' );
$ntsconf =& ntsConf::getInstance();
$taxTitle = $ntsconf->get('taxTitle');
$t = $NTS_VIEW['t'];
$ff =& ntsFormFactory::getInstance();
$printView = ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'print') ? TRUE : FALSE;

$customer = $invoice->getCustomer();

$invoiceHeader = $ntsconf->get('invoiceHeader');
$invoiceFooter = $ntsconf->get('invoiceFooter');
?>
<p>
<?php echo nl2br($invoiceHeader); ?>

<h2><?php echo M('Invoice'); ?> <?php echo $invoice->getProp('refno'); ?></h2>

<p>
<?php
$t->setTimestamp( $invoice->getProp('created_at') );
?>
<?php echo M('Created'); ?>: <strong><?php echo $t->formatWeekDayShort(); ?>, <?php echo $t->formatDate(); ?></strong>
<br>
<?php
$t->setTimestamp( $invoice->getProp('due_at') );
?>
<?php echo M('Due Date'); ?>: <strong><?php echo $t->formatWeekDayShort(); ?>, <?php echo $t->formatDate(); ?></strong> 
</p>

<?php	if( $customer ) : ?>
	<p>
	<?php echo M('Customer'); ?>: <strong><?php echo ntsView::objectTitle($customer); ?></strong> 
	</p>
<?php	endif; ?>

<h3><?php echo M('Items'); ?></h3>
<?php
$items = $invoice->getItems();

$table = new ntsHtmlTable;
$table->configView( 
	array(
		'status'		=> 'text',
		'name'			=> 'text',
		'description'	=> 'text',
		'quantity'		=> 'text',
		'unitCost'		=> 'price',
		)
	);

$header = array();
if ( ! $printView )
	$header[] = '';
$header[] = M('Description');
$header[] = M('Quantity');
$header[] = M('Unit Price');

$discounts = $invoice->getProp('_discount');
$totalDiscount = 0;
reset( $discounts );
foreach( $discounts as $k => $da )
{
	$totalDiscount += $da;
}

if( $totalDiscount )
{
	$header[] = M('Discount');
	$header[] = M('Total');
}

$table->setHeader( $header );

reset( $items );
foreach( $items as $e )
{
	$view = $table->prepareView( $e );

	switch( $e['object']->getClassName() ){
		case 'appointment':
		case 'order':
			$status = ntsView::printStatus($e['object'], false);
			break;
		default:
			$status = '&nbsp;';
			break;
		}

	$row = array();
	if ( ! $printView )
		$row[] = $status;
	$row[] = $view['name'] . '<br>' . $view['description'];
	$row[] = array(
				'value'		=> $view['quantity'],
				'style'		=> 'text-align: center;',
				);

	if( $totalDiscount )
	{
		$key = $e['object']->getClassName() . ':' . $e['object']->getId();
		if( isset($discounts[$key]) )
		{
			$row[] = ntsCurrency::formatPrice( $e['unitCost'] + $discounts[$key] );
			$row[] = ntsCurrency::formatPrice($discounts[$key]);
		}
		else
		{
			$row[] = $view['unitCost'];
			$row[] = '';
		}
	}

// edit cost
	$row[] = $view['unitCost'];
	$table->addRow( $row );
}

$subTotal = $invoice->getSubTotal();
$taxAmount = $invoice->getTaxAmount();
$total = $subTotal + $taxAmount;
$paidAmount = $invoice->getPaidAmount();
$totalDue = $total - $paidAmount;

$colspan = $printView ? 2 : 3;

if( $totalDiscount )
	$colspan += 2;

if( $totalDiscount )
{
	$table->addRow(
		array(
			array(
				'colspan'	=> $colspan,
				'value'		=> M('Total'),
				'style'		=> 'text-align: right; padding-bottom: 0;',
				'class'		=> 'subtotal',
				),
			array(
				'value'		=> ntsCurrency::formatPrice($subTotal + $totalDiscount),
				'class'		=> 'subtotal',
				'style'		=> 'padding-bottom: 0;',
				),
			)
		);
	$table->addRow(
		array(
			array(
				'colspan'	=> $colspan,
				'value'		=> M('Discount'),
				'style'		=> 'text-align: right; padding-bottom: 0;',
				),
			array(
				'value'		=> ntsCurrency::formatPrice($totalDiscount),
				'style'		=> 'padding-bottom: 0;',
				),
			)
		);
}

if( $taxAmount )
{
	$table->addRow(
		array(
			array(
				'colspan'	=> $colspan,
				'value'		=> M('Subtotal'),
				'style'		=> 'text-align: right; padding-bottom: 0;',
				'class'		=> 'subtotal',
				),
			array(
				'value'		=> ntsCurrency::formatPrice($subTotal),
				'class'		=> 'subtotal',
				'style'		=> 'padding-bottom: 0;',
				),
			)
		);

	$table->addRow(
		array(
			array(
				'colspan'	=> $colspan,
				'value'		=> $taxTitle,
				'style'		=> 'text-align: right; padding-top: 0;',
				),
			array(
				'value'		=> ntsCurrency::formatPrice($taxAmount),
				'style'		=> 'padding-top: 0;',
				),
			)
		);
}

if( $paidAmount )
{
	$table->addRow(
		array(
			array(
				'colspan'	=> $colspan,
				'value'		=> '<strong>' . M('Total') . '</strong>',
				'style'		=> 'text-align: right; padding-bottom: 0;',
				'class'		=> 'subtotal',
				),
			array(
				'value'		=> '<strong>' . ntsCurrency::formatPrice($total) . '</strong>',
				'class'		=> 'subtotal',
				'style'		=> 'padding-bottom: 0;',
				),
			)
		);

	$table->addRow(
		array(
			array(
				'colspan'	=> $colspan,
				'value'		=> M('Paid'),
				'style'		=> 'text-align: right; padding-top: 0;',
				),
			array(
				'value'		=> ntsCurrency::formatPrice($paidAmount),
				'style'		=> 'padding-top: 0;',
				),
			)
		);
	}

$table->addRow(
	array(
		array(
			'colspan'	=> $colspan,
			'value'		=> '<strong>' . M('Total Due') . '</strong>',
			'style'		=> 'text-align: right;',
			'class'		=> 'subtotal',
			),
		array(
			'value'		=> '<strong>' . ntsCurrency::formatPrice($totalDue) . '</strong>',
			'class'		=> 'subtotal',
			),
		)
	);
?>
<?php if( $items ) : ?>
<?php 	$table->display(); ?>
<?php else : ?>
<?php 	echo M('None'); ?>
<?php endif; ?>

<h3><?php echo M('Payments'); ?></h3>
<?php require( dirname(__FILE__) . '/transactions.php' ); ?>

<?php if( ($totalDue > 0) && $paymentGateways ) : ?>
<?php
	$pgm =& ntsPaymentGatewaysManager::getInstance();
	$paymentGateways = ntsLib::getVar( 'system/invoice::paymentGateways' );

	/* prepare some common data for payment forms */
	$paymentAmount = $totalDue;
	$paymentCurrency = $ntsconf->get( 'currency' );

	$items = $invoice->getItems(); 
	$invoiceRefNo = $invoice->getProp('refno');
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
	
?>
<p>
<h2><?php echo M('Payment Options'); ?></h2>
<p>
<table>
<tr>
<?php foreach( $paymentGateways as $gateway ) : ?>
<?php
	$gatewayFolder = $pgm->getGatewayFolder( $gateway );
	$gatewayFile = $gatewayFolder . '/paymentForm.php';

	$paymentGatewaySettings = $pgm->getGatewaySettings( $gateway );

	if( $gateway == 'paypal' ){
		$objects = $invoice->getItemsObjects();
		reset( $objects );
		foreach( $objects as $obj ){
			$resourceId = $obj->getProp( 'resource_id' );
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

<?php endif; ?>

<p>
<?php echo nl2br($invoiceFooter); ?>
