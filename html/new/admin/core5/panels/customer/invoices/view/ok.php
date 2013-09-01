<?php
$t = $NTS_VIEW['t'];
$object = $NTS_VIEW['invoiceInfo']['object'];

$conf =& ntsConf::getInstance();
$taxTitle = $conf->get('taxTitle');

$subTotal = $object->getSubTotal();
$taxAmount = $object->getTaxAmount();
$total = $subTotal + $taxAmount;
$paidAmount = $object->getPaidAmount();
$totalDue = $total - $paidAmount;
$paymentAmount = $totalDue;
?>

<H2><?php echo M('Invoice'); ?> <?php echo $NTS_VIEW['invoiceInfo']['refno']; ?></h2>
<?php if( $NTS_VIEW['paidAmount'] >= $NTS_VIEW['invoiceInfo']['totalAmount'] ) : ?>
	<H3><?php echo M('Fully Paid'); ?></H3>
<?php elseif( $NTS_VIEW['paidAmount'] > 0 ) : ?>
	<H3><?php echo M('Partially Paid'); ?></H3>
<?php endif; ?>

<?php if( $NTS_VIEW['paidAmount'] < $NTS_VIEW['invoiceInfo']['totalAmount'] ) : ?>
<h3><?php echo M('Due Amount'); ?>: <?php echo ntsCurrency::formatPrice($NTS_VIEW['invoiceInfo']['totalAmount'] - $NTS_VIEW['paidAmount']); ?></h3>
<?php endif; ?>

<p>
<?php echo $NTS_VIEW['invoiceInfo']['item_name']; ?>

<p>
<?php if( $taxAmount ) : ?>
	<h3><?php echo M('Subtotal'); ?>: <?php echo ntsCurrency::formatPrice($subTotal); ?></h3>
	<?php echo $taxTitle; ?>: <b><?php echo ntsCurrency::formatPrice($taxAmount); ?></b>
<?php endif; ?>

<h3><?php echo M('Total'); ?>: <?php echo ntsCurrency::formatPrice($total); ?></h3>

<?php if( $paidAmount ) : ?>
	<h3><?php echo M('Paid'); ?>: <?php echo ntsCurrency::formatPrice($paidAmount); ?></h3>
<?php endif; ?>

<?php if( $totalDue ) : ?>
	<h3><?php echo M('Total Due'); ?>: <?php echo ntsCurrency::formatPrice($totalDue); ?></h3>
<?php endif; ?>


<?php if( isset($NTS_VIEW['payments']) && $NTS_VIEW['payments'] ) : ?>
<p>
<table>
<tr>
	<td style="padding: 0.5em 1em;"><?php echo M('Date'); ?></td>
	<td style="padding: 0.5em 1em;"><?php echo M('Amount'); ?></td>
</tr>

<?php foreach( $NTS_VIEW['payments'] as $p ) : ?>
<?php $t->setTimestamp( $p['paid_at'] ); ?>
<tr>
	<td style="padding: 0.25em 1em;"><?php echo $t->formatFull(); ?></td>
	<td style="padding: 0.25em 1em;"><b><?php echo ntsCurrency::formatPrice($p['amount']); ?></b></td>
</tr>
<?php endforeach;  ?>
</table>
<?php endif; ?>

<p>
<a href="<?php echo ntsLink::makeLink(); ?>"><?php echo M('Continue'); ?></a>
