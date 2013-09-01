<?php
$ff =& ntsFormFactory::getInstance();
$object = ntsLib::getVar( 'admin/company/services/edit::OBJECT' );

$conf =& ntsConf::getInstance();
$cronEnabled = $conf->get( 'cronEnabled' );
if( ! $cronEnabled )
	ntsView::setBack( ntsLink::makeLink('admin/company/services/edit/auto-reject', '', array('_id' => $object->getId())) );

$thisProp = $object->getProp( '_auto_reject' );

if( $thisProp ){
	$formParams = unserialize( $thisProp );
	$formParams['enable-reject'] = 1;
	}
else {
	$formParams = array(
		'enable-reject'	=> 0,
		'reject-before'	=> 24 * 60 * 60,
		'less-than'		=> 2,
		'reason'		=> M('Automatic Reject'),
		);
	}

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

switch( $action ){
	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();
			if( $formValues['enable-reject'] ){
				unset( $formValues['enable-reject'] );
				$value = serialize($formValues);
				}
			else {
				$value = '';
				}
			$object->setProp( '_auto_reject', $value );

			$cm =& ntsCommandManager::getInstance();
			$cm->runCommand( $object, 'update' );

			if( $cm->isOk() ){
				$msg = array( M('Service'), ntsView::objectTitle($object), M('Update'), M('OK') );
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
		/* form not valid, continue to form */
			}

		break;
	default:
		break;
	}
?>