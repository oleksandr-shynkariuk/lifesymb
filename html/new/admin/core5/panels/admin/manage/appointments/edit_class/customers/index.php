<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$objects = ntsLib::getVar( 'admin/manage/appointments/edit_class::objects' );
$seatsLeft = ntsLib::getVar( 'admin/manage/appointments/edit_class::seatsLeft' );

$locationId = $objects[0]->getProp('location_id');
$resourceId = $objects[0]->getProp('resource_id');
$serviceId = $objects[0]->getProp('service_id');
$startsAt = $objects[0]->getProp('starts_at'); 
$iCanEdit = in_array($resourceId, $appEdit) ? true : false;

$currentCustomers = array();
for( $ii = 0; $ii < count($objects); $ii++ ){
	$currentCustomers[] = $objects[$ii]->getProp('customer_id');
	}
$currentCustomers = array_unique( $currentCustomers );
?>
<ul class="nts-listing">
<?php for( $ii = 0; $ii < count($objects); $ii++ ) : ?>
<?php
		$objId = $objects[$ii]->getId();
		$customer = new ntsUser;
		$customer->setId( $objects[$ii]->getProp('customer_id') );
?>

<li class="nts-ajax-parent">
<div>
<?php echo ntsView::printStatus($objects[$ii], false); ?>

<a href="<?php echo ntsLink::makeLink('admin/manage/appointments/edit', '', array('_id' => $objId)); ?>" class="nts-ajax-loader">
<?php echo ntsView::objectTitle( $customer ); ?>
</a>

</div>
<div class="nts-ajax-container nts-ajax-return" style="margin: 0.5em 0 1em 1em; padding: 0.5em 0.5em; border: #CCCCCC 1px solid;"></div>
</li>
<?php endfor; ?>

<li class="nts-ajax-parent" id="hoho">
<div>
<?php
	if( $seatsLeft > 0 ){
		$linkClass = 'nts-ok';
		$linkTitle = ' [+] ' . M('Create Appointment') . ' (' . M('Seats Left') . ': ' . $seatsLeft . ')';
		}
	else {
		$linkClass = 'ntsNotWorking';
		$linkTitle = M('No Seats Left');
		}
	echo ntsLink::printLink(
		array(
			'panel'		=> '-current-/../../create',
			'title'		=> $linkTitle,
			'params'	=> array(
				'location_id'	=> $locationId,
				'resource_id'	=> $resourceId,
				'service_id'	=> $serviceId,
				'starts_at'		=> $startsAt,
				'no_customer'	=> join( '-', $currentCustomers ),
				'hidden'		=> 1,
				),
			'attr'		=> array(
				'class'	=> "nts-ajax-loader $linkClass nts-button2",
				),
			)
		);
?>
</div>
<div id="boho" class="nts-ajax-container nts-ajax-return" style="margin: 0.5em 0 1em 1em; padding: 0.5em 0.5em; border: #CCCCCC 1px solid;">
</div>

</li>

</ul>