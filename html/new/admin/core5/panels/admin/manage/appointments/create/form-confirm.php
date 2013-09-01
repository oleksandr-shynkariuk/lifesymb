<?php
if( $appsCount > 1 ){
	$colspan = '';
	}
else {
	$re = '/^' . join('-', array($lid, $rid, $sid, $time)) . '$/';
	$ok = ntsLib::reExistsInArray($re, $available);
	$colspan = $reschedule ? ' colspan="3"' : '';
	}
?>

<tr>
<?php if( $appsCount > 1 ) : ?>
	<td>&nbsp;</td>
<?php else : ?>
	<?php if( $ok ) : ?>
	<td class="ntsWorking" style="text-align: right;"<?php echo $colspan; ?>><?php echo M('Available'); ?></td>
	<?php else : ?>
	<td class="ntsAlert" style="text-align: right;"<?php echo $colspan; ?>><?php echo M('Not Available'); ?></td>
	<?php endif; ?>
<?php endif; ?>

<td>
<?php if( count($paymentOptions) == 1 ) : ?>
<?php
	if( $defaultPaymentOption ){
		echo $this->makeInput (
		/* type */
			'hidden',
		/* attributes */
			array(
				'id'	=> 'payment_option',
				'value'	=> $defaultPaymentOption,
				)
			);
		}
?>
<?php endif; ?>

<?php
echo $this->makePostParams('-current-', 'create' );
?>
<?php if( $reschedule ) : ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Change Appointment'); ?>">
<?php else : ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Create Appointment'); ?>">
<?php endif; ?>
</td>
</tr>