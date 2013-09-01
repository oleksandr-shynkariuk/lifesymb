<!-- FOOTER -->
<div id="nts-footer">
<?php if( NTS_APP_WHITELABEL ) : ?>
<?php
$wlFile = NTS_APP_DIR . '/../whitelabel.php';
require( $wlFile );
?>
<?php else : ?>
<?php 
global $NTS_CURRENT_VERSION;
$currentYear = date('Y');
?>
&copy; 2010-<?php echo $currentYear; ?> <a href="http://www.lifesymb.com/new"><b>Lifesymb</b></a>
<?php endif; ?>

<?php
$conf =& ntsConf::getInstance();
$theme = $conf->get( 'theme' );
$themeFolder = NTS_EXTENSIONS_DIR . '/themes/' . $theme;
$adminFooterFile = $themeFolder . '/admin-footer.php';
if( file_exists($adminFooterFile) ){
	require( $adminFooterFile );
	}
?>
<?php /*?><?php
if( $_SERVER['SERVER_NAME'] == 'localhost' ){
	echo '<br>';
	ntsLib::printCurrentExecutionTime();
	$ntsdb =& dbWrapper::getInstance();
	echo '<br>' . $ntsdb->_queryCount . ' queries';
	echo '<br>memory: ' . number_format(memory_get_usage());
	}
?><?php */?>
</div>