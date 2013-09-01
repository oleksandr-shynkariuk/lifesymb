<?php
// take notice
$thisPage = ntsLib::pureUrl( ntsLib::currentPageUrl() );
$from = ntsLib::webDirName($thisPage);
// strip started http:// as apache seems to have troubles with it
$from = preg_replace( '/https?\:\/\//', '', $from );
$trackUrl = 'http://www.hitcode.com/customers/lic.php';
$trackUrl = 'http://localhost/sld2/lic.php';
$trackCode = '';
?>
<html>
<head>
<title>Installation</title>

<STYLE TYPE="text/css">
LABEL {
	DISPLAY: block;
	PADDING: 0.2em 0.2em;
	MARGIN: 0.2em 0.2em 0.5em 0.2em;
	LINE-HEIGHT: 1em;
	overflow: auto;
	}
LABEL SPAN {
	FONT-WEIGHT: bold;
	DISPLAY: block;
	FLOAT: left;
	WIDTH: 12em;
	}
.success {
	FONT-WEIGHT: bold;
	COLOR: #00BB00;
	}
</STYLE>
</head>
<body>
<h1>Installation</h1>
<?php
$step = (isset($_REQUEST['step']) ) ? $_REQUEST['step'] : 'start';

//wordpress?
global $table_prefix;
$wordpress = false;
if( isset($table_prefix) && $table_prefix ){
	$tbPrefix = $table_prefix . 'ha45_';
	$wordpress = true;
	}
else {
	$tbPrefix = NTS_DB_TABLES_PREFIX;
	$wordpress = false;
	}
?>

<?php if( $step == 'start' ) : ?>
<?php
// check if there's an older 4.5 install
$oldWrapper = new ntsMysqlWrapper( NTS_DB_HOST, NTS_DB_USER, NTS_DB_PASS, NTS_DB_NAME, $tbPrefix );
$oldWrapper->init();
$oldTables = $oldWrapper->getTablesInDatabase();
$currentVersion = 0;
if( in_array('conf', $oldTables) ){
	// conf table exists, search installed version
	$sql = 'SELECT value FROM {PRFX}conf WHERE NAME="currentVersion"';
	$result = $oldWrapper->runQuery($sql);
	if( $i = $result->fetch() ){
		$currentVersion = $currentVersion = $i['value'];
		}
	}
?>
<?php if( $currentVersion ) : ?>
	<h2>Upgrade</h2>
	<p>
	Upgrade from <b><?php echo $currentVersion; ?></b>
<?php
$upgradeLink = '?step=upgrade';
if( $wordpress )
	$upgradeLink .= '&page=hitappoint';
?>	
	<a href="<?php echo $upgradeLink; ?>">Click here to upgrade your current installation</a>

<?php 	if( ! $wordpress ) : ?>	
	<p>or
	<h2>New Install</h2>
<?php	endif; ?>
<?php elseif( $wordpress ) : ?>
<?php
		global $NTS_SETUP_ADMINS;
		$NTS_SETUP_ADMINS = array();

		$role = 'Administrator';
		$wp_user_search = new WP_User_Search( '', '', $role);
		$NTS_SETUP_ADMINS = $wp_user_search->get_results();

		require( dirname(__FILE__) . '/create-database.php' );
		require( dirname(__FILE__) . '/populate.php' );	
		$targetLink = '?page=hitappoint';
?>
<script language="JavaScript">
document.location.href="<?php echo $targetLink; ?>";
</script>
<?php endif; ?>

<?php if( ! $wordpress ) : ?>	
<?php	require( dirname(__FILE__) . '/form.php' ); ?>
<?php endif; ?>

<?php elseif( $step == 'upgrade' ): ?>
<?php	require( dirname(__FILE__) . '/upgrade.php' ); ?>

		<span class="success">Database tables created, data imported from old version</span>
		<p>
<?php
$targetLink = 'index.php';
if( $wordpress ){
	$targetLink = '?page=hitappoint';
	}

$currentLicense = '';
$installationId = $conf->get( 'installationId' );
$installedVersion = $conf->get('currentVersion');
$myProduct = ntsLib::getMyProduct();
$thisPage = ntsLib::pureUrl( ntsLib::currentPageUrl() );
$myUrl = ntsLib::webDirName($thisPage);
if( $wordpress ){
	$myUrl = get_bloginfo('wpurl');
	}
$myUrl = ntsLink::makeLinkFull( $myUrl );

$checkUrl2 = $trackUrl . '?code=' . $currentLicense . '&iid=' . $installationId . '&ver=' . $installedVersion . '&prd=' . urlencode($myProduct) . '&url=' . urlencode($myUrl);
?>
		Your <a href="<?php echo $targetLink; ?>">online appointment scheduler</a> is ready.

<script language="JavaScript" type="text/javascript" src="<?php echo $checkUrl2; ?>"></script>

<?php elseif( $step == 'create' ): ?>
<?php	require( NTS_BASE_DIR . '/setup/create-database.php' ); ?>
<?php	require( dirname(__FILE__) . '/populate.php' ); ?>

<?php
$targetLink = 'index.php';
if( $wordpress ){
	$targetLink = '?page=hitappoint';
	}

$currentLicense = '';
$installationId = $conf->get( 'installationId' );
$installedVersion = $conf->get('currentVersion');
$myProduct = ntsLib::getMyProduct();
$thisPage = ntsLib::pureUrl( ntsLib::currentPageUrl() );
$myUrl = ntsLib::webDirName($thisPage);

if( $wordpress ){
	$myUrl = get_bloginfo('wpurl');
	}
$myUrl = ntsLink::makeLinkFull( $myUrl );

$checkUrl2 = $trackUrl . '?code=' . $currentLicense . '&iid=' . $installationId . '&ver=' . $installedVersion . '&prd=' . urlencode($myProduct) . '&url=' . urlencode($myUrl);

if( NTS_APP_LITE )
{
	$trackCode =<<<EOT
<img src="http://www.fiammante.com/piwik/piwik.php?idsite=1&amp;rec=1" style="border:0" alt="" />
EOT;

	$trackCode =<<<EOT
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(["trackPageView"]);
  _paq.push(["enableLinkTracking"]);

  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://www.fiammante.com/piwik/";
    _paq.push(["setTrackerUrl", u+"piwik.php"]);
    _paq.push(["setSiteId", "1"]);
	_paq.push(['trackGoal', 2]);
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
  })();
</script>
EOT;
}
?>
		<span class="success">Database tables created, admin account configured, sample data populated</span>
		<p>
		Your <a href="<?php echo $targetLink; ?>">online appointment scheduler</a> is ready.

<script language="JavaScript" type="text/javascript" src="<?php echo $checkUrl2; ?>"></script>

<?php echo $trackCode; ?>
<?php endif; ?>

</body>
</html>