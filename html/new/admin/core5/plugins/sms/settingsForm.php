<?php
$plugin = 'sms';
$new = $_NTS['REQ']->getParam( 'new' );

$gateway = $plm->getPluginSetting( $plugin, 'gateway' );
$suppliedGateway = $_NTS['REQ']->getParam( 'gateway' );

if( $suppliedGateway && ($suppliedGateway != $gateway) )
	$gateway = $suppliedGateway;

$this->setValue( 'gateway', $gateway );

// available gateways
$dir = dirname(__FILE__) . '/gateways';
$folders = ntsLib::listSubfolders( $dir );

$gatewaysOptions = array();
$gatewaysOptions[] = array( "", M('Select') );
reset( $folders );
foreach( $folders as $f ){
	$gatewaysOptions[] = array( $f, $f );
	}
?>

<table>
<tr>
	<th>SMS Gateway</th>
	<TD>
	<?php
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'gateway',
			'options'	=> $gatewaysOptions,
			'attr'		=> array (
				'onChange'	=> "document.location.href='" . ntsLink::makeLink('-current-', '', array('plugin' => $plugin, 'new' => $new) ) . "&nts-gateway=' + this.value",
				),
			)
		);
	?>
	</TD>
</TR>
</TABLE>

<?php if( $gateway ) : ?>
	<h3><?php echo ucfirst($gateway); ?> Gateway Settings</h3>
	<?php
	$formFile = dirname(__FILE__) . '/gateways/' . $gateway . '/form.php';
	require( $formFile );
	?>
<h3>Misc Settings</h3>

<TABLE>
<tr>
	<th>SMS Test Mode</th>
	<TD>
	<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'debug',
			)
		);
	?>
	<br>
	<i>If set, SMS messages will be printed on screen rather than sent</i>
	</TD>
</TR>

<tr>
	<th>Disable SMS</th>
	<TD>
	<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'disabled',
			)
		);
	?>
	</TD>
</TR>
</TABLE>
<?php else : ?>
	<?php $skipSubmit = true; ?>
<?php endif; ?>

