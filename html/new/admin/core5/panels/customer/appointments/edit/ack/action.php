<?php
global $NTS_VIEW;

$cm =& ntsCommandManager::getInstance();
$ntsdb =& dbWrapper::getInstance();
$id = $_NTS['REQ']->getParam( '_id' );

$NTS_VIEW['id'] = $id;

switch( $action ){
	case 'ack':
		if( ! is_array($id) ){
			$id = array( $id );
			}

		$resultCount = 0;
		$failedCount = 0;
		reset( $id );
		foreach( $id as $i ){
			$object = ntsObjectFactory::get( 'appointment' );
			$object->setId( $i );
			$cm->runCommand( $object, 'acknowledge' );

			if( $cm->isOk() ){
				$resultCount++;
				}
			else {
				$failedCount++;
				}
			}

		if( $resultCount ){
			if( $resultCount > 1 )
				ntsView::addAnnounce( $resultCount . ' ' . M('Appointments') . ': ' . M('Acknowledged'), 'ok' );
			else
				ntsView::addAnnounce( M('Appointment') . ': ' . M('Acknowledged'), 'ok' );
			}

	/* continue to the list with anouncement */
		if( ! isset($forwardTo) ){
			$return = $_NTS['REQ']->getParam( 'return' );
			if( $return == 'all' )
				$forwardTo = ntsLink::makeLink( 'customer/appointments/browse' );
			else
				$forwardTo = ntsLink::makeLink( 'customer/appointments/browse' );
			}

		ntsView::redirect( $forwardTo );
		exit;
		break;

	default:
		break;
	}

$ff =& ntsFormFactory::getInstance();
$return = $_NTS['REQ']->getParam( 'return' );
$formParams = array(
	'return'	=> $return,
	'id'		=> $id,
	);
$confirmForm =& $ff->makeForm( dirname(__FILE__) . '/confirmForm', $formParams );
?>