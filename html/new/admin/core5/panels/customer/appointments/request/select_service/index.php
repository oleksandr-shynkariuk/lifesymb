<?php
$conf =& ntsConf::getInstance();
$selectStyle = $conf->get('selectStyle');
$entries = $NTS_VIEW['entries'];
$currentIndexes = array_keys( $entries );
$categories = $NTS_VIEW['categories'];
$ff =& ntsFormFactory::getInstance();
$cat2service = $NTS_VIEW['cat2service'];

$packs = $NTS_VIEW['packs'];
?>
<p>
<?php require( dirname(__FILE__) . '/../common/flow-header.php' ); ?>

<div id="nts-selector">
<?php if( $packs ) : ?>
	<p>
	<a href="<?php echo ntsLink::makeLink('-current-/../select_pack'); ?>"><?php echo M('Appointment packages available'); ?></a>
<?php endif; ?>

<?php if( ($selectStyle == 'dropdown') || (count($entries) > 1) ): ?>

<h2><?php echo M('Services'); ?></h2>
<?php
$form =& $ff->makeForm( dirname(__FILE__) . '/form' );
$form->display();
?>

<?php else : ?>

<?php if( $entries[$currentIndexes[0]] ) : ?>
	<h2><?php echo M('Services'); ?></h2>
	<?php if( $categories[$currentIndexes[0]] ) : ?>
		<?php require( dirname(__FILE__) . '/index-categories.php' ); ?>
	<?php else : ?>
		<?php require( dirname(__FILE__) . '/index-services.php' ); ?>
	<?php endif; ?>
<?php endif; ?>

<?php endif; ?>
</div>

