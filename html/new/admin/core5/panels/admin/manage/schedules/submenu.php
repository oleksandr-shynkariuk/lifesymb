<div class="nts-ajax-parent" style="padding: 0 0.5em;">
<h2>
<a id="nts-load-calendar" title="<?php echo M('Calendar'); ?>" href="<?php echo ntsLink::makeLink( '-current-/cal'); ?>" class="nts-ajax-loader nts-icon"><i class="icon-calendar"></i></a>
<?php if( $cal ) : ?>
<a class="nts-icon" title="<?php echo M('All'); ?>" href="<?php echo ntsLink::makeLink( '-current-', '', array('cal' => '-reset-') ); ?>"><i class="icon-list"></i></a>
<?php endif; ?>
</h2>
<div class="nts-ajax-container nts-child"></div>
</div>
<?php
$defaultCalendar = ntsLib::getVar( 'admin/manage:defaultCalendar' );
?>
<?php if( $defaultCalendar ) : ?>
<script language="JavaScript">
jQuery(document).ready( function()
{
	jQuery("#nts-load-calendar").click();
});
</script>
<?php endif; ?>