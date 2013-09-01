<?php
function ntsThisBuildCalendarValue( $changeId, $changeValue, $mode = 'cal' ){
	global $NTS_VIEW;
	$return = '';
	switch( $mode ){
		case 'cal':
			reset( $NTS_VIEW['cal'] );
			$param = array();
			foreach( $NTS_VIEW['cal'] as $i => $val ){
				$param[] = ( (! $changeId) OR ($i == $changeId) ) ? $changeValue : $val;
				}
			$param = join( '-', $param );
			$return = array( 'cal'	=> $param );
			break;

		case 'custom-dates':
			reset( $NTS_VIEW['custom-dates'] );
			$param = array();
			foreach( $NTS_VIEW['custom-dates'] as $i => $val ){
				if( $changeId < 0 ){
					if( $val == $changeValue )
						continue;
					}
				$param[] = $val;
				}
			if( $changeId > 0 ){
				if( ! in_array($changeValue, $param) )
					$param[] = $changeValue;
				}
			$param = join( '-', $param );
			$return = array( 'custom-dates'	=> $param );
			break;

		case 'recurring':
			if( $NTS_VIEW['recur-every'] ){
				if( ! $NTS_VIEW['recur-from'] )
					$return = array( 'recur-from' => $changeValue, 'cal' => $changeValue );
				else
					$return = array( 'recur-to' => $changeValue );
				}
			else {
				$return = array( 'cal' => $changeValue );
				}
			break;
		}
	return $return;
	}
?>