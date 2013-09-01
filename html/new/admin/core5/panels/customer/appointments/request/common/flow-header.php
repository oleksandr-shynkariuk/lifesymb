<?php
global $NTS_VIEW, $NTS_AR;
require_once( dirname(__FILE__) . '/prepareViews.php' );
reset( $NTS_VIEW['flowHeader'] );
$showReschedule = isset($NTS_VIEW['flowHeader'][0][3]) ? true : false;
?>

<?php if( $NTS_VIEW['flowCart'] ) : ?>
<div class="nts-appointment-cart">
<?php $appLabel = ( $NTS_VIEW['flowCart'] > 1 ) ? M('Appointments') : M('Appointment');?>
<b><?php echo M('Appointment Cart'); ?></b><br>
<a href="<?php echo ntsLink::makeLink( $NTS_AR->getPanel() . '/confirm'); ?>"><?php echo $NTS_VIEW['flowCart']; ?> <?php echo $appLabel; ?></a>
</div>
<?php endif; ?>

<?php if( $NTS_VIEW['flowHeader'] ) : ?>
<?php if( $showReschedule ) : ?>
<h2><?php echo M('Reschedule'); ?></h2>
<?php endif; ?>
<div class="nts-appointment-flow">
<table>
<?php if( $showReschedule ) : ?>
<tr>
<th>&nbsp;</th>
<th><?php echo M('Old Appointment'); ?></th>
<th><?php echo M('New Appointment'); ?></th>
</tr>
<?php endif; ?>

<?php foreach( $NTS_VIEW['flowHeader'] as $r ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo $NTS_VIEW['flowTitles'][$r[0]]; ?></td>
<?php if( $showReschedule ) : ?>
<td class="ntsFormValue"><?php echo $r[3]; ?></td>
<?php endif; ?>
<td class="ntsFormValue"><?php echo $r[1]; ?></td>
</tr>
<?php endforeach; ?>
</table>

</div>
<?php endif; ?>