<tbody>
<tr>
<td class="ntsFormLabel"><?php echo M('Add Payment'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'checkbox',
/* attributes */
	array(
		'id'		=> 'addPayment',
		)
	);
?>
</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_details_amount">
<tr>
<td class="ntsFormLabel"><?php echo M('Amount'); ?></td>
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
		),
/* validators */
	array(
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
</tbody>

<tbody>
<tr>
<td>&nbsp;</td>
<td>
<?php
echo $this->makePostParams('-current-', 'create' );
?>
<INPUT TYPE="submit" VALUE="<?php echo M('OK'); ?>">
</td>
</tr>
</tbody>

<script language="JavaScript">
jQuery(document).ready( function(){
	if( jQuery("#<?php echo $this->getName(); ?>addPayment").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>_details_amount").show();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>_details_amount").hide();
		}
	});

jQuery("#<?php echo $this->getName(); ?>addPayment").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>_details_amount").toggle();
	});
</script>

