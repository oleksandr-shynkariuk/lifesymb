<?php
$ff =& ntsFormFactory::getInstance();
$cm =& ntsCommandManager::getInstance();

$id = $_NTS['REQ']->getParam( '_id' );
$NTS_VIEW['id'] = $id;

switch( $action ){
	case 'update_password':
		$ff =& ntsFormFactory::getInstance();
		$passwordFormFile = dirname( __FILE__ ) . '/form';
		$NTS_VIEW['passwordForm'] =& $ff->makeForm( $passwordFormFile, array('id' => $id) );

		if( $NTS_VIEW['passwordForm']->validate() ){
			$cm =& ntsCommandManager::getInstance();
			$formValues = $NTS_VIEW['passwordForm']->getValues();

		/* update password */
			$user = new ntsUser();
			$user->setId( $id );
			$user->setProp( 'new_password', $formValues['password'] );

			$cm->runCommand( $user, 'update' );
			if( $cm->isOk() ){
				ntsView::addAnnounce( M('Password Changed'), 'ok' );

			/* continue to customer edit */
				$forwardTo = ntsLink::makeLink( '-current-/../edit', '', array('id' => $id ) );
				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
				$actionError = true;
				$errorString = $cm->printActionErrors();
				}
			}
		else {
		/* form not valid, continue to edit form */
			}
		break;
	}

/* user info */
$userInfo['id'] = $id;

if( ! isset($NTS_VIEW['form']) ){
	$formFile = dirname( __FILE__ ) . '/form';
	$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $userInfo );
	}
?>