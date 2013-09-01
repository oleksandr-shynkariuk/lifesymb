<?php
$ress = ntsLib::getVar( 'admin::ress' );
$cal = ntsLib::getVar( 'admin/manage/timeoff:cal' );

$minStart = NTS_TIME_STARTS;
$maxEnd = NTS_TIME_ENDS;
?>
<table class="ntsForm">
<tr>
<td class="ntsFormLabel"><?php echo M('Bookable Resource'); ?></td>
<td class="ntsFormValue">
<?php if( count($ress) == 1 ) : ?>
<?php
		echo $this->makeInput (
		/* type */
			'hidden',
		/* attributes */
			array(
				'id'	=> 'resource_id',
				'value'	=> $ress[0],
				)
			);
		$obj = ntsObjectFactory::get( 'resource' );
		$obj->setId( $ress[0] );
		$objView = ntsView::objectTitle( $obj );
?>
<?php 	echo $objView; ?>
<?php else : ?>
<?php
		$options = array();
		reset( $ress );
		foreach( $ress as $objId ){
			$obj = ntsObjectFactory::get( 'resource' );
			$obj->setId( $objId );
			$options[] = array( $objId, ntsView::objectTitle($obj) );
			}
		echo $this->makeInput (
		/* type */
			'select',
		/* attributes */
			array(
				'id'		=> 'resource_id',
				'options'	=> $options,
				)
			);
?>
<?php endif; ?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('From'); ?></td>
<td class="ntsFormValue">
<?php
	echo $this->makeInput (
	/* type */
		'date/Calendar',
	/* attributes */
		array(
			'id'		=> 'starts_at_date',
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

<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'starts_at_time',
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
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('To'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'ends_at_date',
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		array(
			'code'		=> 'greaterThan.php', 
			'error'		=> M('The end time should be after the start'),
			'params'	=> array(
				'compareFields'	=> array(
					array('ends_at_date', 'starts_at_date'),
					array('ends_at_time', 'starts_at_time'),
					)
				),
			),
		)
	);
?>

<?php
echo $this->makeInput (
/* type */
	'date/Time',
/* attributes */
	array(
		'id'		=> 'ends_at_time',
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
		)
	);
?>
</td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Description'); ?></td>
<td class="ntsFormValue">
<?php
echo $this->makeInput (
/* type */
	'textarea',
/* attributes */
	array(
		'id'		=> 'description',
		'attr'		=> array(
			'cols'	=> 32,
			'rows'	=> 4,
			),
		),
/* validators */
	array(
		)
	);
?>
</td>
</tr>


<tr>
<td>&nbsp;</td>
<td>
<?php echo $this->makePostParams('-current-', 'create' ); ?>
	<INPUT TYPE="submit" VALUE="<?php echo M('Add'); ?>">
</td>
</tr>
</table>