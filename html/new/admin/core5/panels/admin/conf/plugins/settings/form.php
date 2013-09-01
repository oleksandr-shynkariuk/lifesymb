<?php
$plm =& ntsPluginManager::getInstance();
$plugin = $this->getValue('plugin');
$new = $this->getValue('new');

$plgFolder = $plm->getPluginFolder( $plugin );
$formFile = $plgFolder . '/settingsForm.php';
$skipSubmit = false;
require( $formFile );
?>
<?php if( ! $skipSubmit ) : ?>
	<p>
	<DIV CLASS="buttonBar">
	<?php if( $new ) : ?>
		<?php echo $this->makePostParams('-current-', 'activate', array('plugin' => $plugin, 'new' => $new) ); ?>
		<INPUT TYPE="submit" VALUE="<?php echo M('Install'); ?>">
	<?php else : ?>
		<?php echo $this->makePostParams('-current-', 'update', array('plugin' => $plugin, 'new' => $new) ); ?>
		<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
	<?php endif; ?>
	</DIV>
<?php endif; ?>