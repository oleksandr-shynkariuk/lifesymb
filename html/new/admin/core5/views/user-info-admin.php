<?php
$profilePanel = 'admin/profile';

$userFullName = trim( ntsView::objectTitle($NTS_CURRENT_USER) );
$userTitle = '<b>' . $NTS_CURRENT_USER->getProp('username') . '</b>';
if( $userFullName ){
	$userTitle .= ' (' . $userFullName . ')';
	}
?>
<li><?php echo M('Welcome'); ?> <b><a href="<?php echo ntsLink::makeLink($profilePanel); ?>"><?php echo $userTitle; ?></a></b></li>

<?php if( preg_match('/^admin/', $_NTS['CURRENT_PANEL']) ) : ?>
	<?php /*?><li><a href="<?php echo ntsLink::makeLink('customer'); ?>"><?php echo M('Frontend View'); ?></a></li><?php */?>
<?php else : ?>
	<li><a href="<?php echo ntsLink::makeLink('admin'); ?>"><?php echo M('Admin Area'); ?></a></li>
<?php endif; ?>
<li><a href="<?php echo ntsLink::makeLink('user/logout'); ?>"><?php echo M('Logout'); ?></a></li>
