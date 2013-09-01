<?php
$no_send = TRUE;
require( dirname(__FILE__) . '/phone2email.php' );
$carriersOptions = array();
foreach( array_keys($phone2email) as $c )
{
	$carriersOptions[] = array( $c, $c );
}
?>
<table class="ntsForm">
<tr>
	<td class="ntsFormLabel">Carriers *</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'checkboxSet',
	/* attributes */
		array(
			'id'		=> 'carriers',
			'options'	=> $carriersOptions,
			'default'	=> array_keys($phone2email),
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
	</TD>
</TR>

<tr>
	<td class="ntsFormLabel">Number Sent From</td>
	<td class="ntsFormValue">
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'from',
			'default'	=> '',
			'attr'		=> array(
				'size'	=> 32,
				),
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
	</TD>
</TR>

</TABLE>