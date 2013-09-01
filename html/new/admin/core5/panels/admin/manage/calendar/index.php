<?php
$TYPE_TO_CSS = array(
	HA_SLOT_TYPE_WO		=> 'ntsWorking',
	HA_SLOT_TYPE_APP_BODY	=> '',
	HA_SLOT_TYPE_APP_LEAD	=> 'ntsLead',
	HA_SLOT_TYPE_NA		=> 'ntsNotWorking',
	HA_SLOT_TYPE_TOFF		=> 'ntsTimeoff',
	);

$t = $NTS_VIEW['t'];
$list = ntsLib::getVar( 'admin/manage/calendar::list' );
$filter = ntsLib::getVar( 'admin/manage:filter' );
$ress = ntsLib::getVar( 'admin::ress' );
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$schEdit = ntsLib::getVar( 'admin/manage:schEdit' );
$appView = ntsLib::getVar( 'admin/manage:appView' );
$schView = ntsLib::getVar( 'admin/manage:schView' );
$calendarField = ntsLib::getVar( 'admin/manage/calendar:calendarField' );
$defaultCalendar = ntsLib::getVar( 'admin/manage:defaultCalendar' );

$slotsArray = ntsLib::getVar( 'admin/manage/calendar:slots' );
$cals = ntsLib::getVar( 'admin/manage:cals' );
?>
<?php require( dirname(__FILE__) . '/submenu.php' ); ?>
<?php foreach( $cals as $cal ) : ?>
<?php
	$t->setDateDb( $cal );
	$dateView = $t->formatWeekdayShort() . ', ' . $t->formatDate();
	$slots = $slotsArray[$cal];
?>

<p>
<h3><?php echo $dateView; ?></h3>

<ul class="nts-day-slots">
<?php for( $ii = 0; $ii < count($list); $ii++ ) : ?>
<?php	
	$li = $list[$ii];
	$objClassName = $li[0]->getClassName();
	$thisFilter = $filter;
	$addFilter = substr( $objClassName, 0, 1 ) . $li[0]->getId();
	$thisFilter[] = $addFilter;

	// find current resources
	$thisResIds = array();
	reset( $thisFilter );
	foreach( $thisFilter as $tf ){
		if( substr($tf, 0, 1) == 'r' ){
			$thisResIds[] = substr($tf, 1);
			break;
			}
		}
	if( ! $thisResIds )
		$thisResIds = $ress;

	$iCanEditApps = array_intersect( $thisResIds, $appEdit );
	$iCanViewApps = array_intersect( $thisResIds, $appView );
	$iCanEditSchedules = array_intersect( $thisResIds, $schEdit );
	$iCanViewSchedules = array_intersect( $thisResIds, $schView );
?>

<!-- DAY LINE -->
<li class="nts-day-slot nts-ajax-parent">
<?php if( count($list) > 1 ) : ?>
	<h4 class="nts-bold" style="margin: 0.25em 0 0.5em 0;"><?php echo ntsView::objectTitle( $li[0] ); ?></h4>
<?php endif; ?>

<?php for( $r = 0; $r < count($slots[$ii]); $r++ ) : ?>
<?php	$slotContainer = $slots[$ii][$r]; ?>
<div class="nts-container">
<?php 	foreach( $slotContainer as $slot ) : ?>
<?php
	$availabilityLink = ntsLink::makeLink( 
		'admin/schedules', '', 
		array(
			'nts-filter'	=> join('-', $thisFilter),
			'cal'			=> $cal,
			)
		);

	$t->setTimestamp( $slot[0] );
	$timeViewStart = $t->formatTime();
	$t->setTimestamp( $slot[1] );
	$timeViewEnd = $t->formatTime();

	$targetLink = '#';
	$slotClass = array( $TYPE_TO_CSS[$slot[2]] );
	$slotId = '';
	$slotWidth = $slot[4];
	$slotInfo = '&nbsp;';

	$visibleMe = true;
	$startLabel = '';
	switch( $slot[2] ){
		case HA_SLOT_TYPE_WO:
			$targetLink = ntsLink::makeLink( 
				'-current-/../appointments/create', '', 
				array(
					'from'		=> $slot[0],
					'to'		=> $slot[1],
					'nts-filter'	=> join('-', $thisFilter),
					)
				);
			$startLabel = M('Available');
			break;

		case HA_SLOT_TYPE_APP_BODY:
			if( isset($slot[3][0]) ){
				$cssClasses = array();
				$a = ntsObjectFactory::get( 'appointment' );
				if( is_array($slot[3]) ){
					foreach( $slot[3] as $app ){
						$a->setId( $app['id'] );
						list( $alert, $cssClass, $message ) = $a->getStatus();
						$cssClasses[ $cssClass ] = 1;
						}
					$slotId = 'nts-app-' . $slot[3][0];
					}
				else {
					$a->setId( $slot[3] );
					list( $alert, $cssClass, $message ) = $a->getStatus();
					$startLabel = $a->getProp('approved') ? M('Approved') : M('Pending');
					$cssClasses[ $cssClass ] = 1;
					$slotId = 'nts-app-' . $slot[3];
					}
				}
			$slotClass = array_keys( $cssClasses );

			if( is_array($slot[3]) ){
				$idValue = join( '-', array($a->getProp('location_id'), $a->getProp('resource_id'), $a->getProp('service_id'), $a->getProp('starts_at')) );
				$targetLink = ntsLink::makeLink( 
					'-current-/../appointments/edit_class', '', 
					array(
						'_id' => $idValue,
						)
					);
				}
			else {
				$targetLink = ntsLink::makeLink( 
					'-current-/../appointments/edit', '', 
					array(
						'_id' => $slot[3],
						)
					);
				}

			switch ( $calendarField )
			{
				case 'customer':
					if( ! is_array($slot[3]) )
					{
						$customer = new ntsUser;
						$customer->setId( $a->getProp('customer_id') );
						$slotInfo = ntsView::objectTitle( $customer );
					}
					break;
				case 'service':
					$service = ntsObjectFactory::get('service');
					$service->setId( $a->getProp('service_id') );
					$slotInfo = ntsView::objectTitle( $service );
					break;
			}

			break;

		case HA_SLOT_TYPE_TOFF:
			$targetLink = ntsLink::makeLink( 
				'-current-/../timeoff/edit', '', 
				array(
					'_id' => $slot[3],
					)
				);
			$startLabel = M('Timeoff');
			$timeoff = ntsObjectFactory::get('timeoff');
			$timeoff->setId( $slot[3] );
			$slotInfo = $timeoff->getProp('description');
			break;

		case HA_SLOT_TYPE_NA:
			$visibleMe = ($r > 0) ? false : true;
			$startLabel = M('Not Available');
			break;

		default:
			$targetLink = $availabilityLink;
		}
	$slotClass = join( ' ', $slotClass );
?>
<?php if( $visibleMe ) : ?>
<?php
$linkLabel = '';
if( $startLabel )
	$linkLabel .= $startLabel . ' ';
$linkLabel .= $timeViewStart . '-' . $timeViewEnd;
if( $calendarField )
	$linkLabel .= ' ' . $slotInfo;
?>
	<a id="<?php echo $slotId; ?>" href="<?php echo $targetLink; ?>" class="nts-slot-link nts-ajax-loader" style="width: <?php echo $slotWidth; ?>%;" title="<?php echo $linkLabel; ?>">
	<span class="<?php echo $slotClass; ?>">
	<span class="nts-l"><?php echo $timeViewStart; ?></span>
<?php if( $calendarField ) : ?>
	<span class="nts-c"><?php echo $slotInfo; ?></span>
<?php endif; ?>
	<span class="nts-r"><?php echo $timeViewEnd; ?></span>
	</span>
	</a>
<?php else: ?>
	<div class="nts-slot-hidden" style="width: <?php echo $slotWidth; ?>%;">&nbsp;</div>
<?php endif; ?>

<?php 	endforeach; ?>
</div>
<?php endfor; ?>

<div class="nts-ajax-container nts-ajax-return nts-child"></div>

</li>
<?php endfor; ?>
</ul>

<?php endforeach; ?>