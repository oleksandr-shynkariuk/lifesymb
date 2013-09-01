<?php
define( 'NTS_APP_VERSION', '5.2.4' );
if( file_exists(dirname(__FILE__) . '/panels/admin/company/services/create') )
	define( 'NTS_APP_LITE', false );
else
	define( 'NTS_APP_LITE', true );

if( file_exists(dirname(__FILE__) . '/../whitelabel.php') )
	define( 'NTS_APP_WHITELABEL', true );
else
	define( 'NTS_APP_WHITELABEL', false );

/* check which we are using */
if( ! file_exists(dirname(__FILE__) . '/panels/admin/company/services/create') ){
	define( 'NTS_APP_LEVEL', 'lite' );
	}
elseif( ! file_exists(dirname(__FILE__) . '/panels/admin/company/staff') ){
	define( 'NTS_APP_LEVEL', 'solo' );
	}
else {
	define( 'NTS_APP_LEVEL', 'pro' );
	}

define( 'NTS_DEFAULT_USER_ROLE', 'customer' );

global $NTS_SKIP_PANELS;
$NTS_SKIP_PANELS = array(
	'superadmin',
	);
?>