<?php
global $NTS_AR;
$recurEvery = $NTS_VIEW['recur-every'];
$recurFrom = $NTS_VIEW['recur-from'];
$recurTo = $NTS_VIEW['recur-to'];
$t = $NTS_VIEW['t']; 

$ff =& ntsFormFactory::getInstance();
$service = $NTS_AR->getSelected( $I, 'service' );
?>
<div id="nts-selector">
<?php if( $recurEvery ) : ?>
<?php
	$formParams = array(
		'service'	=> $service,
		);
	$form =& $ff->makeForm( dirname(__FILE__) . '/form_RecurringOptions_FromTo', $formParams );
	$form->display();
?>
<?php else : ?>
<?php
	$formParams = array(
		'service'	=> $service,
		);
	$form =& $ff->makeForm( dirname(__FILE__) . '/form_RecurringOptions_Type', $formParams );
	$form->display();
?>
<?php endif; ?>
</div>