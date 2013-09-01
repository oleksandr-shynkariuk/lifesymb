<?php
$appView = ntsLib::getVar( 'admin/manage:appView' );

$fieldOptions = array();
$fieldOptions[] = array( 'location',	M('Location') );
if( count($appView) > 1 )
	$fieldOptions[] = array( 'resource',	M('Bookable Resource') );
$fieldOptions[] = array( 'service',		M('Service') );

/* custom fields */
$om =& objectMapper::getInstance();
$customFields = $om->getFields( 'appointment', 'internal', array('service_id' => -1) );
reset( $customFields );
foreach( $customFields as $cf ){
	$fieldOptions[] = array( $cf[0], $cf[1] );
	}

$fieldOptions[] = array( 'customer',	M('Customer') . ':' . M('Full Name') );

$customerFields = $om->getFields( 'customer', 'internal' );
$skipCustomerFields = array('first_name', 'last_name');
reset( $customerFields );
foreach( $customerFields as $cf ){
	if( in_array($cf[0], $skipCustomerFields) )
		continue;
	$fieldOptions[] = array( 'customer:' . $cf[0], M('Customer') . ':' . $cf[1] );
	}

$fieldOptions[] = array( 'total_amount',	M('Total Amount') );
$fieldOptions[] = array( 'paid_amount',		M('Paid Amount') );
?>

<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Show Fields'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'checkboxSet',
/* attributes */
	array(
		'id'		=> '_agenda_fields',
		'options'	=> $fieldOptions,
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Please choose at least one option'),
			),
		)
	);
?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<input type="submit" value="<?php echo M('Save'); ?>"></td>
</tr>
</table>