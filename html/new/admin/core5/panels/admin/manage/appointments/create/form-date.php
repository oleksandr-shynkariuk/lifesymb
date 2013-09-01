<?php
$canChangeDate = ntsLib::getVar( 'admin/manage/appointments/create::changeDate' );
?>
<tr>
<td class="ntsFormLabel"><?php echo M('Date'); ?></td>

<?php if( $reschedule ) : ?>
<td class="ntsFormValue">
<?php
	$objId = $reschedule->getProp( 'starts_at' );
	$t->setTimestamp( $objId );
	$objView = $t->formatWeekdayShort() . ', ' . $t->formatDate();
?>
<?php echo $objView; ?>
</td>
<?php endif; ?>

<td class="ntsFormValue">
<?php
$t->setDateDb( $cal );
$objView = $t->formatWeekdayShort() . ', ' . $t->formatDate();
$params = array('starts_at' => '-reset-');
?>

<?php if( $canChangeDate ) : ?>
<div class="nts-ajax-parent" style="padding: 0 0.5em;">
<i class="icon-calendar"></i> <a title="<?php echo M('Calendar'); ?>" href="<?php echo ntsLink::makeLink( '-current-/cal', '', $params); ?>" class="nts-ajax-loader"><?php echo $objView; ?></a>
<div class="nts-ajax-container nts-child"></div>
</div>
<?php else : ?>
<?php echo $objView; ?>
<?php endif; ?>
</td>
</tr>