<?php
$customerId = $NTS_CURRENT_USER->getId();
$ntsdb =& dbWrapper::getInstance();
?>
<?php
$profilePanel = 'customer/profile';
?>
<li><?php echo M('Welcome'); ?> <b><a href="<?php echo ntsLink::makeLink($profilePanel); ?>"><?php echo $NTS_CURRENT_USER->getProp('first_name'); ?> <?php echo $NTS_CURRENT_USER->getProp('last_name'); ?></a></b></li>

<li><a href="<?php echo ntsLink::makeLink('customer'); ?>"><?php echo M('New Appointment'); ?></a></li>
<li><a href="<?php echo ntsLink::makeLink('customer/appointments/browse'); ?>"><?php echo M('My Appointments'); ?></a></li>

<?php
$ntsdb =& dbWrapper::getInstance();
/* check if customer has orders */

$where = array(
	'customer_id'	=> array( '=', $customerId ),
	);
$orderCount = $ntsdb->count( 'orders', $where );
?>
<?php if( $orderCount ) : ?>
<li><a href="<?php echo ntsLink::makeLink('customer/orders/browse'); ?>"><?php echo M('My Packages'); ?></a></li>
<?php endif; ?>

<?php
/* check if customer has invoices */
$invoiceCount = 0;
$pm =& ntsPaymentManager::getInstance();
$ids = $pm->getInvoicesOfCustomer( $customerId );
if( $ids )
{
	$where = array(
		'id' => array('IN', $ids)
		);
	$invoiceCount = $ntsdb->count( 'invoices', $where );
}
?>
<?php if( $invoiceCount ) : ?>
<li><a href="<?php echo ntsLink::makeLink('customer/invoices/browse'); ?>"><?php echo M('My Payments'); ?></a></li>
<?php endif; ?>

<?php if( file_exists(NTS_EXTENSIONS_DIR . '/more-links-customer.php') ) : ?>
<?php	require(NTS_EXTENSIONS_DIR . '/more-links-customer.php'); ?>
<?php endif; ?>

<li><a href="<?php echo ntsLink::makeLink('user/logout'); ?>"><?php echo M('Logout'); ?></a></li>