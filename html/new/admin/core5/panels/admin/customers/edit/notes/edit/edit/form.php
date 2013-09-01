<?php
echo $this->makeInput (
/* type */
	'textarea',
/* attributes */
	array(
		'id'		=> 'note',
		'attr'		=> array(
			'cols'	=> 36,
			'rows'	=> 4,
			),
		),
/* validators */
	array(
		)
	);
?>

<p>
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">