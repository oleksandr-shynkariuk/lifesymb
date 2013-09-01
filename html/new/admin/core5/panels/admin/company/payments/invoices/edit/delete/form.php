<?php
$object = ntsLib::getVar( 'admin/company/locations/edit::OBJECT' );
?>
<H3><?php echo M('Are you sure?'); ?></H3>
<?php if( $NTS_VIEW['appsCount'] ) : ?>
	<p>
	<b><?php echo ntsView::objectTitle($object); ?></b>: <a href="<?php echo ntsLink::makeLink('-current-/../appointments'); ?>"><?php echo M('There are [b]{APPS_COUNT}[/b] appointment(s)', array('APPS_COUNT' => $NTS_VIEW['appsCount']) ); ?></a>
	<p>
	<?php echo M('If you proceed, these appointments will be cancelled' ); ?>.
<?php endif; ?>

<p>
<?php echo $this->makePostParams('-current-', 'delete' ); ?>
<input type="submit" VALUE="<?php echo M('Delete'); ?>">
