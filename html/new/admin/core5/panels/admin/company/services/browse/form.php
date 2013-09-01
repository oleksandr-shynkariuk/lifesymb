<?php
$entries = ntsLib::getVar( 'admin/company/services::entries' );
$totalCols = 5;
?>
<table class="nts-listing">

<?php if( count($entries) > 1 ) : ?>
<tbody>
<tr>
<th><?php echo M('Title'); ?></th>
<th><?php echo M('Duration'); ?></th>
<th><?php echo M('Type'); ?></th>
<th><?php echo M('Base Price'); ?></th>

<?php if( count($entries) > 3 ) : ?>
<th><?php echo M('Show Order'); ?><br><span style="font-size: 0.8em; font-weight: normal;"><?php echo M('Smaller Goes First'); ?></span></th>
<?php else : ?>
<th>&nbsp;</th>
<?php endif; ?>

</tr>
</tbody>
<?php endif; ?>

<?php for( $ii = 0; $ii < count($entries); $ii++ ) : ?>
<?php 	
		$e = $entries[$ii];
?>
<tbody class="nts-ajax-parent">
<tr class="<?php echo ($ii % 2) ? 'even' : 'odd'; ?>">
<td>
<?php
echo ntsLink::printLink(
	array(
		'panel'		=> '-current-/../edit',
		'params'	=> array('_id' => $e->getId()),
		'title'		=> ntsView::objectTitle($e),
		'attr'		=> array(
			'class'	=> 'nts-bold',
			)
		),
	true
	);
?>
</td>

<td>
<?php echo ntsTime::formatPeriod($e->getProp('duration')); ?>
</td>

<td>
<?php 
$type = $e->getType();
switch( $type ){
	case 'class':
		$typeView = M('Class');
		break;
	case 'appointment':
		$typeView = M('Appointment');
		break;
	}
$packOnly = $e->getProp('pack_only');
?>
<?php echo $typeView; ?>
<?php if( $packOnly ) : ?>
<br><?php echo M('Package Only'); ?>
<?php endif; ?>
</td>

<td>
<?php echo ntsCurrency::formatServicePrice($e->getProp('price')); ?>
</td>

<td>
<?php if( count($entries) > 3 ) : ?>

<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'order_' . $e->getId(),
			'attr'		=> array(
				'size'	=> 2,
				),
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			array(
				'code'		=> 'integer.php', 
				'error'		=> M('Numbers only'),
				),
			)
		);
?>

<?php elseif( count($entries) > 1 ) : ?>

<?php
echo ntsLink::printLink(
	array(
		'panel'		=> '-current-/../edit/edit',
		'action'	=> 'up',
		'params'	=> array('_id' => $e->getId()),
		'title'		=> M('Up'),
		'attr'		=> array(
			'class'	=> 'ok',
			),
		)
	);
?>

<?php
echo ntsLink::printLink(
	array(
		'panel'		=> '-current-/../edit/edit',
		'action'	=> 'down',
		'params'	=> array('_id' => $e->getId()),
		'title'		=> M('Down'),
		'attr'		=> array(
			'class'	=> 'ok',
			),
		)
	);
?>
<?php endif; ?>
</td>
</tr>

<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child"></td>
</tr>
</tbody>

<?php endfor; ?>

<?php if( count($entries) > 3 ) : ?>
<tr>
<td colspan="<?php echo ($totalCols - 1); ?>"></td>
<td>
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
</td>
</tr>
<?php endif; ?>

</table>