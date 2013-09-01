<tbody>
<tr>
<td class="ntsFormLabel"><?php echo M('Customer'); ?></td>
<td class="ntsFormValue" id="<?php echo $this->formId; ?>_customers">
<?php
	$obj = new ntsUser();
	$obj->setId( $cid );
	$objView = ntsView::objectTitle( $obj );
?>
<ul class="nts-hori-list">
<li class="nts-hori-list-item selected" title="<?php echo $objView; ?>">
<?php 	echo $objView; ?> 
<a class="ntsDeleteControl2" href="<?php echo ntsLink::makeLink('-current-', '', array('customer_id' => '-reset-') ); ?>">[x]</a>
</li>
</ul>

</td>
</tr>
</tbody>