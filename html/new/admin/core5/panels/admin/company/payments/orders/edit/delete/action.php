<?php
$ntsdb =& dbWrapper::getInstance();
$id = $_NTS['REQ']->getParam( 'order_id' );
$object = ntsObjectFactory::get( 'order' );
$object->setId( $id );
$formParams['order_id'] = $id;

//$object = ntsLib::getVar( 'admin/company/payments/orders/edit::OBJECT' );

$ff =& ntsFormFactory::getInstance();
$NTS_VIEW['form'] =& $ff->makeForm( dirname(__FILE__) . '/form', $formParams );

switch( $action ){
	case 'delete':
		$cm =& ntsCommandManager::getInstance();
		$cm->runCommand( $object, 'delete' );

		if( $cm->isOk() ){
			ntsView::setAnnounce( M('Order') . ': ' . ntsView::objectTitle($object) . ': '. M('Delete') . ': ' . M('OK'), 'ok' );
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