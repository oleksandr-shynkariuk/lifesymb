<?php
$myTimeUnit = defined('NTS_TIME_UNIT') ? NTS_TIME_UNIT : 1;

/* should be sorted desc */
$multiplier = array(
	'h'	=> 60 * 60,
	'm'	=> 60,
	);

switch( $inputAction ){
	case 'display':
		$id_Qty_Hours = $conf['id'] . '_qty_hour';
		$id_Qty_Minutes = $conf['id'] . '_qty_min';

	/* find multiplier first */
		$remainValue = $conf['value'];
		$qty_Hours = 0;
		$qty_Minutes = 0;

		if( $remainValue >= $multiplier['h'] ){
			$qty_Hours = floor( $remainValue / $multiplier['h'] );
			$remainValue = $remainValue - $qty_Hours * $multiplier['h'];
			}

		if( $remainValue >= $multiplier['m'] ){
			$qty_Minutes = floor( $remainValue / $multiplier['m'] );
			$remainValue = $remainValue - $qty_Hours * $multiplier['m'];
			}

	// QTY CONTROL
		$hoursOptions = array();
		for( $i = 0; $i <= 24; $i++ )
			$hoursOptions[] = array( $i, sprintf('%02d', $i) );

		$minutesOptions = array();
		for( $i = 0; $i <= 59; $i+=$myTimeUnit )
			$minutesOptions[] = array( $i, sprintf('%02d', $i) );

		$input .= '<span style="white-space: nowrap;">';
		$input .= $this->makeInput(
			'select',
			array(
				'id'		=> $id_Qty_Hours,
				'options'	=> $hoursOptions,
				'default'	=> $qty_Hours,
				)
			);
		$input .= ':';

		$input .= $this->makeInput(
			'select',
			array(
				'id'		=> $id_Qty_Minutes,
				'options'	=> $minutesOptions,
				'default'	=> $qty_Minutes,
				)
			);
		$input .= '</span>';
		break;

	case 'submit':
		$id_Qty_Hours = $handle . '_qty_hour';
		$id_Qty_Minutes = $handle . '_qty_min';

		$submittedValue_Hours = $_NTS['REQ']->getParam( $id_Qty_Hours );
		$submittedValue_Minutes = $_NTS['REQ']->getParam( $id_Qty_Minutes );

		$input = $multiplier['h'] * $submittedValue_Hours + $multiplier['m'] * $submittedValue_Minutes;
		break;

	case 'check_submit':
		$id_Qty_Hours = $handle . '_qty_hour';
		$id_Qty_Minutes = $handle . '_qty_min';

		$input = (isset($_POST[$id_Qty_Hours]) && isset($_POST[$id_Qty_Minutes])) ? true : false;
		break;
	}
?>