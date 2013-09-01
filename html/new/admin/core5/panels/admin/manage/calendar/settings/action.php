<?php
$ff =& ntsFormFactory::getInstance();
$cm =& ntsCommandManager::getInstance();

global $NTS_CURRENT_USER;
$calendarField = $NTS_CURRENT_USER->getProp('_calendar_field');
$defaultCalendar = $NTS_CURRENT_USER->getProp('_default_calendar');
$defaultAppsView = $NTS_CURRENT_USER->getProp('_default_apps_view');

$fParams = array(
	'_calendar_field'	=> $calendarField,
	'_default_calendar'	=> $defaultCalendar,
	'_default_apps_view'	=> $defaultAppsView,
	);
$NTS_VIEW['form'] =& $ff->makeForm( dirname(__FILE__) . '/form', $fParams );

switch( $action ){
	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

			$NTS_CURRENT_USER->setProp('_calendar_field', $formValues['_calendar_field']);
			$NTS_CURRENT_USER->setProp('_default_calendar', $formValues['_default_calendar']);
			$NTS_CURRENT_USER->setProp('_default_apps_view', $formValues['_default_apps_view']);

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