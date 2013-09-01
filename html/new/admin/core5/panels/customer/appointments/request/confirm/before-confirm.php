<?php
/* REDIRECT IF NEEDED */
if( $NTS_CURRENT_USER->hasRole('admin') ){
	$params = $NTS_AR->getParams();
	$adminParams = array(
		'location_id'	=> $params['location'],
		'resource_id'	=> $params['resource'],
		'service_id'	=> $params['service'],
		'starts_at'		=> $params['time'],
		'seats'			=> $params['seats'],
		);
	$forwardTo = ntsLink::makeLink( 'admin/manage/create', '', $adminParams );
	ntsView::redirect( $forwardTo );
	exit;
	}
/*
elseif( ($NTS_CURRENT_USER->getId() < 1) && (! isset($_SESSION['temp_customer_id'])) ){
	// redirect to login-register
	$forwardTo = ntsLink::makeLink( '-current-/register' );
	ntsView::redirect( $forwardTo );
	exit;
	}
elseif( isset($_SESSION['temp_customer_id']) ){
	$customer = new ntsUser();
	$customer->setId( $_SESSION['temp_customer_id'] );
	if( $customer->notFound() ){
		unset($_SESSION['temp_customer_id']);
		// redirect to login & register
		$targetPanel = '-current-/register';
		$forwardTo = ntsLink::makeLink( $targetPanel );
		ntsView::redirect( $forwardTo );
		exit;
		}
	}
*/
?>