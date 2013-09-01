<?php
$class = 'appointment';
$otherDetails = array(
	'service_id'	=> $this->getValue('service_id'),
	);
$om =& objectMapper::getInstance();
$fields = $om->getFields( $class, 'external', $otherDetails );
reset( $fields );

$updateBtn = ( $fields ) ? true : false;
if( isset($NTS_VIEW['disableUpdate']) && $NTS_VIEW['disableUpdate'] )
	$updateBtn = false;
?>
<?php if( $fields ) : ?>

<?php foreach( $fields as $f ) : ?>
<?php $c = $om->getControl( $class, $f[0], false ); ?>
<tr>
	<th><?php echo $c[0]; ?></th>
	<td>
	<?php
	$fieldType = $c[1];
	if( isset($f[4]) ){
		if( $f[4] == 'read' || ( isset($NTS_VIEW['disableUpdate']) && $NTS_VIEW['disableUpdate'] ) ){
			$c[2]['readonly'] = 1;
			$c[1] = 'label';
			$updateBtn = false;
			}
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
<?php endforeach; ?>

<?php if( $updateBtn ) : ?>
	<tr>
	<th>&nbsp;</th>
	<td>
		<?php echo $this->makePostParams('-current-', 'update', array('id' => $this->getValue('id') ) ); ?>
		<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
	</td>
	</tr>
<?php endif; ?>

<?php endif; ?>
