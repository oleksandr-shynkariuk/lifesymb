<?php
include_once( dirname(__FILE__) . '/listingInvoices.php' );
$pgm =& ntsPaymentGatewaysManager::getInstance();
$paymentGateways = $pgm->getActiveGateways();
$payOnline = true;
if( (count($paymentGateways) == 1) && ($paymentGateways[0] == 'offline') )
	$payOnline = false;
define( 'NTS_CAN_PAY_ONLINE', $payOnline );

$entries = ntsLib::getVar( 'customer/invoices/browse::entries' );
$fields = array(
	'created_at'	=> array( 'date', M('Created') ),
	'due_at'		=> array( 'date', M('Due Date') ),
	'refno'			=> array( 'text', M('Refno') ),
	'amount'		=> array( 'price', M('Amount') ),
	'status'		=> array( 'text', M('Status') ),
	'details'		=> array( 'text', M('Details') ),
	);
?>
<h2><?php echo M('My Payments'); ?></h2>
<?php if( $entries ) : ?>
<?php
$listing = new listingInvoices( $fields, $entries );
echo $listing->display();
?>
<?php else : ?>
<?php echo M('None'); ?>
<?php endif; ?>