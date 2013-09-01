<?php
$invoice = ntsLib::getVar( 'system/invoice::OBJECT' );
$entries = ntsLib::getVar( 'system/invoice::entries' );
$transactionsAmount = ntsLib::getVar( 'system/invoice::transactionsAmount' );

$totalAmount = $invoice ? $invoice->getTotalAmount() : 0;
$dueAmount = ($totalAmount > $transactionsAmount) ? ($totalAmount - $transactionsAmount) : 0;

$t = $NTS_VIEW['t'];

$totalCols = 7;
if( $invoice ){
	$totalCols -= 1;
	}
?>

<?php if( ($transactionsAmount < $totalAmount) && ($NTS_VIEW[NTS_PARAM_VIEW_MODE] != 'print') ) : ?>
<table class="nts-listing">
<tbody class="nts-ajax-parent">
<tr>
<td>
<?php
echo ntsLink::printLink(
	array(
		'panel'		=> 'admin/company/payments/transactions/add',
		'params'	=> array(
			'invoice'	=> $invoice->getId(),
			'default'	=> $dueAmount
			),
		'title'		=> '[+] ' . M('Payment') . ': ' . M('Add'),
		'attr'		=> array(
			'class'	=> 'nts-ajax-loader nts-ok nts-button2',
			),
		)
	);
?>
</td>
</tr>

<tr>
<td class="nts-ajax-container nts-child"></td>
</tr>
</tbody>
</table>
<?php endif; ?>

<?php if( ! count($entries) ) : ?>
<p><?php echo M('None'); ?>
<?php else : ?>

<table class="nts-listing">
<tbody>

<tr>
<?php if ($NTS_VIEW[NTS_PARAM_VIEW_MODE] != 'print') : ?>
<th>&nbsp;</th>
<th>&nbsp;</th>
<?php endif; ?>

<th><?php echo M('Date'); ?></th>
<th><?php echo M('Amount'); ?></th>

<?php if( ! $invoice ) : ?>
<th><?php echo M('Invoice'); ?></th>
<?php endif; ?>
<th><?php echo M('Paid Through'); ?></th>
<th><?php echo M('Notes'); ?></th>
</tr>
</tbody>

<?php for( $ii = 0; $ii < count($entries); $ii++ ) : ?>
<?php
$view = array();
$tra = $entries[$ii];

$deleteLink = ntsLink::makeLink( 'admin/company/payments/edit/delete', '', array('transid' => $tra->getId()) );

$thisInvoiceId = $tra->getProp('invoice_id');
if( $thisInvoiceId ){
	$thisInvoice = ntsObjectFactory::get('invoice');
	$thisInvoice->setId( $thisInvoiceId );
	$view['invoice'] = $thisInvoice->getProp('refno');
	$view['paid_through'] = $tra->getProp('pgateway');

	if( $tra->getProp('pgateway_ref') ){
//		$view['notes'] = $tra->getProp('pgateway_ref') . '<br>' . $tra->getProp('pgateway_response');
		$view['notes'] = $tra->getProp('pgateway_ref');
		}
	else {
		$view['notes'] = $tra->getProp('pgateway_response');
		}
	}
else {
	$view['invoice'] = M('N/A');
	$view['paid_through'] = '&nbsp;';
	$view['notes'] = '';
	}
?>
<tbody class="nts-ajax-parent">

<tr class="<?php echo ($ii % 2) ? 'even' : 'odd'; ?>">

<?php if ($NTS_VIEW[NTS_PARAM_VIEW_MODE] != 'print') : ?>
<td style="width: 1em;">
<a class="alert nts-ajax-loader nts-bold" href="<?php echo $deleteLink; ?>" title="<?php echo M('Delete'); ?>">[x]</a>
</td>
<td>
#<?php echo $tra->getId(); ?>
</td>
<?php endif; ?>

<td>
<?php
$t->setTimestamp( $tra->getProp('created_at') );
$dateView = $t->formatFull();
?>
<?php echo $dateView; ?>
</td>

<td>
<?php echo ntsCurrency::formatPrice($tra->getProp('amount')); ?>
</td>

<?php if( ! $invoice ) : ?>
<td>
<?php if( $thisInvoiceId ) : ?>
	<a class="nts-ajax-loader" href="<?php echo ntsLink::makeLink('admin/company/payments/invoices/edit/appointments', '', array('_id' => $thisInvoiceId )); ?>">
	<?php echo $view['invoice']; ?>
	</a>
<?php else : ?>
	<?php echo $view['invoice']; ?>
<?php endif; ?>
</td>
<?php endif; ?>

<td><?php echo $view['paid_through']; ?></td>
<td><?php echo $view['notes']; ?></td>

</tr>
<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child"></td>
</tr>

</tbody>
<?php endfor; ?>

</table>
<?php endif; ?>

