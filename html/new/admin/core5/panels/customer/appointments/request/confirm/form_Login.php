<div id="ntsLoginForm">
<table>
<tr>
<?php if( ! NTS_EMAIL_AS_USERNAME ) : ?>
	<td class="ntsFormLabel"><?php echo M('Username'); ?></td>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'login_username',
			'attr'		=> array(
				'size'	=> 20,
				),
			)
		);
	?>
	</td>
<?php else : ?>
	<td class="ntsFormLabel"><?php echo M('Email'); ?></td>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'login_email',
			'attr'		=> array(
				'size'	=> 28,
				),
			)
		);
	?>
	</td>
<?php endif; ?>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Password'); ?></td>
<td>
<?php
echo $this->makeInput (
/* type */
	'password',
/* attributes */
	array(
		'id'		=> 'login_password',
		'attr'		=> array(
			'size'	=> 20,
			),
		)
	);
?>
</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td>
	<INPUT NAME="nts-login" TYPE="submit" VALUE="<?php echo M('Login'); ?> &amp; <?php echo M('Confirm Appointments'); ?>">
	</td>
</tr>
</table>
</div>

<?php if( defined('NTS_SKIP_COOKIE') && NTS_SKIP_COOKIE ) : ?>
	<input type="hidden" name="nts-skip-cookie" value="1">
<?php else : ?>
	<div id="ntsCookieAlert" style="display: none;">
	<b class="alert">Your browser's cookie functionality is turned off. Please turn it on.</b>
	<b>[<a href="http://www.google.com/support/accounts/bin/answer.py?answer=61416" target="_blank">?</a>]</b>
	</div>
	<script language="JavaScript" type="text/javascript" src="<?php echo ntsLink::makeLink('system/pull', '', array('what' => 'js', 'files' => 'loginCookie.js') ); ?>"></script>
<?php endif; ?>