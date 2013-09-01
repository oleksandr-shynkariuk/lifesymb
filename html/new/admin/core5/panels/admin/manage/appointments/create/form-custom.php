<?php
$class = 'appointment';
reset( $customFields );
if( ! $customFields )
	return;
?>

<?php foreach( $customFields as $f ) : ?>
<?php $c = $om->getControl( $class, $f[0], false ); ?>
<tr>
<td class="ntsFormLabel"><?php echo $c[0]; ?></td>

<?php if( $reschedule ) : ?>
<td class="ntsFormValue">
<?php
	$objView = $reschedule->getProp( $f[0] );
	if( $f[2] == 'checkbox' ){
		$objView = $objView ? M('Yes') : M('No');
		}
?>
<?php echo $objView; ?>
</td>
<?php endif; ?>

<td class="ntsFormValue">
<ul class="nts-hori-list"">
	<li>
	<?php
	echo $this->makeInput (
		$c[1],
		$c[2],
		$c[3]
		);
	?>
	</li>
</ul>
</td>
</tr>
<?php endforeach; ?>