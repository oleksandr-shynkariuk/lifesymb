<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );

$className = 'customer';

$om =& objectMapper::getInstance();
$fields = $om->getFields( $className, 'internal' );
reset( $fields );

/* status */
list( $alert, $cssClass, $message ) = $object->getStatus();
$class = $alert ? 'alert' : 'ok';
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

<?php foreach( $fields as $f ) : ?>
<?php $c = $om->getControl( 'customer', $f[0], false ); ?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td class="ntsFormValue">
<?php
	$value = $object->getProp($c[2]['id']);
	if( $c[1] == 'checkbox' )
		$value = $value ? M('Yes') : M('No');
	echo $value;
?>
	<?php if( NTS_ALLOW_DUPLICATE_EMAILS && ($c[2]['id'] == 'email') ) : ?>
<?php // 	check if there're duplicates
			$checkEmail = $object->getProp('email');
			$countDuplicates = 0;
			if( strlen($checkEmail) ){
				$myWhere = array();
				$myWhere['email'] = array('=', $checkEmail);
				$myWhere['id'] = array('<>', $object->getId());
				$countDuplicates = $integrator->countUsers( $myWhere );
				}
?>
		<?php if( $countDuplicates ) : ?>
			<br>Also <a target="_blank" href="<?php echo ntsLink::makeLink('admin/customers/browse', 'search', array('search' => $checkEmail) ); ?>"><?php echo $countDuplicates; ?> other user(s)</a> with this email
		<?php endif; ?>
	<?php endif; ?>
	</td>
</tr>
<?php endforeach; ?>

<?php if( NTS_ENABLE_TIMEZONES > 0 ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Timezone'); ?></td>
	<td class="ntsFormValue">
	<?php
	$value = $object->getProp( '_timezone' );
	echo $value;
	?>
	</td>
</tr>
<?php endif; ?>
</table>