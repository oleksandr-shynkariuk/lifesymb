<?php
$object = ntsLib::getVar( 'admin/company/payments/invoices/edit::OBJECT' );
$ntsconf =& ntsConf::getInstance();
$taxTitle = $ntsconf->get('taxTitle');
$t = $NTS_VIEW['t'];
$ff =& ntsFormFactory::getInstance();
$printView = ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'print') ? TRUE : FALSE;

$customer = $object->getCustomer();
?>
<?php if ( ! $printView ) : ?>
	<a target="_blank" class="nts-no-ajax" href="<?php echo ntsLink::makeLink('-current-', '', array(NTS_PARAM_VIEW_MODE => 'print')); ?>"><?php echo M('Print View'); ?></a> 
	<a href="<?php echo ntsLink::makeLink('-current-', '', array('display' => 'send') ); ?>"><?php echo M('Send Invoice'); ?></a> 
<?php endif; ?>

<h2><?php echo M('Invoice'); ?> <?php echo $object->getProp('refno'); ?></h2>

<?php if ( $printView ) : ?>
<p>
<?php
$t->setTimestamp( $object->getProp('created_at') );
?>
<?php echo M('Created'); ?>: <strong><?php echo $t->formatWeekDayShort(); ?>, <?php echo $t->formatDate(); ?></strong>
<br>
<?php
$t->setTimestamp( $object->getProp('due_at') );
?>
<?php echo M('Due Date'); ?>: <strong><?php echo $t->formatWeekDayShort(); ?>, <?php echo $t->formatDate(); ?></strong> 
</p>

<?php	if( $customer ) : ?>
	<p>
	<?php echo M('Customer'); ?>: <strong><?php echo ntsView::objectTitle($customer); ?></strong> 
	</p>
<?php	endif; ?>

<?php endif; ?>

<h3><?php echo M('Items'); ?></h3>
<?php
$items = $object->getItems();

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

$discounts = $object->getProp('_discount');
$totalDiscount = 0;
reset( $discounts );
foreach( $discounts as $k => $da )
{
	$totalDiscount += $da;
}

if( $totalDiscount OR (! $printView) )
{
	$header[] = M('Discount');
	$header[] = M('Total');
}

reset( $items );

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

	if( $discounts )
	{
		$key = $e['object']->getClassName() . ':' . $e['object']->getId();
		if( isset($discounts[$key]) )
		{
			$discount = $discounts[$key];
			$discountView = ntsCurrency::formatPrice($discounts[$key]);
		}
		else
		{
			$discount = 0;
			$discountView = '';
		}
	}

	// edit cost
	$fParams = array(
		'cost'		=> $e['unitCost'],
		'costView'	=> $view['unitCost'],
		'item'		=> $e['object']->getClassName(),
		'item_id'	=> $e['object']->getId(),
		'itemObj'	=> $e['object'],
		'discounts'	=> $discounts,
		);
	if( $discounts )
	{
		$fParams['discount'] = $discount;
		$fParams['discountView'] = $discountView;
	}

	$costForm = $ff->makeForm( dirname(__FILE__) . '/formUnitCost', $fParams, $e['object']->getClassName() . '_' .  $e['object']->getId() );
	$costForm->noprint = TRUE;
	if( $printView )
		$costForm->readonly = TRUE;
	$costView = $costForm->display();

	$discountForm = $ff->makeForm( dirname(__FILE__) . '/formDiscount', $fParams, $e['object']->getClassName() . '_' .  $e['object']->getId() );
	$discountForm->noprint = TRUE;
	if( $printView )
		$discountForm->readonly = TRUE;
	$discountView = $discountForm->display();

	if( $totalDiscount OR (! $printView) )
	{
		$key = $e['object']->getClassName() . ':' . $e['object']->getId();
		if( isset($discounts[$key]) )
		{
			$row[] = ntsCurrency::formatPrice( $e['unitCost'] + $discounts[$key] );
			$row[] = $discountView;
		}
		else
		{
			$row[] = $view['unitCost'];
//			$row[] = '';
			$row[] = $discountView;
		}
	}

	$row[] = $costView;
	$table->addRow( $row );
}

$subTotal = $object->getSubTotal();
$taxAmount = $object->getTaxAmount();
$total = $subTotal + $taxAmount;
$paidAmount = $object->getPaidAmount();
$totalDue = $total - $paidAmount;

$colspan = $printView ? 2 : 3;
if( $totalDiscount OR (! $printView) )
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
<?php require( dirname(__FILE__) . '/../../transactions/index.php' ); ?>
