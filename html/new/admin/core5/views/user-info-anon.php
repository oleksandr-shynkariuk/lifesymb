<li><a href="<?php echo ntsLink::makeLink(); ?>"><?php echo M('Home'); ?></a></li>
<?php if( $_NTS['CURRENT_PANEL'] != 'anon/login') : ?>
	<li><a href="<?php echo ntsLink::makeLink('anon/login'); ?>"><?php echo M('Login'); ?></a></li>
<?php endif; ?>
<?php if( $_NTS['CURRENT_PANEL'] != 'anon/register') : ?>
	<?php if( NTS_ENABLE_REGISTRATION ) : ?>
		<li><a href="<?php echo ntsLink::makeLink('anon/register'); ?>"><?php echo M('Register'); ?></a></li>
	<?php endif; ?>
<?php endif; ?>