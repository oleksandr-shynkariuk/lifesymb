<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Amount'); ?></td>
<td>
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
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'number.php', 
			'error'		=> M('Numbers only'),
			),
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Type'); ?></td>
<td>
<?php
$typeOptions = array(
	array( 'cash', M('Cash') ),
	array( 'check', M('Check') ),
	array( 'credit card', M('Credit Card') ),
	);

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'type',
		'options'	=> $typeOptions,
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
<td class="ntsFormLabel"><?php echo M('Notes'); ?></td>
<td>
<?php
echo $this->makeInput (
/* type */
	'textarea',
/* attributes */
	array(
		'id'		=> 'notes',
		'attr'		=> array(
			'cols'	=> 24,
			'rows'	=> 2,
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
<?php echo $this->makePostParams('-current-', 'add', array('invoice' => $this->getValue('invoice')) ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Add'); ?>">
</td>
</tr>
</table>