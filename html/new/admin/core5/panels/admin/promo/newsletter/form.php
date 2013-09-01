<?php
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();
$ntsdb =& dbWrapper::getInstance();

$conf =& ntsConf::getInstance();
$commonHeader = $conf->get('emailCommonHeader');
$commonFooter = $conf->get('emailCommonFooter');
?>
<table class="ntsForm">
<?php
// count providers and customers
?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Send To'); ?></td>
	<td class="ntsFormValue">

	<?php
	// find suspended
	$where = array(
		'obj_class'		=> array( '=', 'user' ),
		'meta_name'		=> array( '=', '_restriction' ),
		'meta_value'	=> array( '=', 'suspended' ),
		);
	$result = $ntsdb->select( 'obj_id', 'objectmeta', $where );
	$suspendedIds = array();
	while( $i = $result->fetch() ){
		$suspendedIds[] = $i['obj_id'];
		}

	/* count */
	$customersCount = $integrator->countUsers( 
		array(
			'_role' => array('=', 'customer'),
			'id'	=> array('NOT IN', $suspendedIds),
			)
		);
	$providersCount = $integrator->countUsers( array('_role' => array('=', 'admin')) );

	$sendToOptions = array();
	$sendToOptions[] = array( 'customers', M('Customers') . ' [' . $customersCount . ']' );
	$sendToOptions[] = array( 'providers', M('Administrative Users') . ' [' . $providersCount . ']' );
	?>
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'send_to',
			'value'		=> 'customer',
			'default'	=> 'customer',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Please choose whom to send your newsletter to'),
				),
			)
		);
	?> <?php echo M('Customers'); ?> [<?php echo $customersCount; ?>]

<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'send_to',
			'value'		=> 'admin',
			'default'	=> 'customer',
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Please choose whom to send your newsletter to'),
				),
			)
		);
	?> <?php echo M('Administrative Users'); ?> [<?php echo $providersCount; ?>]
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Subject'); ?> *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'subject',
			'attr'		=> array(
				'size'	=> 42,
				),
			'default'	=> '',
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
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Message'); ?> *</td>
	<td class="ntsFormValue">
	<a href="<?php echo ntsLink::makeLink('admin/conf/email_settings'); ?>"><?php echo M('Header'); ?>: <?php echo M('Edit'); ?></a>
	<br>
	<?php echo $commonHeader; ?>
	<br>
	<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'text',
			'attr'		=> array(
				'cols'	=> 48,
				'rows'	=> 8,
				),
			'default'	=> '',
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
	<br>
	<?php echo nl2br($commonFooter); ?>
	<br>
	<a href="<?php echo ntsLink::makeLink('admin/conf/email_settings'); ?>"><?php echo M('Footer'); ?>: <?php echo M('Edit'); ?></a>
	</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'send' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Send'); ?>">
</td>
</tr>
</table>
