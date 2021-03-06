<?php
$date = $this->getValue('date');
$slotType = $this->getValue('selectable_every') ? 'range' : 'fixed';

$allLocs = ntsObjectFactory::getAllIds( 'location' );
$allRess = ntsObjectFactory::getAllIds( 'resource' );
$allSers = ntsObjectFactory::getAllIds( 'service' );

global $NTS_TIME_WEEKDAYS;
$params = array(
	'id'	=> $this->getValue('id'),
	);
echo $this->makePostParams('-current-', '', $params);

$minStart = NTS_TIME_STARTS;
$maxEnd = NTS_TIME_ENDS;

$minDuration = 0;
$durations = array();
$serviceIds = $this->getValue('service_id');
if( (count($serviceIds) == 1) && ($serviceIds[0] == 0) ){
	$checkServiceIds = $allSers;
	}
else {
	$checkServiceIds = $serviceIds;
	}

reset( $checkServiceIds );
foreach( $checkServiceIds as $sid ){
	$service = ntsObjectFactory::get( 'service' );
	$service->setId( $sid );
	$thisDuration = $service->getProp( 'duration' );
	$durations[] = array( $service->getId(), $thisDuration );
	if( (! $minDuration) || ($thisDuration < $minDuration) )
		$minDuration = $thisDuration;
	}
$thisDuration = $minDuration;

if( count($allLocs) == 1 ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'location_id',
			'value'	=> $allLocs[0],
			)
		);
	}
echo $this->makeInput (
/* type */
	'hidden',
/* attributes */
	array(
		'id'		=> 'resource_id',
		)
	);
if( count($allSers) == 1 ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'service_id',
			'value'	=> $allSers[0],
			)
		);
	}
?>

<table class="ntsForm">

<?php if( $slotType == 'range' ) : ?>
<tbody>
<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'starts_at',
		'conf'	=> array(
			'min'	=> $minStart,
			'max'	=> $maxEnd - $minDuration,
			),
		'default'	=> $minStart
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
 - 
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'ends_at',
		'conf'	=> array(
			'min'	=> $minStart + $minDuration,
			'max'	=> $maxEnd,
			),
		'default'	=> $maxEnd
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> "Slot can't start before end",
			'params'	=> array(
				'compareWithField' => 'starts_at',
				),
			)
		)
	);
?>
 <?php echo M('Interval'); ?>: 
<?php
$options = array( 3, 5, 6, 9, 10, 12, 15, 18, 20, 21, 24, 25, 27, 30, 40, 45, 50, 60, 75, 90, 2*60, 2.5*60, 3*60, 4*60, 5*60, 6*60, 8*60, 9*60, 12*60, 18*60, 24*60 );
$selectabeOptions = array();
foreach( $options as $o ){
	if( $o % NTS_TIME_UNIT )
		continue;
	if( $o > ($maxEnd - $minStart) )
		continue;
	$selectabeOptions[] = array( 60 * $o, $o );
	}

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'selectable_every',
		'options'	=> $selectabeOptions,
		)
	);
?> <?php echo M('Minutes'); ?>
</td>
</tr>
</tbody>

<?php else : ?>

<tbody id="<?php echo $this->formId; ?>_details">
<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'starts_at',
		'conf'	=> array(
			'min'	=> $minStart,
//			'max'	=> $maxEnd - $minDuration,
			'max'	=> $maxEnd,
			),
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
</td>
</tr>

</tbody>
<?php endif; ?>

<tbody>
<tr>
<td class="ntsFormLabel"><?php echo M('Capacity'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'		=> 'capacity',
		'attr'		=> array(
			'size'	=> 3,
			),
		'default'	=> 1,
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'integer.php', 
			'error'		=> M('Numbers only'),
			),
		)
	);
?> <?php echo M('Seats'); ?>
</td>
</tr>

<?php if( count($allLocs) > 1 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Locations'); ?></td>
<td class="ntsFormValue" colspan="3">
<?php
	echo $this->makeInput (
	/* type */
		'locations',
	/* attributes */
		array(
			'id'	=> 'location_id',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required Field'),
				),
			)
		);
?>
</td>
</tr>
<?php endif; ?>

<?php if( count($allSers) > 1 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Services'); ?></td>
<td class="ntsFormValue" colspan="3">
<?php
	echo $this->makeInput (
	/* type */
		'services',
	/* attributes */
		array(
			'id'	=> 'service_id',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required Field'),
				),
			)
		);
?>
</td>
</tr>
<?php endif; ?>

<tr>
<td class="ntsFormLabel"><?php echo M('Weekdays'); ?></td>
<td class="ntsFormValue" colspan="3">
<?php
	echo $this->makeInput (
	/* type */
		'date/Weekday',
	/* attributes */
		array(
			'id'	=> 'applied_on',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required Field'),
				),
			)
		);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Dates'); ?></td>
<td class="ntsFormValue" colspan="3">
<?php
	echo $this->makeInput (
	/* type */
		'date/Calendar',
	/* attributes */
		array(
			'id'		=> 'valid_from',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> 'Please enter the from date',
				),
			)
		);
?>
 - 
<?php
	echo $this->makeInput (
	/* type */
		'date/Calendar',
	/* attributes */
		array(
			'id'		=> 'valid_to',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> 'Please enter the from date',
				),
			array(
				'code'		=> 'greaterEqualThan.php', 
				'error'		=> "This date can't be before the from date",
				'params'	=> array(
					'compareWithField' => 'valid_from',
					),
				),
			)
		);
?>
</td>
</tr>
</tbody>

<tbody>
<tr>
	<td class="ntsFormLabel"><?php echo M('Min Advance Booking'); ?></td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'period/MinHourDayWeek',
	/* attributes */
		array(
			'id'		=> 'min_from_now',
			'attr'		=> array(
				),
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Max Advance Booking'); ?></td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'period/MinHourDayWeek',
	/* attributes */
		array(
			'id'		=> 'max_from_now',
			'attr'		=> array(
				),
			),
	/* validators */
		array(
			array(
				'code'		=> 'greaterEqualThan.php', 
				'error'		=> M('This should not be smaller than the min advance booking'),
				'params'	=> array(
					'compareWithField'	=> 'min_from_now',
					),
				),
			)
		);
	?>
	</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update'); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</td>
</tr>
</tbody>
</table>

<script language="JavaScript">
/* check the options of start */
var STARTS_FIXED<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>starts_at');
var STARTS_AT<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>starts_at');

var CURRENT_DURATION<?php echo $this->formId; ?> = <?php echo $thisDuration; ?>;

var STARTS_FIXED_OPTIONS<?php echo $this->formId; ?> = new Array( STARTS_FIXED<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < STARTS_FIXED<?php echo $this->formId; ?>.options.length; ii++ ){
	STARTS_FIXED_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( STARTS_FIXED<?php echo $this->formId; ?>.options[ii].value, STARTS_FIXED<?php echo $this->formId; ?>.options[ii].text );
	}
var STARTS_AT_OPTIONS<?php echo $this->formId; ?> = new Array( STARTS_AT<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < STARTS_AT<?php echo $this->formId; ?>.options.length; ii++ ){
	STARTS_AT_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( STARTS_AT<?php echo $this->formId; ?>.options[ii].value, STARTS_AT<?php echo $this->formId; ?>.options[ii].text );
	}

<?php if ( $slotType == 'range' ) : ?>
var ENDS_AT<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>ends_at');
var INTERVAL<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>selectable_every');

var ENDS_AT_OPTIONS<?php echo $this->formId; ?> = new Array( ENDS_AT<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < ENDS_AT<?php echo $this->formId; ?>.options.length; ii++ ){
	ENDS_AT_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( ENDS_AT<?php echo $this->formId; ?>.options[ii].value, ENDS_AT<?php echo $this->formId; ?>.options[ii].text );
	}
var INTERVAL_OPTIONS<?php echo $this->formId; ?> = new Array( INTERVAL<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < INTERVAL<?php echo $this->formId; ?>.options.length; ii++ ){
	INTERVAL_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( INTERVAL<?php echo $this->formId; ?>.options[ii].value, INTERVAL<?php echo $this->formId; ?>.options[ii].text );
	}
<?php endif; ?>

function setEndOption<?php echo $this->formId; ?>(){
	var currentDuration = CURRENT_DURATION<?php echo $this->formId; ?>;

	var currentValue = ENDS_AT<?php echo $this->formId; ?>.value;
	ENDS_AT<?php echo $this->formId; ?>.options.length = 0;
	var currentStart = STARTS_AT<?php echo $this->formId; ?>.value;
	var checkWith = parseInt(currentStart) + parseInt(currentDuration);

	for( ii = 0; ii < ENDS_AT_OPTIONS<?php echo $this->formId; ?>.length; ii++ ){
		var testOption = ENDS_AT_OPTIONS<?php echo $this->formId; ?>[ii];
		if( testOption[0] >= checkWith ){
			var selectMe = (currentValue == testOption[0]) ? true : false;
			ENDS_AT<?php echo $this->formId; ?>.options.add( new Option(testOption[1], testOption[0], selectMe, selectMe) );
			}
		}
	}

function setStartOption<?php echo $this->formId; ?>(){
	var currentDuration = CURRENT_DURATION<?php echo $this->formId; ?>;

	var currentValue = STARTS_AT<?php echo $this->formId; ?>.value;
	STARTS_AT<?php echo $this->formId; ?>.options.length = 0;
	var currentEnd = ENDS_AT<?php echo $this->formId; ?>.value;
	var checkWith = parseInt(currentEnd) - parseInt(currentDuration);

	for( ii = 0; ii < STARTS_AT_OPTIONS<?php echo $this->formId; ?>.length; ii++ ){
		var testOption = STARTS_AT_OPTIONS<?php echo $this->formId; ?>[ii];
		if( testOption[0] <= checkWith ){
			var selectMe = (currentValue == testOption[0]) ? true : false;
			STARTS_AT<?php echo $this->formId; ?>.options.add( new Option(testOption[1], testOption[0], selectMe, selectMe) );
			}
		}
	}

function setFixedStartOption<?php echo $this->formId; ?>(){
	var currentDuration = CURRENT_DURATION<?php echo $this->formId; ?>;

	var currentValue = STARTS_FIXED<?php echo $this->formId; ?>.value;
	STARTS_FIXED<?php echo $this->formId; ?>.options.length = 0;
	var currentEnd = STARTS_FIXED_OPTIONS<?php echo $this->formId; ?>[STARTS_FIXED_OPTIONS<?php echo $this->formId; ?>.length - 1][0];
	var checkWith = parseInt(currentEnd) - parseInt(currentDuration);

	for( ii = 0; ii < STARTS_FIXED_OPTIONS<?php echo $this->formId; ?>.length; ii++ ){
		var testOption = STARTS_FIXED_OPTIONS<?php echo $this->formId; ?>[ii];
		if( testOption[0] <= checkWith ){
			var selectMe = (currentValue == testOption[0]) ? true : false;
			STARTS_FIXED<?php echo $this->formId; ?>.options.add( new Option(testOption[1], testOption[0], selectMe, selectMe) );
			}
		}
	}

function setIntervalOption<?php echo $this->formId; ?>(){
/* interval options */
	var currentValue = INTERVAL<?php echo $this->formId; ?>.value;
	var maxDuration = ENDS_AT<?php echo $this->formId; ?>.value - STARTS_AT<?php echo $this->formId; ?>.value;
	INTERVAL<?php echo $this->formId; ?>.options.length = 0;

	for( ii = 0; ii < INTERVAL_OPTIONS<?php echo $this->formId; ?>.length; ii++ ){
		var testOption = INTERVAL_OPTIONS<?php echo $this->formId; ?>[ii];
		if( maxDuration >= testOption[0] ){
			var selectMe = (currentValue == testOption[0]) ? true : false;
			INTERVAL<?php echo $this->formId; ?>.options.add( new Option(testOption[1], testOption[0], selectMe, selectMe) );
			}
		}
	}

<?php if ( $slotType == 'range' ) : ?>
	setEndOption<?php echo $this->formId; ?>();
	setIntervalOption<?php echo $this->formId; ?>();
<?php else : ?>
//	setFixedStartOption<?php echo $this->formId; ?>();
<?php endif; ?>

<?php if ( $slotType == 'range' ) : ?>
	jQuery('#<?php echo $this->formId; ?>starts_at').live("change", function() {
		setEndOption<?php echo $this->formId; ?>();
		setIntervalOption<?php echo $this->formId; ?>();
		});

	jQuery('#<?php echo $this->formId; ?>ends_at').live("change", function() {
		setStartOption<?php echo $this->formId; ?>();
		setIntervalOption<?php echo $this->formId; ?>();
		});
<?php endif; ?>
</script>