<?php
$permissionOptions = array(
	array( 'not_allowed',	M('Not Allowed') ),
	array( 'not_shown',	M('Not Shown') ),
	array( 'allowed',		M('Company Confirmation Required') ),
	array( 'auto_confirm',	M('Auto Confirmed') ),
	);
$permissionOptions2 = array(
	array( 'keep_same',	M('Same As Above') ),
	array( 'auto_confirm',	M('Auto Confirmed') ),
	array( 'allowed',		M('Company Confirmation Required') ),
	);
$groups = array(
	array( -1, M('Non Registered Users') ),
	array( 0, M('Registered Users') ),
	);
?>
<table class="ntsForm">

<?php foreach( $groups as $g ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo $g[1]; ?></td>
	<td class="ntsFormValue">
<?php
	$ctlId = 'group' . $g[0];
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> $ctlId,
			'options'	=> $permissionOptions,
			)
		);
?>
	</td>
</tr>
<?php endforeach; ?>

<tr>
	<td class="ntsFormLabel"><?php echo M('After Payment'); ?></td>
	<td class="ntsFormValue">
<?php
	$ctlId = 'group-2';
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> $ctlId,
			'options'	=> $permissionOptions2,
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Cancellation/Reschedule Deadline'); ?> *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'period/MinHourDayWeek',
	/* attributes */
		array(
			'id'		=> 'min_cancel',
			'default'	=> 1 * 24 * 60 * 60,
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
	<td class="ntsFormLabel"><?php echo M('Available In Package Only'); ?></td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'pack_only',
			)
		);
	?>
	</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update', array('id' => $this->getValue('id')) ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</td>
</table>
