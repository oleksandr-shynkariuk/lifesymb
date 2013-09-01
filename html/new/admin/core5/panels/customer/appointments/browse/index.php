<?php
global $NTS_CURRENT_USER;
$customerId = $NTS_CURRENT_USER->getId();
$ntsdb =& dbWrapper::getInstance();
$displayColumns = array();

$t = $NTS_VIEW['t'];

$conf =& ntsConf::getInstance();
$customerAcknowledge = $conf->get( 'customerAcknowledge' );
$showSessionDuration = $conf->get('showSessionDuration');
$canCancel = $conf->get('customerCanCancel');
$canReschedule = $conf->get('customerCanReschedule');

if( ! NTS_SINGLE_LOCATION )
	$displayColumns[] = 'location';
if( ! NTS_SINGLE_RESOURCE )
	$displayColumns[] = 'resource';

/* check if this customer has orders or payments */
$paidAppsCount = 0;
$where = array(
	'customer_id'	=> array('=', $customerId)
	);
$orderCount = $ntsdb->count( 'orders', $where );
if( ! $orderCount ){
	$where = array(
		'customer_id'	=> array( '=', $customerId ),
		'price'			=> array( '>', 0 ),
		);
	$paidAppsCount = $ntsdb->count( 'appointments', $where );
	}
if( $paidAppsCount || $orderCount ){
	$displayColumns[] = 'payment';
	}
?>

<?php if( $NTS_VIEW['show'] == 'old' ) : ?>
	<h2><?php echo M('Old Appointments'); ?></h2>
	<a href="<?php echo ntsLink::makeLink('-current-', '', array('show' => 'upcoming') ); ?>"><?php echo M('Upcoming Appointments'); ?></a>
<?php else : ?>
	<h2><?php echo M('Upcoming Appointments'); ?></h2>
	<a href="<?php echo ntsLink::makeLink('-current-', '', array('show' => 'old') ); ?>"><?php echo M('Old Appointments'); ?></a>
<?php endif; ?>

<?php if( count($NTS_VIEW['entries']) ) : ?>
	<p style="font-size: 70%; padding: 0 1em; vertical-align: bottom;">
		<?php 
			$overParams = array(
				'show'	=> $NTS_VIEW['show'],
				);
		?>
		<?php echo M('Other Views & Export'); ?>:
		<?php $overParams['display'] = 'ical'; ?>	
		<a href="<?php echo ntsLink::makeLink('-current-', 'export', $overParams ); ?>">iCal</a>
		<?php $overParams['display'] = 'excel'; ?>	
		<a href="<?php echo ntsLink::makeLink('-current-', 'export', $overParams ); ?>">Excel</a>
<?php endif; ?>

<?php
$now = time();
?>
<p>
<?php if( ! count($NTS_VIEW['entries']) ) : ?>
	<?php echo M('None'); return; ?>
<?php endif; ?>

<p>
<table style="width: auto;">
<tr>
	<td><b><?php echo M('Legend'); ?>:</b></td>
	<td style="width: 1em; padding: 0;" class="nts-approved">&nbsp;</td>
	<td style="text-align: left; padding-left: 0.5em; padding-right: 2em;"><?php echo M('Approved'); ?></td>
	<td style="width: 1em; padding: 0;" class="nts-completed">&nbsp;</td>
	<td style="text-align: left; padding-left: 0.5em; padding-right: 2em;"><?php echo M('Completed'); ?></td>
	<td style="width: 1em; padding: 0;" class="nts-pending">&nbsp;</td>
	<td style="text-align: left; padding-left: 0.5em; padding-right: 2em;"><?php echo M('Pending'); ?></td>
	<td style="width: 1em; padding: 0;" class="nts-noshow">&nbsp;</td>
	<td style="text-align: left; padding-left: 0.5em; padding-right: 2em;"><?php echo M('No Show'); ?></td>
	<td style="width: 1em; padding: 0;" class="nts-cancelled">&nbsp;</td>
	<td style="text-align: left; padding-left: 0.5em; padding-right: 2em;"><?php echo M('Cancelled'); ?></td>
</tr>
</table>

<p>
<div id="nts-appointment-list">
<table>
<tr>
	<th style="width: 1em; padding: 0;">&nbsp;</th>
	<th><?php echo M('When'); ?></th>
	<th><?php echo M('Service'); ?></th>
<?php if( in_array('location', $displayColumns) ) : ?>
	<th><?php echo M('Location'); ?></th>
<?php endif; ?>
<?php if( in_array('resource', $displayColumns) ) : ?>
	<th><?php echo M('Bookable Resource'); ?></th>
<?php endif; ?>
<?php if( in_array('payment', $displayColumns) ) : ?>
	<th><?php echo M('Payment'); ?></th>
<?php endif; ?>
</tr>

<?php $count = 0; ?>
<?php foreach( $NTS_VIEW['entries'] as $a ) : ?>
<tr class="<?php echo ($count % 2) ? 'odd' : ''; ?>">
<?php
	$completedStatus = $a->getProp('completed');
	$approvedStatus = $a->getProp('approved');

	if( $completedStatus ){
		switch( $completedStatus ){
			case HA_STATUS_NOSHOW:
				$class = 'nts-noshow';
				break;
			case HA_STATUS_CANCELLED:
				$class = 'nts-cancelled';
				break;
			case HA_STATUS_COMPLETED:
				$class = 'nts-completed';
				break;
				
			}
		}
	else {
		if( $approvedStatus )
			$class = 'nts-approved';
		else
			$class = 'nts-pending';
		}
?>
	<td style="padding: 0;" class="<?php echo $class; ?>">&nbsp;</td>
	<td>
	<?php
		$t->setTimestamp( $a->getProp('starts_at') );

		$serviceId = $a->getProp( 'service_id' );
		$service = ntsObjectFactory::get( 'service' );
		$service->setId( $serviceId );

		$seats = $a->getProp('seats');

		$cellView = $t->formatWeekday() . ', ' . $t->formatDate();

		$showEndTime = $conf->get('showEndTime');
		$timeView = $showEndTime ? $t->formatTime( $a->getProp('duration') ) : $t->formatTime();
		$cellView .= '<br><b>' . $timeView . '</b>';
		?>
		<a href="<?php echo ntsLink::makeLink('-current-/../view', '', array('id' => $a->getId()) ); ?>">
<?php 		echo $cellView; ?>
		</a>
		<?php
		$t->setTimestamp( $a->getProp('created_at') );
		$createdView = $t->formatWeekdayShort() . ', ' . $t->formatDate() . ' ' . $t->formatTime();
		?>
		<br>
		<span style="font-size: 80%; font-style: italic;"><?php echo M('Created'); ?>: <?php echo $createdView; ?></span>

		<br><br>
		<?php if( ! $completedStatus ) : ?>
			<?php $minCancel = $service->getProp('min_cancel'); ?>
			<?php if( ($now + $minCancel) > $a->getProp('starts_at') ) : ?>
				<?php echo M('You cannot cancel or reschedule this appointment now'); ?>
			<?php else : ?>
<?php			if( $canCancel ) : ?>
					<a href="<?php echo ntsLink::makeLink('-current-/../edit/cancel', '', array('_id' => $a->getId(), 'return' => 'all') ); ?>"><?php echo M('Cancel'); ?></a>
<?php			endif; ?>
<?php			if( $canReschedule ) : ?>
<?php
				$t->setTimestamp( $a->getProp('starts_at') );
				$oldDate = $t->formatDate_Db();
?>
					<a href="<?php echo ntsLink::makeLink('customer', '', array('reschedule' => $a->getId(), 'cal' => $oldDate) ); ?>"><?php echo M('Reschedule'); ?></a>
<?php			endif; ?>
			<?php endif; ?>
		<?php endif; ?>

<?php 	if( $completedStatus ) : ?>
<?php		if( $customerAcknowledge && (! $a->getProp('_ack')) ) : ?>
				<a href="<?php echo ntsLink::makeLink('-current-/../edit/ack', '', array('_id' => $a->getId(), 'return' => 'all') ); ?>"><?php echo M('Acknowledge Completion'); ?></a>
<?php		endif; ?>
<?php	endif; ?>

	</td>
	<td>
		<?php echo nl2br( ntsView::appServiceView($a) ); ?>
	</td>

<?php if( in_array('location', $displayColumns) ) : ?>
	<td>
		<?php
		$location = new ntsObject('location');
		$location->setId( $a->getProp('location_id') );
		?>
		<?php echo ntsView::objectTitle($location); ?>
	</td>
<?php endif; ?>

<?php if( in_array('resource', $displayColumns) ) : ?>
	<td>
		<?php
		$resource = ntsObjectFactory::get( 'resource' );
		$resource->setId( $a->getProp('resource_id') );
		?>
		<?php echo ntsView::objectTitle($resource); ?>
	</td>
<?php endif; ?>

<?php if( in_array('payment', $displayColumns) ) : ?>
	<td>
<?php
$thisView = '';
$invoices = $a->getInvoices();
$orders = $a->getProp('_order');
if( $invoices ){
	$totalAmount = $a->getTotalAmount();
	$paidAmount = $a->getPaidAmount(); 

	if( $paidAmount > 0 ){
		if( $paidAmount < $totalAmount ){
			$thisView .= M('Partially Paid');
			$percent = floor( 100 * ($paidAmount / $totalAmount) );
			$thisView .= ' ' . $percent . '%';
			}
		else {
			$thisView .= M('Fully Paid');
			}
		}
	else {
		$thisView .= M('Not Paid');
		}
	}
elseif( $orders && isset($orders[0]) ){
	/* find order */
	$order = ntsObjectFactory::get( 'order' );
	$order->setId( $orders[0] );
	$thisView .= ntsView::objectTitle($order);
	}
?>	
<?php echo $thisView; ?>	
	</td>
<?php endif; ?>

</tr>
<?php $count++; ?>
<?php endforeach; ?>
</table>
</div>