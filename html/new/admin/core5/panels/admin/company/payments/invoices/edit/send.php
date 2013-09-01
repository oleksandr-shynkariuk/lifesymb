<?php
$object = ntsLib::getVar( 'admin/company/payments/invoices/edit::OBJECT' );
$ntsconf =& ntsConf::getInstance();
$taxTitle = $ntsconf->get('taxTitle');
$t = $NTS_VIEW['t'];
$ff =& ntsFormFactory::getInstance();
$printView = ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'print') ? TRUE : FALSE;

$customer = $object->getCustomer();
?>
<a href="<?php echo ntsLink::makeLink('-current-'); ?>"><?php echo M('Edit Invoice'); ?></a> 

<h2><?php echo M('Invoice'); ?> <?php echo $object->getProp('refno'); ?></h2>

<?php if( $customer ) : ?>
	<p>
	<?php echo M('Customer'); ?>: <strong><?php echo ntsView::objectTitle($customer); ?> [<?php echo $customer->getProp('email'); ?>]</strong> 
	</p>
<?php	endif; ?>

<h3><?php echo M('Send Invoice'); ?></h3>
<?php
	$customerLink = $NTS_VIEW['customerLink'];
?>
<?php echo M('URL For Customer'); ?><br><input size="64" type="text" class="nts-url-to-send" value="<?php echo $customerLink; ?>" onclick="this.focus();this.select();">
<a target="_blank" class="nts-no-ajax" href="<?php echo $customerLink; ?>"><?php echo M('Preview'); ?></a> 
<?php
echo $NTS_VIEW['formSend']->display();
?>