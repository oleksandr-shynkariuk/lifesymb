<?php
$entries = ntsLib::getVar( 'admin/company/payments/invoices::entries' );
$currentPage = ntsLib::getVar( 'admin/company/payments/invoices::currentPage' );
$showFrom = ntsLib::getVar( 'admin/company/payments/invoices::showFrom' );
$showTo = ntsLib::getVar( 'admin/company/payments/invoices::showTo' );
$totalCount = ntsLib::getVar( 'admin/company/payments/invoices::totalCount' );
$showPerPage = ntsLib::getVar( 'admin/company/payments/invoices::showPerPage' );
$search = ntsLib::getVar( 'admin/company/payments/invoices::search' );

$t = $NTS_VIEW['t'];
$totalCols = 6;

$now = time();

include_once( NTS_BASE_DIR . '/lib/view/ntsPager.php' );
$pager = new ntsPager( $totalCount, $showPerPage, 10 );
$pager->setPage( $currentPage );

$pages = $pager->getPages();
reset( $pages );
reset( $pages );
$pagerParams = array();
if( $NTS_VIEW['search'] )
	$pagerParams['search'] = $NTS_VIEW['search'];

$amounts = array();	
for( $ii = 0; $ii < count($entries); $ii++ ){
	$inv = $entries[$ii];
	$totalAmount = $inv->getTotalAmount();
	$paidAmount = $inv->getPaidAmount();
	$amounts[ $ii ] = array( $totalAmount, $paidAmount );
	}

$customerId = ntsLib::getVar( 'admin/company/payments/invoices::customer' );
if( $customerId ){
	// compile totals
	$grandTotalAmount = 0;
	$grandPaidAmount = 0;
	for( $ii = 0; $ii < count($entries); $ii++ ){
		$grandTotalAmount += $amounts[ $ii ][0];
		$grandPaidAmount += $amounts[ $ii ][1];
		}
	}
?>
<?php if( $customerId ) : ?>
<p>
<table class="ntsForm" style="margin: 0 0 1em 0;">
<tr>
<td class="ntsFormLabel"><?php echo M('Total Amount'); ?></td>
<td style="font-size: 1.1em;"><?php echo ntsCurrency::formatPrice($grandTotalAmount); ?></td>
<td class="ntsFormLabel"><?php echo M('Total Paid'); ?></td>
<td style="font-size: 1.1em;"><?php echo ntsCurrency::formatPrice($grandPaidAmount); ?></td>
</tr>
</table>
<?php endif; ?>

<?php if( $showPerPage != 'all' ) : ?>
<p>
<table class="nts-listing">
<tr>
<td style="text-align: left;">
[<?php echo $showFrom; ?> - <?php echo $showTo; ?> of <?php echo $totalCount; ?>]
<?php if( count($pages) > 1 ) : ?>
&nbsp;&nbsp;<?php echo M('Pages'); ?>: 
<?php foreach( $pages as $pi ): ?>
	<?php if( $currentPage != $pi['number'] ) : ?>
		<?php $pagerParams['p'] = $pi['number']; ?>
		<a href="<?php echo ntsLink::makeLink('-current-', '', $pagerParams ); ?>"><?php echo $pi['title']; ?></a>
	<?php else : ?>
		<b><?php echo $pi['title']; ?></b>
	<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
</td>

<td>
<?php $NTS_VIEW['searchForm']->display(); ?>
</td>
</tr>
</table>
<?php endif; ?>

<table class="nts-listing">

<?php if( ! count($entries) ) : ?>
<?php echo M('None'); ?>
<?php else : ?>
<tbody>

<tr>
<th><?php echo M('Created'); ?></th>
<th><?php echo M('Due Date'); ?></th>
<th><?php echo M('Refno'); ?></th>
<th><?php echo M('Amount'); ?></th>
<th><?php echo M('Status'); ?></th>
<th><?php echo M('Customer'); ?></th>

</tr>
</tbody>

<?php for( $ii = 0; $ii < count($entries); $ii++ ) : ?>
<?php
$inv = $entries[$ii];
list( $totalAmount, $paidAmount ) = $amounts[ $ii ];
?>
<tbody class="nts-ajax-parent">

<tr class="<?php echo ($ii % 2) ? 'even' : 'odd'; ?>">

<td>
<?php
$t->setTimestamp( $inv->getProp('created_at') );
$dateView = $t->formatDate();
?>
<?php echo $dateView; ?>
</td>

<td>
<?php
$dueAt = $inv->getProp('due_at');
if( $dueAt > 0 ){
	$t->setTimestamp( $inv->getProp('due_at') );
	$dueDateView = $t->formatDate();
	}
else {
	$dueDateView = M('N/A');
	}
?>
<?php echo $dueDateView; ?>
</td>

<td>
<a class="nts-ajax-loader nts-bold" href="<?php echo ntsLink::makeLink('admin/company/payments/invoices/edit', '', array('_id' => $inv->getId())); ?>"><?php echo ntsLib::viewHighlighted( $inv->getProp('refno'), $search ); ?></a>
</td>

<td>
<?php echo ntsCurrency::formatPrice($totalAmount); ?>
</td>

<td>
<?php
$balance = $paidAmount - $totalAmount;
?>
<?php if( $balance > 0 ) : ?>
<span class="nts-ok nts-bold"><?php echo ntsCurrency::formatPrice($balance); ?></span>
<?php elseif( ($balance == 0) && ($paidAmount > 0)) : ?>
<span class="nts-ok nts-bold"><?php echo M('Paid'); ?></span>
<?php elseif( $balance < 0 ) : ?>
<?php 	if( $now > $inv->getProp('due_at') ) : ?>
<span class="nts-alert nts-bold"><?php echo ntsCurrency::formatPrice($balance); ?></span>
<?php 	else : ?>
<?php echo ntsCurrency::formatPrice($balance); ?>
<?php 	endif; ?>
<?php else : ?>
&nbsp;
<?php endif; ?>
</td>

<td>
<?php
$customer = $inv->getCustomer();
$customerView = $customer->getProp('first_name') . ' ' . $customer->getProp('last_name');
?>
<?php echo $customerView; ?>
</td>

</tr>
<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child nts-ajax-return" style="padding-left: 2em;"></td>
</tr>

</tbody>
<?php endfor; ?>

<?php endif; ?>

</table>