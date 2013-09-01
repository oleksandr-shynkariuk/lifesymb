<?php
$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile );
if( $form->validate() ){
	include_once( dirname(__FILE__) . '/../../../../lib/ntsSms.php' );

	$formValues = $form->getValues();

	$mailer = new ntsSms;
	reset( $formValues );
	foreach( $formValues as $k => $v )
	{
		$mailer->setParam( $k, $v );
	}

	$mailer->setBody( $formValues['message'] );
	$mailer->sendToOne( $formValues['to'] );

	if( $mailer->isError() ){
		$mailerError = $mailer->getError();
		ntsView::setAnnounce( 'SMS sending error, see log for more info', 'error' );
		}
	else {
		ntsView::setAnnounce( 'SMS: ' . M('Send') . ': ' . M('OK'), 'ok' );
		}
/* continue to the list with anouncement */
	$forwardTo = ntsLink::makeLink( '-current-' );
	ntsView::redirect( $forwardTo );
	exit;
	}
else {
/* form not valid, continue to edit form */
	}
?>