<?php
$class = 'customer';
$om =& objectMapper::getInstance();
$fields = $om->getFields( $class, 'internal' );
reset( $fields );
?>
<table class="ntsForm">
<tbody>
<?php foreach( $fields as $f ) : ?>
<?php 
if( ! NTS_ENABLE_REGISTRATION && ($f[0] == 'username') )
	continue;
?>
<?php $c = $om->getControl( $class, $f[0], false ); ?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
		$c[1],
		$c[2],
		$c[3]
		);
?>

<?php if( NTS_ALLOW_NO_EMAIL && ($c[2]['id'] == 'email') && ($class == 'customer') ) : ?>
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

<?php endif; ?>
	</td>
</tr>
<?php endforeach; ?>

<tr>
	<td class="ntsFormLabel"><?php echo M('Timezone'); ?></td>
	<td class="ntsFormValue">
	<?php
	$timezoneOptions = ntsTime::getTimezones();
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> '_timezone',
			'options'	=> $timezoneOptions,
			'default'	=> NTS_COMPANY_TIMEZONE,
			)
		);
	?>
	</td>
</tr>

<?php if( ! NTS_ENABLE_REGISTRATION ) : ?>
<tr>
	<td>&nbsp;</td>
	<td>
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'login-details',
			'default'	=> 0,
			)
		);
?>
<?php echo M('Login Details'); ?>

	</td>
</tr>
<?php endif; ?>

</tbody>

<tbody id="<?php echo $this->getName(); ?>login-wrapper">
<?php if( ! NTS_ENABLE_REGISTRATION ) : ?>
<?php $c = $om->getControl( $class, 'username', false ); ?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
		$c[1],
		$c[2],
		$c[3]
		);
?>
</tr>
<?php endif; ?>

<tr>
	<td class="help" colspan="2"><?php echo M('Leave these blank to autogenerate a random password'); ?></td>
</tr>
<tr>
	<td class="ntsFormLabel"><?php echo M('Password'); ?> *</td>
	<td class="ntsFormValue">
<?php
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
		array(
			)
		);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Confirm Password'); ?> *</td>
	<td class="ntsFormValue">
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
				'error'		=> "Passwords don't match!",
				'params'	=> array(
					'mainPasswordField' => 'password',
					),
				),
			)
		);
	?>
	</td>
</tr>
<tr>
	<td></td>
	<td>
<div id="<?php echo $this->getName(); ?>notify-wrapper">
<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'notify',
			'default'	=> NTS_ENABLE_REGISTRATION ? 1 : 0,
			)
		);
?>
<?php echo M('Notify Customer On Account Creation'); ?>
</div>
	</td>
</tr>
</tbody>

<tbody>
<tr>
	<td></td>
	<td>
<?php 
$params = array();
$params[NTS_PARAM_VIEW_MODE] = $NTS_VIEW[NTS_PARAM_VIEW_MODE];
echo $this->makePostParams('-current-', 'create-customer', $params);
?>
<INPUT TYPE="submit" VALUE="<?php echo M('Customer'); ?>: <?php echo M('Create'); ?>">
	</td>
</tr>
</tbody>
</table>

<?php if( NTS_ALLOW_NO_EMAIL && ($class == 'customer') ) : ?>
<script language="JavaScript">
jQuery(document).ready( function(){
	if( jQuery("#<?php echo $this->getName(); ?>noEmail").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>email").hide();
		jQuery("#<?php echo $this->getName(); ?>notify-wrapper").hide();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>email").show();
		jQuery("#<?php echo $this->getName(); ?>notify-wrapper").show();
		}
	});
jQuery("#<?php echo $this->getName(); ?>noEmail").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>email").toggle();
	jQuery("#<?php echo $this->getName(); ?>notify-wrapper").toggle();
	});
</script>
<?php endif; ?>

<?php if( ! NTS_ENABLE_REGISTRATION ) : ?>
<script language="JavaScript">
jQuery(document).ready( function(){
	if( jQuery("#<?php echo $this->getName(); ?>login-details").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>login-wrapper").show();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>login-wrapper").hide();
		}
	});
jQuery("#<?php echo $this->getName(); ?>login-details").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>login-wrapper").toggle();
	});
</script>
<?php endif; ?>
