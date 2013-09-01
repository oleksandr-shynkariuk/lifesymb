<H2><?php echo ucfirst($NTS_VIEW['gateway']); ?> Payment Gateway Settings</H2>
<?php
$pgm =& ntsPaymentGatewaysManager::getInstance();
$gateway = $NTS_VIEW['gateway'];
$new = $NTS_VIEW['new'];

$defaults = $pgm->getGatewaySettings( $gateway );
$defaults['gateway'] = $gateway;
$defaults['new'] = $new;

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile, $defaults );

$form->display();
?>
