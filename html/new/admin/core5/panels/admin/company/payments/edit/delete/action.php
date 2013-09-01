<?php
$ff =& ntsFormFactory::getInstance();
$transId = ntsLib::getVar( 'admin/company/payments/edit::transId' );

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile );

switch( $action ){
	case 'delete':
		$object = ntsObjectFactory::get('transaction');
		$object->setId( $transId );
		$amount = $object->getProp('amount');

		$pm =& ntsPaymentManager::getInstance();
		$ok = $pm->deleteTransaction( $transId );

		if( $ok ){
			$amountFormatted = ntsCurrency::formatPrice( $amount );
			$msg = array( M('Payment'), $amountFormatted, M('Delete'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );
			}
		else {
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );
			}
	/* continue to the list with anouncement */
		ntsView::getBack();
		exit;
		break;
	}
?>