<?php
$reschedule = ntsLib::getVar( 'admin/manage/appointments/create::reschedule' );
$noCustomer = ntsLib::getVar( 'admin/manage/appointments/create::noCustomer' );

$viewTitle = '';
if (($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax') && (! ($reschedule)) ){
	$viewTitle = M('Create Appointment');
	}

$lid = $NTS_VIEW['form']->getValue( 'location_id' );
$rid = $NTS_VIEW['form']->getValue( 'resource_id' );
$sid = $NTS_VIEW['form']->getValue( 'service_id' );
$time = $NTS_VIEW['form']->getValue( 'starts_at' );
$cid = $NTS_VIEW['form']->getValue( 'customer_id' );
$fixCustomer = ntsLib::getVar( 'admin/manage/appointments/create::fixCustomer' );

$displayCustomer = ( $lid && $rid && $sid && $time && (! $fixCustomer) );	
?>
<?php if( $viewTitle ) : ?>
<h2><?php echo $viewTitle; ?></h2>
<?php endif; ?>

<?php
$NTS_VIEW['form']->display();
?>

<?php if( $displayCustomer && (! $cid) ) : ?>
<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Customer'); ?></td>
<td class="nts-ajax-parent ntsFormValue nts-ajax-return nts-ajax-container" id="<?php echo $NTS_VIEW['form']->formId; ?>_customers">
<script language="JavaScript">
var targetDiv = jQuery('#<?php echo $NTS_VIEW['form']->formId; ?>_customers');
var targetUrl = "<?php echo ntsLink::makeLink('-current-/customer', '', array('skip' => $noCustomer, NTS_PARAM_VIEW_MODE => 'ajax')); ?>";
targetDiv.show();
targetDiv.html( 'loading' );
targetDiv.data( 'targetUrl', targetUrl );
targetDiv.load( targetUrl, function(){
	var offset = targetDiv.position();
	jQuery(document).scrollTop( offset.top - 30 );
	});
</script>
</td>
</tr>
</table>
<?php endif; ?>