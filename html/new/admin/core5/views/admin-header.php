<?php
$now = time();
$check = $now - 1 * 60 * 60;
$conf =& ntsConf::getInstance();

$currentLicense = $conf->get('licenseCode');
$installationId = $conf->get( 'installationId' );
$installedVersion = $conf->get('currentVersion');
$installedVersionNumber = ntsLib::parseVersion( $installedVersion );

$myProduct = ntsLib::getMyProduct();

$myUrl = ntsLink::makeLinkFull( NTS_FRONTEND_WEBPAGE );
// strip started http:// as apache seems to have troubles with it
$myUrl = preg_replace( '/https?\:\/\//', '', $myUrl );

$checkUrl2 = $_NTS['CHECK_LICENSE_URL'] . '?code=' . $currentLicense . '&iid=' . $installationId . '&ver=' . $installedVersion . '&prd=' . urlencode($myProduct) . '&url=' . urlencode($myUrl);

$checkLicense = false;
if( (! isset($_SESSION['home_call'])) || $_SESSION['home_call'] ){
	if( $NTS_CURRENT_USER->hasRole('admin') && (! $NTS_CURRENT_USER->isPanelDisabled('admin/conf/upgrade')) ){
		$checkLicense = true;
		}
	else {
		$checkLicense = false;
		}
	$_SESSION['home_call'] = 0;
	}

$skipPanels = array('admin/conf/upgrade');
reset( $skipPanels );
foreach( $skipPanels as $sp ){
	if( substr($_NTS['CURRENT_PANEL'], 0, strlen($sp)) == $sp ){
		$checkLicense = false;
		break;
		}
	}
$licenseLink = ntsLink::makeLink( 'admin/conf/upgrade' );
?>

<?php /*?><?php if( NTS_APP_LITE ) : ?>
<ul id="nts-admin-announce">
<li class="ok">
Check out the <a target="_blank" href="http://www.hitappoint.com/order/">full version</a> to get a lot more!
</li>
</ul>
<?php endif; ?>
<?php if( $checkLicense ) : ?>
	<script language="JavaScript" type="text/javascript" src="<?php echo $checkUrl2; ?>">
	</script>
	<script language="JavaScript" type="text/javascript">
	if( ! ntsLicenseStatus ){
		document.write( '<ul id="nts-admin-announce">' );
		document.write( '<li>' );

		document.write( '<a href="<?php echo $licenseLink; ?>">' );
		document.write( ntsLicenseText );
		document.write( '</a>' );

		document.write( '</li>' );
		document.write( '</ul>' );
		}

	var currentVersionNumber = 0;
	if( ntsVersion.length ){
		var myV = ntsVersion.split( '.' );
		currentVersionNumber = myV[0] + '' + myV[1] + '' + ntsZeroFill(myV[2], 2);
		}
	if( (currentVersionNumber > 0) && (currentVersionNumber > <?php echo $installedVersionNumber; ?>) ){
		document.write( '<ul id="nts-admin-announce">' );
		document.write( '<li class="ok">' );

<?php if( $_NTS['DOWNLOAD_URL'] ) : ?>
		document.write( '<a target="_blank" href="<?php echo $_NTS['DOWNLOAD_URL']; ?>">' );
<?php endif; ?>
		document.write("New version available: " + '<b>' + ntsVersion + '</b>');
<?php if( $_NTS['DOWNLOAD_URL'] ) : ?>
		document.write( '</a>' );
<?php endif; ?>

		document.write( '</li>' );
		document.write( '</ul>' );
		}
	</script>
<?php endif; ?><?php */?>