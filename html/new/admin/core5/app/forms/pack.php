<?php
$serviceType = $this->getValue('service_type');
$dateType = $this->getValue('date_type');
$packType = $this->getValue('pack_type');
$orderId = $this->getValue('order_id');

$minStart = NTS_TIME_STARTS;
$maxEnd = NTS_TIME_ENDS;
$t = new ntsTime;
$t->setNow();
$today = $t->formatDate_Db();

$serviceOptions = array();
$ntsdb =& dbWrapper::getInstance();
$allServices = ntsObjectFactory::getAll( 'service', 'price > 0' );
reset( $allServices );
foreach( $allServices as $s ){
	$servicePrice = $s->getProp('price');
	if( strlen($servicePrice) && ($servicePrice > 0) ){
		$serviceView = $s->getProp('title');
		$serviceView .= ' [' . ntsTime::formatPeriod($s->getProp('duration')) . ']';
		$serviceView .= ' - ' . ntsCurrency::formatPrice($s->getProp('price')) . '';
		$serviceOptions[] = array( $s->getId(), $serviceView );
		}
	}
array_unshift( $serviceOptions, array(0, ' - ' . M('Any') . ' - ') );
array_unshift( $serviceOptions, array(-1, ' - ' . M('Select') . ' - ') );

$resourceOptions = array();
$ntsdb =& dbWrapper::getInstance();
$allResources = ntsObjectFactory::getAll( 'resource' );
reset( $allResources );
foreach( $allResources as $r ){
	$resourceView = ntsView::objectTitle( $r );
	$resourceOptions[] = array( $r->getId(), $resourceView );
	}
array_unshift( $resourceOptions, array(0, ' - ' . M('Any') . ' - ') );
?>
<table class="ntsForm">
<tbody>

<?php if( ! $orderId ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Title'); ?> *</td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'title',
			'attr'		=> array(
				'size'	=> 32,
				),
			'required'	=> 1,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			array(
				'code'		=> 'checkUniqueProperty.php', 
				'error'		=> M('Already in use'),
				'params'	=> array(
					'prop'	=> 'title',
					'class'	=> 'pack',
					'skipMe'	=> 1
					),
				),
			)
		);
?>
</tr>
<?php endif; ?>

<tr>
	<td class="ntsFormLabel"><?php echo M('Services In Package'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'service_type',
			'value'		=> 'one',
			'default'	=> $serviceType
			)
		);
?> <?php echo M('Any From List'); ?>

<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'service_type',
			'value'		=> 'fixed',
			'default'	=> $serviceType
			)
		);
?> <?php echo M('Fixed'); ?>

	</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_fixed_services">
<tr>
	<td class="ntsFormLabel"><?php echo M('Services'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'fixedServices',
	/* attributes */
		array(
			'id'		=> 'fixed_service_id',
			'options'	=> $serviceOptions
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			)
		);
?>
	</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_service">
<tr>
	<td class="ntsFormLabel"><?php echo M('Services'); ?></td>
	<td class="ntsFormValue">

<?php
echo $this->makeInput (
/* type */
	'checkbox',
/* attributes */
	array(
		'id'		=> 'service_id_all',
		'box_value'	=> 1,
		'htmlId'	=> 'nts-toggle-all-services'
		)
	);
?><?php echo ' - ' . M('Any') . ' - '; ?>
<?php
$oneServiceOptions = $serviceOptions;
// remove Select
array_shift( $oneServiceOptions );
array_shift( $oneServiceOptions );

echo $this->makeInput (
/* type */
	'checkboxSet',
/* attributes */
	array(
		'id'		=> 'service_id',
		'options'	=> $oneServiceOptions,
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
	<td class="ntsFormLabel"><?php echo M('Package Type'); ?></td>
	<td class="ntsFormValue">
<?php
	$slotType = 'qty';
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'pack_type',
			'value'		=> 'qty',
			'default'	=> $packType
			)
		);
?> <?php echo M('Number of appointments'); ?>
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'pack_type',
			'value'		=> 'duration',
			'default'	=> $slotType
			)
		);
?> <?php echo M('Duration'); ?>
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'pack_type',
			'value'		=> 'amount',
			'default'	=> $slotType
			)
		);
?> <?php echo M('Amount'); ?>
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'pack_type',
			'value'		=> 'unlimited',
			'default'	=> $slotType
			)
		);
?> <?php echo M('Unlimited'); ?>

</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_details_qty">
<tr>
	<td class="ntsFormLabel"><?php echo M('Number of appointments'); ?> *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'qty',
			'attr'		=> array(
				'size'	=> 4,
				),
			'required'	=> 1,
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
	</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_details_duration">
<tr>
	<td class="ntsFormLabel"><?php echo M('Duration'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'period/MinHour',
	/* attributes */
		array(
			'id'		=> 'duration',
			'default'	=> 2 * 60 * 60,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			)
		);
?>
	</td>
</tr>
</tbody>

<tbody id="<?php echo $this->formId; ?>_details_amount">
<tr>
	<td class="ntsFormLabel"><?php echo M('Total Amount'); ?> *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'amount',
			'attr'		=> array(
				'size'	=> 8,
				),
			'required'	=> 1,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			array(
				'code'		=> 'number.php', 
				'error'		=> M('Numbers only'),
				),
			array(
				'code'		=> 'greaterThan.php', 
				'error'		=> M('Required field'),
				'params'	=> array(
					'compareWith'	=> 0,
					)
				),
			)
		);
	?>
	</td>
</tr>

</tbody>

<tbody>
<?php if( ! $orderId ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Selling Price'); ?> *</td>
	<td class="ntsFormValue">
<span id="<?php echo $this->getName(); ?>price-wrapper">
<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'		=> 'price',
		'attr'		=> array(
			'size'	=> 8,
			),
		'required'	=> 1,
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'number.php', 
			'error'		=> M('Numbers only'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> M('Required field'),
			'params'	=> array(
				'compareWith'	=> 0,
				)
			),
		)
	);
?>
</span>
<?php
	$default = $this->getValue('price') ? 0 : 1;
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'notForSale',
			'default'	=> $default,
			)
		);
?> <?php echo M('Not For Sale'); ?>
	</td>
</tr>
<?php endif; ?>

<tr>
	<td class="ntsFormLabel"><?php echo M('Bookable Resource'); ?></td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'resource_id',
			'options'	=> $resourceOptions
			),
	/* validators */
		array(
			)
		);
	?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Expires in'); ?> *</td>
	<td class="ntsFormValue">
<span id="<?php echo $this->getName(); ?>expires-wrapper">
<?php
	echo $this->makeInput (
	/* type */
		'period/DayWeekMonthYear',
	/* attributes */
		array(
			'id'		=> 'expires_in',
			),
	/* validators */
		array(
			array(
				'code'	=> 'notEmpty.php', 
				'error'	=> M('Required field'),
				),
			)
		);
?>
</span>
<?php
	$default = $this->getValue('expires_in') ? 0 : 1;
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'		=> 'neverExpires',
			'default'	=> $default,
			)
		);
?> <?php echo M('Never Expires'); ?>
	</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Dates'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'checkbox',
/* attributes */
	array(
		'id'		=> 'date_all',
		'default'	=> 1,
		)
	);
?><?php echo ' - ' . M('All') . ' - '; ?>

<div id="<?php echo $this->formId; ?>date_container">

<div>
<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'date_type',
			'value'		=> 'range',
			'default'	=> $dateType
			)
		);
?> <?php echo M('Date Range'); ?>

<?php
	echo $this->makeInput (
	/* type */
		'radio',
	/* attributes */
		array(
			'id'		=> 'date_type',
			'value'		=> 'fixed',
			'default'	=> $dateType
			)
		);
?> <?php echo M('Fixed Dates'); ?>
</div>

<div id="<?php echo $this->formId; ?>date_range" style="padding: 0.25em 0.5em; margin: 0.25em 0;">
<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'from_date',
		'default'	=> $today
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
 - 
<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'to_date',
		'default'	=> $today
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> "The end date should be after the start date",
			'params'	=> array(
				'compareWithField' => 'from_date',
				),
			)
		)
	);
?>
</div>

<div id="<?php echo $this->formId; ?>date_fixed" style="padding: 0.25em 0.5em; margin: 0.25em 0;">
<?php
echo $this->makeInput (
/* type */
	'fixedDates',
/* attributes */
	array(
		'id'		=> 'fixed_date',
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
</div>

</div>
</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Weekdays'); ?></td>
	<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'date/Weekday',
	/* attributes */
		array(
			'id'			=> 'weekday',
			'includeAll'	=> TRUE,
			'allValue'		=> -1,
			'default'		=> array(-1),
			),
	/* validators */
		array(
			)
		);
?>
	</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'checkbox',
/* attributes */
	array(
		'id'		=> 'time_all',
		'default'	=> 1,
		)
	);
?><?php echo ' - ' . M('All') . ' - '; ?>

<div id="<?php echo $this->formId; ?>time_container">
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'from_time',
		'conf'	=> array(
			'min'	=> $minStart,
			'max'	=> $maxEnd,
			),
		'default'	=> $minStart
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
 - 
<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'to_time',
		'conf'	=> array(
			'min'	=> $minStart,
			'max'	=> $maxEnd,
			),
		'default'	=> $maxEnd
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> "Slot can't start before end",
			'params'	=> array(
				'compareWithField' => 'from_time',
				),
			)
		)
	);
?>
</div>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'save', array('order_id' => $orderId) ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</td>
</tr>
</tbody>
</table>

<script language="JavaScript">
jQuery(document).ready( function(){
/* time */
	jQuery("#<?php echo $this->formId; ?>time_all").is(":checked") ? jQuery('#<?php echo $this->formId; ?>time_container').hide() : jQuery('#<?php echo $this->formId; ?>time_container').show(); 
	jQuery("#<?php echo $this->formId; ?>date_all").is(":checked") ? jQuery('#<?php echo $this->formId; ?>date_container').hide() : jQuery('#<?php echo $this->formId; ?>date_container').show(); 

	if( jQuery("#<?php echo $this->getName(); ?>neverExpires").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>expires-wrapper").hide();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>expires-wrapper").show();
		}

	if( jQuery("#<?php echo $this->getName(); ?>notForSale").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>price-wrapper").hide();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>price-wrapper").show();
		}

	var date_type = jQuery('#<?php echo $this->formId; ?>date_type:checked').val();
	switch( date_type )
	{
		case 'range':
			jQuery('#<?php echo $this->formId; ?>date_range').show();
			jQuery('#<?php echo $this->formId; ?>date_fixed').hide();
			break;
		case 'fixed':
			jQuery('#<?php echo $this->formId; ?>date_range').hide();
			jQuery('#<?php echo $this->formId; ?>date_fixed').show();
			break;
	}

	jQuery('#<?php echo $this->formId; ?>_service').hide();
	var serviceType = jQuery('#<?php echo $this->formId; ?>service_type:checked').val();
	switch( serviceType ){
		case 'one':
			jQuery('#<?php echo $this->formId; ?>_fixed_services').hide();

			jQuery('#<?php echo $this->formId; ?>_service').show();
			jQuery('#<?php echo $this->formId; ?>_details_qty').hide();
			jQuery('#<?php echo $this->formId; ?>_details_duration').hide();
			jQuery('#<?php echo $this->formId; ?>_details_amount').hide();

			var what2show = jQuery('#<?php echo $this->formId; ?>pack_type:checked').val();
			what2show = '#<?php echo $this->formId; ?>_details_' + what2show;
			jQuery(what2show).show();
			break;
		case 'fixed':
			jQuery('#<?php echo $this->formId; ?>_service').hide();
			jQuery('#<?php echo $this->formId; ?>_details_qty').hide();
			jQuery('#<?php echo $this->formId; ?>_details_duration').hide();
			jQuery('#<?php echo $this->formId; ?>_details_amount').hide();

			jQuery('#<?php echo $this->formId; ?>_fixed_services').show();
			break;
		}

	if( jQuery("#nts-toggle-all-services").is(":checked") ){
		jQuery('#<?php echo $this->formId; ?>service_id_container').hide();
		}
	else {
		jQuery('#<?php echo $this->formId; ?>service_id_container').show();
		}
	});

jQuery('#<?php echo $this->formId; ?>time_all').live("change", function()
{
	this.checked ? jQuery('#<?php echo $this->formId; ?>time_container').hide() : jQuery('#<?php echo $this->formId; ?>time_container').show(); 
});
jQuery('#<?php echo $this->formId; ?>date_all').live("change", function()
{
	this.checked ? jQuery('#<?php echo $this->formId; ?>date_container').hide() : jQuery('#<?php echo $this->formId; ?>date_container').show(); 
});

jQuery("#<?php echo $this->getName(); ?>neverExpires").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>expires-wrapper").toggle();
	});

jQuery("#<?php echo $this->getName(); ?>notForSale").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>price-wrapper").toggle();
	});

jQuery('#<?php echo $this->formId; ?>date_type').live("change", function(){
	var date_type = jQuery('#<?php echo $this->formId; ?>date_type:checked').val();
	switch( date_type )
	{
		case 'range':
			jQuery('#<?php echo $this->formId; ?>date_range').show();
			jQuery('#<?php echo $this->formId; ?>date_fixed').hide();
			break;
		case 'fixed':
			jQuery('#<?php echo $this->formId; ?>date_range').hide();
			jQuery('#<?php echo $this->formId; ?>date_fixed').show();
			break;
	}
});

jQuery('#<?php echo $this->formId; ?>service_type').live("change", function() {
	jQuery('#<?php echo $this->formId; ?>_service').hide();
	var serviceType = jQuery('#<?php echo $this->formId; ?>service_type:checked').val();
	switch( serviceType ){
		case 'one':
			jQuery('#<?php echo $this->formId; ?>_fixed_services').hide();

			jQuery('#<?php echo $this->formId; ?>_service').show();
			jQuery('#<?php echo $this->formId; ?>_details_qty').hide();
			jQuery('#<?php echo $this->formId; ?>_details_duration').hide();
			jQuery('#<?php echo $this->formId; ?>_details_amount').hide();

			var what2show = jQuery('#<?php echo $this->formId; ?>pack_type:checked').val();
			what2show = '#<?php echo $this->formId; ?>_details_' + what2show;
			jQuery(what2show).show();
			break;
		case 'fixed':
			jQuery('#<?php echo $this->formId; ?>_service').hide();
			jQuery('#<?php echo $this->formId; ?>_details_qty').hide();
			jQuery('#<?php echo $this->formId; ?>_details_duration').hide();
			jQuery('#<?php echo $this->formId; ?>_details_amount').hide();

			jQuery('#<?php echo $this->formId; ?>_fixed_services').show();
			break;
		}
	});

jQuery('#<?php echo $this->formId; ?>pack_type').live("change", function() {
	jQuery('#<?php echo $this->formId; ?>_details_qty').hide();
	jQuery('#<?php echo $this->formId; ?>_details_duration').hide();
	jQuery('#<?php echo $this->formId; ?>_details_amount').hide();

	var what2show = jQuery('#<?php echo $this->formId; ?>pack_type:checked').val();
	what2show = '#<?php echo $this->formId; ?>_details_' + what2show;
	jQuery(what2show).show();
	});

jQuery('#nts-toggle-all-services').live("change", function(){
	if( this.checked ){
		jQuery('#<?php echo $this->formId; ?>service_id_container').hide();
		}
	else {
		jQuery('#<?php echo $this->formId; ?>service_id_container').show();
		}
	});
</script>