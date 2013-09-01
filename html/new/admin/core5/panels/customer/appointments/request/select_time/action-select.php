<?php
require( dirname(__FILE__) . '/action.php' );

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form_Time';
$form =& $ff->makeForm( $formFile );

if( $form->validate() ){
	$deleteOnes = array();
	$currentIndexes = $NTS_AR->getCurrentIndexes();
	reset( $currentIndexes );

	foreach( $currentIndexes as $i ){
		$key = 'id_' . $i;
		$selectedTime = isset($formValues[$key]) ? $formValues[$key] : 0;
		$selectedTime = $_NTS['REQ']->getParam( $key );

		if( $selectedTime ){
			$NTS_AR->setSelected( $i, 'time', $selectedTime );
			$NTS_AR->resetSelected( $i, 'date' );
			$NTS_AR->resetSelected( $i, 'cal' );
			}
		else
			$deleteOnes[] = $i;
		}

	reset( $deleteOnes );
	while( $i = array_pop($deleteOnes) ){
		$NTS_AR->resetAll( $i );
		}

	$NTS_AR->resetOther( 'preferred-time' );
	$NTS_AR->sort();

	/* forward to dispatcher to see what's next? */
	$noForward = false;
	require( dirname(__FILE__) . '/../common/dispatcher.php' );
	exit;
	}
?>