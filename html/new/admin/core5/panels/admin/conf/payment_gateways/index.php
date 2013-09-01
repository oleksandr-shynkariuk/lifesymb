<?php
/* current gateway */
$conf =& ntsConf::getInstance();
$activeGateways = $conf->get('paymentGateways');

/* payment manager */
$pgm =& ntsPaymentGatewaysManager::getInstance();
$gateways = $pgm->getGateways();

$active = array();
$available = array();

reset( $gateways );
foreach( $gateways as $g ){
	if( in_array($g, $activeGateways) )
		$active[] = $g;
	else
		$available[] = $g;
	}

$payOnline = TRUE;
if( (count($active) == 1) && ($active[0] == 'offline') ){
	$payOnline = FALSE;
	}
?>
<?php if( $payOnline ) : ?>
<p>
<h3><?php echo M('Require Deposit'); ?></h3>
<ul>
<li>
<?php
	$ff =& ntsFormFactory::getInstance();
	$formFile = dirname( __FILE__ ) . '/form';
	$form =& $ff->makeForm( $formFile );
	$form->display();
?>
</li>
</ul>
<?php endif; ?>

<p>
<h3><?php echo M('Active'); ?></h3>
<?php if( ! $active ) : ?>
	<?php echo M('None'); ?>
<?php endif; ?>

<ul>
<?php foreach( $active as $gw ) : ?>
	<?php	$gName = $pgm->getGatewayName( $gw ); ?>
	<li>
	<b><?php echo $gName; ?></b>

	<br><a href="<?php echo ntsLink::makeLink('-current-/settings', '', array('gateway' => $gw, 'new' => 0) ); ?>"><?php echo M('Settings'); ?></a>
	<?php if( count($active) > 1 ) : ?>
		<a href="<?php echo ntsLink::makeLink('-current-', 'disable', array('gateway' => $gw) ); ?>"><?php echo M('Disable'); ?></a>
	<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>

<?php if( count($available) > 0 ) : ?>
	<p>
	<h3><?php echo M('Available'); ?></h3>
	<ul>
	<?php foreach( $available as $gw ) : ?>
		<li>
		<?php	$gName = $pgm->getGatewayName( $gw ); ?>
		<b><?php echo $gName; ?></b><br>
		
		<?php
		// check if current currency is supported by the gateway
		$currentCurrency = $conf->get( 'currency' );
		$gatewayCurrencies = $pgm->getGatewayCurrencies( $gw );
		?>
		<?php if( in_array($currentCurrency, $gatewayCurrencies) ) : ?>
			<a href="<?php echo ntsLink::makeLink('-current-/settings', '', array('gateway' => $gw, 'new' => 1) ); ?>"><?php echo M('Activate'); ?></a>
		<?php else : ?>
			<b><?php echo strtoupper($currentCurrency); ?></b>: <?php echo M('Not Allowed'); ?>
		<?php endif; ?>
		<br>
		</li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<script language="JavaScript">
jQuery('#nts-set-deposit').live("click", function()
{
	var targetUrl = jQuery(this).attr('href');
	var value = jQuery(this).closest('form').find('[name=nts-prepay]').val();
	targetUrl += '&nts-prepay=' + encodeURI(value);
	document.location.href = targetUrl;
	return false;
});
</script>