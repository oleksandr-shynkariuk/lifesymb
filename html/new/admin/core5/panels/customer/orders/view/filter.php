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

/* order */
$id = $_NTS['REQ']->getParam( 'id' );
$object = ntsObjectFactory::get( 'order' );
$object->setId( $id );
$customerId = $object->getProp('customer_id');

if( $customerId != $currentCustomerId ){
	ntsView::setAnnounce( M('Access Denied'), 'error' );
	$forwardTo = ntsLink::makeLink();
	ntsView::redirect( $forwardTo );
	exit;
	}
?>