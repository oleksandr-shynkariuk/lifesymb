<?php
$plm =& ntsPluginManager::getInstance();
$plgFolder = $plm->getPluginFolder( 'sms' );
$formFile = $plgFolder . '/settingsForm.php';
require( $formFile );
?>
<p>
<DIV CLASS="buttonBar">
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</DIV>