<?php
$failed = array();
/* do not check for availability */
return;

$tm2 = ntsLib::getVar('admin::tm2');

reset( $apps );
$index = 0;
foreach( $apps as $a ){
	$tm2->setLocation( $a['location_id'] );
	$tm2->setResource( $a['resource_id'] );
	$tm2->setService( $a['service_id'] );

	$nextTimes = $tm2->getNextTimes( $a['starts_at'] );
	if( in_array($formValues['starts_at'], $nextTimes) ){
		// ok
		}
	else {
		// failed
		$failed[] = $index;
		}
	$index++;
	}
?>