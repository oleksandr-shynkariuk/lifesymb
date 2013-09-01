<?php
global $NTS_CURRENT_USER, $NTS_AR;
$earliestTs = isset($NTS_VIEW['availability'][$currentIndexes[0]][$s->getId()]) ? $NTS_VIEW['availability'][$currentIndexes[0]][$s->getId()] : 0;
$targetId = 'id_' . $currentIndexes[0];
$t = $NTS_VIEW['t'];
$packOnly = $s->getProp('pack_only');
$showSessionDuration = $conf->get('showSessionDuration');

$timeAlreadySelected = $NTS_AR->getSelected( 0, 'time' );
?>
<li>
<h3><?php echo ntsView::objectTitle($s); ?></h3>

<?php if( strlen($s->getProp('price')) ) : ?> 
	<?php echo M('Price'); ?>: <b><?php echo ntsCurrency::formatServicePrice($s->getProp('price')); ?></b>
<?php endif; ?>

<?php if( $showSessionDuration ) : ?>
	<br><?php echo M('Duration'); ?>: <b><?php echo ntsTime::formatPeriod($s->getProp('duration')); ?></b>
<?php endif; ?>

<?php if( $earliestTs ) : ?>
	<br><?php $t->setTimestamp( $earliestTs ); ?>
<?php 	if( ! $timeAlreadySelected ) : ?>
<?php		echo M('Nearest Availability'); ?>: <b><?php echo $t->formatDate(); ?> <?php echo $t->formatTime(); ?></b>
<?php 	endif; ?>
<?php else : ?>
	<?php echo M('Not Available'); ?>
<?php endif; ?>

<?php if ( $packOnly ) : ?>
<?php	if( NTS_CURRENT_USERID ) : ?>
<?php		if( $NTS_CURRENT_USER->hasRole('admin') ) : ?>
<?php
				$canChooseThis = true;
?>
<?php		else : ?>
<?php
/* check if current customer has orders */
				$current = $NTS_AR->getCurrent();
				$checkReady = array(
					array(
						'service_id'	=> $s->getId(),
						'location_id'	=> 0,
						'resource_id'	=> 0,
						'seats'			=> 1,
						),
					);
				$availableOrders = $NTS_CURRENT_USER->checkOrders( $checkReady, $NTS_AR->getOrder() );
				$canChooseThis = $availableOrders[0] ? true : false;
?>
<?php		endif; ?>
<?php	else : ?>
<?php
		$canChooseThis = false;
?>
		<br><a href="<?php echo ntsLink::makeLink('anon/login'); ?>"><?php echo M('Please login'); ?></a>
<?php	endif; ?>

<?php else : ?>
<?php
		$canChooseThis = true;
?>
<?php endif; ?>

<?php if( $canChooseThis ) : ?>
	<?php if( $earliestTs ) : ?>
		<br><a href="<?php echo ntsLink::makeLink('-current-', 'select', array($targetId => $s->getId()) ); ?>"><?php echo M('Schedule Now'); ?></a>
	<?php endif; ?>
<?php elseif ( $packOnly ) : ?>
	<br><i><?php echo M('You can not select this service now'); ?></i>
<?php elseif ( ! $packOnly ) : ?>
	<br><i><?php echo M('You can not select this service now'); ?></i>
<?php endif; ?>

<?php if( $s->getProp('description') ) : ?> 
	<p><?php echo $s->getProp('description'); ?>
<?php endif; ?>
</li>
