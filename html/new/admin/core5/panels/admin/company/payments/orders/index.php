<?php
$fixCustomer = ntsLib::getVar( 'admin/company/payments/orders::customer' );
$entries = ntsLib::getVar( 'admin/company/payments/orders::entries' );
$currentPage = ntsLib::getVar( 'admin/company/payments/orders::currentPage' );
$showFrom = ntsLib::getVar( 'admin/company/payments/orders::showFrom' );
$showTo = ntsLib::getVar( 'admin/company/payments/orders::showTo' );
$totalCount = ntsLib::getVar( 'admin/company/payments/orders::totalCount' );
$showPerPage = ntsLib::getVar( 'admin/company/payments/orders::showPerPage' );
$search = ntsLib::getVar( 'admin/company/payments/orders::search' );

$t = $NTS_VIEW['t'];
$totalCols = 10;
if( $fixCustomer )
	$totalCols--;

$now = time();
$t->setNow();
$today = $t->formatDate_Db();

include_once( NTS_BASE_DIR . '/lib/view/ntsPager.php' );
$pager = new ntsPager( $totalCount, $showPerPage, 10 );
$pager->setPage( $currentPage );

$pages = $pager->getPages();
reset( $pages );
reset( $pages );
$pagerParams = array();
if( $NTS_VIEW['search'] )
	$pagerParams['search'] = $NTS_VIEW['search'];

$packs = ntsObjectFactory::getAllIds( 'pack' );
?>

<?php if( $packs && (! $fixCustomer) ) : ?>
<table class="nts-listing">
<tbody class="nts-ajax-parent">
<tr>
<td>
<?php
echo ntsLink::printLink(
	array(
		'panel'		=> '-current-/create',
		'title'		=> '[+] ' . M('Add To Customer'),
		'attr'		=> array(
			'class'	=> 'nts-ajax-loader nts-ok nts-button2',
			),
		)
	);
?>
</td>
</tr>
<tr>
<td class="nts-ajax-container nts-child nts-ajax-return"></td>
</tr>
</tbody>
</table>
<?php endif; ?>

<?php if( ! count($entries) ) : ?>
<?php echo M('None'); ?>
<?php else : ?>

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
</tr>
</table>

<table class="nts-listing">
<tbody>

<tr>
<?php if ($NTS_VIEW[NTS_PARAM_VIEW_MODE] != 'print') : ?>
<th>&nbsp;</th>
<?php endif; ?>
<th>&nbsp;</th>
<th><?php echo M('Created'); ?></th>
<th><?php echo M('Valid Till'); ?></th>
<th><?php echo M('Is Active'); ?></th>
<th><?php echo M('Value'); ?></th>
<th><?php echo M('When'); ?></th>
<th><?php echo M('Bookable Resource'); ?></th>
<th><?php echo M('Usage'); ?></th>
<?php if( ! $fixCustomer ) : ?>
<th><?php echo M('Customer'); ?></th>
<?php endif; ?>

</tr>
</tbody>

<?php for( $ii = 0; $ii < count($entries); $ii++ ) : ?>
<?php
$e = $entries[$ii];
$deps = $e->getItems();
$deleteLink = ntsLink::makeLink( '-current-/edit/delete', '', array('order_id' => $e->getId()) );
?>
<tbody class="nts-ajax-parent">

<tr class="<?php echo ($ii % 2) ? 'even' : 'odd'; ?>">

<?php if ($NTS_VIEW[NTS_PARAM_VIEW_MODE] != 'print') : ?>
<td style="width: 1em;">
<a class="alert nts-ajax-loader nts-bold" href="<?php echo $deleteLink; ?>" title="<?php echo M('Delete'); ?>">[x]</a>
</td>
<?php endif; ?>

<td>
#<?php echo $e->getId(); ?>
</td>
<td>
<?php
$t->setTimestamp( $e->getProp('created_at') );
$dateView = $t->formatDate();
?>
<?php echo $dateView; ?>
</td>

<td>
<?php
$validTo = $e->getProp('valid_to');
if( $validTo > 0 ){
	$t->setTimestamp( $validTo );
	$validToDate = $t->formatDate_Db();
	$validView = $t->formatDate();
	}
else {
	$validView = M('Never Expires');
	}
?>
<?php if( ($validTo > 0) && ($today > $validToDate) ) : ?>
<span class="nts-alert">
<?php else : ?>
<span class="nts-ok">
<?php endif; ?>
<?php echo $validView; ?></span>
</td>

<td>
<?php
$isActive = $e->getProp( 'is_active' );
$thisView = $isActive ? M('Yes') : M('No');
$expired = (($validTo > 0) && ($today > $validToDate)) ? 1 : 0;
if( $expired ){
	$thisView = M('Expired');
	}
$linkTitle = $isActive ? M('Disable') : M('Activate');
?>
<?php if( (! $isActive) || $expired ) : ?>
<?php 	if( ! $expired ) : ?>
<a href="<?php echo ntsLink::makeLink('-current-/edit/toggle', '', array('order_id' => $e->getId())); ?>" title="<?php echo $linkTitle; ?>">
<?php 	endif; ?>
	<span class="nts-alert"><?php echo $thisView; ?></span>
<?php 	if( ! $expired ) : ?>
</a>
<?php 	endif; ?>

<?php else : ?>
<?php 	if( ! $expired ) : ?>
<a href="<?php echo ntsLink::makeLink('-current-/edit/toggle', '', array('order_id' => $e->getId())); ?>" title="<?php echo $linkTitle; ?>">
<?php 	endif; ?>
	<span class="nts-ok"><?php echo $thisView; ?></span>
<?php if( ! $expired ) : ?>
</a>
<?php endif; ?>
<?php endif; ?>
</td>

<td>
<?php
$thisView = $e->getFullTitle();
$type = $e->getType();
?>
<a class="nts-ajax-loader nts-bold" href="<?php echo ntsLink::makeLink('-current-/edit/edit', '', array('order_id' => $e->getId())); ?>">
<?php echo $thisView; ?>
</a>
</td>

<?php
$thisView = array();
$rule = $e->getRuleView();
foreach( $rule as $r ){
	if( is_array($r) )
		$thisView[] = join( ': ', array( '<strong>' . $r[0] . '</strong>', join(', ', $r[1])) );
	else
		$thisView[] = '<strong>' . $r . '</strong>';
	}
$thisView = join( '<br>', $thisView );
?>
<td>
	<?php echo $thisView; ?>
</td>

<?php
$resourceId = $e->getProp('resource_id');
if( $resourceId ){
	$resource = ntsObjectFactory::get('resource');
	$resource->setId( $resourceId );
	$resourceView = ntsView::objectTitle($resource);
	}
else {
	$resourceView = ' - ' . M('Any') . ' - ';
	}
?>
<td>
<?php echo $resourceView; ?>
</td>


<td>
<?php
$usage = $e->getUsage();
$left = $e->getLeft();

$thisView = $e->getUsageText();
?>
<?php if( $usage ) : ?>
<a class="nts-ajax-loader nts-bold" href="<?php echo ntsLink::makeLink('admin/company/payments/orders/edit/appointments', '', array('_id' => $e->getId())); ?>">
<?php 	if( $left ) : ?>
<span class="nts-ok">
<?php	else : ?>
<span>
<?php	endif; ?>
<?php 	echo $thisView; ?>
</span>
</a>
<?php else : ?>

<?php 	if( $left ) : ?>
<span class="nts-ok">
<?php	else : ?>
<span>
<?php	endif; ?>
<?php 	echo $thisView; ?>
</span>

<?php endif; ?>
</span>
</td>

<?php if( ! $fixCustomer ) : ?>
<td>
<?php
$customer = $e->getCustomer();
$customerView = $customer->getProp('first_name') . ' ' . $customer->getProp('last_name');
?>
<?php echo $customerView; ?>
</td>
<?php endif; ?>

</tr>
<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child" style="padding-left: 2em;"></td>
</tr>

</tbody>
<?php endfor; ?>

</table>
<?php endif; ?>