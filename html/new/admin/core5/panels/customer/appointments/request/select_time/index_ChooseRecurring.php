<?php
global $NTS_AR;
$service = $NTS_AR->getSelected( $I, 'service' );

$myRecurTotal = 1;
if( $service ){
	$myRecurTotal = $service->getProp( 'recur_total' );
	}
if( $myRecurTotal <= 1 ){
	return;
	}

$recurringOptions = array();
$recurringOptions[] = array( 'single', M('Single Appointment') );

$myOptions = $service->getProp( 'recur_options' );
$myOptions = explode( '-', $myOptions );
if( (count($myOptions) > 1) || (! in_array('custom', $myOptions) ) ){
	$recurringOptions[] = array( 'recurring', M('Recurring Appointments') );
	}
if( in_array('custom', $myOptions) )
	$recurringOptions[] = array( 'custom', M('Custom Dates') );

?>
<div id="nts-selector">
<?php echo M('Recurring Options'); ?>: 
<?php foreach( $recurringOptions as $ro ) : ?>
<?php	if( $ro[0] != $recurring ) : ?>
<?php
		$params = array();
		$params['custom-dates'] = '';
		$params['recur-every'] = '';
		$params['recur-to'] = '';
		$params['recur-from'] = '';
		$params['recurring'] = $ro[0];
		switch( $ro[0] ){
			case 'custom':
				$params['custom-dates'] = $NTS_VIEW['cal'][$I];
				break;
			}
?>	
	<a href="<?php echo ntsLink::makeLink('-current-', '', $params ); ?>"><?php echo $ro[1]; ?></a>
<?php	else : ?>
	<b><?php echo $ro[1]; ?></b>
<?php	endif; ?>

<?php endforeach; ?>
</div>