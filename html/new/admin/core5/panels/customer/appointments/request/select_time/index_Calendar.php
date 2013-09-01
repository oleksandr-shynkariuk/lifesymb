<?php
global $NTS_CALENDAR_MODE, $NTS_CALENDAR_STOP;
$t = $NTS_VIEW['t'];
include_once( NTS_BASE_DIR . '/lib/datetime/ntsCalendar.php' );
$ntsConf =& ntsConf::getInstance();
$weekStartsOn = $ntsConf->get('weekStartsOn');
$text_Monthnames = array( M('Jan'), M('Feb'), M('Mar'), M('Apr'), M('May'), M('Jun'), M('Jul'), M('Aug'), M('Sep'), M('Oct'), M('Nov'), M('Dec') );
$text_Weekdays = array( M('Sun'), M('Mon'), M('Tue'), M('Wed'), M('Thu'), M('Fri'), M('Sat') );
$calendar = new ntsCalendar();

$selectedDay = $NTS_VIEW['cal'][$I];

$highlightDays = array();
switch( $NTS_CALENDAR_MODE ){
	case 'recurring':
		if( $NTS_VIEW['recurring-dates'] )
			$highlightDays = $NTS_VIEW['recurring-dates'];
		else
			$highlightDays = array( $NTS_VIEW['recur-from'], $NTS_VIEW['recur-to'] );
		break;
	case 'custom-dates':
		$highlightDays = $NTS_VIEW['custom-dates'];
		break;
	default:
		$highlightDays = array( $selectedDay );
	}

$dates = $NTS_VIEW['dates'][$I];
list( $calYear, $calMonth, $calDay ) = ntsTime::splitDate( $selectedDay );

$t->setDateTime( $calYear, $calMonth - $NTS_VIEW['showMonths'], 1, 0, 0, 0 );
$previousMo = $t->formatDate_Db();
$t->setDateTime( $calYear, $calMonth + $NTS_VIEW['showMonths'], 1, 0, 0, 0 );
$nextMo = $t->formatDate_Db();
?>

<div class="nts-calendar">
<table>

<?php for( $k = 0; $k < $NTS_VIEW['showMonths']; $k++ ) : ?>
<?php
		$monthMatrix = $calendar->getMonthMatrix( $calYear, $calMonth );
		$currentCalendar = array();
		$changeI = $NTS_VIEW['isBundle'] ? 0 : $I;
?>
	<tr class="months">
	<td>
		<?php if( $k == 0 ) : ?>
			<a href="<?php echo ntsLink::makeLink('-current-', '', ntsThisBuildCalendarValue($changeI, $previousMo, 'cal') ); ?>">&lt;</a>
		<?php else : ?>
			&nbsp;
		<?php endif; ?>
	</td>

	<td colspan="5" style="width: auto;">
		<?php echo $text_Monthnames[ $calMonth - 1 ]; ?> <?php echo $calYear; ?>
	</td>

	<td>
		<?php if( $k == ($NTS_VIEW['showMonths']-1) ) : ?>
			<a href="<?php echo ntsLink::makeLink('-current-', '', ntsThisBuildCalendarValue($changeI, $nextMo, 'cal') ); ?>">&gt;</a>
		<?php else : ?>
			&nbsp;
		<?php endif; ?>
	</td>
	</tr>

	<tr class="days">
	<?php for( $i = 0; $i <= 6; $i++ ) : ?>
		<?php
		$dayIndex = $weekStartsOn + $i;
		$dayIndex = $dayIndex % 7;
		?>
		<td>
		<div><?php echo $text_Weekdays[$dayIndex]; ?></div>
		</td>
	<?php endfor; ?>
	</tr>

	<?php foreach( $monthMatrix as $week => $days ) : ?>
	<tr>
		<?php foreach( $days as $day ) : ?>
		<?php if( $day ) : ?>
			<?php
			$thisDate = ntsTime::formatDateParam( $calYear, $calMonth, $day );
 			$ok = ( in_array($thisDate, $dates) ) ? true : false;
			$class = '';
			$class .= ( $ok ) ? ' available' : 'not_available';
			$class .= ( in_array($thisDate, $highlightDays) ) ? ' selected' : '';			
			?>
			<td>
			<div class="<?php echo $class; ?>">
			<?php if( $ok && (! $NTS_CALENDAR_STOP) ) : ?>
				<a href="<?php echo ntsLink::makeLink('-current-', '', ntsThisBuildCalendarValue($changeI, $thisDate, $NTS_CALENDAR_MODE) ); ?>"><?php echo $day; ?></a>
			<?php else : ?>
				<?php echo $day; ?>
			<?php endif; ?>
			</div>
			</td>
		<?php else : ?>
			<td>
			&nbsp;
			</td>
		<?php endif; ?>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
<?php
	$calMonth++;
	if( $calMonth > 12 ){
		$calMonth = 1;
		$calYear++;
		}
?>
<?php endfor; ?>

</table>
</div>
