<?php
require( dirname(__FILE__) . '/action.php' );

$appDetails = $object->getByArray();

$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile, $appDetails );

if( $form->validate() ){
	$formValues = $form->getValues();

	$object->setByArray( $formValues );

	$cm->runCommand( $object, 'update' );
	if( $cm->isOk() ){
		ntsView::addAnnounce( M('Appointment') . ': ' . M('Update') . ': ' . M('OK'), 'ok' );

	/* continue to the list with anouncement */
		$forwardTo = ntsLink::makeLink( '-current-', '', array('id' => $id) );
		ntsView::redirect( $forwardTo );
		exit;
		}
	else {
		$errorText = $cm->printActionErrors();
		ntsView::addAnnounce( $errorText, 'error' );
		}
	}
else {
/* form not valid, continue to create form */
	}
?>