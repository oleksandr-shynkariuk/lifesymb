<?php
$conf =& ntsConf::getInstance();
$useCaptcha = $conf->get( 'useCaptcha' );
$strongPassword = $conf->get( 'strongPassword' );

$om =& objectMapper::getInstance();
$fields = $om->getFields( 'customer', 'external' );
reset( $fields );
?>
<table>
<?php foreach( $fields as $f ) : ?>
<?php
if( $f[0] == 'username' )
	continue;
?>
<?php $c = $om->getControl( 'customer', $f[0], false ); ?>
<?php
if( isset($f[4]) ){
	if( $f[4] == 'read' ){
		$c[2]['readonly'] = 1;
		}
	}
?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td>
	<?php
	// skip email check if no registration
	if(! NTS_ENABLE_REGISTRATION ){
		if( $f[0] == 'email' ){
			/* traverse validators */
			reset( $c[3] );
			$copyVali = $c[3];
			$c[3] = array();
			foreach( $copyVali as $vali ){
				if( preg_match('/checkUserEmail\.php$/', $vali['code']) ){
					continue;
					}
				$c[3][] = $vali;
				}
			}
		}

	echo $this->makeInput (
		$c[1],
		$c[2],
		$c[3]
		);
	?>
<?php if( $c[2]['description'] ) : ?>
&nbsp;<i><?php echo $c[2]['description']; ?></i>
<?php endif; ?>
	</td>
</tr>

<?php if( NTS_ALLOW_NO_EMAIL && ($c[2]['id'] == 'email') ) : ?>
<tr>
	<td>&nbsp;</td>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'	=> 'noEmail',
			)
		);
	?><?php echo M('No Email?'); ?>
	</td>
</tr>
<?php endif; ?>

<?php endforeach; ?>

<?php if( NTS_ENABLE_REGISTRATION ) : ?>
	<tr>
		<td colspan="2">
		<b><?php echo M('Login details'); ?></b>
		</td>
	</tr>

<?php if( ! NTS_EMAIL_AS_USERNAME ) : ?>
	<tr>
		<td class="ntsFormLabel"><?php echo M('Desired Username'); ?> *</td>
		<td>
		<?php
		echo $this->makeInput (
		/* type */
			'text',
		/* attributes */
			array(
				'id'		=> 'username',
				'attr'		=> array(
					'size'	=> 16,
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
				array(
					'code'		=> 'checkUsername.php', 
					'error'		=> M('Already in use'),
					'params'	=> array(
						'skipMe'	=> 1,
						)
					),
				)
			);
		?>
		</td>
	</tr>
<?php endif; ?>
	<tr>
		<td class="ntsFormLabel"><?php echo M('Password'); ?> *</td>
		<td>
<?php
		$passwordValidate = array();
		$passwordValidate[] = array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			);
		if( $strongPassword ){
			$passwordValidate[] = array(
				'code'		=> 'strongPassword.php', 
				);
			}
		echo $this->makeInput (
		/* type */
			'password',
		/* attributes */
			array(
				'id'		=> 'password',
				'attr'		=> array(
					'size'	=> 16,
					),
				'default'	=> '',
				'required'	=> 1,
				),
		/* validators */
			$passwordValidate
			);
?>
		</td>
	</tr>

	<tr>
		<td class="ntsFormLabel"><?php echo M('Confirm Password'); ?> *</td>
		<td>
		<?php
		echo $this->makeInput (
		/* type */
			'password',
		/* attributes */
			array(
				'id'		=> 'password2',
				'attr'		=> array(
					'size'	=> 16,
					),
				'default'	=> '',
				'required'	=> 1,
				),
		/* validators */
			array(
				array(
					'code'		=> 'confirmPassword.php', 
					'error'		=> M("Passwords don't match!"),
					'params'	=> array(
						'mainPasswordField' => 'password',
						),
					),
				)
			);
		?>
		</td>
	</tr>
<?php endif; ?>

<?php if( $useCaptcha ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Enter Code Shown'); ?></td>
<td>
<?php
	echo $this->makeInput (
	/* type */
		'captcha',
	/* attributes */
		array(
			'id'	=> 'captcha',
			'attr'	=> array(
				'size'	=> 6
				)
			)
		);
?>
</td>
</tr>
<?php endif; ?>

<tr>
	<td>&nbsp;</td>
	<td>
<?php
$btnTitle = NTS_ENABLE_REGISTRATION ? M('Register') . ' &amp; ' . M('Confirm Appointments') : M('Confirm Appointments');
?>
<INPUT TYPE="submit" NAME="nts-register" VALUE="<?php echo $btnTitle; ?>">
	</td>
</table>