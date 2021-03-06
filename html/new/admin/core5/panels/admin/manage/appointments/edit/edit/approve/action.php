<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$ff =& ntsFormFactory::getInstance();
$NTS_VIEW['form'] =& $ff->makeForm( dirname(__FILE__) . '/form' );

switch( $action ){
	case 'approve':
		$cm =& ntsCommandManager::getInstance();

		if( ! is_array($object) )
			$object = array( $object );

		$resultCount = 0;
		for( $ii = 0; $ii < count($object); $ii++ ){
			$sid = $object[$ii]->getProp('service_id');
			$service = ntsObjectFactory::get( 'service' );
			$service->setId( $sid );
			$serviceType = $service->getType();

			$cm->runCommand( $object[$ii], 'approve' );
			if( $cm->isOk() ){
				$resultCount++;
				}
			else {
				$errorText = $cm->printActionErrors();
				ntsView::addAnnounce( $errorText, 'error' );
				$failedCount++;
				$actionOk = false;
				}
			}

		if( $resultCount ){
			if( count($object) == 1 )
				$msg = array( M('Appointment'), ntsView::objectTitle($object[0]) );
			else
				$msg = array( $resultCount . ' ' . M('Appointments') );
			$msg[] = M('Approve');
			$msg[] = M('OK');
			$msg = join( ': ' , $msg );
			ntsView::addAnnounce( $msg, 'ok' );
			}

		/* continue */
		$forceRedirect = ($serviceType == 'class') ? false : true;
		ntsView::getBack( $forceRedirect, true );
		exit;

		break;
	}
?>