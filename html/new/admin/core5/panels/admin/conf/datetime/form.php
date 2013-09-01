<?php
/* tags */
$tm =& ntsEmailTemplateManager::getInstance();
$tags = $tm->getTags( 'common-header-footer' );
?>
<TABLE class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Company Timezone'); ?></td>
	<td class="ntsFormValue">
<?php
$timezoneOptions = ntsTime::getTimezones();
echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'companyTimezone',
		'options'	=> $timezoneOptions,
		)
	);
$t = new ntsTime;
$timeString = $t->formatFull();
?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td style="font-style: italic;">
<?php echo $timeString; ?>
</td>

<tr>
<td class="ntsFormLabel"><?php echo M('Date Format'); ?></td>
<td class="ntsFormValue">
<?php
$dateFormats = array( 'd/m/Y', 'd-m-Y', 'n/j/Y', 'Y/m/d', 'd.m.Y', 'j M Y' );
$dateFormatsOptions = array();
$t = new ntsTime;
reset( $dateFormats );
foreach( $dateFormats as $f ){
	$t->dateFormat = $f;
	$dateFormatsOptions[] = array( $f, $t->formatDate() );
	}

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'dateFormat',
		'options'	=> $dateFormatsOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Time Format'); ?></td>
<td class="ntsFormValue">
<?php
$timeFormats = array( 'H:i', 'g:i A');
$timeFormatsOptions = array();
reset( $timeFormats );
foreach( $timeFormats as $f ){
	$timeFormatsOptions[] = array( $f, date($f) );
	}

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'timeFormat',
		'options'	=> $timeFormatsOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Max Measure For Duration Display'); ?></td>
<td class="ntsFormValue">
<?php
$limitOptions = array(
	array( 'minute', M('Minute') ),
	array( 'hour', M('Hour') ),
	array( 'day', M('Day') ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'limitTimeMeasure',
		'options'	=> $limitOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td style="font-style: italic;">
<?php echo M('If set to Minute for example, it will show 90 Minutes rather than 1 Hour 30 Minutes.'); ?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Week Starts On'); ?></td>
<td class="ntsFormValue">
<?php
$weekStartsOnOptions = array(
	array( 1, M('Monday') ),
	array( 0, M('Sunday') ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'weekStartsOn',
		'options'	=> $weekStartsOnOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Time Unit'); ?></td>
<td class="ntsFormValue">
<?php
$timeunitOptions = array(
	array( 3, 3 ),
	array( 5, 5 ),
	array( 10, 10 ),
	array( 15, 15 ),
	array( 30, 30 ),
	array( 60, 60 ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'timeUnit',
		'options'	=> $timeunitOptions,
		)
	);
?> <?php echo M('Minutes'); ?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Working Time Start'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'timeStarts',
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Working Time End'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'timeEnds',
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required Field'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> "This can't be before the working time start",
			'params'	=> array(
				'compareWithField' => 'timeStarts',
				),
			)
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Months To Show'); ?></td>
<td class="ntsFormValue">
<?php
$monthsToShowOptions = array(
	array( 1, 1 ),
	array( 2, 2 ),
	array( 3, 3 ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'monthsToShow',
		'options'	=> $monthsToShowOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Days To Show'); ?></td>
<td class="ntsFormValue">
<?php
$daysToShowOptions = array(
	array( 1, 1 ),
	array( 2, 2 ),
	array( 3, 3 ),
	array( 5, 5 ),
	array( 7, 7 ),
	array( 10, 10 ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'daysToShowCustomer',
		'options'	=> $daysToShowOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Months To Show Admin Side'); ?></td>
<td class="ntsFormValue">
<?php
$monthsToShowAdminOptions = array(
	array( 1, 1 ),
	array( 2, 2 ),
	array( 3, 3 ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'monthsToShowAdmin',
		'options'	=> $monthsToShowAdminOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</td>

</table>