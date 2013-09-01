<?php
$entries = ntsLib::getVar( 'admin/customers/browse:entries' );
$returnTo = ntsLib::getVar( 'admin:returnTo' );
$returnTo = ntsLib::getVar('admin/customers/browse::returnTo');

$upcomingCount = ntsLib::getVar( 'admin/customers::upcomingCount' );
$oldCount = ntsLib::getVar( 'admin/customers::oldCount' );
$orderCount = ntsLib::getVar( 'admin/customers::orderCount' ); 
$packs = ntsObjectFactory::getAllIds( 'pack' );

include_once( NTS_BASE_DIR . '/lib/view/ntsPager.php' );
$pager = new ntsPager( $NTS_VIEW['totalCount'], $NTS_VIEW['showPerPage'], 10 );
$pager->setPage( $NTS_VIEW['currentPage'] );

$pages = $pager->getPages();
reset( $pages );
$pagerParams = array();
if( $NTS_VIEW['search'] )
	$pagerParams['search'] = $NTS_VIEW['search'];
$totalCols = 4;
if( ! NTS_EMAIL_AS_USERNAME )
	$totalCols++;
if( $packs )
	$totalCols++;
?>
<table class="nts-listing">
<tr>

<td style="text-align: left;">
[<?php echo $NTS_VIEW['showFrom']; ?> - <?php echo $NTS_VIEW['showTo']; ?> of <?php echo $NTS_VIEW['totalCount']; ?>]
<?php if( count($pages) > 1 ) : ?>
&nbsp;&nbsp;<?php echo M('Pages'); ?>: 
<?php foreach( $pages as $pi ): ?>
	<?php if( $NTS_VIEW['currentPage'] != $pi['number'] ) : ?>
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

<td>
<a href="<?php echo ntsLink::makeLink('-current-', 'export' ); ?>"><?php echo M('CSV Export'); ?></a>
</td>

</tr>
</table>

<table class="nts-listing">
<?php if( count($entries) > 0 ) : ?>

<tbody>
<tr>
<?php if( NTS_EMAIL_AS_USERNAME ) : ?>
	<th><?php echo M('Email'); ?></th>
<?php else: ?>
	<th><?php echo M('Username'); ?></th>
	<th><?php echo M('Email'); ?></th>
<?php endif; ?>
<th><?php echo M('Full Name'); ?></th>

<th><?php echo M('Appointments'); ?></th>
<?php if( $packs ) : ?>
	<th><?php echo M('Packages'); ?></th>
<?php endif; ?>

<th>&nbsp;</th>

</tr>
</tbody>

<?php for( $ii = 0; $ii < count($entries); $ii++ ) : ?>
<?php
		$e = $entries[$ii];
		$objId = $e->getId();
		$restrictions = $e->getProp('_restriction');
		$targetLink = $returnTo ? $returnTo . '&nts-customer_id=' . $e->getId() : ntsLink::makeLink( '-current-/../edit', '', array('_id' => $e->getId()) );
?>
<tbody class="nts-ajax-parent">
<tr class="<?php echo ($ii % 2) ? 'even' : 'odd'; ?>">
<td>

<?php if( $returnTo ) : ?>
<a class="nts-target-parent2 nts-bold" href="<?php echo $targetLink; ?>">
<?php else : ?>
<a class="nts-bold" href="<?php echo $targetLink; ?>">
<?php endif; ?>

<?php if( NTS_EMAIL_AS_USERNAME ) : ?>
<?php
$email = $e->getProp('email');
$thisView = $email ? $email : '- ' .  M('No Email') . ' -';
?>
	<?php echo $thisView; ?>
<?php else: ?>
	<?php echo $e->getProp('username'); ?>
<?php endif; ?>
</a>
</td>

<?php if( ! NTS_EMAIL_AS_USERNAME ) : ?>
<td>
<?php
$email = $e->getProp('email');
$thisView = $email ? $email : '- ' .  M('No Email') . ' -';
?>
	<?php echo $thisView; ?>
</td>
<?php endif; ?>

<td>
	<?php echo ntsView::objectTitle($e); ?>
</td>

<td>
<?php
$totalCount = 0;
if( isset($upcomingCount[$objId]) )
	$totalCount += $upcomingCount[$objId];
if( isset($oldCount[$objId]) )
	$totalCount += $oldCount[$objId];
?>
<?php echo $totalCount; ?>
</td>

<?php if( $packs ) : ?>
<td>
<?php
$thisCount = isset($orderCount[$objId]) ? $orderCount[$objId] : 0;
?>
<?php echo $thisCount; ?>
</td>
<?php endif; ?>

<td>
<?php if( $restrictions ) : ?>
<?php
list( $alert, $cssClass, $message ) = $e->getStatus();
$class = $alert ? 'alert' : 'ok';
?>
<span class="<?php echo $class; ?>"><?php echo $message; ?></span>
<?php else: ?>
&nbsp;
<?php endif; ?>
</td>
</tr>

<?php
$notes = $e->getProp('_note');
?>
<?php if( $notes ) : ?>
<tr>
<td colspan="<?php echo ($totalCols); ?>">
<ul>
<?php foreach( $notes as $noteText => $note ) : ?>
<?php
		list( $noteTime, $noteUserId ) = explode( ':', $note );
		$noteUser = new ntsUser;
		$noteUser->setId( $noteUserId );
		$noteUserView = ntsView::objectTitle( $noteUser );
?>

<li>
	<?php echo $noteUserView; ?>: <i><?php echo $noteText; ?></i>
</li>
<?php endforeach; ?>
</ul>
</td>
</tr>
<?php endif; ?>



</tbody>

<?php	endfor; ?>

<?php else : ?>
	<tr>
	<td colspan="2"><?php echo M('Not found'); ?></td>
	</tr>
<?php endif; ?>
</table>

<?php if( $NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax' ) : ?>
<script language="JavaScript">
jQuery(document).ready( function(){
	jQuery("#<?php echo $NTS_VIEW['searchForm']->getName(); ?>").live( 'submit', function(event) {
		/* stop form from submitting normally */
		event.preventDefault(); 

		/* get some values from elements on the page: */
		var thisForm = jQuery( this );
		var thisFormData = thisForm.serialize();
		if( thisForm.data('trigger') ){
			thisFormData += '&' + thisForm.data('trigger') + '=1';
			}
		thisFormData += '&<?php echo NTS_PARAM_VIEW_MODE; ?>=ajax';

		var targetUrl = thisForm.attr( 'action' );
//		var resultDiv = thisForm.closest('.nts-ajax-container');
		var resultDiv = thisForm.closest('.nts-ajax-return');

		/* Send the data using post and put the results in a div */
		jQuery.ajax({
			type: "GET",
			url: targetUrl,
			data: thisFormData
			})
			.done( function(msg){
				resultDiv.html( msg );
				});
		return false;
		});
	});
</script>
<?php endif; ?>