<style>
#nts #nts-selector a {
	display: block;
	border-style: solid;
	border-width: 1px;
	padding: 0.25em 0.5em;
	margin: 0 0.25em;
	white-space: normal;
	line-height: 2em;
	text-decoration: none;
	}
#nts #nts-selector h3 {
	text-decoration: underline;
	}
</style>
<?php
global $NTS_CURRENT_USER, $NTS_AR;
$earliestTs = isset($NTS_VIEW['availability'][$currentIndexes[0]][$s->getId()]) ? $NTS_VIEW['availability'][$currentIndexes[0]][$s->getId()] : 0;
$targetId = 'id_' . $currentIndexes[0];
$t = $NTS_VIEW['t'];
$packOnly = $s->getProp('pack_only');
$showSessionDuration = $conf->get('showSessionDuration');

$timeAlreadySelected = $NTS_AR->getSelected( 0, 'time' );
$selectLink = ntsLink::makeLink('-current-', 'select', array($targetId => $s->getId()));

$canChooseThis = TRUE;
if ( $packOnly )
{
	if( NTS_CURRENT_USERID )
	{
		if( $NTS_CURRENT_USER->hasRole('admin') )
		{
			$canChooseThis = TRUE;
		}
		else
		{
/* check if current customer has orders */
			$current = $NTS_AR->getCurrent();
			$checkReady = array(
				array(
					'service_id'	=> $s->getId(),
					'location_id'	=> 0,
					'resource_id'	=> 0,
					),
				);
			$availableOrders = $NTS_CURRENT_USER->checkOrders( $checkReady, $NTS_AR->getOrder() );
			$canChooseThis = $availableOrders[0] ? TRUE : FALSE;
		}
	}
}
else
{
	$canChooseThis = TRUE;
}
?>
<li>
<div>

<?php if( $canChooseThis ) : ?>
<a href="<?php echo $selectLink; ?>">
<?php endif; ?>

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
<?php	if( ! NTS_CURRENT_USERID ) : ?>
		<br><a href="<?php echo ntsLink::makeLink('anon/login'); ?>"><?php echo M('Please login'); ?></a>
<?php	endif; ?>
<?php endif; ?>

<?php if( $canChooseThis ) : ?>
<?php else : ?>
	<br><i><?php echo M('You can not select this service now'); ?></i>
<?php endif; ?>

<?php if( $s->getProp('description') ) : ?> 
	<br><?php echo $s->getProp('description'); ?>
<?php endif; ?>

<?php if( $canChooseThis ) : ?>
</a>
<?php endif; ?>

</div>
</li>
