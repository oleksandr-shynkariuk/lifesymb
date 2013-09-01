<?php
$period = ntsLib::getVar( 'admin/manage/agenda:period' );
$tm2 = ntsLib::getVar( 'admin::tm2' );

$conf =& ntsConf::getInstance();
$showCompleted = $conf->get( 'showCompletedAppsAdmin' );
if( $showCompleted ){
	$startWhere['completed'] = array( '<>', HA_STATUS_CANCELLED );
	$startWhere['completed '] = array( '<>', HA_STATUS_NOSHOW );
	}
else {
	$startWhere = array();
	}

$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

$periodOptions = array();

// day
$NTS_VIEW['t']->setDateDb( $cal );
$from = $NTS_VIEW['t']->getStartDay();
$title = $NTS_VIEW['t']->formatWeekdayShort() . ', ' . $NTS_VIEW['t']->formatDate();

$to = $NTS_VIEW['t']->getEndDay();
$where = array(
	'(starts_at+duration+lead_out)'	=> array( '>=', $from ),
	'(starts_at-lead_in)'			=> array( '<', $to ),
	'location_id'					=> array( 'IN', $locs ),
	'resource_id'					=> array( 'IN', $ress ),
	'service_id'					=> array( 'IN', $sers )
	);
$where = array_merge( $where, $startWhere );
$count = $tm2->countAppointments( $where );

$periodOptions['day'] = $title . ' [' . $count . ']';

// month
$NTS_VIEW['t']->setStartMonth();
$title = $NTS_VIEW['t']->getMonthName() . ' ' . $NTS_VIEW['t']->getYear();
$from = $NTS_VIEW['t']->getTimestamp();
$NTS_VIEW['t']->setEndMonth();
$to = $NTS_VIEW['t']->getTimestamp();
$where = array(
	'(starts_at+duration+lead_out)'	=> array( '>=', $from ),
	'(starts_at-lead_in)'			=> array( '<', $to ),
	'location_id'					=> array( 'IN', $locs ),
	'resource_id'					=> array( 'IN', $ress ),
	'service_id'					=> array( 'IN', $sers )
	);
$where = array_merge( $where, $startWhere );
$count = $tm2->countAppointments( $where );
$periodOptions['month'] = $title . ' [' . $count . ']';

// all upcoming
$NTS_VIEW['t']->setNow();
$from = $NTS_VIEW['t']->getStartDay();
$NTS_VIEW['t']->modify( '+1 year' );
$to = $NTS_VIEW['t']->getEndDay();
$where = array(
	'(starts_at+duration+lead_out)'	=> array( '>=', $from ),
	'(starts_at-lead_in)'			=> array( '<', $to ),
	'location_id'					=> array( 'IN', $locs ),
	'resource_id'					=> array( 'IN', $ress ),
	'service_id'					=> array( 'IN', $sers )
	);
$where = array_merge( $where, $startWhere );
$count = $tm2->countAppointments( $where );
$periodOptions['upcoming'] = M('All Upcoming') . ' [' . $count . ']';

// all
$where = array(
	'location_id'	=> array( 'IN', $locs ),
	'resource_id'	=> array( 'IN', $ress ),
	'service_id'	=> array( 'IN', $sers )
	);
$where = array_merge( $where, $startWhere );
$count = $tm2->countAppointments( $where );
$periodOptions['all'] = M('All') . ' [' . $count . ']';

// all pending
$where = array(
	'approved'		=> array( '=', 0 ),
	'location_id'	=> array( 'IN', $locs ),
	'resource_id'	=> array( 'IN', $ress ),
	'service_id'	=> array( 'IN', $sers )
	);
$where = array_merge( $where, $startWhere );
$count = $tm2->countAppointments( $where );
if( $count > 0 )
	$periodOptions['pending'] = M('Pending') . ' [' . $count . ']';

reset( $periodOptions );

if( $printView )
{
	$menu3[] = array(
		'',
		$periodOptions[$period],
		TRUE
		);
}
else
{
	foreach( $periodOptions as $k => $title )
	{
		if( $period == $k )
		{
			$menu3[] = array(
				'',
				$title,
				TRUE
				);
		}
		else
		{
			$menu3[] = array(
				ntsLink::makeLink('-current-', '', array('period' => $k)),
				$title,
				FALSE
				);
		}
	}
}
?>