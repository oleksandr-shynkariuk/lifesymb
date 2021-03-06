<?php if( count($locs) > 1 ) : ?>
<tr>
<td class="ntsFormLabel"><?php echo M('Location'); ?></td>

<?php if( $reschedule ) : ?>
<td class="ntsFormValue">
<?php
	$obj = ntsObjectFactory::get( 'location' );
	$objId = $reschedule->getProp( 'location_id' );
	$obj->setId( $objId );
	$objView = ntsView::objectTitle( $obj );
?>
<?php echo $objView; ?>
</td>
<?php endif; ?>

<td class="ntsFormValue">
<ul class="nts-hori-list"">
<?php if( ! $lid ) : ?>
<?php	foreach( $locs as $objId ) : ?>	
<?php
			$obj = ntsObjectFactory::get( 'location' );
			$obj->setId( $objId );
			$objView = ntsView::objectTitle( $obj );
			$thisRe = $re;
			$thisRe[0] = $objId;
			$thisRe = '/^' . join('\-', $thisRe) . '$/';
			$class = ntsLib::reExistsInArray( $thisRe, $available ) ? 'ntsWorking' : 'ntsNotWorking';
?>
	<li title="<?php echo $objView; ?>" class="nts-hori-list-item <?php echo $class; ?>">
	<a href="<?php echo ntsLink::makeLink('-current-', '', array('location_id' => $objId) ); ?>"><?php echo $objView; ?></a>
	</li>
<?php	endforeach; ?>
<?php else : ?>
<?php
		$obj = ntsObjectFactory::get( 'location' );
		$obj->setId( $lid );
		$objView = ntsView::objectTitle( $obj );
?>
	<li title="<?php echo $objView; ?>" class="nts-hori-list-item selected">
	<?php echo $objView; ?>
	<?php 	if( count($locs) > 1 ) : ?>
		<a class="ntsDeleteControl2" href="<?php echo ntsLink::makeLink('-current-', '', array('location_id' => '-reset-') ); ?>">[x]</a>
	<?php 	endif; ?>
	</li>
<?php endif; ?>
</ul>
</td>
</tr>
<?php endif; ?>