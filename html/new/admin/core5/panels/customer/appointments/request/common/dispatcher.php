<?php
global $NTS_AR;
if( ! isset($dispatcherParams) )
	$dispatcherParams = array();

/* check what's choosing now */
$what = $NTS_AR->whatNext();
if( $what == 'confirm' ){
	/* it may redirect and exit in this file */
//	require( dirname(__FILE__) . '/before-confirm.php' );
	}
	
$panelPrefix = $NTS_AR->getPanel();
switch( $what ){
	case 'confirm':
		$nextPanel = $panelPrefix . '/confirm';
		break;
	case 'location':
		$nextPanel = $panelPrefix . '/select_location';
		break;
	case 'recurring':
		$nextPanel = $panelPrefix . '/select_recurring';
		break;
	case 'seats':
		$nextPanel = $panelPrefix . '/select_seats';
		break;
	case 'time':
		$nextPanel = $panelPrefix . '/select_time';
		break;
	case 'resource':
		$nextPanel = $panelPrefix . '/select_resource';
		break;
	case 'service':
		$nextPanel = $panelPrefix . '/select_service';
		break;
	}

if( ! (isset($noForward) && $noForward) ){
	$forwardTo = ntsLink::makeLink( $nextPanel, '', $dispatcherParams );
	ntsView::redirect( $forwardTo );
	exit;
	}
else {
	ntsView::setNextAction( $nextPanel );
	return;
	}
?>