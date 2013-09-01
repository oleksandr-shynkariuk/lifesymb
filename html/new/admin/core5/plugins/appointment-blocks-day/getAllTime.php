<?php
if( ! $return )
	return;

reset( $return );
$timestamps = array_keys( $return );

$startTs = $timestamps[ 0 ];
if( count($timestamps) == 1 ){
	$endTs = $timestamps[ 0 ];
	}
else {
	$endTs = $timestamps[ count($timestamps) - 1 ];
	}

$this->companyT->setTimestamp( $startTs );
$dayStart = $this->companyT->getStartDay();
$this->companyT->setTimestamp( $endTs );
$dayEnd = $this->companyT->getEndDay();

$where = array(
	'(starts_at + duration + lead_out)'	=> array('>', $dayStart),
	'starts_at - lead_in'				=> array('<', $dayEnd)
	);

// get apps
// temporarily reset location
$appointments = $this->getAppointments( $where );

reset( $appointments );
foreach( $appointments as $a ){
	$thisResId = $a['resource_id'];
	$thisLocId = $a['location_id'];

	$this->companyT->setTimestamp( $a['starts_at'] - $a['lead_in'] );
	$thisStart = $this->companyT->getStartDay();
	$thisDate = $this->companyT->formatDate_Db();

	$this->companyT->setTimestamp( $a['starts_at'] + $a['duration'] + $a['lead_out'] );
	$thisEnd = $this->companyT->getEndDay();

	reset( $return );
	foreach( $return as $ts => $tArray ){
		if( ($ts + $duration + $leadOut) <= $thisStart ){
			continue;
			}
		if( ($ts - $leadIn) >= $thisEnd ){
			continue;
			}

		$tArrayCount = count( $tArray );
		for( $jj = ($tArrayCount - 1); $jj >=0; $jj-- ){
			if(
				isset( $tArray[ $jj ][ $this->SLT_INDX['resource_id'] ] ) && 
				( $tArray[ $jj ][ $this->SLT_INDX['resource_id'] ] == $thisResId )
				)
				{
//				echo "UNSET: app resid = $thisResId, thisresid = " . $tArray[ $jj ][ $this->SLT_INDX['resource_id'] ] . '<br>';
//				echo "app loc id = $thisLocId, thislocid = " . $tArray[ $jj ][ $this->SLT_INDX['location_id'] ] . '<br><br>';
				unset( $tArray[$jj] );
				}
			}

		if( $tArray )
			$return[ $ts ] = $tArray;
		else
			unset( $return[ $ts ] );
		}
	}
?>