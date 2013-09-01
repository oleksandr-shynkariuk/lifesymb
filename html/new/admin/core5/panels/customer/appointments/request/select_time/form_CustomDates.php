<?php
$preferredTimes = $NTS_VIEW['preferred-times'];
$t = $NTS_VIEW['t'];
?>
<?php 	if( $preferredTimes) : ?>
<table>
		<tr>
		<th>
		<?php echo M('Preferred Time'); ?>
		</th>
<?php 
		$preferredTimesOptions = array();
		$t->setDateDb(20110516);
		$startDay = $t->getStartDay();
		foreach( $preferredTimes as $pt ){
			$t->setTimestamp( $startDay + $pt );
			$ptView = $t->formatTime();
			$preferredTimesOptions[] = array( $pt, $ptView );
			}
?>
		<td>
<?php
echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'preferred-time',
		'options'	=> $preferredTimesOptions,
		'attr'		=> array(
			),
		)
	);
?>
		</td>
		</tr>
</table>
<?php 	endif; ?>

<p>
<?php echo $this->makePostParams('-current-', 'select-custom-dates' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Continue'); ?>">