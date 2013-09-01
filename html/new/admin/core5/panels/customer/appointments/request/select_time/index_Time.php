<?php
$t = $NTS_VIEW['t'];
?>
<?php if( ! $NTS_VIEW['isBundle'] ) : ?>

<?php
$showTimes = array();
reset( $NTS_VIEW['times'][$I] );
foreach( $NTS_VIEW['times'][$I] as $ts ){
	$t->setTimestamp( $ts );
	$thisDate = $t->formatDate_Db();
	if( ! isset($showTimes[$thisDate]) )
		$showTimes[$thisDate] = array();
	$showTimes[$thisDate][] = $ts;	
	}
?>
<div id="nts-time-selector">
<?php foreach( $showTimes as $dayTimes ) : ?>
	<?php $t->setTimestamp( $dayTimes[0] ); ?>
	<h3><?php echo $t->formatWeekday(); ?>, <?php echo $t->formatDate(); ?></h3>
	<ul>
	<?php foreach( $dayTimes as $ts ) : ?>
	<?php 	$t->setTimestamp( $ts ); ?>
<li><a href="<?php echo ntsLink::makeLink('-current-', 'select', array('id_' . $I => $ts) ); ?>"><?php echo $t->formatTime(); ?></a>
	<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
</div>

<?php else : ?>

<?php
$showTimes = array();
reset( $NTS_VIEW['bundleTimes'] );
foreach( $NTS_VIEW['bundleTimes'] as $tss ){
	$t->setTimestamp( $tss[0] );
	$thisDate = $t->formatDate_Db();
	if( ! isset($showTimes[$thisDate]) )
		$showTimes[$thisDate] = array();
	$showTimes[$thisDate][] = $tss;	
	}
?>

<div id="nts-time-selector">
<?php foreach( $showTimes as $dayTimes ) : ?>
	<?php $t->setTimestamp( $dayTimes[0][0] ); ?>
	<h3><?php echo $t->formatWeekday(); ?>, <?php echo $t->formatDate(); ?></h3>
	<ul>
	<?php foreach( $dayTimes as $tss ) : ?>
	<?php 	$t->setTimestamp( $tss[0] ); ?>
<?php
			$timeParam = array();
			for( $ii = 1; $ii <= count($tss); $ii++ ){
				$timeParam[ 'id_' . $ii ] = $tss[ $ii-1 ];
				}
?>
	
<li><a href="<?php echo ntsLink::makeLink('-current-', 'select', $timeParam ); ?>"><?php echo $t->formatTime(); ?></a>
	<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
</div>

<?php endif; ?>