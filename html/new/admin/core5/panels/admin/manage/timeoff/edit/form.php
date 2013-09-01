<?php
$minStart = NTS_TIME_STARTS;
$maxEnd = NTS_TIME_ENDS;
?>
<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Bookable Resource'); ?></td>
<td class="ntsFormValue">
<?php
	$obj = ntsObjectFactory::get( 'resource' );
	$obj->setId( $this->getValue('resource_id') );
	$objView = ntsView::objectTitle( $obj );
?>
<?php 	echo $objView; ?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('From'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'starts_at_date',
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

<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'starts_at_time',
		'conf'	=> array(
			'min'	=> $minStart,
			'max'	=> $maxEnd,
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
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('To'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'ends_at_date',
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> M('The end time should be after the start'),
			'params'	=> array(
				'compareFields'	=> array(
					array('ends_at_date', 'starts_at_date'),
					array('ends_at_time', 'starts_at_time'),
					)
				),
			),
		)
	);
?>

<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'ends_at_time',
		'conf'	=> array(
			'min'	=> $minStart,
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
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Description'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'textarea',
/* attributes */
	array(
		'id'		=> 'description',
		'attr'		=> array(
			'cols'	=> 32,
			'rows'	=> 4,
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
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</td>
</tr>
</table>