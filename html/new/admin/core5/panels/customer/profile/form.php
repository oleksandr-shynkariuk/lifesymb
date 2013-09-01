<?php
$id = $this->getValue('id');
/* form params - used later for validation */
$this->setParams(
	array(
		'myId'	=> $id,
		)
	);

$object = $this->getValue('object');

if( $object->hasRole('customer') )
	$className = 'customer';
elseif( $object->hasRole('provider') )
	$className = 'provider';
else
	$className = 'user';

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
/* status */
$restrictions = $object->getProp( '_restriction' );
?>
<table>
<tr>
	<th><?php echo M('User Status'); ?></th>
	<td>
	<?php if( $restrictions ) : ?>
		<span class="alert">
		<?php if( in_array('email_not_confirmed', $restrictions) ) : ?>
			<?php echo M('Email Not Confirmed'); ?>
		<?php elseif( in_array('not_approved', $restrictions) ) : ?>
			<?php echo M('Not Approved'); ?>
		<?php elseif( in_array('suspended', $restrictions) ) : ?>
			<?php echo M('Suspended'); ?>
		<?php endif; ?>
		</span>
	<?php else : ?>
		<span class="ok">
		<?php echo M('Active'); ?>
		</span>
	<?php endif; ?>
	</td>
</tr>

<?php foreach( $fields as $f ) : ?>
<?php $c = $om->getControl( $className, $f[0], false ); ?>
<tr>
	<th><?php echo $c[0]; ?></th>
	<td>
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
<?php if( $c[2]['description'] ) : ?>
&nbsp;<i><?php echo $c[2]['description']; ?></i></td>
<?php endif; ?>
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
</table>

<DIV CLASS="buttonBar">
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</DIV>