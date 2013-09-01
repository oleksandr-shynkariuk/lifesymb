<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$serviceId = $object->getProp( 'service_id' );

$class = 'appointment';
$otherDetails = array(
	'service_id'	=> $serviceId,
	);
$om =& objectMapper::getInstance();
$fields = $om->getFields( $class, 'internal', $otherDetails );
reset( $fields );
?>
<?php if( $fields ) : ?>
<table class="ntsForm">
<?php foreach( $fields as $f ) : ?>
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
	</td>
</tr>
<?php endforeach; ?>

<tr>
<td>&nbsp;</td>
<td>
<?php if( ! $this->readonly ) : ?>
	<?php echo $this->makePostParams('-current-', 'update' ); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
<?php endif; ?>
</td>
</tr>
</table>
<?php endif; ?>
