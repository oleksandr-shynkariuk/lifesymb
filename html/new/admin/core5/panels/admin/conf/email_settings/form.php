<?php
/* tags */
$tm =& ntsEmailTemplateManager::getInstance();
$tags = $tm->getTags( 'common-header-footer' );
?>

<table class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Sender Email'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'emailSentFrom',
			'attr'		=> array(
				'size'	=> 42,
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
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Sender Name'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'emailSentFromName',
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
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Email Test Mode'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'	=> 'emailDebug',
			)
		);
?>
	<br>
	<i><?php echo M('If set, email messages will be printed on screen rather than sent'); ?></i>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Email'); ?>: <?php echo M('Disable'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'	=> 'emailDisabled',
			)
		);
?>
	</td>
</tr>
</table>

<p>
<table class="ntsForm">
<tr>
	<th><?php echo M('Header For All Emails'); ?></th>
	<th><?php echo M('Tags'); ?></th>
</tr>
<tr>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'emailCommonHeader',
			'attr'		=> array(
				'cols'	=> 48,
				'rows'	=> 3,
				),
			'required'	=> 1,
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
	<td rowspan="3" style="vertical-align: top;">
		<?php foreach( $tags as $t ) : ?>
			<?php echo $t; ?><br>
		<?php endforeach; ?>
	</td>
</tr>

<tr>
	<th><?php echo M('Footer For All Emails'); ?> *</th>
</tr>
<tr>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'emailCommonFooter',
			'attr'		=> array(
				'cols'	=> 48,
				'rows'	=> 3,
				),
			'required'	=> 1,
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
</tr>
</table>

<h3><?php echo M('SMTP Settings'); ?></h3>
<i><?php echo M('Fill in if required by your web hosting. You may need to consult your web hosting administrator or help documentation.'); ?></i>

<table class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Host'); ?></td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'smtpHost',
			'attr'		=> array(
				'size'	=> 42,
				),
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Username'); ?></td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'smtpUser',
			'attr'		=> array(
				'size'	=> 42,
				),
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
</tr>
<tr>
	<td class="ntsFormLabel"><?php echo M('Password'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'smtpPass',
			'attr'		=> array(
				'size'	=> 42,
				),
			),
	/* validators */
		array(
			)
		);
?>
	</td>
</tr>
<tr>
	<td class="ntsFormLabel"><?php echo M('Secure'); ?></td>
	<td class="ntsFormValue">
<?php
	$secureOptions = array(
		array( '', M('None') ),
		array( 'tls', 'TLS' ),
		array( 'ssl', 'SSL' ),
		);
	
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'smtpSecure',
			'options'	=> $secureOptions,
			),
	/* validators */
		array(
			)
		);
?>
	</td>
</tr>
</TABLE>

<p>
<DIV CLASS="buttonBar">
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</DIV>