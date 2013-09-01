<?php
$id = $this->getValue('id');
/* form params - used later for validation */
$this->setParams(
	array(
		'myId'	=> $id,
		)
	);
?>
<table>
<tr>
	<td class="ntsFormLabel"><?php echo M('Password'); ?> *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'password',
	/* attributes */
		array(
			'id'		=> 'password',
			'attr'		=> array(
				'size'	=> 16,
				),
			'required'	=> 1,
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
	<td class="ntsFormLabel"><?php echo M('Confirm Password'); ?> *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'password',
	/* attributes */
		array(
			'id'		=> 'password2',
			'attr'		=> array(
				'size'	=> 16,
				),
			'required'	=> 1,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Please confirm the password'),
				),
			array(
				'code'		=> 'confirmPassword.php', 
				'error'		=> M("Passwords don't match!"),
				'params'	=> array(
					'mainPasswordField' => 'password',
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
<?php echo $this->makePostParams('-current-', 'update_password', array('id' => $this->getValue('id')) ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Change Password'); ?>">
</td>
</tr>
</table>