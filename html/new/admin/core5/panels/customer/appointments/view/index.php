<?php
global $object, $NTS_CURRENT_USER;
$ntsdb =& dbWrapper::getInstance();

$t = $NTS_VIEW['t'];

$conf =& ntsConf::getInstance();
$customerAcknowledge = $conf->get( 'customerAcknowledge' );
$showSessionDuration = $conf->get('showSessionDuration');
$canCancel = $conf->get('customerCanCancel');
$canReschedule = $conf->get('customerCanReschedule');

$confReturnUrl = $conf->get( 'returnAfterRequest' );
if( $confReturnUrl )
{
	$idString = is_array($NTS_VIEW['id']) ? join( '-', $NTS_VIEW['id'] ) : $NTS_VIEW['id'];
	if( preg_match('/\?/', $confReturnUrl) )
		$confReturnUrl .= '&id=' . $idString;
	else
		$confReturnUrl .= '?id=' . $idString;
}
?>
<?php if( $NTS_VIEW['isRequest'] ) : ?>
<?php
	if( $confReturnUrl ){
		echo '<script language="JavaScript">document.location.href="' . $confReturnUrl . '";</script>';
		return;
		}
?>
<?php
	$afterRequestInfo = ( $NTS_VIEW['object'][0]->getProp('approved') ) ? 'accepted' : 'waitingApproval';
	$infoFile = dirname(__FILE__) . '/' . $afterRequestInfo . '.php';
	require( $infoFile );
?>
<p>
<?php
// check where to return
$returnUrl = '';
if( count($NTS_VIEW['object']) == 1 ){
	$service = ntsObjectFactory::get( 'service' );
	$service->setId( $NTS_VIEW['object'][0]->getProp('service_id') );
	$returnUrl = $service->getProp( 'return_url' );
	}
if( ! $returnUrl ){
	$returnUrl = strlen($confReturnUrl) ? $confReturnUrl : ntsLink::makeLink();
	}
?>

<a href="<?php echo $returnUrl; ?>"><?php echo M('Continue'); ?></a>
<?php return; ?>

<?php endif; ?>
<?php
$appDetails = $NTS_VIEW['object']->getByArray();
$ff =& ntsFormFactory::getInstance();
$form =& $ff->makeForm( dirname(__FILE__) . '/form', $appDetails );
?>
<h2><?php echo M('Appointment Details'); ?></h2>

<?php
$now = time();
$service = ntsObjectFactory::get( 'service' );
$service->setId( $NTS_VIEW['object']->getProp('service_id') );

$completedStatus = $NTS_VIEW['object']->getProp('completed');
$approvedStatus = $NTS_VIEW['object']->getProp('approved');
?>
<p>
<?php if( ! $completedStatus ) : ?>
	<?php $minCancel = $service->getProp('min_cancel'); ?>
	<?php if( ($now + $minCancel) > $NTS_VIEW['object']->getProp('starts_at') ) : ?>
		<?php echo M('You cannot cancel or reschedule this appointment now'); ?>
	<?php else : ?>
<?php		if( $canCancel ) : ?>
				<a href="<?php echo ntsLink::makeLink('-current-/../edit/cancel', '', array('_id' => $NTS_VIEW['object']->getId(), 'return' => 'all') ); ?>"><?php echo M('Cancel'); ?></a>
<?php		endif; ?>
<?php		if( $canReschedule ) : ?>
<?php
				$t->setTimestamp( $NTS_VIEW['object']->getProp('starts_at') );
				$oldDate = $t->formatDate_Db();
?>
				<a href="<?php echo ntsLink::makeLink('customer', '', array('reschedule' => $NTS_VIEW['object']->getId(), 'cal' => $oldDate) ); ?>"><?php echo M('Reschedule'); ?></a>
<?php		endif; ?>
	<?php endif; ?>
<?php endif; ?>
<p>
<table>
<tr>
	<th><?php echo M('Status'); ?></th>
<?php
if( $completedStatus ){
	switch( $completedStatus ){
		case HA_STATUS_NOSHOW:
			$class = 'alert';
			$message = M('No Show');
			break;
		case HA_STATUS_CANCELLED:
			$class = 'alert';
			$message = M('Cancelled');
			break;
		case HA_STATUS_COMPLETED:
			$class = 'ok';
			$message = M('Completed');
			break;
		}
	}
else {
	if( $approvedStatus ){
		$class = 'ok';
		$message = M('Approved');
		}
	else {
		$class = 'alert';
		$message = M('Pending');
		}
	}
?>
	<td>
	<b class="<?php echo $class; ?>"><?php echo $message; ?></b>
<?php if( $completedStatus ) : ?>
<?php	if( $customerAcknowledge && (! $NTS_VIEW['object']->getProp('_ack')) ) : ?>
				<a href="<?php echo ntsLink::makeLink('-current-/../edit/ack', '', array('_id' => $NTS_VIEW['object']->getId(), 'return' => 'all') ); ?>"><?php echo M('Acknowledge Completion'); ?></a>
<?php	endif; ?>
<?php endif; ?>
	</td>
</tr>

<?php
$totalAmount = $object->getProp('price');
?>

<?php if( $totalAmount > 0 ) : ?>
<tr>
	<th><?php echo M('Payment'); ?></th>
<?php
	$thisView = '';
	$invoices = $NTS_VIEW['object']->getInvoices();
	$orders = $NTS_VIEW['object']->getProp('_order');
	if( $invoices ){
		$totalAmount = $NTS_VIEW['object']->getTotalAmount();
		$paidAmount = $NTS_VIEW['object']->getPaidAmount(); 

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
		if( $paidAmount >= $totalAmount ){
			$class = 'ok';
			}
		elseif( $paidAmount > 0 ){
			$class = 'warning';
			}
		else {
			$class = 'alert';
			}
		}
	elseif( $orders && isset($orders[0]) ){
		/* find order */
		$order = ntsObjectFactory::get( 'order' );
		$order->setId( $orders[0] );
		$thisView .= ntsView::objectTitle($order);
		$class = 'ok';
		}
?>
	<td>
	<b class="<?php echo $class; ?>"><?php echo $thisView; ?></b>
	</td>
</tr>
<?php endif; ?>

<tr>
	<th><?php echo M('Date and Time'); ?></th>
	<td>
<?php 
			$t->setTimestamp( $NTS_VIEW['object']->getProp('starts_at') );
?>
		<b>
		<?php echo $t->formatWeekday(); ?>, <?php echo $t->formatDate(); ?><br>
		<?php 
			$showEndTime = $conf->get('showEndTime');
			$timeView = $showEndTime ? $t->formatTime($NTS_VIEW['object']->getProp('duration') ) : $t->formatTime();
		?>
		<?php echo $timeView; ?>
		</b>
	</td>
</tr>

<tr>
	<th><?php echo M('Service'); ?></th>
	<td>
	<?php 	
	$serviceView = ntsView::appServiceView( $NTS_VIEW['object'] );
	echo nl2br( $serviceView );
	?>
	</td>
</tr>

<?php if( (! NTS_SINGLE_LOCATION) ) : ?>
<tr>
	<th><?php echo M('Location'); ?></th>
	<td>
		<b><?php echo ntsView::objectTitle($NTS_VIEW['location']); ?></b>
	</td>
</tr>
<?php endif; ?>

<?php if( (! NTS_SINGLE_RESOURCE) ) : ?>
<tr>
	<th><?php echo M('Bookable Resource'); ?></th>
	<td>
		<b><?php echo ntsView::objectTitle($NTS_VIEW['resource']); ?></b>
	</td>
</tr>
<?php endif; ?>

<?php if( ! $NTS_VIEW['isRequest'] ) : ?>
	<?php $form->display(); ?>
<?php else: ?>
<?php
	$otherDetails = array(
		'service_id'	=> $NTS_VIEW['object']->getProp('service_id'),
		);
	$om =& objectMapper::getInstance();
	$fields = $om->getFields( 'appointment', 'external', $otherDetails );
	reset( $fields );
?>
<?php	foreach( $fields as $f ) : ?>
	<tr>
	<th><?php echo $f[1]; ?></th>
	<td><?php echo  $NTS_VIEW['object']->getProp($f[0]); ?></td>
	</tr>
<?php	endforeach; ?>
<?php endif; ?>
</table>
