<?php
global $NTS_PERSISTENT_PARAMS, $NTS_CURRENT_USER;

$ff =& ntsFormFactory::getInstance();
$conf =& ntsConf::getInstance();

$removeValidation = array();
if( NTS_ALLOW_NO_EMAIL && $_NTS['REQ']->getParam('noEmail') ){
	$removeValidation[] = 'email';
	}

if( $NTS_VIEW['form']->validate($removeValidation) ){
	$registerNew = true;
	$formValues = $NTS_VIEW['form']->getValues();
	
	$cm =& ntsCommandManager::getInstance();

/* customer */
	$object = new ntsUser();
	unset( $formValues['password2'] );

	$conf =& ntsConf::getInstance();
	$allowDuplicateEmails = $conf->get( 'allowDuplicateEmails' );

/* if no reg enabled and this email exists, find it first */
	if( (! NTS_ENABLE_REGISTRATION) && $formValues['email'] && (! $allowDuplicateEmails) ){
		$uif =& ntsUserIntegratorFactory::getInstance();
		$integrator =& $uif->getIntegrator();

		$myWhere = array(
			'email'	=> array('=', $formValues['email']),
			);
		$thisUsers = $integrator->getUsers( $myWhere );

		if( $thisUsers && count($thisUsers) > 0 ){
			$existingUserId = $thisUsers[0]['id'];
			$object->setId( $existingUserId );
			$registerNew = false;
			}
		}

	if( (! NTS_ENABLE_REGISTRATION) && $registerNew ){
		if( $formValues['email'] )
			$formValues['username'] = $formValues['email'];
		}

	$object->setByArray( $formValues );
	$object->setProp( '_timezone', $NTS_CURRENT_USER->getTimezone() );

	if( $registerNew ){
		$cm->runCommand( $object, 'create' );

		if( $cm->isOk() ){
			if( NTS_ENABLE_REGISTRATION ){
			/* check if we need to require email validation */
				$userEmailConfirmation = $conf->get('userEmailConfirmation');
			/* or admin approval */
				$userAdminApproval = $conf->get('userAdminApproval');
				}
			else {
			/* registration not enabled - not email confirmation required */	
				$userEmailConfirmation = 0;
				$userAdminApproval = 1;
				}

			if( $userEmailConfirmation || $userAdminApproval ){
				if( $userEmailConfirmation ){
					$cm->runCommand( $object, 'require_email_confirmation' );
					}
				elseif( $userAdminApproval ) {
					$cm->runCommand( $object, 'require_approval' );
					}

				$_SESSION['temp_customer_id'] = $object->getId();
				}
			else {
			/* autoapprove */
				$cm->runCommand( $object, 'activate' );
//					ntsView::addAnnounce( M('Congratulations, your account has been created and activated'), 'ok' );
			/* then login */
				$cm->runCommand( $object, 'login' );
				$customerId = $object->getId();
				}
			}
		else {
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );
			}
		}
	else {
		// update existing customer record
		$cm->runCommand( $object, 'update' );
		if( $cm->isOk() ){
			$_SESSION['temp_customer_id'] = $object->getId();
			}
		else {
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );
			}
		}
	}
else {
/* form not valid, continue to create form */
	return;
	}
?>