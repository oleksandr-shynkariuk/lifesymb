<?php
$pgm =& ntsPaymentGatewaysManager::getInstance();
$gateway = $this->getValue('gateway');
$new = $this->getValue('new');

$formFile = $pgm->getGatewayFolder( $gateway ) . '/settingsForm.php';
require( $formFile );
?>

<p>
<DIV CLASS="buttonBar">
<?php if( $new ) : ?>
	<?php echo $this->makePostParams('-current-', 'activate', array('gateway' => $gateway, 'new' => $new) ); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Activate'); ?>">
<?php else : ?>
	<?php echo $this->makePostParams('-current-', 'update', array('gateway' => $gateway, 'new' => $new) ); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
<?php endif; ?>
</DIV>