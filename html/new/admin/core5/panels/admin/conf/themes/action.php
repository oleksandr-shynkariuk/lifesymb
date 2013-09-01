<?php
$conf =& ntsConf::getInstance();

switch( $action ){
	case 'activate':
		$newTheme = $_NTS['REQ']->getParam( 'theme' );
		$conf->set( 'theme', $newTheme );

		if( ! ($error = $conf->getError()) ){
			ntsView::setAnnounce( M('Theme') . ': ' . M('Activate') . ': ' . M('OK'), 'ok' );
		/* continue */
			$forwardTo = ntsLink::makeLink( '-current-' );
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
			echo '<BR>Database error:<BR>' . $error . '<BR>';
			}
		break;

	default:
		break;
	}
?>