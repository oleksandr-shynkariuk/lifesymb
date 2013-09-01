<?php
$id = $this->getValue('id');
/* form params - used later for validation */
$this->setParams(
	array(
		'myId'	=> $id,
		)
	);

$object = $this->getValue('object');
$className = 'provider';

$om =& objectMapper::getInstance();
if( $className == 'customer' || $className == 'user' ){
	if( $object->hasRole('admin') )
		$side = 'internal';
	else
		$side = 'external';
	}
else {
	$side = 'internal';
	}

$fields = $om->getFields( $className, $side );
reset( $fields );
//_print_r( $fields );
$roles = $object->getProp( '_role' );

/* status */
list( $alert, $cssClass, $message ) = $object->getStatus();
$class = $alert ? 'alert' : 'ok';

$rolesNames = array(
	'admin'		=> M('Admin'),
	'customer'	=> M('Customer'),
	);
?>
<table class="ntsForm">
<tr>
<td class="ntsFormValue">
ID: <?php echo $object->getId(); ?>
</td>
<td class="ntsFormValue">
<b class="<?php echo $class; ?>"><?php echo $message; ?></b>
</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Role'); ?></td>
	<td class="ntsFormValue">
		<?php
		reset( $roles );
		$myRoles = array();
		foreach( $roles as $r )
			$myRoles[] = $rolesNames[$r];
		$myRolesView = join( '; ', $myRoles );
		?>
		<b><?php echo $myRolesView; ?></b>
	</td>
</tr>

<?php foreach( $fields as $f ) : ?>
<?php $c = $om->getControl( $className, $f[0], false ); ?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td class="ntsFormValue">
	<?php
	$fieldType = $c[1];
	if( isset($f[4]) ){
		if( $f[4] == 'read' ){
			$c[1] = 'label';
			$c[2]['readonly'] = 1;
			}
		}
	if( defined('NTS_REMOTE_INTEGRATION') && (NTS_REMOTE_INTEGRATION == 'wordpress') && ($c[2]['id'] == 'username') ){
		$c[1] = 'labelData';
		}
	echo $this->makeInput (
		$c[1],
		$c[2],
		$c[3]
		);
	?>
	</td>
</tr>

<?php if( NTS_ALLOW_NO_EMAIL && ($className == 'customer') && ($c[2]['id'] == 'email') ) : ?>
<tr>
	<th>&nbsp;</th>
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

<?php if( NTS_ENABLE_TIMEZONES > 0 ) : ?>
	<?php if( $className == 'customer' ) : ?>
	<tr>
		<th><?php echo M('My Timezone'); ?></th>
		<td>
		<?php
		$timezoneOptions = ntsTime::getTimezones();

		echo $this->makeInput (
		/* type */
			'select',
		/* attributes */
			array(
				'id'		=> '_timezone',
				'options'	=> $timezoneOptions,
				)
			);
		?>
		</td>
	</tr>
	<?php endif; ?>
<?php endif; ?>

<tr>
<td></td>
<td>
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</td>
</tr>

</table>