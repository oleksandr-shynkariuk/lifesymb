<?php
$class = 'appointment';
reset( $customFields );
if( ! $customFields )
	return;
$needServiceHeader = (count(array_keys($customFields)) > 1) ? true : false;
?>

<?php foreach( $customFields as $thisServiceId => $thisFields ) : ?>
<?php if( $needServiceHeader ) : ?>
<?php
$thisService = ntsObjectFactory::get( 'service' );
$thisService->setId( $thisServiceId );
?>
<tr>
<td class="ntsFormValue">&nbsp;</td>
<td class="ntsFormValue" style="font-weight: bold;">
<?php echo ntsView::objectTitle($thisService); ?>
</td>
</tr>
<?php endif; ?>

<?php 	foreach( $thisFields as $f ) : ?>
<?php $c = $om->getControl( $class, $f[0], false ); ?>
<tr>
<td class="ntsFormLabel"><?php echo $c[0]; ?></td>

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
<?php 	endforeach; ?>
<?php endforeach; ?>