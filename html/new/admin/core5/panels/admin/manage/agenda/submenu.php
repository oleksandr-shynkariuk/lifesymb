<?php
if( isset($NTS_VIEW['aliasFile']) && $NTS_VIEW['aliasFile'] )
{
	return;
}

$period = ntsLib::getVar( 'admin/manage/agenda:period' );
$defaultCalendar = ntsLib::getVar( 'admin/manage:defaultCalendar' );

$skipCalendar = FALSE;
if( in_array($period, array('all', 'upcoming')) )
{
	$skipCalendar = TRUE;
}
?>
<div class="nts-ajax-parent" style="padding: 0 0.5em;">
<h2>
<?php if( ! $skipCalendar ) : ?>
<a id="nts-load-calendar" title="<?php echo M('Calendar'); ?>" href="<?php echo ntsLink::makeLink( '-current-/cal'); ?>" class="nts-ajax-loader nts-icon"><i class="icon-calendar"></i></a>
<?php endif; ?>
<a title="<?php echo M('Settings'); ?>" href="<?php echo ntsLink::makeLink( '-current-/settings'); ?>" class="nts-ajax-loader nts-icon"><i class="icon-cogs"></i></a>
</h2>
<div class="nts-ajax-container nts-child"></div>
</div>

<?php if( (! $skipCalendar) && $defaultCalendar ) : ?>
<script language="JavaScript">
jQuery(document).ready( function()
{
	jQuery("#nts-load-calendar").click();
});
</script>
<?php endif; ?>