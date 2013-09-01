<?php
$ff =& ntsFormFactory::getInstance();
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );

$formParams = array();
$formFile = dirname(__FILE__) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

switch( $action ){
	case 'add':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();
			$amount = $formValues['amount'];

			$makeInvoices = array( $object );
			$pm =& ntsPaymentManager::getInstance();
			$invoices = $pm->makeInvoices( $makeInvoices, $amount );

			if( $invoices ){
				$msg = array( M('Invoice'), M('Add'), M('OK') );
				$msg = join( ': ', $msg );
				ntsView::addAnnounce( $msg, 'ok' );

				/* continue */
				$forwardTo = ntsLink::makeLink( '-current-/../invoice' );
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

		break;
	default:
		break;
	}
?>