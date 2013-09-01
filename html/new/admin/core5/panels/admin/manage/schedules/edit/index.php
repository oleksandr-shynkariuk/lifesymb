<?php
$groupId = ntsLib::getVar( 'admin/manage/schedules/edit::groupId' );
$deleteLink = ntsLink::makeLink( 
	'-current-/delete',
	'',
	array(
		'gid' => $groupId
		)
	);
?>
<table class="ntsForm">
<tbody class="nts-ajax-parent">
<tr>
<td style="text-align: right;"><a class="alert nts-ajax-loader" href="<?php echo $deleteLink; ?>" title="<?php echo M('Delete'); ?>"><?php echo M('Delete'); ?>?</a></td>
<td class="nts-ajax-container nts-child">
</td>
</tr>
</tbody>
</table>

<?php
$NTS_VIEW['form']->display();
?>