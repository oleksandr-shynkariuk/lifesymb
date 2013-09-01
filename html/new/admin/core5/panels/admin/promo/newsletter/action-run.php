<?php
$cm =& ntsCommandManager::getInstance();
$ntsdb =& dbWrapper::getInstance();

$step = 5;
$all = $_NTS['REQ']->getParam( 'all' );
$start = $_NTS['REQ']->getParam( 'start' );
if( ! $start )
	$start = 0;

$end = $start + $step - 1;
if( $end >= $all )
	$end = $all - 1;

$limit = $start . ', ' . $step;

$sendTo = $_SESSION['NTS_NEWSLETTER_SENDTO'];
$subj = $_SESSION['NTS_NEWSLETTER_SUBJ'];
$msg = $_SESSION['NTS_NEWSLETTER_MSG'];

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

$users = $integrator->getUsers( 
	array(
		'_role' => array('=', $sendTo),
		'id'	=> array('NOT IN', $suspendedIds)
		),
	array(),
	$limit
	);

echo ($start + 1) . ' - ' . ($end + 1) . ' of ' . $all;
echo '<br>=======<br>';

reset( $users );
foreach( $users as $userInfo ){
	$user = new ntsUser();
	$user->setByArray( $userInfo );
	$cm->runCommand( $user, 'email', array('body' => $msg, 'subject' => $subj) );
	echo "" . $userInfo['email'] . "<br>";
	}
echo '=======<br>';

$nextStart = $start + $step;
if( $nextStart >= $all ){
	unset( $_SESSION['NTS_NEWSLETTER_SENDTO'] );
	unset( $_SESSION['NTS_NEWSLETTER_SUBJ'] );
	unset( $_SESSION['NTS_NEWSLETTER_MSG'] );
	ntsView::addAnnounce( M('Newsletter') . ': ' . M('Send') . ': ' . M('OK'), 'ok' );
	$forwardTo = ntsLink::makeLink( '-current-' );

	}
else {
	$forwardTo = ntsLink::makeLink( '-current-', 'run', array('start' => $nextStart, 'all' => $all) );
	}
echo "<META http-equiv=\"refresh\" content=\"2;URL=$forwardTo\">";
exit;
?>