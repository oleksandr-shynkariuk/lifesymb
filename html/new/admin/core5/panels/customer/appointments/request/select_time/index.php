<?php
$ff =& ntsFormFactory::getInstance();

global $NTS_CURRENT_USER;
if( NTS_ENABLE_TIMEZONES > 0 )
{
	$formTimezoneParams = array(
		'tz'	=> $NTS_CURRENT_USER->getTimezone(),
		);
	$formTimezone =& $ff->makeForm( dirname(__FILE__) . '/formTimezone', $formTimezoneParams );
	if( ($NTS_CURRENT_USER->getId() == 0) && (! isset($_SESSION['nts_timezone'])) )
	{
		$formTimezone->display();
		return;
	}
}
?>
<?php
include_once( dirname(__FILE__) . '/ntsThisBuildCalendarValue.php' );

global $NTS_AR;
$current = $NTS_AR->getCurrent();
$SLOTS = array( '', '', '' ); // calendar / recurring type / time
$DISPLAY_ONE_FORM = '';

global $NTS_CALENDAR_MODE;
$NTS_CALENDAR_MODE = 'cal';
$currentIndexes = $NTS_AR->getCurrentIndexes();
$reschedule = $NTS_AR->getReschedule();

if( count($current) == 1 ){
	$recurring = $NTS_VIEW['recurring'];
	switch( $recurring ){
		case 'recurring':
			$NTS_CALENDAR_MODE = 'recurring';
			$SLOT[ 0 ] = dirname(__FILE__) . '/index_Calendar.php';
			$SLOT[ 1 ] = dirname(__FILE__) . '/index_ChooseRecurring.php';
			$SLOT[ 2 ] = dirname(__FILE__) . '/index_RecurringOptions.php';
			break;
		case 'custom':
			$NTS_CALENDAR_MODE = 'custom-dates';
			$SLOT[ 0 ] = dirname(__FILE__) . '/index_Calendar.php';
			$SLOT[ 1 ] = dirname(__FILE__) . '/index_ChooseRecurring.php';
			$SLOT[ 2 ] = dirname(__FILE__) . '/index_CustomDates.php';
			break;
		default:
			$SLOT[ 0 ] = dirname(__FILE__) . '/index_Calendar.php';
			$SLOT[ 1 ] = ( $reschedule ) ? null : dirname(__FILE__) . '/index_ChooseRecurring.php';
			$SLOT[ 2 ] = dirname(__FILE__) . '/index_Time.php';
			break;
		}
	}
else {
	reset( $currentIndexes );
	foreach( $currentIndexes as $i ){
		$thisDate = $NTS_AR->getSelected( $i, 'date' );
		if( $thisDate ){
			$DISPLAY_ONE_FORM = dirname(__FILE__) . '/form_Time';
			}
		else {
			$SLOT[ 0 ] = dirname(__FILE__) . '/index_Calendar.php';
			$SLOT[ 1 ] = null;
			$SLOT[ 2 ] = dirname(__FILE__) . '/index_Time.php';
			}
		}
	}
?>

<p>
<?php require( dirname(__FILE__) . '/../common/flow-header.php' ); ?>

<h2><?php echo M('Date and Time'); ?></h2>

<?php if( NTS_ENABLE_TIMEZONES > 0 ) : ?>
<?php
	$formTimezone->display();
?>
<?php elseif( NTS_ENABLE_TIMEZONES < 0 ) : ?>
<?php else : ?>
	<?php echo M('Times shown in [b]{TIME_ZONE}[/b] time zone', array('TIME_ZONE' => ntsTime::timezoneTitle($NTS_CURRENT_USER->getTimezone()) )); ?>
<?php endif; ?>

<?php if( $DISPLAY_ONE_FORM ) : ?>
<?php
$ff =& ntsFormFactory::getInstance();
$form =& $ff->makeForm( $DISPLAY_ONE_FORM );
$form->display();
?>
<?php else : ?>
<?php reset( $currentIndexes ); ?>
<?php foreach( $currentIndexes as $I ) : ?>
<?php 		require( dirname(__FILE__) . '/../common/flow.php' ); ?>
<?php 		if( $SLOT[1] ) : ?>
<?php 			require( $SLOT[1] ); ?>
<?php 		endif; ?>
<?php 		if( $SLOT[0] ) : ?>
<p>

<div id="ha-calendar-container" style="padding: 0 0; margin: 0 0;"><?php require( $SLOT[0] ); ?></div>
<div style="width: auto; padding-top: 0.5em;"><?php require( $SLOT[2] ); ?></div>
<div style="float: none; clear: both;"></div>

<?php 		else :  ?>
<?php 			require( $SLOT[2] ); ?>
<?php 		endif; ?>
<?php 	break; ?>
<?php 	endforeach; ?>
<?php endif; ?>
