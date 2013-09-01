<span class="nts-alert"><?php echo M('Are you sure?'); ?></span>
<p>
<?php echo M('The appointment will be completely deleted including all related payments. No notifications will be sent.'); ?>
<p>
<?php echo $this->makePostParams('-current-', 'delete' ); ?>
<input type="submit" VALUE="<?php echo M('Delete'); ?>">
