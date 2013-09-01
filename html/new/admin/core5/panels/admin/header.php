<?php
$conf =& ntsConf::getInstance();
$theme = $conf->get( 'theme' );
$themeFolder = NTS_EXTENSIONS_DIR . '/themes/' . $theme;
$adminHeaderFile = $themeFolder . '/admin-header.php';
if( file_exists($adminHeaderFile) ){
	require( $adminHeaderFile );
	}
$display = isset($_REQUEST['display']) ? $_REQUEST['display'] : '';
$fontLink = ntsLink::makeLink('system/pull', '', array('what' => 'font'));

$jsFiles = 'jquery-ui-1.8.18.custom.min.js|glDatePicker.js|functions.js';
if( defined('NTS_REMOTE_INTEGRATION') && (NTS_REMOTE_INTEGRATION == 'joomla') )
	$jsFiles = 'jquery-1.7.2.min.js|' . $jsFiles;
?>
<script language="JavaScript" type="text/javascript" src="<?php echo ntsLink::makeLink('system/pull', '', array('what' => 'js', 'files' => $jsFiles) ); ?>">
</script>
<style>
@font-face {
	font-family: 'FontAwesome';

	src: url('<?php echo $fontLink; ?>&nts-file=fontawesome-webfont.eot');
	src: 
		url('<?php echo $fontLink; ?>&nts-file=fontawesome-webfont.eot&#iefix') format('embedded-opentype'),
		url('<?php echo $fontLink; ?>&nts-file=fontawesome-webfont.woff') format('woff'),
		url('<?php echo $fontLink; ?>&nts-file=fontawesome-webfont.ttf') format('truetype'),
		url('<?php echo $fontLink; ?>&nts-file=fontawesome-webfont.svg#fontawesomeregular') format('svg');
	font-weight: normal;
	font-style: normal;
	}
</style>