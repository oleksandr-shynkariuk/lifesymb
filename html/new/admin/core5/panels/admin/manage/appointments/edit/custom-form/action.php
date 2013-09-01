<?php
global $NTS_READ_ONLY;

$ff =& ntsFormFactory::getInstance();
$formValues = $object->getByArray();

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formValues );
// if i can edit this
$rid = $object->getProp( 'resource_id' );
if( ! in_array($rid, $NTS_VIEW['APP_EDIT']) ){
	$NTS_VIEW['form']->readonly = 1;
	}

$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );

switch( $action ){
	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();
			$object->setByArray( $formValues );

			$cm =& ntsCommandManager::getInstance();
			$cm->runCommand( $object, 'update' );

			if( $cm->isOk() ){
				$msg = array( M('Appointment'), ntsView::objectTitle($object), M('Update'), M('OK') );
				$msg = join( ': ', $msg );
				ntsView::addAnnounce( $msg, 'ok' );

			/* continue to the list with anouncement */
				$forwardTo = ntsLink::makeLink( '-current-' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
				$errorText = $cm->printActionErrors();
				ntsView::addAnnounce( $errorText, 'error' );
				}
			}
		else {
		/* form not valid, continue to create form */
			}
		
		break;
	}
?>