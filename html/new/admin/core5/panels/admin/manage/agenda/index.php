<?php
$printView = ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'print') ? TRUE : FALSE;
?>
<?php
$ntsConf =& ntsConf::getInstance();
$customerAcknowledge = $ntsConf->get( 'customerAcknowledge' );

$apps = ntsLib::getVar( 'admin/manage/agenda::apps' );
$appView = ntsLib::getVar( 'admin/manage:appView' );
$cal = ntsLib::getVar( 'admin/manage:cal' );
$period = ntsLib::getVar( 'admin/manage/agenda:period' );
$filter = ntsLib::getVar( 'admin/manage/agenda:filter' );
$parseClasses = ntsLib::getVar( 'admin/manage/agenda:parseClasses' );
$classSlots = ntsLib::getVar( 'admin/manage/agenda:slots' );
$mainView = ntsLib::getVar( 'admin/manage/agenda:mainView' );

$ntsdb =& dbWrapper::getInstance();

require( dirname(__FILE__) . '/prepareView.php' );

if( $parseClasses ){
	for( $ii = (count($viewEntries) - 1); $ii >= 0; $ii-- ){
		if( $viewEntries[$ii]['type'] != 'class' )
			continue;
		if( ! in_array($viewEntries[$ii]['status_completed'], array(HA_STATUS_CANCELLED, HA_STATUS_NOSHOW) ) ){
			continue;
			}
		array_splice( $viewEntries, $ii, 1 );
		}

	if( $classSlots ){
		/* build lrst index */
		$lrstIndex = array();
		reset($viewEntries);
		for( $ii = 0; $ii < count($viewEntries); $ii++ ){
			if( $viewEntries[$ii]['type'] != 'class' )
				continue;
			if( ! isset($lrstIndex[ $viewEntries[$ii]['lrst'] ]) )
				$lrstIndex[ $viewEntries[$ii]['lrst'] ] = array();
			$lrstIndex[ $viewEntries[$ii]['lrst'] ][] = $ii;
			}

		$t = $NTS_VIEW['t'];

	// add class slots to view
		$resortViews = false;
		reset( $classSlots );
		foreach( $classSlots as $cslot ){
		// check if we have already an appointment for this
			if( isset($lrstIndex[$cslot['lrst']]) ){
				reset( $lrstIndex[$cslot['lrst']] );
				foreach( $lrstIndex[$cslot['lrst']] as $tempI ){
					$viewEntries[$tempI]['seats_left'] = $cslot['seats']; 
					}
				continue;
				}

			$t->setTimestamp( $cslot['starts_at'] );
			$timeView = $t->formatTime();
			if( $cslot['duration'] )
			{
				$t->modify( '+' . $cslot['duration'] . ' seconds' );
				$timeView .= ' - ' . $t->formatTime();
			}

			$seatsLeftView = '';
			if( $cslot['seats'] >= 0 )
			{
				$seatsLeftView = '[' . M('Total Seats') . ': ' . $cslot['seats'] . ']';
			}
			$cslot['customer'] = $seatsLeftView;

			$cslot['time'] = $timeView;

			$t->setTimestamp( $cslot['starts_at'] );
			$dateView = $t->formatWeekdayShort() . ', ' . $t->formatDate();
			$cslot['date'] = $dateView;

			$service = ntsObjectFactory::get( 'service' );
			$service->setId( $cslot['service_id'] );
			$cslot['service'] = ntsView::objectTitle( $service );

			list( $lid, $rid, $sid, $ts ) = explode( '-', $cslot['lrst'] );
			$resource = ntsObjectFactory::get( 'resource' );
			$resource->setId( $rid );
			$cslot['resource'] = ntsView::objectTitle( $resource );;

			$location = ntsObjectFactory::get( 'location' );
			$location->setId( $lid );
			$cslot['location'] = ntsView::objectTitle( $location );;
			
			$viewEntries[] = $cslot;
			}

		if( $resortViews ){
			uasort( $viewEntries, create_function(
				'$a, $b',
				'
				if( $a["starts_at"] != $b["starts_at"] ){
					$return = ($b["starts_at"] - $a["starts_at"]);
					}
				else {
					$return = ($b["starts_at"] - $a["starts_at"]);
					}
				return $return;
				'
				)
			);
			}
		}
	}

$t = $NTS_VIEW['t'];
$t->setDateDb( $cal );
$dateView = $t->formatWeekdayShort() . ', ' . $t->formatDate();

$shownDates = array();
$lrst = array();

$skipFields = array('date', 'duration', 'id', 'lrst', 'type', 'time_end', 'status', 'status_approved', 'status_completed', 'starts_at');

$totalColumns = 1;
reset( $showFields );
foreach( $showFields as $f )
{
	if( ! in_array($f, $skipFields) )
		$totalColumns++;
}

if( $filter ){
	reset( $filter );
	foreach( $filter as $filterParam => $filterValue ){
		switch( $filterParam ){
			case 'resource_id':
				$skipFields[] = 'resource';
				break;
			case 'location_id':
				$skipFields[] = 'resource';
				break;
			case 'customer_id':
				$skipFields[] = 'customer';

				$om =& objectMapper::getInstance();
				$customerFields = $om->getFields( 'customer', 'internal' );
				$skipCustomerFields = array('first_name', 'last_name');
				reset( $customerFields );
				foreach( $customerFields as $cf ){
					$skipFields[] = 'customer:' . $cf[0];
					}
				break;
			}
		}
	}
?>
<?php require( dirname(__FILE__) . '/submenu.php' ); ?>

<?php
$menu3 = array();
?>

<?php if( $mainView ) : ?>
<?php require( dirname(__FILE__) . '/indexPeriod.php' ); ?>
<?php endif; ?>

<?php
if( ($NTS_VIEW[NTS_PARAM_VIEW_MODE] != 'ajax') && (! $printView) )
{
	$menu3[] = array(
		ntsLink::makeLink('-current-', '', array(NTS_PARAM_VIEW_MODE => 'print')),
		M('Print View'),
		FALSE,
		'target="_blank"'
		);
	$menu3[] = array(
		ntsLink::makeLink('-current-', 'export', array('display' => 'excel')),
		M('Excel'),
		FALSE,
		);
}
?>
<?php if( $menu3 ) : ?>
	<div class="nts-menu3">
	<ul>
<?php	foreach( $menu3 as $m ) : ?>
		<li<?php if( $m[2] ){echo ' class="selected"';} ?>>
<?php		if( $m[0] ) : ?>
				<a <?php if( isset($m[3]) ){echo $m[3];} ?> href="<?php echo $m[0]; ?>"><?php echo $m[1]; ?></a> 
<?php		else : ?>
<?php			echo $m[1]; ?>
<?php		endif; ?>
		</li>
<?php	endforeach; ?>
	</ul>
	</div>
<?php endif; ?>

<p>
<?php if( ! $viewEntries ) : ?>

<?php if( ! in_array($period, array('upcoming', 'all', 'pending', 'month')) ) : ?>
	<h3><?php echo $dateView; ?></h3>
<?php endif; ?>
<p>
<?php 	echo M('No Appointments'); ?>

<?php else : ?>

<table class="nts-listing">

<?php if( count($apps) ) : ?>
<tbody>
<tr>
<th style="width: 10px; padding: 1px 1px;">&nbsp;</th>

<?php 	reset( $showFields ); ?>
<?php 	foreach( $showFields as $f ) : ?>
<?php
			if( in_array($f, $skipFields) )
				continue;
?>
<th><?php 	echo isset($allFields[$f]) ? $allFields[$f] : '&nbsp;'; ?></th>
<?php	endforeach; ?>

</tr>
</tbody>
<?php endif; ?>

<?php 
reset( $viewEntries );

$countViewEntries = count($viewEntries);
for( $ii = 0; $ii < $countViewEntries; $ii++ ){
	list($l,$r,$s,$t) = explode( '-', $viewEntries[$ii]['lrst'] );
	$viewEntries[$ii]['tlrs'] = join( '-', array($t,$l,$r,$s) );
	}

usort( $viewEntries, create_function(
	'$a, $b',
	'return strcmp($a["tlrs"], $b["tlrs"]);'
	)
);

$glue = array();
$cssClasses = array();
?>
<?php for( $ii = 0; $ii < $countViewEntries; $ii++ ) : ?>
<?php
	$ve = $viewEntries[$ii];
?>
<?php
$linkClass = '';
// check if we need to glue this if it's a class
if( $ve['type'] == 'class' ){ // class
	if( $parseClasses ){
		// next is the same
		if( isset($viewEntries[$ii+1]) && ($viewEntries[$ii+1]['lrst'] == $ve['lrst']) && ($viewEntries[$ii+1]['type'] == 'class') ){
			$glue[] = $ve['id'];
			if( $ve['status_approved'] )
				$cssClasses[ 'ntsApproved2' ] = 1;
			else
				$cssClasses[ 'ntsPending2' ] = 1;
			continue;
			}
		$glue[] = $ve['id'];
		}
	}
if( $glue ){
	$ve['customer'] = count($glue) . ' ' . ((count($glue) > 1) ? M('Customers') : M('Customer'));

	if( ($ve['starts_at'] > 0) && isset($ve['time_end']) ){
		$thisView = $ve[$f] . ' - ' . $ve['time_end'];
		if( $ve['type'] == 'class' ){
			if (isset($ve['seats_left']) ){
				$ve['customer'] .= ' [' . M('Seats Left') . ': ' . $ve['seats_left'] . ']';
				}
			else {
				$ve['customer'] .= ' [' . M('Full') . ']';
				}
			}
		}

	$link = ntsLink::makeLink( 'admin/manage/appointments/edit_class', '', array('_id' => $ve['lrst']) );
	}
else {
	list( $thisLid, $thisRid, $thisSid, $thisTs ) = explode('-', $ve['lrst']);
	switch( $ve['type'] ){
		case 'slot' :
			$createParams = array(
				'starts_at' => $ve['starts_at'],
				'service_id' => $ve['service_id'],
				'location_id' => $thisLid,
				'resource_id' => $thisRid,
				);
			$link = ntsLink::makeLink( '-current-/../appointments/create', '', $createParams );
			break;

		case 'tslot' :
			$createParams = array(
				'from'	=> $ve['starts_at'],
				'to'	=> $ve['starts_at'] + $ve['duration'],
				);

			if( ($ve['starts_at'] > 0) && isset($ve['time_end']) ){
				}
			else {
			// fixed time starts	
				$createParams['starts_at'] = $ve['starts_at'];
				}

			$link = ntsLink::makeLink( '-current-/../appointments/create', '', $createParams );
			$linkClass = 'ntsWorking';
			break;
			
		case 'toff' :
			$createParams = array(
				'_id'	=> $ve['id'],
				);
			$link = ntsLink::makeLink( 'admin/manage/timeoff/edit', '', $createParams );
			$linkClass = 'ntsTimeoff';
			break;

		default:
			$link = ntsLink::makeLink( 'admin/manage/appointments/edit', '', array('_id' => $ve['id'], 'noheader' => 1) );
			break;
		}
	}

$label = '';
if( isset($ve['status_completed']) ){
	if( $ve['status_completed'] ){
		switch( $ve['status_completed'] ){
			case HA_STATUS_COMPLETED:
				if( $customerAcknowledge && (! $ve['object']->getProp('_ack')) )
				{
					$cssClasses[ 'ntsCompleted2_NotAck' ] = 1;
					$label = M('Completed') . ', ' . M('Not Acknowledged By Customer');
				}
				else
				{
					$cssClasses[ 'ntsCompleted2' ] = 1;
					$label = M('Completed');
				}
				break;
			case HA_STATUS_CANCELLED:
				$cssClasses[ 'ntsCancelled2' ] = 1;
				$label = M('Cancelled');
				break;
			case HA_STATUS_NOSHOW:
				$cssClasses[ 'ntsNoShow2' ] = 1;
				$label = M('No Show');
				break;
			}
		}
	else {
		if( $ve['status_approved'] ){
			$cssClasses[ 'ntsApproved2' ] = 1;
			$label = M('Approved');
			}
		else{
			$cssClasses[ 'ntsPending2' ] = 1;
			$label = M('Pending');
			}
		}
	}
else {
	$cssClasses[ 'ntsWorking2' ] = 1;
	$label = M('Available');
	}
$cssClass = join( ' ', array_keys($cssClasses) );
?>

<?php	$dateView = $ve['date']; ?>
<?php	if( ! isset($shownDates[$dateView]) ) : ?>
<tbody>
<tr><td colspan="<?php echo $totalColumns; ?>">
<h3 style="margin: 0 0; padding: 0 0;"><?php echo $dateView; ?></h3>
</td></tr>
</tbody>
<?php	$shownDates[$dateView] = 1; ?>
<?php endif; ?>

<tbody class="nts-ajax-parent">
<tr>

<?php 	reset( $showFields ); ?>
<?php 	foreach( $showFields as $f ) : ?>
<?php
			if( in_array($f, $skipFields) )
				continue;
?>
<?php		if( $f == 'time' ) : ?>
<?php
				if( ($ve['starts_at'] > 0) && isset($ve['time_end']) ){
					$thisView = $ve[$f] . ' - ' . $ve['time_end'];
					}
				else {
					$thisView = $ve[$f];
					}
?>

<td style="width: 10px; padding: 1px 1px; vertical-align: middle;">
<?php if( ($ve['starts_at'] > 0) && (! $linkClass) ) : ?>
<span class="<?php echo $cssClass; ?>" title="<?php echo $label; ?>">&nbsp;</span>
<?php endif; ?>
</td>

<?php if( $linkClass ) : ?>
<td colspan="<?php echo ($totalColumns - 1); ?>" class="<?php echo $linkClass; ?>">
	<?php if( $printView ) : ?>
		<span style="font-weight: bold;"><?php echo $thisView; ?></span>
	<?php else : ?>
		<a href="<?php echo $link; ?>" class="nts-ajax-loader nts-bold" title="<?php echo $label; ?>"><?php echo $thisView; ?></a>
	<?php endif; ?>

	<?php if( isset($ve['_note']) ) : ?>
	<?php echo $ve['_note']; ?>
	<?php endif; ?>
</td>
<?php continue; ?>
<?php endif; ?>

<td style="white-space: nowrap;">
<?php if( $printView ) : ?>
	<span style="font-weight: bold;" title="<?php echo $label; ?>"><?php echo $thisView; ?></span>
<?php else : ?>
	<a href="<?php echo $link; ?>" class="nts-ajax-loader nts-bold" title="<?php echo $label; ?>"><?php echo $thisView; ?></a>
<?php endif; ?>
</td>
<?php 		else : ?>
<?php 			
				if( $linkClass )
					continue;

				if( (count($glue) > 1) && (substr($f, 0, strlen('customer:')) == 'customer:') )
					$ve[$f] = '';
$thisView = isset($ve[$f]) ? $ve[$f] : '';
?>
<td><?php echo $thisView; ?></td>
<?php 		endif; ?>
<?php 	endforeach; ?>
</tr>

<tr>
<td colspan="<?php echo $totalColumns; ?>" style="padding: 0 0 0 1em;">
<div class="nts-ajax-container nts-ajax-return nts-child"></div>
</td>
</tr>

<?php 
// check if we have notes
if( $ve['type'] == 'appointment' )
	$notes = $ve['object']->getProp('_note');
else
	$notes = array();
?>

<?php if( $notes ) : ?>
<tr>
<td></td>
<td colspan="<?php echo ($totalColumns - 1); ?>">
<ul>
<?php foreach( $notes as $note ) : ?>
<?php
		$noteText = $note[0];
		if( $ve['type'] == 'toff' )
		{
			$noteResource = ntsObjectFactory::get('resource');
			$noteResource->setId( $thisRid );
			$noteUserView = ntsView::objectTitle( $noteResource );
		}
		else
		{
			list( $noteTime, $noteUserId ) = explode( ':', $note[1] );
			$noteUser = new ntsUser;
			$noteUser->setId( $noteUserId );
			$noteUserView = ntsView::objectTitle( $noteUser );
		}
?>

<li>
	<?php echo $noteUserView; ?>: <i><?php echo $noteText; ?></i>
</li>
<?php endforeach; ?>
</ul>
</td>
</tr>
<?php endif; ?>


</tbody>
<?php
$glue = array();
$cssClasses = array();
?>
<?php endfor; ?>

</table>

<?php endif; ?>