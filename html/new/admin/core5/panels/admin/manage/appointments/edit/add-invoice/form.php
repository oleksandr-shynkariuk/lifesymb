<table class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Amount'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'amount',
			'attr'		=> array(
				'size'	=> 6,
				),
			'default'	=> '',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty', 
				'error'		=> M('Required field'),
				),
			array(
				'code'		=> 'number', 
				'error'		=> M('Numbers only'),
				),
			)
		);
?>
	</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'add'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Add Invoice'); ?>">
</td>
</tr>

</table>