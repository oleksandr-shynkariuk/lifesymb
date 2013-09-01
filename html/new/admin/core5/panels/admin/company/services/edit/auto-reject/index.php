<?php
$conf =& ntsConf::getInstance();
$cronEnabled = $conf->get( 'cronEnabled' );
?>
<?php if( ! $cronEnabled ) : ?>
<a href="<?php echo ntsLink::makeLink('admin/conf/cron'); ?>"><?php echo M('Please configure automatic actions first'); ?></a>
<?php else : ?>
<?php
$NTS_VIEW['form']->display();
?>
<?php endif; ?>
