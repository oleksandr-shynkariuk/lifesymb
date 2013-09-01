<?php
$pgm =& ntsPaymentGatewaysManager::getInstance();
$allGateways = $pgm->getActiveGateways();
$onlinePaymentAllowed = TRUE;
if( (count($allGateways) == 1) && ($allGateways[0] == 'offline') ){
	$onlinePaymentAllowed = FALSE;
	}
?>
<table class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Currency'); ?></td>
<?php
$pgm =& ntsPaymentGatewaysManager::getInstance();
$allCurrOptions = $pgm->getAllCurrencies();
$allowedCurrencies = $pgm->getActiveCurrencies();

if( $allowedCurrencies ){
	$currOptions = array();
	reset( $allCurrOptions );
	foreach( $allCurrOptions as $co ){
		if( in_array($co[0], $allowedCurrencies) )
			$currOptions[] = $co;
		}
	}
else {
	$currOptions = $allCurrOptions;
	}
?>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'currency',
			'options'	=> $currOptions,
			)
		);
	?>
	</td>
</tr>
<tr>
	<td class="ntsFormLabel"><?php echo M('Price Format'); ?></td>
	<td>
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'sign-before',
			'attr'		=> array(
				'size'	=> 4,
				'style'	=> 'text-align: right;'
				),
			)
		);
	?>
	<?php
	$formats = array(
		'.||,',
		'.|| ',
		',|| ',
		'.||',
		',||',
		',||.',
		);

	$demoPrice = 54321;
	reset( $formats );
	$formatOptions = array();
	foreach( $formats as $f ){
		list( $decPoint, $thousandSep ) = explode( '||', $f );
		$formatOptions[] = array( $f, number_format($demoPrice, 2, $decPoint, $thousandSep) );
		}

	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'format',
			'options'	=> $formatOptions,
			)
		);
	?>	
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'sign-after',
			'attr'		=> array(
				'size'	=> 4,
				),
			)
		);
	?>
	<a href="<?php echo ntsLink::makeLink('-current-', 'reset'); ?>"><?php echo M('Reset To Defaults'); ?></a> 
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Tax Title'); ?></td>
	<td>
<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'		=> 'taxTitle',
		'attr'		=> array(
			'size'	=> 20,
			),
		)
	);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Tax Rate'); ?></td>
	<td>
<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'		=> 'taxRate',
		'attr'		=> array(
			'size'	=> 3,
			),
		),
/* validators */
	array(
		array(
			'code'		=> 'number', 
			'error'		=> M('Numbers only'),
			),
		)
	);
?> %
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Invoice Header'); ?></td>
	<td>
<?php
echo $this->makeInput (
/* type */
	'textarea',
/* attributes */
	array(
		'id'	=> 'invoiceHeader',
		'attr'	=> array(
			'cols'	=> 64,
			'rows'	=> 4,
			),
		)
	);
?>
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Invoice Footer'); ?></td>
	<td>
<?php
echo $this->makeInput (
/* type */
	'textarea',
/* attributes */
	array(
		'id'	=> 'invoiceFooter',
		'attr'	=> array(
			'cols'	=> 64,
			'rows'	=> 4,
			),
		)
	);
?>
	</td>
</tr>

<tr>
<td></td>
<td>
<?php echo $this->makePostParams('-current-', 'update'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
</td>
</tr>
</TABLE>