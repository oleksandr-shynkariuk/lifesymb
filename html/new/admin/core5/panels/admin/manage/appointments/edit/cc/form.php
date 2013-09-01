<?php
$ccTo = 3;
?>
<ul class="nts-listing">
<?php 	for( $cc = 1; $cc <= $ccTo; $cc++ ) : ?>
	<li>
<?php echo M('Email'); ?>: 
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'cc_' . $cc,
			'attr'		=> array(
				'size'	=> 32,
				),
			'default'	=> '',
			),
	/* validators */
		array(
			array(
				'code'		=> 'email', 
				'error'		=> M('Valid email required'),
				),
			)
		);
?>
	</li>
<?php 	endfor; ?>
</ul>

<?php echo $this->makePostParams('-current-', 'save'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
