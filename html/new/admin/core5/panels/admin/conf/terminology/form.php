<?php
$defaults = $this->getDefaults();
reset( $defaults );
$count = 1;
?>
<table class="ntsForm">
<?php foreach( $defaults as $dv ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M($dv[0], array(), true); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'term-' . $count,
			'attr'		=> array(
				'size'	=> 42,
				),
			'required'	=> 1,
			'default'	=> $dv[1],
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
<?php $count++; ?>
<?php endforeach; ?>
<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</td>
</tr>
</table>