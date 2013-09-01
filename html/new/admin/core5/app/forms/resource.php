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
				'size'	=> 36,
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
					'class'	=> 'resource',
					'skipMe'	=> 1
					),
				),
			)
		);
?>
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
				'cols'	=> 36,
				'rows'	=> 5,
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
	<td class="ntsFormLabel"><?php echo M('Internal'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> '_internal',
			'default'	=> 0,
			),
	/* validators */
		array(
			)
		);
?>
<br>
	<i><?php echo M('Set this if not available for booking by customers'); ?></i>
	</td>
</tr>


<?php
$pgm =& ntsPaymentGatewaysManager::getInstance();
$paymentGateways = $pgm->getActiveGateways();
$paypalEnabled = in_array('paypal', $paymentGateways) ? true : false;
?>
<?php if( $paypalEnabled ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Paypal Email'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> '_paypal',
			'attr'		=> array(
				'size'	=> 32,
				),
			'default'	=> '',
			'required'	=> 0,
			),
	/* validators */
		array(
			)
		);
?>
<br>
	<i>Set this if you wish to provide a separate Paypal account for this resource. Otherwise the global account will be used.</i>
	</td>
</tr>
<?php endif; ?>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'save'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</td>
</table>