<?php
$conf =& ntsConf::getInstance();
$currentLicense = $conf->get('licenseCode');
$installationId = $conf->get( 'installationId' );
$installedVersion = $conf->get('currentVersion');

$myProduct = ntsLib::getMyProduct();

$myUrl = ntsLink::makeLinkFull( NTS_FRONTEND_WEBPAGE );
// strip started http:// as apache seems to have troubles with it
$myUrl = preg_replace( '/https?\:\/\//', '', $myUrl );

$checkUrl2 = $_NTS['CHECK_LICENSE_URL'] . '?code=' . $currentLicense . '&iid=' . $installationId . '&ver=' . $installedVersion . '&prd=' . urlencode($myProduct) . '&url=' . urlencode($myUrl);
?>
<tr>
	<td class="ntsFormLabel"><?php echo M('License Code'); ?></td>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'licenseCode',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			)
		);
	?>
	</td>
	<td>
<?php if( $currentLicense ) : ?>
<script language="JavaScript" type="text/javascript" src="<?php echo $checkUrl2; ?>">
</script>
<script language="JavaScript" type="text/javascript">
var myWrapper = ntsLicenseStatus ? "<span class='ok'>" : "<span class='alert'>";
document.write( myWrapper )
document.write( ntsLicenseText );
document.write( '</span>' )
</script>
<?php endif; ?>
	</td>
</tr>

<tr>
<td></td>
<td>
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</td>
<td></td>
</tr>
