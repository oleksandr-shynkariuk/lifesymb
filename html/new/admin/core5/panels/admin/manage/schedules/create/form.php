<?php
global $NTS_TIME_WEEKDAYS;
$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

$allLocs = ntsObjectFactory::getAllIds( 'location' );
$allRess = ntsObjectFactory::getAllIds( 'resource' );
$allSers = ntsObjectFactory::getAllIds( 'service' );

$cal = ntsLib::getVar( 'admin/manage/schedules:cal' );

$minStart = NTS_TIME_STARTS;
$maxEnd = NTS_TIME_ENDS;

$action = $this->getValue('action');
$when = $this->getValue('showWhen');
$currentWhen = $this->getValue('when');
$slotType = $this->getValue('slot_type');

$whenLabels = array(
	'date'		=> M('This Date Only'),
	'range'		=> M('Every Week'),
	);

$params = array(
	'id'	=> $this->getValue('id'),
	);
echo $this->makePostParams('-current-', '', $params);

$minDuration = -1;
reset( $sers );
foreach( $sers as $objId ){
	$obj = ntsObjectFactory::get( 'service' );
	$obj->setId( $objId );
	$options[] = array( $objId, ntsView::objectTitle($obj) );
	$thisDuration = $obj->getProp( 'duration' );
	if( ($minDuration == -1) || ($thisDuration < $minDuration) ){
		$minDuration = $thisDuration;
		}
	$durations[] = array( $obj->getId(), $thisDuration );
	}
?>

<?php
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
if( count($ress) == 1 ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'resource_id',
			'value'	=> $ress[0],
			)
		);
	}
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
<tbody>
<?php if( count($allRess) > 1 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Bookable Resource'); ?></td>
<td class="ntsFormValue">
<?php 	if( count($ress) == 1 ) : ?>
<?php
			$obj = ntsObjectFactory::get( 'resource' );
			$obj->setId( $ress[0] );
			$objView = ntsView::objectTitle( $obj );
?>
<?php 		echo $objView; ?>
<?php 	else : ?>
<?php
			$options = array();
			reset( $ress );
			foreach( $ress as $objId ){
				$obj = ntsObjectFactory::get( 'resource' );
				$obj->setId( $objId );
				$options[] = array( $objId, ntsView::objectTitle($obj) );
				}
			echo $this->makeInput (
			/* type */
				'select',
			/* attributes */
				array(
					'id'		=> 'resource_id',
					'options'	=> $options,
					)
				);
?>
<?php 	endif; ?>
</td>
</tr>
<?php endif; ?>

<?php if( count($allLocs) > 1 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Locations'); ?></td>
<td class="ntsFormValue">
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
<td class="ntsFormValue">
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
<td class="ntsFormLabel"><?php echo M('Slot Type'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'radio',
/* attributes */
	array(
		'id'		=> 'slot_type',
		'value'		=> 'range',
		'default'	=> $slotType
		)
	);
?> <?php echo M('Time Range'); ?>
<?php
echo $this->makeInput (
/* type */
	'radio',
/* attributes */
	array(
		'id'		=> 'slot_type',
		'value'		=> 'fixed',
		'default'	=> $slotType
		)
	);
?> <?php echo M('Fixed Time'); ?>
</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_details_range">
<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'starts_at_range',
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
		'id'		=> 'ends_at_range',
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
				'compareWithField' => 'starts_at_range',
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

<tbody id="<?php echo $this->formId; ?>_details_fixed">
<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'starts_at_fixed',
		'conf'	=> array(
			'min'	=> $minStart,
			'max'	=> $maxEnd - $minDuration,
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

<?php if( count($when) > 1 ) : ?>
<tbody>
<tr>
<td class="ntsFormLabel"><?php echo M('When'); ?></td>
<td class="ntsFormValue">
<?php foreach( $when as $wh ) : ?>
<?php
		echo $this->makeInput (
		/* type */
			'radio',
		/* attributes */
			array(
				'id'		=> 'when',
				'value'		=> $wh,
				)
			);
?> <?php echo $whenLabels[$wh]; ?>
<?php 	endforeach; ?>
</td>
</tr>
</tbody>
<?php else : ?>
<?php
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'		=> 'when',
			)
		);
?> 
<?php endif; ?>

<?php if( in_array('date', $when) ) : ?>
<tbody id="<?php echo $this->formId; ?>_when_date"<?php if($currentWhen != 'date'){echo ' style="display: none;"';}; ?>>
<tr>
<td class="ntsFormLabel"><?php echo M('Date'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'hidden',
/* attributes */
	array(
		'id'	=> 'date',
		'value'	=> $cal,
		)
	);
$NTS_VIEW['t']->setDateDb( $cal );
$thisDateView = $NTS_VIEW['t']->formatWeekdayShort() . ', ' . $NTS_VIEW['t']->formatDate();
?>
<?php echo $thisDateView; ?>
</td>
</tr>
</tbody>
<?php endif; ?>

<?php if( in_array('range', $when) ) : ?>
<tbody id="<?php echo $this->formId; ?>_when_range"<?php if($currentWhen != 'range'){echo ' style="display: none;"';}; ?>>
<tr>
<td class="ntsFormLabel"><?php echo M('Weekdays'); ?></td>
<td class="ntsFormValue">
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
<td class="ntsFormValue">
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
<?php endif; ?>

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
			'default'	=> 3 * 60 * 60,
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
			'default'	=> 8 * 7 * 24 * 60 * 60,
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
<?php echo $this->makePostParams('-current-', 'create' ); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Add'); ?>">
</td>
</tr>
</tbody>
</table>

<?php
$jsDurationOptions = array();
reset( $durations );
foreach( $durations as $du ){
	$jsDurationOptions[] = '[' . join(',', $du) . ']';
	}
?>

<script language="JavaScript">
jQuery('#<?php echo $this->formId; ?>when').live("change", function() {
<?php foreach( $when as $wh ) : ?>
	jQuery('#<?php echo $this->formId; ?>_when_<?php echo $wh; ?>').toggle();
<?php endforeach; ?>
	});
	
jQuery('#<?php echo $this->formId; ?>slot_type').live("change", function() {
	jQuery('#<?php echo $this->formId; ?>_details_range').toggle();
	jQuery('#<?php echo $this->formId; ?>_details_fixed').toggle();
	});

<?php if( $slotType == 'range' ) : ?>
jQuery('#<?php echo $this->formId; ?>_details_range').show();
jQuery('#<?php echo $this->formId; ?>_details_fixed').hide();
<?php else : ?>
jQuery('#<?php echo $this->formId; ?>_details_range').hide();
jQuery('#<?php echo $this->formId; ?>_details_fixed').show();
<?php endif; ?>

/* check the options of start */
var STARTS_FIXED<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>starts_at_fixed');
var STARTS_AT<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>starts_at_range');
var ENDS_AT<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>ends_at_range');
var INTERVAL<?php echo $this->formId; ?> = document.getElementById('<?php echo $this->formId; ?>selectable_every');

var SERVICE_DURATIONS<?php echo $this->formId; ?> = [<?php echo join(',', $jsDurationOptions); ?>];

var STARTS_FIXED_OPTIONS<?php echo $this->formId; ?> = new Array( STARTS_FIXED<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < STARTS_FIXED<?php echo $this->formId; ?>.options.length; ii++ ){
	STARTS_FIXED_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( STARTS_FIXED<?php echo $this->formId; ?>.options[ii].value, STARTS_FIXED<?php echo $this->formId; ?>.options[ii].text );
	}

var STARTS_AT_OPTIONS<?php echo $this->formId; ?> = new Array( STARTS_AT<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < STARTS_AT<?php echo $this->formId; ?>.options.length; ii++ ){
	STARTS_AT_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( STARTS_AT<?php echo $this->formId; ?>.options[ii].value, STARTS_AT<?php echo $this->formId; ?>.options[ii].text );
	}
var ENDS_AT_OPTIONS<?php echo $this->formId; ?> = new Array( ENDS_AT<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < ENDS_AT<?php echo $this->formId; ?>.options.length; ii++ ){
	ENDS_AT_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( ENDS_AT<?php echo $this->formId; ?>.options[ii].value, ENDS_AT<?php echo $this->formId; ?>.options[ii].text );
	}
var INTERVAL_OPTIONS<?php echo $this->formId; ?> = new Array( INTERVAL<?php echo $this->formId; ?>.options.length );
for( ii = 0; ii < INTERVAL<?php echo $this->formId; ?>.options.length; ii++ ){
	INTERVAL_OPTIONS<?php echo $this->formId; ?>[ii] = new Array( INTERVAL<?php echo $this->formId; ?>.options[ii].value, INTERVAL<?php echo $this->formId; ?>.options[ii].text );
	}

var currentDuration = 0;
var currentServiceId = jQuery('#<?php echo $this->formId; ?>service_id').val();
for( ii = 0; ii < SERVICE_DURATIONS<?php echo $this->formId; ?>.length; ii++ ){
	if( SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][0] == currentServiceId ){
		currentDuration = SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][1];
		}
	}

function setEndOption<?php echo $this->formId; ?>(){
	var currentDuration = 0;
	var currentServiceId = jQuery('#<?php echo $this->formId; ?>service_id').val();
	for( ii = 0; ii < SERVICE_DURATIONS<?php echo $this->formId; ?>.length; ii++ ){
		if( SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][0] == currentServiceId ){
			currentDuration = SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][1];
			}
		}

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
	var currentDuration = 0;
	var currentServiceId = jQuery('#<?php echo $this->formId; ?>service_id').val();
	for( ii = 0; ii < SERVICE_DURATIONS<?php echo $this->formId; ?>.length; ii++ ){
		if( SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][0] == currentServiceId ){
			currentDuration = SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][1];
			}
		}

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
	var currentDuration = 0;
	var currentServiceId = jQuery('#<?php echo $this->formId; ?>service_id').val();
	for( ii = 0; ii < SERVICE_DURATIONS<?php echo $this->formId; ?>.length; ii++ ){
		if( SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][0] == currentServiceId ){
			currentDuration = SERVICE_DURATIONS<?php echo $this->formId; ?>[ii][1];
			}
		}

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

var currentType = jQuery('#<?php echo $this->formId; ?>slot_type:checked').val();
if( currentType == 'range' ){
	setEndOption<?php echo $this->formId; ?>();
	setIntervalOption<?php echo $this->formId; ?>();
	}
else {
	setFixedStartOption<?php echo $this->formId; ?>();
	}

jQuery('#<?php echo $this->formId; ?>starts_at_range').live("change", function() {
	setEndOption<?php echo $this->formId; ?>();
	setIntervalOption<?php echo $this->formId; ?>();
	});

jQuery('#<?php echo $this->formId; ?>ends_at_range').live("change", function() {
	setStartOption<?php echo $this->formId; ?>();
	setIntervalOption<?php echo $this->formId; ?>();
	});

jQuery('#<?php echo $this->formId; ?>service_id').live("change", function() {
	var currentType = jQuery('#<?php echo $this->formId; ?>slot_type:checked').val();
	if( currentType == 'range' ){
		setEndOption<?php echo $this->formId; ?>();
		setIntervalOption<?php echo $this->formId; ?>();
		}
	else {
		setFixedStartOption<?php echo $this->formId; ?>();
		}
	});
</script>