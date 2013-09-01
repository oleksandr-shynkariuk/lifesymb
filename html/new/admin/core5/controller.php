<?php
global $_NTS, $NTS_VIEW;
include_once( dirname(__FILE__) . '/model/init.php' );

$viewMode = isset($_REQUEST[NTS_PARAM_VIEW_MODE]) ? $_REQUEST[NTS_PARAM_VIEW_MODE] : '';
$NTS_VIEW[NTS_PARAM_VIEW_MODE] = $viewMode;

require_once( dirname(__FILE__) . '/panels/init2.php' );
// if view mode was redefined
$viewMode = $NTS_VIEW[NTS_PARAM_VIEW_MODE];

switch( $viewMode ){
	case 'ajax':
		require( dirname(__FILE__) . '/views/ajax.php' );
		exit;
		break;
	case 'print':
		require( dirname(__FILE__) . '/views/print.php' );
		exit;
		break;
	}
?>