<?php
/**
 * @package hitAppoint
 * @author hitAppoint
 * @version 5.0.0
 */
/*
Plugin Name: hitAppoint
Plugin URI: http://www.hitappoint.com/
Description: This plugin integrates hitAppoint within your WordPress site. Please note that hitAppoint will make use of the WordPress users accounts.
Author: hitAppoint
Version: 5.0.0
Author URI: http://www.hitappoint.com/
*/
define( 'NTS_SKIP_COOKIE', 1 );
define( 'HITAPPOINT_PATH', dirname(__FILE__) );
define( 'HITAPPOINT_WP_PAGE_PARAM', 'page_id' );

register_activation_hook( __FILE__, 'ha_install' );
register_deactivation_hook( __FILE__, 'ha_uninstall' );

add_action('init', 'ha_init');
add_action('get_header', 'ha_head');
add_action( 'wp_print_styles', 'ha_print_styles' );
add_action( 'admin_print_styles', 'ha_admin_print_styles' );

add_action('admin_init', 'ha_admin_init');
add_action('admin_menu', 'ha_admin_menu');
add_action('admin_head', 'ha_admin_head');

add_filter('the_content', 'ha_customer_display');
add_filter('wp_title', 'ha_title');

define( 'HITAPPOINT_ADMIN_PAGE', 'hitappoint' );
define( 'HITAPPOINT_REPLACE_TAG', '[[HITAPPOINT]]' );
define( 'HITAPPOINT_SHORTCODE', '[hitappoint]' );

function ha_print_styles(){
	global $post, $HITAPPOINT_PAGES;

	if(
		($post->ID) && 
		$HITAPPOINT_PAGES && 
		in_array($post->ID, $HITAPPOINT_PAGES) && 
		defined('NTS_ROOT_WEBPAGE')
		)
		{
		$file = HITAPPOINT_PATH . '/core5/views/css.php';
		require( $file );

		$html =<<<EOT
<link rel="stylesheet" type="text/css" href="$NTS_CSS_URL" />

EOT;
		echo $html;
		}
	}

function ha_admin_print_styles(){
	if( isset($_GET['page']) && ($_GET['page'] == HITAPPOINT_ADMIN_PAGE) ){
		$file = HITAPPOINT_PATH . '/core5/views/css.php';
		require( $file );

		$html =<<<EOT
<link rel="stylesheet" type="text/css" href="$NTS_CSS_URL" />

EOT;
		echo $html;
		}
	}

function ha_customer_display( $content, $core = FALSE ){
	if( $core )
	{
		ob_start();
		$file = HITAPPOINT_PATH . '/core5/view.php';
		require( $file );
		$haContent = ob_get_contents();
		ob_end_clean();
		return $haContent;
	}

	if(
		(strpos($content, HITAPPOINT_REPLACE_TAG) !== false) OR
		(strpos($content, HITAPPOINT_SHORTCODE) !== false)
		){
		if( ! defined('NTS_CURRENT_USERID') ){
			ha_head();
			}
		// check if we have PHP code as well
		$tag = '[php]';
		$endtag = '[/php]';
		$php_start = 0;
		while(is_long($php_start = strpos($content, $tag, $php_start))){
			$start_pos = $php_start + strlen($tag);
			$end_pos = strpos($content, $endtag, $start_pos); //the 3rd param is to start searching from the starting tag - not to mix the ending tag of the 1st block if we want for the 2nd

			if (!$end_pos) { echo "php code has no ending tag!", exit; }
			$php_end = $end_pos + strlen($endtag);

			$php_code = substr($content, $start_pos, $end_pos - $start_pos);

			// before php code
			$part1 = substr($content, 0, $php_start);

			// after php code
			$part2 = substr($content, $php_end, strlen($content));

			$content = $part1 . $part2;
			}

		ob_start();
		$file = HITAPPOINT_PATH . '/core5/view.php';
		require( $file );
		$haContent = ob_get_contents();
		ob_end_clean();

		if( strpos($content, HITAPPOINT_REPLACE_TAG) !== false )
			$content = str_replace( HITAPPOINT_REPLACE_TAG, $haContent, $content );
		else
			$content = str_replace( HITAPPOINT_SHORTCODE, $haContent, $content );
		}
	return $content;
	}

function ha_title( $content ){
	global $post, $HITAPPOINT_PAGES;

	if( 
		($post->ID) && 
		$HITAPPOINT_PAGES && 
		in_array($post->ID, $HITAPPOINT_PAGES) && 
		defined('NTS_ROOT_WEBPAGE')
		)
		{
		$file = HITAPPOINT_PATH . '/core5/views/title.php';
		require( $file );
		$content = ntsView::getTitle() . ' - ' . $content;
		}
	return $content;
	}

function ha_admin_menu(){
//	add_menu_page( 'hitAppoint', 'hitAppoint', 'manage_options', HITAPPOINT_ADMIN_PAGE, 'ha_admin_options' );	
	add_menu_page( 'hitAppoint', 'hitAppoint', 'read', HITAPPOINT_ADMIN_PAGE, 'ha_admin_options' );	
	}

function ha_admin_options(){
	global $HITAPPOINT_PAGES;
	if( ! $HITAPPOINT_PAGES )
	{
		$announceText = M("You have not yet added hitAppoint &#91;hitappoint&#93; shortcode to any of your posts or pages, some features may not work properly!");
		ntsView::setAdminAnnounce( $announceText, 'alert' );
	}
	$file = HITAPPOINT_PATH . '/core5/view.php';
	require( $file );
	}

function ha_admin_head(){
	}

function ha_init(){
	global $HITAPPOINT_PAGES;
	ob_start();
	global $wpdb;

	$HITAPPOINT_PAGES = array();
	$pages = $wpdb->get_results( 
		"
		SELECT 
			ID 
		FROM $wpdb->posts 
		WHERE 
			(post_type = 'post' OR post_type = 'page') AND 
			(
			post_content LIKE '%" . HITAPPOINT_REPLACE_TAG . "%' OR 
			post_content LIKE '%" . HITAPPOINT_SHORTCODE . "%'
			)
		"
		);
	foreach( $pages as $p ){
		$HITAPPOINT_PAGES[] = $p->ID;
		}

	$webPage = $HITAPPOINT_PAGES ? $HITAPPOINT_PAGES[0] : '';
	define( 'NTS_FRONTEND_WEBPAGE', get_bloginfo('wpurl') . '?' . HITAPPOINT_WP_PAGE_PARAM . '=' . $webPage );

/* database */
	$ownDbFile = HITAPPOINT_PATH . '/db.php';
	if( file_exists($ownDbFile) ){
		include_once( $ownDbFile );
		}
	else {
		global $table_prefix;
		define( 'NTS_DB_HOST',		DB_HOST);
		define( 'NTS_DB_USER',		DB_USER);
		define( 'NTS_DB_PASS',		DB_PASSWORD);
		define( 'NTS_DB_NAME',		DB_NAME);
		define( 'NTS_DB_TABLES_PREFIX',	$table_prefix . 'ha_');
		}

	define( 'NTS_REMOTE_INTEGRATION',	'wordpress' );
	define( 'NTS_SESSION_NAME', 'ntssess_hitappoint' );
	session_name( NTS_SESSION_NAME );
	session_start();

	ob_start();
	}

function ha_head(){
	global $post, $HITAPPOINT_PAGES, $_NTS;
	$currentUser = wp_get_current_user();
	define( 'NTS_CURRENT_USERID', $currentUser->ID );
	// customer view */
	if( ($post->ID) && $HITAPPOINT_PAGES && in_array($post->ID, $HITAPPOINT_PAGES) ){
		$rootWebpage = get_permalink( $post->ID );

//echo "rd = '$rootWebpage'<br>";
		define( 'NTS_ROOT_WEBPAGE',	$rootWebpage );

		$content = $post->post_content;

		// check if we have PHP code as well
		$tag = '[php]';
		$endtag = '[/php]';
		$php_start = 0;
		while(is_long($php_start = strpos($content, $tag, $php_start))){
			$start_pos = $php_start + strlen($tag);
			$end_pos = strpos($content, $endtag, $start_pos); //the 3rd param is to start searching from the starting tag - not to mix the ending tag of the 1st block if we want for the 2nd

			if (!$end_pos) { echo "php code has no ending tag!", exit; }
			$php_end = $end_pos + strlen($endtag);

			$php_code = substr($content, $start_pos, $end_pos - $start_pos);

			// before php code
			$part1 = substr($content, 0, $php_start);

			// here set the php output
			$php_code = strip_tags( $php_code );
			$php_code = ha_unhtmlentities( $php_code );

			ob_start();
			
			eval($php_code);
			$output = ob_get_contents();
			ob_end_clean();

			// after php code
			$part2 = substr($content, $php_end, strlen($content));

			$content = $part1 . $output . $part2;
			}

		$file = HITAPPOINT_PATH . '/core5/controller.php';
		require( $file );

		if( (substr($_NTS['CURRENT_PANEL'], 0, strlen('admin')) == 'admin') ){
			reset( $_GET );
			$linkParts = array( 'page=' . HITAPPOINT_ADMIN_PAGE );
			foreach( $_GET as $p => $v ){
				if( $p == HITAPPOINT_WP_PAGE_PARAM )
					continue;
				$linkParts[] = $p . '=' . urlencode($v);
				}
			$link = join( '&', $linkParts );
			wp_redirect( admin_url('admin.php?' . $link) );
			exit;
			}
		else {
			if( isset($_REQUEST['nts-layout']) && ($_REQUEST['nts-layout'] == 'core') )
			{
				echo ha_customer_display( '', TRUE );
				exit;
			}
			}
		}
	else {
		}
	}

function ha_admin_init(){
	global $HITAPPOINT_PAGES, $_NTS;

	$getCopy = $_GET;
	if( isset($_REQUEST['page']) ){
		$getCopy['page'] = $_REQUEST['page'];
		}

	if( isset($getCopy['page']) && ($getCopy['page'] == HITAPPOINT_ADMIN_PAGE) ){
		$rootWebpage = admin_url('admin.php?page=' . HITAPPOINT_ADMIN_PAGE);
// echo "ra = '$rootWebpage'<br>";
		define( 'NTS_ROOT_WEBPAGE',	$rootWebpage );

		$file = HITAPPOINT_PATH . '/core5/controller.php';
		require( $file );

		$startCustomer = 'customer';
		if( substr($_NTS['CURRENT_PANEL'], 0, strlen($startCustomer)) == $startCustomer ){
			wp_redirect( get_bloginfo('wpurl') . '?' . HITAPPOINT_WP_PAGE_PARAM . '=' . $HITAPPOINT_PAGES[0] . '&nts-panel=' . $_NTS['CURRENT_PANEL'] );
			exit;
			}
		}
	}

function ha_install(){
	/* make the current user the admin in hitAppoint */
	$currentUser = wp_get_current_user();
	$currentUserId = $currentUser->ID;
	define( 'NTS_CURRENT_USERID', $currentUser->ID );

	global $NTS_SETUP_ADMINS;
	$NTS_SETUP_ADMINS = array();

	$role = 'Administrator';
	$wp_user_search = new WP_User_Search( '', '', $role);
	$NTS_SETUP_ADMINS = $wp_user_search->get_results();

/* database */
	global $table_prefix;
	define( 'NTS_DB_HOST',		DB_HOST);
	define( 'NTS_DB_USER',		DB_USER);
	define( 'NTS_DB_PASS',		DB_PASSWORD);
	define( 'NTS_DB_NAME',		DB_NAME);
	define( 'NTS_DB_TABLES_PREFIX',	$table_prefix . 'ha_');

	$file = HITAPPOINT_PATH . '/core5/model/init.php';
	include_once( $file );

	$NTS_CURRENT_VERSION = $conf->get('currentVersion');
	if( ! $NTS_CURRENT_VERSION ){
		require( HITAPPOINT_PATH . '/core5/setup/create-database.php' );
		require( HITAPPOINT_PATH . '/core5/setup/populate.php' );
		}
	}

function ha_uninstall(){
/* database */
	global $table_prefix;
	define( 'NTS_DB_HOST',		DB_HOST);
	define( 'NTS_DB_USER',		DB_USER);
	define( 'NTS_DB_PASS',		DB_PASSWORD);
	define( 'NTS_DB_NAME',		DB_NAME);
	define( 'NTS_DB_TABLES_PREFIX',	$table_prefix . 'ha_');

	$file = HITAPPOINT_PATH . '/core5/model/init.php';
	include_once( $file );

// don't remove data
//	require( NTS_BASE_DIR . '/setup/uninstall.php' ); //
	}
	
function ha_unhtmlentities($string){
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
	}
?>