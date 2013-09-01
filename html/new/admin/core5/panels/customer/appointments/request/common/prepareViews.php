<?php
global $NTS_AR;
$t = $NTS_VIEW['t'];
$tm2 = $NTS_VIEW['tm2'];
$ntspm =& ntsPaymentManager::getInstance();

if( ! isset($NTS_SHOW_READY) )
	$NTS_SHOW_READY = false;

$flow = array( 'date', 'time', 'resource', 'location', 'service' );
$NTS_VIEW['flowTitles'] = array(
	'order'		=> M('Package'),
	'date'		=> M('Date'),
	'time'		=> M('Time'),
	'service'	=> M('Service'),
	'price'		=> M('Price'),
	'status'	=> M('Availability'),
	'terms'		=> M('Notes'),
	);
if( ! NTS_SINGLE_LOCATION ){
	$NTS_VIEW['flowTitles']['location'] = M('Location');
	}
if( ! NTS_SINGLE_RESOURCE ){
	$NTS_VIEW['flowTitles']['resource'] = M('Bookable Resource');
	}

/* init reschedule */
$resch = $NTS_AR->getReschedule();

$flow = array_keys( $NTS_VIEW['flowTitles'] );
// sort by conf
$conf =& ntsConf::getInstance();
$showEndTime = $conf->get('showEndTime');

$confFlow = $conf->get('appointmentFlow');
$orderFlow = array();
reset( $confFlow );
foreach( $confFlow as $cf ){
	if( $cf[0] == 'time' )
		$orderFlow[] = 'date';
	$orderFlow[] = $cf[0];
	}
$flow = ntsLib::sortArrayByArray( $flow, $orderFlow );

$flow2 = array();

$NTS_VIEW['flowCart'] = 0;
$NTS_VIEW['flowHeader'] = array();
$NTS_VIEW['flowFlow'] = array();

/* APPOINTMENT CART */
if( ! $NTS_SHOW_READY ){
	$readyIndexes = $NTS_AR->getReadyIndexes();
	$NTS_VIEW['flowCart'] = count( $readyIndexes );
	}

$currentIndexes = $NTS_SHOW_READY ? $NTS_AR->getReadyIndexes() : $NTS_AR->getCurrentIndexes();
$reschedule = $NTS_AR->getReschedule();

/* HEADER */
$order = $NTS_AR->getOrder();
if( $order )
{
	$NTS_VIEW['flowHeader'][] = array('order', ntsView::objectTitle($order), NULL, NULL);
}

reset( $flow );
foreach( $flow as $r ){
	if( in_array($r, array('price', 'status', 'terms')) )
		continue;

	if( $reschedule ){
		$optionsCount = 1;
		}
	else {
		$optionsCount = $NTS_SHOW_READY ? $NTS_AR->getCount( $r, 'ready' ) : $NTS_AR->getCount( $r, 'current' );
		}

	if( $optionsCount ){
		$selectedValue = $NTS_AR->getSelectedValue( $currentIndexes[0], $r );

		if( ($optionsCount == 1) && ($selectedValue || $reschedule) ){
			$addFlow = array();
			if( $selectedValue ){
				if( $selectedValue == 'a' ){
					$addFlow = array( $r, ' - ' . M("Don't have a particular preference") . ' - ', 'a' );
					}
				else {
					$selected = $NTS_AR->getSelected( $currentIndexes[0], $r );
					$addFlow = array( $r, ntsPrepareFlowViews($r, $selected, $currentIndexes[0]), $selectedValue );
					}
				}
			elseif( $reschedule ){
				$addFlow = array( $r, '', null );
				}
			if( $reschedule ){
				$selectedReschedule = $NTS_AR->getSelected( -1, $r );
				$addFlow[] = ntsPrepareFlowViews($r, $selectedReschedule, $currentIndexes[0]);
				}
			$NTS_VIEW['flowHeader'][] = $addFlow;
			}
		else
			$flow2[] = $r;
		}
	}

$currentIndexes = $NTS_SHOW_READY ? $NTS_AR->getReadyIndexes() : $NTS_AR->getCurrentIndexes();

/* FLOW */
reset( $flow2 );
foreach( $flow2 as $r )
{
	$thisViews = array();
	$displayThis = false;
	foreach( $currentIndexes as $j )
	{
		$selected = $NTS_AR->getSelected( $j, $r );
		if( $selected )
		{
			$displayThis = TRUE;
			$thisViews[] = ntsPrepareFlowViews($r, $selected, $j);
		}
		else 
		{
			// skip this thing
			$displayThis = FALSE;
			break;
		}
	}
	if( $displayThis )
	{
		$NTS_VIEW['flowFlow'][] = array($r, $thisViews);
	}
}

$availableOrders = array();
if( isset($ready) ){
	if( $checkCustomerId ){
		$availableOrders = $NTS_CURRENT_USER->checkOrders( $ready, $NTS_AR->getOrder() );
		}
	}

/* show prices */
if( $NTS_SHOW_READY && (! $availableOrders) ){
	$thisViews = array();
	$prices = $NTS_AR->getPrices();
	if( $prices ){
		reset( $prices );
		foreach( $prices as $pi ){
			$thisViews[] = ntsPrepareFlowViews( 'price', $pi );
			}
		if( $NTS_VIEW['flowFlow'] ){
			$NTS_VIEW['flowFlow'][] = array('price', $thisViews);
			}
		elseif( $NTS_VIEW['flowHeader'] ) {
			$NTS_VIEW['flowHeader'][] = array('price', $thisViews[0], null, null);
			}
		}
	}

/* show status */
reset( $currentIndexes );
$displayStatus = true;
$thisViews = array();
$selectedNotAvailable = array();

foreach( $currentIndexes as $j ){
	$service = $NTS_AR->getSelected( $j, 'service' );
	$location = $NTS_AR->getSelected( $j, 'location' );
	$resource = $NTS_AR->getSelected( $j, 'resource' );
	$time = $NTS_AR->getSelected( $j, 'time' );

	if( $service && $location && $resource && $time ){
		$tm2->setService( $service->getId() );
		$tm2->setResource( $resource->getId() );
		$tm2->setLocation( $location->getId() );
		$tm2->virtualIndex = $j;
		$nextTimes = $tm2->getNextTimes( $time );

		if( ! in_array($time, $nextTimes) ){
			// not available
			$thisViews[] = ntsPrepareFlowViews('status', '<b class="alert">' . M('Not Available') . '</b>');
			$selectedNotAvailable[] = $j;
			}
		else {
			$thisViews[] = ntsPrepareFlowViews('status', '<b class="ok">' . M('Available') . '</b>' );
			}
		}
	else {
		// skip this thing
		$displayStatus = false;
		break;
		}
	}
	
if( $displayStatus ){
	if( $NTS_VIEW['flowFlow'] ){
		$NTS_VIEW['flowFlow'][] = array('status', $thisViews);
		}
	elseif( $NTS_VIEW['flowHeader'] ) {
		$NTS_VIEW['flowHeader'][] = array('status', $thisViews[0], null, null);
		}
	}

$NTS_VIEW['selectedNotAvailable'] = $selectedNotAvailable;
if( $selectedNotAvailable ){
	$NTS_AR->delete( $selectedNotAvailable );
	}
	
/* show terms */
global $NTS_CURRENT_USER;
if( ($NTS_CURRENT_USER->getId() > 0) && (! ($NTS_CURRENT_USER->hasRole('admin'))) ){
	$checkCustomerId = $NTS_CURRENT_USER->getId();
	}
else {
	$checkCustomerId = 0;
	}

reset( $currentIndexes );
$displayTerms = false;
$thisViews = array();
foreach( $currentIndexes as $j ){
	$service = $NTS_AR->getSelected( $j, 'service' );
	$location = $NTS_AR->getSelected( $j, 'location' );
	$resource = $NTS_AR->getSelected( $j, 'resource' );
	$time = $NTS_AR->getSelected( $j, 'time' );
	$termsView = '';
	$suppliedCoupon = $NTS_AR->getCoupon();

	if( $service && $location && $resource && $time ){
		$termsView = array();
		$rr = array(
			'service'	=> $service->getId(),
			'location'	=> $location->getId(),
			'resource'	=> $resource->getId(),
			'time'		=> $time,
			);
		$price = $ntspm->getPrice( $rr, $suppliedCoupon );

	/* check price */
		if( isset($availableOrders[$j - 1]) && $availableOrders[$j - 1] ){
			$termsView[] = ntsPrepareFlowViews('terms', ntsView::objectTitle($availableOrders[$j - 1]));
			$payAmount = $price;
			}
		else {
			$prepay = $service->getPrepay();
			$payAmount = $ntspm->getPrepayAmount( $rr, $suppliedCoupon );
			
			if( strlen($price) ){
				if( $prepay ){
					if( substr($prepay, -1) == '%' ){
						$prepayView = $prepay;
						}
					else {
						$prepayView = ntsCurrency::formatPrice($prepay);
						}
					$termsView[] = $prepayView . ' ' . M('Prepayment');
					}
				}
			}

	/* check approval */
		$approvalRequired = $service->checkApproval( $checkCustomerId, $payAmount );
		if( $approvalRequired )
			$termsView[] = M('Approval Required');
		else
			$termsView[] = M('Instant Approval');

		if( $termsView )
			$displayTerms = true;
		$termsView = join( ', ', $termsView );
		$thisViews[] = $termsView;
		}
	else {
		// skip this thing
//		$displayTerms = false;
//		break;
		}
	}
if( $displayTerms ){
	if( $NTS_VIEW['flowFlow'] ){
		$NTS_VIEW['flowFlow'][] = array('terms', $thisViews);
		}
	elseif( $NTS_VIEW['flowHeader'] ) {
		$NTS_VIEW['flowHeader'][] = array('terms', $thisViews[0], null, null);
		}
	}

function ntsPrepareFlowViews( $what, $selected, $ri = 0 ){
	global $NTS_VIEW, $NTS_AR;
	$conf =& ntsConf::getInstance();
	$showEndTime = $conf->get('showEndTime');

	switch( $what ){
		case 'location':
		case 'resource':
		case 'service':
			$view = ntsView::objectTitle( $selected );
			break;
		case 'price':
			if( $selected[3] != $selected[0] ) // price modification
			{
				$basePrice = '<span style="text-decoration: line-through;">' . ntsCurrency::formatServicePrice($selected[3]) . '</span>'; 
				$view = $basePrice . ' ' . ntsCurrency::formatServicePrice($selected[0]);
				if( $selected[4] ) // promotions
				{
					$tooltipInfo = array();
					reset( $selected[4] );
					foreach( $selected[4] as $pro )
					{
						$tooltipInfo[] = $pro->getModificationView() . ': ' . $pro->getTitle();
					}
					$tooltipInfo = join( '; ', $tooltipInfo );
					$view .= ' <a class="nts-tooltip" title="' . $tooltipInfo . '"><span title="?"> ? </span></a>';
				}
			}
			else
			{
				$view = ntsCurrency::formatServicePrice($selected[0]);
			}
			break;
		case 'time':
			$t = $NTS_VIEW['t'];
			$t->setTimestamp( $selected );
			if( $showEndTime && $ri ){
				$service = $NTS_AR->getSelected( $ri, 'service' );
				if( $service ){
					$duration = $service->getProp('duration');
					$view = $t->formatTime( $duration );
					}
				else {
					$view = $t->formatTime();
					}
				}
			else {
				$view = $t->formatTime();
				}
			break;
		case 'date':
			$t = $NTS_VIEW['t'];
			$t->setDateDb( $selected ); 
			$view = $t->formatWeekdayShort() . ', ' . $t->formatDate();
			break;
		default:
			$view = $selected;
		}
	return $view;
	}
?>