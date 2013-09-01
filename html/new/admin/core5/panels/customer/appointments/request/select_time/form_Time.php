<?php
global $NTS_AR;
$t = $NTS_VIEW['t'];
$preferredTime = $NTS_VIEW['preferredTime'];

$addFlowFlow = array( 'time', array() );
$currentIndexes = array_keys( $NTS_VIEW['times'] );
foreach( $currentIndexes as $i ){
	$selected = $NTS_AR->getSelected( $i, 'time' );
	if( $selected ){
		$t->setTimestamp( $selected );
		$view = $t->formatTime();
		}
	else {
		$dayTimes = $NTS_VIEW['times'][$i];
		$timeOptions = array();
		reset( $dayTimes );
		foreach( $dayTimes as $ts ){
			$t->setTimestamp( $ts );
			$timeOptions[] = array( $ts, $t->formatTime() );
			}
		if( $timeOptions ){
			$timeOptions[] = array(0, ' - ' . M('No Appointment') . ' - ');
			}

		$default = 0;
		$defaultMismatch = false;
		if( strlen($preferredTime) && $dayTimes ){
			$t->setTimestamp( $dayTimes[0] );
			$startDay = $t->getStartDay();
			$askedDefault = $startDay + $preferredTime;
			$default = $dayTimes[0];
			$delta = ( $default > $askedDefault ) ? ( $default - $askedDefault ) : ( $askedDefault - $default );

			if( $delta > 0 ){
				$defaultMismatch = true;
				reset( $dayTimes );
				foreach( $dayTimes as $dt ){
					$thisDelta = ( $dt > $askedDefault ) ? ( $dt - $askedDefault ) : ( $askedDefault - $dt );
					if( $thisDelta < $delta ){
						$delta = $thisDelta;
						$default = $dt;
						}
					if( $delta == 0 ){
						$defaultMismatch = false;
						break;
						}
					}
				}
			}

		if( count($timeOptions) <= 0 ){
			$view = '<span class="alert">' . M('No Selectable Times') . '</span>';
			}
		else {
			$view = $this->makeInput (
			/* type */
				'select',
			/* attributes */
				array(
					'id'		=> 'id_' . $i,
					'options'	=> $timeOptions,
					'attr'		=> array(),
					'default'	=> $default,
					),
			/* validators */
				array(
					)
				);
			
			if( $defaultMismatch ){
				$view = '<span class="warning">' . M('Preferred time not available') . '</span><br>' . $view;
				}
			}
		}
	$addFlowFlow[1][] = $view;
	}
$NTS_VIEW['flowFlow'][] = $addFlowFlow;
?>
<?php require( dirname(__FILE__) . '/../common/flow.php' ); ?>

<p>
<?php echo $this->makePostParams('-current-', 'select' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Continue'); ?>">