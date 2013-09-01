<?php
$ntsConf =& ntsConf::getInstance();
$customerAcknowledge = $ntsConf->get( 'customerAcknowledge' );

$printView = ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'print') ? TRUE : FALSE;

$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );

$locationId = $object->getProp('location_id');
$resourceId = $object->getProp('resource_id');
$serviceId = $object->getProp('service_id');
$startsAt = $object->getProp('starts_at'); 
$createdAt = $object->getProp('created_at'); 
$duration = $object->getProp('duration'); 

$iCanEdit = in_array($resourceId, $appEdit) ? TRUE : FALSE;
if( $printView )
	$iCanEdit = FALSE;

$service = ntsObjectFactory::get( 'service' );
$service->setId( $serviceId ); 
$location = ntsObjectFactory::get( 'location' );
$location->setId( $locationId );
$resource = ntsObjectFactory::get( 'resource' );
$resource->setId( $resourceId );

$NTS_VIEW['t']->setTimestamp( $startsAt );
$dateView = ( $startsAt > 0 ) ? $NTS_VIEW['t']->formatWeekdayShort() . ', ' . $NTS_VIEW['t']->formatDate() : M('Not Scheduled');

if( $startsAt > 0 ){
	$timeView = $NTS_VIEW['t']->formatTime();
	$NTS_VIEW['t']->modify( '+' . $duration . ' seconds' );
	$timeView .= ' - ' . $NTS_VIEW['t']->formatTime();
	}
else {
	$timeView = M('Not Scheduled');
	}

$completedStatus = $object->getProp('completed');
$approvedStatus = $object->getProp('approved');
?>

<?php
$NTS_VIEW['t']->setTimestamp( $createdAt );
?>
<div style="font-size: 0.9em; font-style: italic; "><?php echo M('Created'); ?>: <?php echo $NTS_VIEW['t']->formatFull(); ?></div>
<?php
$NTS_VIEW['t']->setTimestamp( $startsAt );
?>

<table class="ntsForm">

<?php
$customerId = $object->getProp('customer_id');
$customer = new ntsUser;
$customer->setId( $customerId );
$customerIds = array();
?>
<tr>
<td class="ntsFormValue">
<div style="float: right; ">ID: <?php echo $object->getId(); ?></div>
<?php if( ! $printView ) : ?>
	<a target="_blank" class="nts-no-ajax" href="<?php echo ntsLink::makeLink('-current-', '', array(NTS_PARAM_VIEW_MODE => 'print')); ?>"><?php echo M('Print View'); ?></a>
<?php endif; ?>
</td>
<td class="ntsFormValue">
<?php echo ntsView::printStatus($object, true); ?>
</td>
<td class="ntsFormValue" style="vertical-align: top; padding: 0 1em;" rowspan="7">

<?php if( $iCanEdit ) : ?>
<ul class="nts-listing">
<?php
$actions = array();
if( ! $completedStatus )
{
	if( $approvedStatus )
	{
		$actions[] = array( 'complete',	'nts-ok',		'<i class="icon-check"></i> ' . M('Set Completed') );
		$actions[] = array( 'reject',	'nts-alert',	M('Reject') );
		$actions[] = array( 'noshow',	'nts-alert',	M('No Show') );
	}
	else
	{
		$actions[] = array( 'approve',	'nts-ok',		M('Approve') );
		$actions[] = array( 'reject',	'nts-alert',	M('Reject') );
	}
}
else
{
	if( $completedStatus == HA_STATUS_NOSHOW )
	{
		$actions[] = array( 'showup',	'nts-ok',	M('Unmark No Show') );
	}
	if( $completedStatus == HA_STATUS_COMPLETED )
	{
		$actions[] = array( 'incomplete',	'nts-ok',	M('Unmark Completed') );
	}
}
reset( $actions );
?>
<?php foreach( $actions as $a ) : ?>
	<li class="nts-ajax-parent">
	<a href="<?php echo ntsLink::makeLink('-current-/../edit/' . $a[0]); ?>" class="nts-ajax-loader nts-button <?php echo $a[1]; ?>"><?php echo $a[2]; ?></a>
	<div class="nts-ajax-container"></div>
	</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

</td>
</tr>

<?php if( $locationId ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Location'); ?></td>
<td class="ntsFormValue"><?php echo $location->getProp('title'); ?></td>
</tr>
<?php endif; ?>

<?php if( $resourceId ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Bookable Resource'); ?></td>
<td class="ntsFormValue"><?php echo $resource->getProp('title'); ?></td>
</tr>
<?php endif; ?>

<tr>
<td class="ntsFormLabel"><?php echo M('Service'); ?></td>
<td class="ntsFormValue"><?php echo $service->getProp('title'); ?></td>
</tr>

<?php if( $startsAt > 0 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Date'); ?></td>
<td class="ntsFormValue"><?php echo $dateView; ?></td>
</tr>
<?php endif;; ?>

<?php if( $startsAt > 0 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue">
<?php if( $startsAt > 0 ) : ?>
<?php 
if( $printView )
	$NTS_VIEW['formEndTime']->readonly = TRUE;
echo $NTS_VIEW['formEndTime']->display();
?>
<?php endif; ?>
</td>
</tr>
<?php endif;; ?>

<tr>
<td class="ntsFormLabel"><?php echo M('Customer'); ?></td>
<td class="ntsFormValue">
<?php if( $printView ) : ?>
<?php echo ntsView::objectTitle($customer); ?>
<?php else : ?>
<a href="<?php echo ntsLink::makeLink('-current-/../customer'); ?>"><?php echo ntsView::objectTitle($customer); ?></a>
<?php endif; ?>
</td>
</tr>

<?php if( $printView ) : ?>
<?php
$om =& objectMapper::getInstance();
$formId = $om->isFormForService( $serviceId );

if( $formId ){
	$form = ntsObjectFactory::get( 'form' );
	$form->setId( $formId );
	$formTitle = $form->getProp('title');

	$serviceId = $object->getProp( 'service_id' );

	$class = 'appointment';
	$otherDetails = array(
		'service_id'	=> $serviceId,
		);
	$fields = $om->getFields( $class, 'internal', $otherDetails );
	}
?>
<?php	if( $formId && $fields ) : ?>
<tr>
<td class="ntsFormLabel" colspan="2"><strong><?php echo $formTitle; ?></strong></td>
<td></td>
</tr>

<?php foreach( $fields as $f ) : ?>
<?php $c = $om->getControl( $class, $f[0], false ); ?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td class="ntsFormValue">
	<?php
	$value = $object->getProp($f[0]);
	if( $c[1] == 'checkbox' )
		$value = $value ? M('Yes') : M('No');
	echo $value;
	?>
	</td>
	<td></td>
</tr>
<?php endforeach; ?>

<?php	endif; ?>
<?php endif; ?>

</table>