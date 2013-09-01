<?php if( isset($NTS_VIEW['display']) ) : ?>
	<?php require($NTS_VIEW['display']); ?>
<?php else : ?>
<?php
$conf =& ntsConf::getInstance();

$currentLicense = $conf->get('licenseCode');

$currentVersion = $conf->get('currentVersion');
if( ! $currentVersion )
	$currentVersion = NTS_APP_VERSION;

list( $v1, $v2, $v3 ) = explode( '.', $currentVersion );
$dgtCurrentVersion = $v1 . $v2 . sprintf('%02d', $v3 );

$fileVersion = NTS_APP_VERSION;
list( $v1, $v2, $v3 ) = explode( '.', $fileVersion );
$dgtFileVersion = $v1 . $v2 . sprintf('%02d', $v3 );
?>

<table class="ntsForm">

<?php
if( NTS_APP_LEVEL != 'lite' ){
	$NTS_VIEW['form']->display();
	}
?>

<tr>
<td class="ntsFormLabel"><?php echo M('Installation Path'); ?></td>
<td class="ntsFormValue"><?php echo realpath(NTS_APP_DIR . '/../'); ?></td>
<td></td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Installed Version'); ?></td>
<td class="ntsFormValue"><?php echo $currentVersion; ?></td>
<td>
<a class="alert" href="<?php echo ntsLink::makeLink('-current-', 'uninstall' ); ?>" onClick="return confirm('<?php echo M('Are you sure?'); ?>');"><?php echo M('Uninstall'); ?>?</a>
</td>
</tr>
<tr>
<td class="ntsFormLabel"><?php echo M('Uploaded Version'); ?></td>
<td class="ntsFormValue"><?php echo $fileVersion; ?></td>
<td></td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Current Version'); ?></td>
<td class="ntsFormValue">
<?php
$myProduct = ntsLib::getMyProduct();
$myUrl = ntsLink::makeLinkFull( NTS_FRONTEND_WEBPAGE );
// strip started http:// as apache seems to have troubles with it
$myUrl = preg_replace( '/https?\:\/\//', '', $myUrl );

$checkUrl2 = $_NTS['CHECK_LICENSE_URL'] . '?code=' . $currentLicense . '&iid=' . $installationId;
$checkUrl2 = $_NTS['CHECK_LICENSE_URL'] . '?code=' . $currentLicense . '&iid=' . $installationId . '&ver=' . $installedVersion . '&prd=' . urlencode($myProduct) . '&url=' . urlencode($myUrl);
$installedVersionNumber = ntsLib::parseVersion( $currentVersion );
?>
<script language="JavaScript" type="text/javascript" src="<?php echo $checkUrl2; ?>">
</script>
<script language="JavaScript" type="text/javascript">
	document.write(ntsVersion);
</script>
</td>

<td>
<script language="JavaScript" type="text/javascript">
if( ntsVersion.length ){
	var myV = ntsVersion.split( '.' );
	currentVersionNumber = myV[0] + '' + myV[1] + '' + ntsZeroFill(myV[2], 2);
	}
if( (currentVersionNumber > 0) && (currentVersionNumber > <?php echo $dgtFileVersion; ?>) ){
<?php if( $_NTS['DOWNLOAD_URL'] ) : ?>
	document.write( '<a class="nts-notice" target="_blank" href="<?php echo $_NTS['DOWNLOAD_URL']; ?>">' );
<?php endif; ?>
	document.write( "<?php echo M('Please Upgrade'); ?>" );
<?php if( $_NTS['DOWNLOAD_URL'] ) : ?>
	document.write( '</a>' );
<?php endif; ?>
	}
</script>
</td>
</tr>

<tr>
<td colspan="3">
<?php if( $dgtFileVersion > $dgtCurrentVersion ) : ?>
	<p>
	<a href="<?php echo ntsLink::makeLink('-current-/../backup', 'make' ); ?>"><?php echo M('Download Backup'); ?></a> - highly recommended!
	<p>
	<a class="nts-button3" href="<?php echo ntsLink::makeLink('-current-', 'upgrade' ); ?>"><?php echo M('Run Upgrade Procedure'); ?>: <?php echo $fileVersion; ?></a>
<?php else: ?>
	<?php echo M('No Upgrade Procedure To Run'); ?>
<?php endif; ?>
</td>
</tr>
</table>
<?php endif; ?>