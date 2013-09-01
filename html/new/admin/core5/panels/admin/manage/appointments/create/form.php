<?php
$ntsConf =& ntsConf::getInstance();
$sendCcForAppointment = $ntsConf->get('sendCcForAppointment');

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

$appsCount = 1;

$locs = $showFull ? ntsLib::getVar( 'admin::locs' ) : ntsLib::getVar( 'admin::locs2' );
$ress = $showFull ? ntsLib::getVar( 'admin::ress' ) : ntsLib::getVar( 'admin::ress2' );
$sers = $showFull ? ntsLib::getVar( 'admin::sers' ) : ntsLib::getVar( 'admin::sers2' );

$re = array();
$re[0] = $lid ? $lid : '\d+';
$re[1] = $rid ? $rid : '\d+';
$re[2] = $sid ? $sid : '\d+';
$re[3] = $time ? $time : '\d+';

$class = 'appointment';
$otherDetails = array(
	'service_id'	=> $sid,
	);
$om =& objectMapper::getInstance();
$customFields = $om->getFields( $class, 'internal', $otherDetails );

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
?>
<table class="ntsForm">
<?php if( $reschedule ) : ?>
<?php
$rowspan = 4;
if( count($ress) > 1 )
	$rowspan++;
if( count($locs) > 1 )
	$rowspan++;
$rowspan += count($customFields);
?>
<tr>
<td></td>
<td style="width: 12em;"></td>
<td rowspan="<?php echo $rowspan; ?>" style="font-size: 1.25em; padding: 0.25em 0.25em; width: 2em; vertical-align: middle;">
&gt;&gt;
</td>
<td></td>
</tr>
<?php endif; ?>

<?php
$paymentOptions = array();
if( $this->formAction == 'display' ){
	if( ! $hidden ){
		require( dirname(__FILE__) . '/form-location.php' );
		require( dirname(__FILE__) . '/form-resource.php' );
		require( dirname(__FILE__) . '/form-service.php' );
		require( dirname(__FILE__) . '/form-date.php' );
		require( dirname(__FILE__) . '/form-time.php' );
		}

	$displayCustomer = ( $lid && $rid && $sid && $time && (! $fixCustomer) );
	if( $displayCustomer && $cid ){
		require( dirname(__FILE__) . '/form-customer.php' );
		}
  
	$displayConfirm = ( $lid && $rid && $sid && $time && $cid );
	if( $displayConfirm ){
		require( dirname(__FILE__) . '/form-custom.php' );
		require( dirname(__FILE__) . '/form-payment.php' );
		require( dirname(__FILE__) . '/form-confirm.php' );
		}
	}
else {
	if( $sendCcForAppointment ){
		require( dirname(__FILE__) . '/form-customer.php' );
		}
	require( dirname(__FILE__) . '/form-custom.php' );
	require( dirname(__FILE__) . '/form-payment.php' );
	}
?>
</table>