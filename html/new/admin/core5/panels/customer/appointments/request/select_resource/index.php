<?php
$conf =& ntsConf::getInstance();
$selectStyle = $conf->get('selectStyle');
$entries = $NTS_VIEW['entries'];
$t = $NTS_VIEW['t'];
$currentIndexes = array_keys( $entries );
?>
<p>
<?php require( dirname(__FILE__) . '/../common/flow-header.php' ); ?>

<h2><?php echo M('Bookable Resources'); ?></h2>

<?php if( ($selectStyle == 'dropdown') || (count($entries) > 1) ): ?>
<?php
		$ff =& ntsFormFactory::getInstance();
		$form =& $ff->makeForm( dirname(__FILE__) . '/form' );
		$form->display();
?>
<?php else : ?>
<div id="nts-selector">
<?php
$targetId = 'id_' . $currentIndexes[0];
?>
<ul>
<?php 	if( $NTS_VIEW['selectionMode'] == 'manualplus' ) : ?>
	<li>
		<h3> - <a href="<?php echo ntsLink::makeLink('-current-', 'select', array($targetId => 'a') ); ?>"><?php echo M("Don't have a particular preference"); ?></a> - </h3>
	</li>
	<li><?php echo M('Or select one below'); ?></li>
<?php 	endif; ?>
<?php 	foreach( $entries[$currentIndexes[0]] as $e ) : ?>
	<li>
		<h3><a href="<?php echo ntsLink::makeLink('-current-', 'select', array($targetId => $e->getId()) ); ?>"><?php echo $e->getProp('title'); ?></a></h3>
<?php
			if( isset($NTS_VIEW['availability'][1]) ){
				$earliestTs = isset($NTS_VIEW['availability'][1][$e->getId()]) ? $NTS_VIEW['availability'][1][$e->getId()] : 0;
?>
<?php	 		if( $earliestTs ) : ?>
<?php				$t->setTimestamp( $earliestTs ); ?>
<?php 				echo M('Nearest Availability'); ?>: <b><?php echo $t->formatDate(); ?> <?php echo $t->formatTime(); ?></b>
<?php 			else : ?>
<?php 				echo M('Not Available'); ?>
<?php 			endif; ?>
<?php 			} ?>

<?php 		if( $e->getProp('description') ) : ?> 
			<p><?php echo $e->getProp('description'); ?>
<?php 		endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
