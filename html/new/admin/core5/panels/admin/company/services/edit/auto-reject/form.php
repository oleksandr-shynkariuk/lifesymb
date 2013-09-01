<table class="ntsForm">
<tbody>
<tr>
	<td class="ntsFormLabel"><?php echo M('Enable'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'enable-reject',
			'default'	=> 0,
			)
		);
?>
	</td>
</tr>
</tbody>

<tbody id="<?php echo $this->getName(); ?>reject-wrapper">
<tr>
	<td class="ntsFormLabel"><?php echo M('Reject Before'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'period/MinHourDayWeek',
	/* attributes */
		array(
			'id'		=> 'reject-before',
			'default'	=> 24 * 60 * 60,
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Booked Seats Less Than'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'less-than',
			'attr'		=> array(
				'size'	=> 6,
				),
			'default'	=> 2,
			'required'	=> 1,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			array(
				'code'		=> 'integer.php', 
				'error'		=> M('Numbers only'),
				),
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Please give a reason'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'reason',
			'attr'		=> array(
				'cols'	=> 42,
				'rows'	=> 6,
				),
			'default'	=> M('Automatic Reject'),
			),
	/* validators */
		array(
			)
		);
?>
	</td>
</tr>
</tbody>

<tbody>
<tr>
	<td>&nbsp;</td>
	<td>
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
	</td>
</tr>
</tbody>
</table>

<script language="JavaScript">
jQuery(document).ready( function(){
	if( jQuery("#<?php echo $this->getName(); ?>enable-reject").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>reject-wrapper").show();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>reject-wrapper").hide();
		}
	});
jQuery("#<?php echo $this->getName(); ?>enable-reject").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>reject-wrapper").toggle();
	});
</script>
