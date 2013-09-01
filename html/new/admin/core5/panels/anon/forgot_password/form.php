<H2><?php echo M('Forgot Your Password'); ?>?</H2>

<table>
<tr>
	<th><?php echo M('Email'); ?> *</th>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'email',
			'attr'		=> array(
				'size'	=> 42,
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

<tr>
<td>&nbsp;</td>
<td>
	<DIV CLASS="buttonBar">
	<?php echo $this->makePostParams('-current-', 'reset' ); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Send New Password'); ?>">
	</DIV>
</td>
</tr>
</table>