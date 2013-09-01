<?php
$cal = ntsLib::getVar( 'admin/manage:cal' );
$tm2 = ntsLib::getVar( 'admin::tm2' );
$t = $NTS_VIEW['t'];

$noCustomer = ntsLib::getVar( 'admin/manage/appointments/create::noCustomer' );
$fixCustomer = ntsLib::getVar( 'admin/manage/appointments/create::fixCustomer' );
$showFull = ntsLib::getVar( 'admin/manage/appointments/create::showFull' );
$reschedule = ntsLib::getVar( 'admin/manage/appointments/create::reschedule' );
$hidden = ntsLib::getVar( 'admin/manage/appointments/create::hidden' );

$available = $this->getValue( 'available' );
$lid = $this->getValue( 'location_id' );
$rid = $this->getValue( 'resource_id' );
$sid = $this->getValue( 'service_id' );
$time = $this->getValue( 'starts_at' );
$cid = $this->getValue( 'customer_id' );
$seats = 1;
$appsCount = count($time);

$locs = $showFull ? ntsLib::getVar( 'admin::locs' ) : ntsLib::getVar( 'admin::locs2' );
$ress = $showFull ? ntsLib::getVar( 'admin::ress' ) : ntsLib::getVar( 'admin::ress2' );
$sers = $showFull ? ntsLib::getVar( 'admin::sers' ) : ntsLib::getVar( 'admin::sers2' );

$re = array();
$re[0] = $lid ? $lid : '\d+';
$re[1] = $rid ? $rid : '\d+';
$re[2] = $sid ? $sid : '\d+';
$re[3] = $time ? $time : '\d+';

$class = 'appointment';
$om =& objectMapper::getInstance();

$customFields = array();
reset( $sid );
foreach( $sid as $sid2 ){
	$otherDetails = array(
		'service_id'	=> $sid2,
		);
	$thisCustomFields = $om->getFields( $class, 'internal', $otherDetails );
	if( $thisCustomFields ){
		$customFields[$sid2] = $thisCustomFields;
		}
	}

if( $lid ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'location_id',
			'value'	=> $lid,
			)
		);
	}
if( $rid ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'resource_id',
			'value'	=> $rid,
			)
		);
	}
if( $sid ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'service_id',
			'value'	=> $sid,
			)
		);
	}
if( $time ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'starts_at',
			'value'	=> $time,
			)
		);
	}
if( $cid ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'customer_id',
			'value'	=> $cid,
			)
		);
	}
$totalCount = count( $time );

if( $cid ){
	$customer = new ntsUser();
	$customer->setId( $cid );
	$ready = array();
	for( $ii = 0; $ii < $totalCount; $ii++ ){
		$ready[] = array(
			'location_id'	=> $lid[$ii],
			'resource_id'	=> $rid[$ii],
			'service_id'	=> $sid[$ii],
			'seats'			=> $seats,
			);
		}
	$availableOrders = $customer->checkOrders( $ready );
	}
?>

<table class="ntsForm">

<tr>
<td class="ntsFormLabel"><?php echo M('Appointments'); ?></td>
<td class="ntsFormValue">
	<table class="nts-listing">
	<tbody>
	<tr>
	<?php if( count($locs) > 1 ) : ?>
		<th><?php echo M('Location'); ?></th>
	<?php endif; ?>
	<?php if( count($ress) > 1 ) : ?>
		<th><?php echo M('Resource'); ?></th>
	<?php endif; ?>
	<th><?php echo M('Service'); ?></th>
	<th><?php echo M('Date'); ?></th>
	<th><?php echo M('Time'); ?></th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
	</tbody>

<?php for( $ii = 0; $ii < $totalCount; $ii++ ) : ?>
<?php
	$t->setTimestamp( $time[$ii] );
?>
	<tbody>
	<tr>
<?php if( count($locs) > 1 ) : ?>
	<td>
<?php
$obj = ntsObjectFactory::get( 'location' );
$obj->setId( $lid[$ii] );
$objView = ntsView::objectTitle( $obj );
?>
<?php echo $objView; ?>
	</td>
<?php endif; ?>

<?php if( count($ress) > 1 ) : ?>
	<td>
<?php
$obj = ntsObjectFactory::get( 'resource' );
$obj->setId( $rid[$ii] );
$objView = ntsView::objectTitle( $obj );
?>
<?php echo $objView; ?>
	</td>
<?php endif; ?>

	<td>
<?php
$obj = ntsObjectFactory::get( 'service' );
$obj->setId( $sid[$ii] );
$objView = ntsView::objectTitle( $obj );
?>
<?php echo $objView; ?>
	</td>
	<td><?php echo $t->formatDate(); ?></td>
	<td><?php echo $t->formatTime(); ?></td>
<?php
$re = '/^' . join('-', array($lid[$ii], $rid[$ii], $sid[$ii], $time[$ii])) . '$/';
$ok = ntsLib::reExistsInArray($re, $available);
?>
<?php if( $ok ) : ?>
<td class="ntsWorking"><?php echo M('Available'); ?></td>
<?php else : ?>
<td class="ntsAlert"><?php echo M('Not Available'); ?></td>
<?php endif; ?>

<td>
<?php
if( $cid )
	require( dirname(__FILE__) . '/form-paymentMultiple.php' );
?>
</td>
	</tr>
	</tbody>
<?php endfor; ?>
	</table>
</td>
</tr>

<?php
if( $this->formAction == 'display' ){
	$displayCustomer = ( $lid && $rid && $sid && $time && (! $fixCustomer) );
	if( $displayCustomer && $cid ){
		require( dirname(__FILE__) . '/form-customer.php' );
		}
  
	$displayConfirm = ( $lid && $rid && $sid && $time && $cid );
	if( $displayConfirm ){
		require( dirname(__FILE__) . '/form-customMultiple.php' );
		require( dirname(__FILE__) . '/form-confirm.php' );
		}
	}
else {
	require( dirname(__FILE__) . '/form-customMultiple.php' );
	}
?>
</table>