<?php
global $NTS_CALENDAR_STOP;

$currentDates = $NTS_VIEW['custom-dates'];
reset( $currentDates );
$t = $NTS_VIEW['t']; 
?>
<h2><?php echo M('Custom Dates'); ?></h2>
<?php if( ! $NTS_CALENDAR_STOP ) : ?>
	<p>
	<?php echo M('Click on a date in calendar to add'); ?>. 
<?php endif; ?>
<?php if( $currentDates ) : ?>
<?php echo M('Click on a selected date to remove'); ?>.
<p>
<div id="nts-selector">
<?php foreach( $currentDates as $cd ) : ?>
<?php	$t->setDateDb( $cd ); ?>
<a href="<?php echo ntsLink::makeLink('-current-', '', ntsThisBuildCalendarValue(-1, $cd, 'custom-dates') ); ?>"><?php echo $t->formatDate(); ?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php
$ff =& ntsFormFactory::getInstance();
$form =& $ff->makeForm( dirname(__FILE__) . '/form_CustomDates' );
$form->display();
?>
