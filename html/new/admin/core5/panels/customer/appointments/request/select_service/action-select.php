<?php
require_once( dirname(__FILE__) . '/action.php' );

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile );

if( $form->validate() ){
	$currentIndexes = $NTS_AR->getCurrentIndexes();

	$bundle = '';
	reset( $currentIndexes );
	foreach( $currentIndexes as $i ){
		$selectedId = $_NTS['REQ']->getParam( 'id_' . $i );
		if( preg_match('/-/', $selectedId )){
			// bundle
			$bundle = $selectedId;
			break;
			}
		else {
			$NTS_AR->setSelected( $i, 'service', $selectedId );
			}
		}
	if( $bundle ){
		$bundle = explode( '-', $bundle );
		for( $ii = 1; $ii <= count($bundle); $ii++ ){
			$NTS_AR->setSelected( $ii, 'service', $bundle[$ii-1] );
			}
		}

	/* forward to dispatcher to see what's next? */
	$noForward = true;
	require( dirname(__FILE__) . '/../common/dispatcher.php' );
	return;
	}
?>