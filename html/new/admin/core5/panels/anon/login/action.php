<?php
if( defined('NTS_LOGIN_REDIRECT') && NTS_LOGIN_REDIRECT ){
	ntsView::redirect( NTS_LOGIN_REDIRECT );
	exit;
	}

$user = $_NTS['REQ']->getParam('user');
$NTS_VIEW['user'] = $user;

switch( $action ){
	case 'login':
		/* check if cookies enabled */
		$skipCookie = ( defined('NTS_SKIP_COOKIE') && NTS_SKIP_COOKIE ) ? 1 : $_NTS['REQ']->getParam( 'nts-skip-cookie' );
		if( (! $skipCookie) && (! ( isset($_COOKIE['ntsTestCookie']) && ($_COOKIE['ntsTestCookie'] == 'ntsTestCookie') )) ){
			$display = 'noCookies';
			$forwardTo = ntsLink::makeLink( 'anon/login', '', array('display' => $display) );
			ntsView::redirect( $forwardTo );
			exit;
			}

		$ff =& ntsFormFactory::getInstance();
		$formFile = dirname( __FILE__ ) . '/form';
		$form =& $ff->makeForm( $formFile );

		if( $form->validate() ){
			$formValues = $form->getValues();

		/* local handler */
			$object = new ntsUser();
			if( NTS_EMAIL_AS_USERNAME )
				$object->setProp( 'email', $formValues['email'] );
			else
				$object->setProp( 'username', $formValues['username'] );
			$object->setProp( 'password', $formValues['password'] );

			$cm =& ntsCommandManager::getInstance();
			$cm->runCommand( $object, 'check_password' );

			if( ! $cm->isOk() ){
				$errorText = $cm->printActionErrors();
				ntsView::addAnnounce( $errorText, 'error' );

			/* continue to login form */
				$forwardTo = ntsLink::makeLink( '-current-' );
				ntsView::redirect( $forwardTo );
				exit;
				}

		/* check user restrictions if any */
			$restrictions = $object->getProp('_restriction');

		/* restrictions apply */
			if( $restrictions ){
				$display = '';
				if( in_array('email_not_confirmed', $restrictions) ){
					$display = 'emailNotConfirmed';
					}
				elseif( in_array('not_approved', $restrictions) ){
					$display = 'notApproved';
					}
				elseif( in_array('suspended', $restrictions) ){
					$display = 'suspended';
					}
				else {
					$msg = M('There is a problem with your account');
					}

				if( $display ){
					$forwardTo = ntsLink::makeLink( '-current-', '', array('display' => $display) );
					}
				else {
					ntsView::addAnnounce( $msg, 'error' );
					$forwardTo = ntsLink::makeLink();
					}

				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
			/* complete actions */
				$cm->runCommand( $object, 'login' );
			/* continue to login dispatcher */
				$forwardTo = ntsLink::makeLink( '-current-/dispatcher' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			}
		else {
		/* form not valid, continue to login form */
			}
		break;

	default:
		break;
	}
?>