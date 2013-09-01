<?php
$id = $this->getValue( 'id' );
$this->setParams(
	array(
		'myId'	=> $id,
		)
	);
?>
<table class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Title'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'title',
			'attr'		=> array(
				'size'	=> 42,
				),
			'default'	=> '',
			'required'	=> 1,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			array(
				'code'		=> 'checkUniqueProperty.php', 
				'error'		=> M('Already in use'),
				'params'	=> array(
					'prop'	=> 'title',
					'class'	=> 'service',
					'skipMe'	=> 1
					),
				),
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Type'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'class_type',
			'value'		=> 0,
			)
		);
?><?php echo M('Appointment'); ?>

<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'class_type',
			'value'		=> 1,
			)
		);
?><?php echo M('Class'); ?>
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
				'cols'	=> 42,
				'rows'	=> 6,
				),
			'default'	=> '',
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Duration'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'period/HourMinute',
	/* attributes */
		array(
			'id'		=> 'duration',
			),
	/* validators */
		array(
			array(
				'code'		=> 'greaterThan.php', 
				'error'		=> M('Required field'),
				'params'	=> array(
					'compareWith'	=> 0,
					)
				),
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Lead In'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'period/HourMinute',
	/* attributes */
		array(
			'id'		=> 'lead_in',
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Lead Out'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'period/HourMinute',
	/* attributes */
		array(
			'id'		=> 'lead_out',
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Base Price'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'	=> 'price',
			'attr'	=> array(
				'size'	=> 4,
				)
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Occupies Entire Location'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'	=> 'blocks_location',
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

</table>