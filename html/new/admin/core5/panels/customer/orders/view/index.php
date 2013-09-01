<?php
global $NTS_VIEW;
$t = $NTS_VIEW['t'];

$order = ntsLib::getVar( 'customer/orders/view::OBJECT' );
$deps = ntsLib::getVar( 'customer/orders/view::deps' );

$packId = $order->getProp('pack_id');
$pack = ntsObjectFactory::get( 'pack' );
$pack->setId( $packId );
$totalFinalPrice = $pack->getProp('price');
?>
<h2><?php echo M('Package Details'); ?></h2>

<p>
<?php echo $pack->getFullTitle(); ?>

<?php
$validTo = $order->getProp('valid_to');
if( $validTo > 0 ){
	$t->setTimestamp( $validTo );
	$validToDate = $t->formatDate_Db();
	$validView = $t->formatDate();
	}
else {
	$validView = M('Never Expires');
	}
?>
<br>
<?php echo M('Expires'); ?>: <?php echo $validView; ?>
<br>
<?php
$t = new ntsTime;
$t->setNow();
$today = $t->formatDate_Db();

$validTo = $order->getProp('valid_to');
$t->setTimestamp( $validTo );
$validToDate = $t->formatDate_Db();

$isActive = $order->getProp( 'is_active' );
$return = $isActive ? M('Yes') : M('No');
$expired = (($validTo > 0) && ($today > $validToDate)) ? 1 : 0;
if( $expired ){
	$return = M('Expired');
	}
?>
<?php echo M('Is Active'); ?>: <?php echo $return; ?>

<h3><?php echo M('Total Price'); ?>: <?php echo ntsCurrency::formatPrice($totalFinalPrice); ?></h3>

<h2><?php echo M('Usage'); ?></h2>
<?php if( ! $deps ) : ?>
<?php echo M('Not Used Yet'); ?>
<?php else : ?>
<?php
_print_r( $deps );
?>
<?php endif; ?>