<?php
$ff =& ntsFormFactory::getInstance();
$conf =& ntsConf::getInstance();
$ntsdb =& dbWrapper::getInstance();

$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile );

if( $form->validate() ){
	$formValues = $form->getValues();

	$sendTo = $formValues['send_to'];
	$subj = $formValues['subject']; 
	$msg = $formValues['text']; 

	$where = array(
		'obj_class'		=> array( '=', 'user' ),
		'meta_name'		=> array( '=', '_restriction' ),
		'meta_value'	=> array( '=', 'suspended' ),
		);
	$result = $ntsdb->select( 'obj_id', 'objectmeta', $where );
	$suspendedIds = array();
	while( $i = $result->fetch() ){
		$suspendedIds[] = $i['obj_id'];
		}

	$countUsers = $integrator->countUsers( 
		array(
			'_role' => array('=', $sendTo),
			'id'	=> array('NOT IN', $suspendedIds)
			)
		);

	if( ! $countUsers ){
		ntsView::addAnnounce( 'No users to send newsletter to', 'error' );

		$forwardTo = ntsLink::makeLink( '-current-' );
		ntsView::redirect( $forwardTo );
		exit;
		}
	else {
	/* store in session for running */
		$_SESSION['NTS_NEWSLETTER_SENDTO'] = $sendTo;
		$_SESSION['NTS_NEWSLETTER_SUBJ'] = $subj;
		$_SESSION['NTS_NEWSLETTER_MSG'] = $msg;

	/* redirect to run */
		$forwardTo = ntsLink::makeLink( '-current-', 'run', array('start' => 0, 'all' => $countUsers) );
		ntsView::redirect( $forwardTo );
		exit;
		}
	}
else {
/* form not valid, continue to create form */
	}

?>