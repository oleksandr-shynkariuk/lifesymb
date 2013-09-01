<?php
$ff =& ntsFormFactory::getInstance();
$cm =& ntsCommandManager::getInstance();

global $NTS_CURRENT_USER;
$fParams = array(
	'_agenda_fields' => $NTS_CURRENT_USER->getProp('_agenda_fields'),
	);
array_unshift( $fParams['_agenda_fields'], 'time' );
$NTS_VIEW['form'] =& $ff->makeForm( dirname(__FILE__) . '/form', $fParams );

switch( $action ){
	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();
			$NTS_CURRENT_USER->setProp('_agenda_fields', $formValues['_agenda_fields']);
			$cm->runCommand( $NTS_CURRENT_USER, 'update' );
			if( $cm->isOk() ){
				ntsView::setAnnounce( M('Settings') . ': ' . M('Update') . ': ' . M('OK'), 'ok' );

			/* continue to the list with anouncement */
				$forwardTo = ntsLink::makeLink( '-current-/..' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
				$errorText = $cm->printActionErrors();
				ntsView::addAnnounce( $errorText, 'error' );
				}
			}
		else {
		/* form not valid, continue to edit form */
			}
		break;
	}
?>