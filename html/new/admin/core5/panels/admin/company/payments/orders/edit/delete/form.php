<?php
$orderId = $this->getValue('order_id');
?>
<span class="nts-alert"><?php echo M('Are you sure?'); ?></span>
<?php echo $this->makePostParams('-current-', 'delete', array('order_id' => $orderId) ); ?>
<input type="submit" VALUE="<?php echo M('Delete'); ?>">