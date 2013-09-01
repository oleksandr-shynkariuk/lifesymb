<?php
/* check if cookies enabled */
$skipCookie = ( defined('NTS_SKIP_COOKIE') && NTS_SKIP_COOKIE ) ? 1 : $_NTS['REQ']->getParam( 'nts-skip-cookie' );
if( (! $skipCookie) && (! ( isset($_COOKIE['ntsTestCookie']) && ($_COOKIE['ntsTestCookie'] == 'ntsTestCookie') )) ){
	$display = 'noCookies';
	$forwardTo = ntsLink::makeLink( 'anon/login', '', array('display' => $display) );
	ntsView::redirect( $forwardTo );
	exit;
	}

global $NTS_PERSISTENT_PARAMS;

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form_Login';
$form =& $ff->makeForm( $formFile );

if( $form->validate() ){
	$formValues = $form->getValues();

/* local handler */
	$object = new ntsUser();
	if( NTS_EMAIL_AS_USERNAME )
		$object->setProp( 'email', $formValues['login_email'] );
	else
		$object->setProp( 'username', $formValues['login_username'] );
	$object->setProp( 'password', $formValues['login_password'] );

	$cm =& ntsCommandManager::getInstance();
	$cm->runCommand( $object, 'check_password' );

	if( ! $cm->isOk() ){
	/* form not valid, continue to login form */
		$errorText = $cm->printActionErrors();
		$NTS_VIEW['form']->errors[ 'login_password' ] = $errorText;
		return;
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
			$forwardTo = ntsLink::makeLink( 'anon/login', '', array('display' => $display) );
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

		if( ! $object->hasRole('admin') ){
			$customerId = $object->getId();
			}
		}
	}
else {
/* form not valid, continue to login form */
	return;
	}
?>