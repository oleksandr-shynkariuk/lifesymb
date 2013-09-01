<?php
$dayStart = $this->getValue( 'dayStart' );
$dayEnd = $this->getValue( 'dayEnd' );
$from = $this->getValue( 'from' );
$to = $this->getValue( 'to' );
$showAll = $this->getValue( 'showAll' ); 

$minStart = NTS_TIME_STARTS;
$maxEnd = NTS_TIME_ENDS;

$times = array();

$timesFrom = $from ? $from : ($dayStart + $minStart);
$timesTo = $to ? $to : ($dayStart + $maxEnd);

for( $ts = $timesFrom; $ts <= $timesTo; $ts += NTS_TIME_UNIT * 60 ){
	$times[] = $ts;
	}

?>
<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>

<?php if( $reschedule ) : ?>
<td class="ntsFormValue" style="vertical-align: top;">
<?php
	$objId = $reschedule->getProp( 'starts_at' );
	$t->setTimestamp( $objId );
	$objView = $t->formatTime();
?>
<?php echo $objView; ?>
</td>
<?php endif; ?>

<td class="ntsFormValue">

<ul class="nts-hori-list">
<?php if( ! $time ) : ?>
<?php	foreach( $times as $objId ) : ?>	
<?php
			$t->setTimestamp( $objId );
			$objView = $t->formatTime();

			$thisRe = $re;
			$thisRe[3] = $objId;
			$thisRe = '/^' . join('\-', $thisRe) . '$/';

			$on = ntsLib::reExistsInArray( $thisRe, $available );
			if( (! $on) && (! $showAll) )
				continue;
			$class = $on ? 'ntsWorking' : 'ntsNotWorking';
?>
	<li title="<?php echo $objView; ?>" class="nts-hori-list-item <?php echo $class; ?>" style="width: 5em; text-align: center;">
	<a href="<?php echo ntsLink::makeLink('-current-', '', array('starts_at' => $objId) ); ?>"><?php echo $objView; ?></a>
	</li>
<?php	endforeach; ?>

<?php if( ! $showAll ) : ?>
	<li class="nts-hori-list-item">
	<a href="<?php echo ntsLink::makeLink('-current-', '', array('from' => '-reset-', 'to' => '-reset-', 'all' => 1) ); ?>"><?php echo M('Show All Time'); ?></a>
	</li>
<?php endif; ?>

<?php else : ?>
<?php
		$t->setTimestamp( $time );
		$objView = $t->formatTime();
?>
	<li title="<?php echo $objView; ?>" class="nts-hori-list-item selected">
	<?php echo $objView; ?>
	<a class="ntsDeleteControl2" href="<?php echo ntsLink::makeLink('-current-', '', array('starts_at' => '-reset-') ); ?>">[x]</a>
	</li>
<?php endif; ?>
</ul>

</td>
</tr>