<?php
$plm =& ntsPluginManager::getInstance();
$plugin = 'sms';
$sms_settings = $plm->getPluginSettings( $plugin );

$carrier_options = array();
if( isset($sms_settings['carriers']) )
{
	reset( $sms_settings['carriers'] );
	foreach( $sms_settings['carriers'] as $carrier )
	{
		$carrier_options[] = array( $carrier, $carrier );
	}
}

if( $carrier_options ) 
{
	array_unshift( $carrier_options, array('', ' - ' . M('Select') . ' - ') );
}
?>
<table class="ntsForm">
<tr>
	<td class="ntsFormLabel">Mobile Phone *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'to',
			'attr'		=> array(
				'size'	=> 32,
				),
			'required'	=> 1,
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
	</TD>
</TR>
<?php if( $carrier_options ) : ?>
<tr>
	<td class="ntsFormLabel">Mobile Carrier *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'carrier',
			'required'	=> 1,
			'options'	=> $carrier_options
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
	</TD>
</TR>
<?php endif; ?>

<tr>
	<td class="ntsFormLabel">Message *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'message',
			'default'	=> '',
			'attr'		=> array(
				'cols'	=> 32,
				'rows'	=> 4,
				),
			'required'	=> 1,
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
	</TD>
</TR>

<tr>
<td>
&nbsp;
</td>
<td>
<?php echo $this->makePostParams('-current-', 'send' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Send'); ?>">
</td>
</tr>
</TABLE>
