<?php
$ff =& ntsFormFactory::getInstance();
$object = ntsLib::getVar( 'admin/manage/timeoff/edit::OBJECT' );

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile );

switch( $action ){
	case 'delete':
		$cm =& ntsCommandManager::getInstance();
		$cm->runCommand( $object, 'delete' );

		if( $cm->isOk() ){
			$msg = array( M('Timeoff'), M('Delete'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );
			}
		else {
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );
			}
	/* continue to the list with anouncement */
		$forwardTo = ntsLink::makeLink( '-current-/../..' );
		ntsView::redirect( $forwardTo );
		exit;
		break;
	}
?>