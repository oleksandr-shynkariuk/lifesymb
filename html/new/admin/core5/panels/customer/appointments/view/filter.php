<?php
if( $NTS_CURRENT_USER->getId() > 0 ){
	$currentCustomerId = $NTS_CURRENT_USER->getId();
	}
elseif( isset($_SESSION['temp_customer_id']) ){
	$currentCustomerId = $_SESSION['temp_customer_id'];
	}
else {
	$currentCustomerId = 0;
	}

/* appointment */
$idString = $_NTS['REQ']->getParam( 'id' );
$ids = explode( '-', $idString );
foreach( $ids as $id ){
	$object = ntsObjectFactory::get( 'appointment' );
	$object->setId( $id );
	$customerId = $object->getProp('customer_id');

	if( $customerId != $currentCustomerId ){
		ntsView::setAnnounce( M('Access Denied'), 'error' );
		$forwardTo = ntsLink::makeLink();
		ntsView::redirect( $forwardTo );
		exit;
		}
	}
?>