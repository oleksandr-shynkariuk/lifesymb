<?php
$entries = ntsLib::getVar( 'admin/manage/appointments/edit/notes::entries' );
$totalCols = 3;

$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$resourceId = $object->getProp('resource_id');
$iCanEdit = in_array($resourceId, $appEdit) ? true : false;
?>
<table class="nts-listing">

<?php if( $iCanEdit ) : ?>
<tbody class="nts-ajax-parent">
<tr>
<td colspan="<?php echo $totalCols; ?>">
<?php
echo ntsLink::printLink(
	array(
		'panel'		=> '-current-/create',
		'title'		=> '[+] ' . M('Note') . ': ' . M('Add'),
		'attr'		=> array(
			'class'	=> 'nts-ajax-loader nts-ok nts-button2',
			),
		)
	);
?>
</td>
</tr>
<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child"></td>
</tr>
</tbody>
<?php endif; ?>

<?php for( $ii = 0; $ii < count($entries); $ii++ ) : ?>
<?php 	
$e = $entries[$ii];
$editLink = ntsLink::makeLink( '-current-/edit/edit', '', array('noteid' => $e['id']) );
$deleteLink = ntsLink::makeLink( '-current-/edit/delete', '', array('noteid' => $e['id']) );

list( $time, $adminId ) = explode( ':', $e['meta_data'] );

$NTS_VIEW['t']->setTimestamp( $time );
$timeView =  $NTS_VIEW['t']->formatFull();

$admin = new ntsUser;
$admin->setId( $adminId );
$adminView = ntsView::objectTitle( $admin );
?>
<tbody class="nts-ajax-parent">
<tr class="<?php echo ($ii % 2) ? 'even' : 'odd'; ?>">

<?php if( $iCanEdit ) : ?>
<td style="width: 1em;">
<a class="alert nts-ajax-loader nts-bold" href="<?php echo $deleteLink; ?>" title="<?php echo M('Delete'); ?>">[x]</a>
</td>
<?php endif; ?>

<td>
<?php if( $iCanEdit ) : ?>
	<a class="nts-ajax-loader nts-bold" href="<?php echo $editLink; ?>"><?php echo $e['meta_value']; ?></a>
<?php else : ?>
	<?php echo $e['meta_value']; ?>
<?php endif; ?>
</td>

<td style="width: 20em;">
<?php echo $timeView; ?><br>
<?php echo $adminView; ?>
</td>

</tr>

<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child"></td>
</tr>
</tbody>

<?php endfor; ?>

</table>