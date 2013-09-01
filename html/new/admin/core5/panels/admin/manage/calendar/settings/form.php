<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Info In Appointment Slot'); ?></td>
<td class="ntsFormValue">
<?php
$fieldOptions = array( 
	array('',			M('No')),
	array('customer',	M('Customer')),
	array('service',	M('Service'))
	);
?>
<?php
echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> '_calendar_field',
		'options'	=> $fieldOptions,
		),
/* validators */
	array(
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Automatically Open Calendar'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'checkbox',
/* attributes */
	array(
		'id'		=> '_default_calendar',
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Default Appointments View'); ?></td>
<td class="ntsFormValue">
<?php
$fieldOptions = array( 
	array('calendar',	M('Calendar')),
	array('agenda',		M('Agenda'))
	);
?>
<?php
echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> '_default_apps_view',
		'options'	=> $fieldOptions,
		)
	);
?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<input type="submit" value="<?php echo M('Save'); ?>"></td>
</tr>
</table>