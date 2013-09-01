<?php
require_once( dirname(__FILE__) . '/_common.php' );

/* NO - REDIRECT BACK TO SERVICE SELECTION */
reset( $currentIndexes );
foreach( $currentIndexes as $i ){
	if( ! count($allValidIds[$i]) ){
		ntsView::setAnnounce( M('Location') . ': ' . M('Not Available'), 'error' );
		$forwardTo = ntsLink::makeLink( '-current-/../select_service' );
		ntsView::redirect( $forwardTo );
		exit;
		}
	}

$NTS_VIEW['selectionMode'] = 'manual';
$confFlow = $conf->get('appointmentFlow');
reset( $confFlow );
foreach( $confFlow as $f ){
	if( $f[0] == 'location' ){
		$NTS_VIEW['selectionMode'] = $f[1];
		break;
		}
	}
	
/* ONLY ONE - REDIRECT */
$redirect = false;
reset( $currentIndexes );
foreach( $currentIndexes as $i ){
	$redirect = true;
	if( (count($allValidIds[$i]) == 1) || ($NTS_VIEW['selectionMode'] == 'auto') ){
		$validId = ntsLib::pickRandom( $allValidIds[$i] );
		$NTS_AR->setSelected( $i, 'location', $validId );
		}
	else {
		$redirect = false;
		}
	}


if( $redirect ){
	$tryChange = $_NTS['REQ']->getParam( 'trychange' );
	if( $tryChange ){
		ntsView::addAnnounce( M('Location') . ': ' . M('No more options available'), 'error' );
		$noForward = false;
		}
	else {
		$noForward = true;
		}

	/* forward to dispatcher to see what's next? */
	require( dirname(__FILE__) . '/../common/dispatcher.php' );
	return;
	}

/* OR CHOOSE ONE */
ntsView::setTitle( M('Locations') );

$entries = array();
reset( $currentIndexes );
foreach( $currentIndexes as $i ){
	$entries[$i] = array();
	reset( $allValidIds[$i] );
	foreach( $allValidIds[$i] as $vid ){
		$validOne = ntsObjectFactory::get( 'location' );
		$validOne->setId( $vid );
		$entries[$i][] = $validOne;
		}
	/* sort by show order */
	usort( $entries[$i], create_function('$a, $b', 'return ntsLib::numberCompare($a->getProp("show_order"), $b->getProp("show_order"));' ) );
	}
$NTS_VIEW['entries'] = $entries;
?>