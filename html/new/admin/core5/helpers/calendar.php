<?php
/*  should have these vars defined 

$linkedDates
$okDates
$cssDates
$linkedDates
$params // optional

$calendarMode = recurring | custom-dates | default
if default
	$selectedDate
if recurring
	$recurringDates
	$recurFrom
	$recurTo
if custom-dates
	$customDates
*/
if( ! isset($calendarMode) )
	$calendarMode = 'default';
if( ! isset($params) )
	$params = array();

$t = $NTS_VIEW['t'];
include_once( NTS_BASE_DIR . '/lib/datetime/ntsCalendar.php' );

$ntsConf =& ntsConf::getInstance();
$weekStartsOn = $ntsConf->get('weekStartsOn');
$text_Monthnames = array( M('Jan'), M('Feb'), M('Mar'), M('Apr'), M('May'), M('Jun'), M('Jul'), M('Aug'), M('Sep'), M('Oct'), M('Nov'), M('Dec') );
$text_Weekdays = array( M('Sun'), M('Mon'), M('Tue'), M('Wed'), M('Thu'), M('Fri'), M('Sat') );
$calendar = new ntsCalendar();

$highlightDays = array();
switch( $calendarMode ){
	case 'recurring':
		$highlightDays = $recurringDates ? $recurringDates : array( $recurFrom, $recurTo );
		break;
	case 'custom-dates':
		$highlightDays = $customDates;
		break;
	default:
		if( isset($highlightDay) )
			$highlightDays = array( $highlightDay );
		else
			$highlightDays = array( $selectedDate );
	}

list( $calYear, $calMonth, $calDay ) = ntsTime::splitDate( $selectedDate );

$showMonths = isset($calendarMonths) ? $calendarMonths : 1;
$t->setDateTime( $calYear, $calMonth - $showMonths, 1, 0, 0, 0 );
$previousMo = $t->formatDate_Db();

$t->setDateTime( $calYear, $calMonth + 1, 1, 0, 0, 0 );
$nextMo = $t->formatDate_Db();
?>

<div class="nts-calendar">
<table>

<?php for( $k = 0; $k < 1; $k++ ) : ?>
<?php
		$monthMatrix = $calendar->getMonthMatrix( $calYear, $calMonth );
		$currentCalendar = array();
		$myParams = $params;
		$myParams['cal'] = $previousMo;
?>
	<tr class="months">
	<td>
		<?php if( $k == 0 ) : ?>
<?php 			if( ! (isset($skipPrevLink) && $skipPrevLink) ) : ?>
					<a href="<?php echo ntsLink::makeLink('-current-', '', $myParams ); ?>">&lt;</a>
<?php 			endif; ?>
		<?php else : ?>
			&nbsp;
		<?php endif; ?>
	</td>

	<td colspan="5" style="width: auto;">
		<?php echo $text_Monthnames[ $calMonth - 1 ]; ?> <?php echo $calYear; ?>
	</td>

	<td>
<?php
		$myParams = $params;
		$myParams['cal'] = $nextMo;
?>
		<?php if( $k == 0 ) : ?>
<?php 			if( ! (isset($skipNextLink) && $skipNextLink) ) : ?>
					<a href="<?php echo ntsLink::makeLink('-current-', '', $myParams); ?>">&gt;</a>
<?php 			endif; ?>
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
			
 			$linked = in_array($thisDate, $linkedDates) ? true : false;
 			$ok = in_array($thisDate, $okDates) ? true : false;

			$class = $cssDates[ $thisDate ];
			$label = isset($labelDates[$thisDate]) ? $labelDates[$thisDate] : '';
			$class = join( ' ', $class );
			$myParams = $params;
			$myParams['cal'] = $thisDate;
			?>
			<td<?php if( in_array($thisDate, $highlightDays) ){ echo " class='selected'";}; ?>>
			<div class="<?php echo $class; ?>" title="<?php echo $label; ?>">
<?php		if( $linked ) : ?>
<?php
				$targetPanel = isset($calendarReturnTo) ? $calendarReturnTo : '-current-';
				$targetLink = ntsLink::makeLink($targetPanel, '', $myParams);
?>
			<a class="nts-target-parent" href="<?php echo $targetLink; ?>"><?php echo $day; ?></a>
<?php		else : ?>
			<?php echo $day; ?>
<?php		endif; ?>
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
