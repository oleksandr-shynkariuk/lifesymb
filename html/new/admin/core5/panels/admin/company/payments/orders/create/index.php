<?php
$fixCustomer = ntsLib::getVar( 'admin/company/payments/orders/create::fixCustomer' );
$displayCustomer = ( ! $fixCustomer );	
$packs = ntsObjectFactory::getAllIds( 'pack' );
?>
<?php if( ! $packs ) : ?>
<a href="<?php echo ntsLink::makeLink('admin/company/services/packs'); ?>"><?php echo M('Create a package first'); ?></a>
<?php else : ?>
<?php
$NTS_VIEW['form']->display();
?>
<?php if( $displayCustomer && (! $cid) ) : ?>
<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Customer'); ?></td>
<td class="nts-ajax-parent ntsFormValue nts-ajax-return nts-ajax-container" id="<?php echo $NTS_VIEW['form']->formId; ?>_customers">
<script language="JavaScript">
jQuery(document).ready( function(){
	var targetDiv = jQuery('#<?php echo $NTS_VIEW['form']->formId; ?>_customers');
	var targetUrl = "<?php echo ntsLink::makeLink('-current-/customer', '', array(NTS_PARAM_VIEW_MODE => 'ajax')); ?>";
	targetDiv.show();
	targetDiv.html( 'loading' );
	targetDiv.data( 'targetUrl', targetUrl );
	targetDiv.load( targetUrl, function(){
		var offset = targetDiv.position();
		jQuery(document).scrollTop( offset.top - 30 );
		});
	});
</script>
</td>
</tr>
</table>
<?php endif; ?>
<?php endif; ?>
