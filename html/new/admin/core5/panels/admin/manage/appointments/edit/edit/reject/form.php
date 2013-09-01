<p>
<?php echo M('Please give a reason'); ?>
<p>
<?php
echo $this->makeInput (
	'textarea',
	array(
		'id'		=> 'reason',
		'attr'		=> array(
			'cols'	=> 32,
			'rows'	=> 3,
			),
		'default'	=> '',
		),
	array(
		)
	);
?>
<p>
<?php echo $this->makePostParams('-current-', 'reject' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Confirm'); ?>">
