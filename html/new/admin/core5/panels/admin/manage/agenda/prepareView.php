<?php
$ntsConf =& ntsConf::getInstance();
$customerAcknowledge = $ntsConf->get( 'customerAcknowledge' );

$apps = ntsLib::getVar( 'admin/manage/agenda::apps' );
$daySlotsStart = ntsLib::getVar( 'admin/manage/agenda::daySlotsStart' );
$daySlotsEnd = ntsLib::getVar( 'admin/manage/agenda::daySlotsEnd' );

$t = $NTS_VIEW['t'];
$allFields = array(
	'date'		=> M('Date'),
	'time'		=> M('Time'),
	'duration'	=> M('Duration'),
	'location'	=> M('Location'),
	'resource'	=> M('Bookable Resource'),
	'service'	=> M('Service'),
	'customer'	=> M('Customer'),
	'notes'		=> M('Notes'),
	);

/* custom fields */
$om =& objectMapper::getInstance();
$customFields = $om->getFields( 'appointment', 'internal', array('service_id' => -1) );
reset( $customFields );
$customFieldTypes = array();
foreach( $customFields as $cf ){
	$allFields[ $cf[0] ] = $cf[1];
	$customFieldTypes[$cf[0]] = $cf[2];
	}
	
$customerFields = $om->getFields( 'customer', 'internal' );
$skipCustomerFields = array('first_name', 'last_name');
reset( $customerFields );
foreach( $customerFields as $cf ){
	if( in_array($cf[0], $skipCustomerFields) )
		continue;
	$allFields[ 'customer:' . $cf[0] ] = M('Customer') . '<br>' . $cf[1];
	$customFieldTypes[ 'customer:' . $cf[0] ] = $cf[2];
	}
$allFields[ 'id' ] = 'id';
$allFields[ 'lrst' ] = 'lrst';
$allFields[ 'type' ] = 'type';
$allFields[ 'time_end' ] = 'End Time';
$allFields[ 'status' ] = M('Status');
$allFields[ 'total_amount' ] = M('Total Amount');
$allFields[ 'paid_amount' ] = M('Paid Amount');

$showFieldsTemp = $NTS_CURRENT_USER->getProp('_agenda_fields');
array_unshift( $showFieldsTemp, 'duration' );
array_unshift( $showFieldsTemp, 'time' );
array_unshift( $showFieldsTemp, 'date' );
array_unshift( $showFieldsTemp, 'id' );
array_unshift( $showFieldsTemp, 'type' );
array_unshift( $showFieldsTemp, 'lrst' );
array_unshift( $showFieldsTemp, 'time_end' );
array_unshift( $showFieldsTemp, 'status_completed' );
array_unshift( $showFieldsTemp, 'status_approved' );
array_unshift( $showFieldsTemp, 'starts_at' );

$showFieldsTemp = ntsLib::sortArrayByArray( $showFieldsTemp, array_keys($allFields) );
array_unshift( $showFieldsTemp, 'status' );

$showFields = array();
foreach( $showFieldsTemp as $sh )
	$showFields[$sh] = 1;
if( count($appView) <= 1 )
	unset( $showFields['resource'] );
$showFields = array_keys($showFields);

/* build the view */
$viewEntries = array();
foreach( $apps as $appId ){
	$thisEntry = array();
	
	$a = ntsObjectFactory::get( 'appointment' );
	$a->setId( $appId );
	$customer = new ntsUser();
	$customer->setId( $a->getProp('customer_id') );
	$thisEntry['object'] = $a;

	reset( $showFields );
	foreach( $showFields as $sh ){
		$thisView = '';
		switch( $sh ){
			case 'total_amount':
				$price = $a->getProp('price');
				if( strlen($price) > 0 ){
					$amount = $a->getTotalAmount();
					$thisView = ntsCurrency::formatPrice( $amount );
					}
				else {
					$thisView = '';
					}
				break;

			case 'paid_amount':
				$price = $a->getProp('price');
				if( strlen($price) > 0 ){
					$amount = $a->getPaidAmount();
					$thisView = ntsCurrency::formatPrice( $amount );
					}
				else {
					$thisView = '';
					}
				break;

			case 'status' :
				if( $completed = $a->getProp('completed') ){
					switch( $completed ){
						case HA_STATUS_COMPLETED:
							$thisView = M('Completed');
							if( $customerAcknowledge && (! $a->getProp('_ack')) )
							{
								$thisView .= ', ' . M('Not Acknowledged By Customer');;
							}
							break;
						case HA_STATUS_CANCELLED:
							$thisView = M('Cancelled');
							break;
						case HA_STATUS_NOSHOW:
							$thisView = M('No Show');
							break;
						}
					}
				else {
					if( $a->getProp('approved') ){
						$thisView = M('Approved');
						}
					else {
						$thisView = M('Pending');
						}
					}
				break;

			case 'status_approved' :
				$thisView = $a->getProp('approved');
				break;

			case 'status_completed' :
				$thisView = $a->getProp('completed');
				break;

			case 'type' :
				$service = ntsObjectFactory::get( 'service' );
				$service->setId( $a->getProp('service_id') );
				$thisView = $service->getType();
				break;

			case 'lrst' :
				$thisView = join( '-', array($a->getProp('location_id'), $a->getProp('resource_id'), $a->getProp('service_id'), $a->getProp('starts_at')) );
				break;

			case 'id' :
				$thisView = $appId;
				break;

			case 'date' :
				$startsAt = $a->getProp('starts_at');
				if( $startsAt > 0 ){
					$t->setTimestamp( $startsAt );
					$thisView = $t->formatWeekdayShort() . ', ' . $t->formatDate();
					}
				else {
					$thisView = M('Not Scheduled');
					}
				break;

			case 'time' :
				$startsAt = $a->getProp('starts_at');
				if( $startsAt > 0 ){
					$t->setTimestamp( $startsAt );
					$thisView = $t->formatTime();
					}
				else {
					$thisView = M('Not Scheduled');
					}
				break;

			case 'time_end' :
				$startsAt = $a->getProp('starts_at');
				if( $startsAt > 0 ){
					$endsAt = $startsAt + $a->getProp('duration');
					$t->setTimestamp( $endsAt );
					$thisView = $t->formatTime();
					}
				else {
					$thisView = M('Not Scheduled');
					}
				break;

			case 'duration' :
				$duration = $a->getProp( 'duration' );
				$thisView = ntsTime::formatPeriod( $duration );
				break;

			case 'service' :
				$service = ntsObjectFactory::get( 'service' );
				$service->setId( $a->getProp('service_id') );
				$thisView = ntsView::objectTitle( $service );
				break;

			case 'resource' :
				$resource = ntsObjectFactory::get( 'resource' );
				$resource->setId( $a->getProp('resource_id') );
				$thisView = ntsView::objectTitle( $resource );
				break;

			case 'location' :
				$location = ntsObjectFactory::get( 'location' );
				$location->setId( $a->getProp('location_id') );
				$thisView = ntsView::objectTitle( $location );
				break;

			case 'customer' :
				$thisView = ntsView::objectTitle($customer);
				break;

			case 'starts_at' :
				$thisView = $a->getProp('starts_at');
				break;

			default :
				$thisView = '';
				if( substr($sh, 0, strlen('customer:')) == 'customer:' ){
					$csh = substr( $sh, strlen('customer:') );
					$thisView = $customer->getProp($csh);
					if( isset($customFieldTypes[$sh]) && ($customFieldTypes[$sh] == 'checkbox') ){
						$thisView = $thisView ? M('Yes') : M('No');
						}
					}
				else {
					$thisView = $a->getProp($sh);
					if( $customFieldTypes[$sh] == 'checkbox' ){
						$thisView = $thisView ? M('Yes') : M('No');
						}
					}
				break;
			}

		$thisEntry[ $sh ] = $thisView;
		}

	$thisEntry['notes'] = '';
	if( $thisEntry['type'] == 'appointment' ){
		$notes = $a->getProp('_note');
		if( $notes ){
			$notesView = array();
			foreach( $notes as $note ){
				$noteText = $note[0];
				list( $noteTime, $noteUserId ) = explode( ':', $note[1] );
				$noteUser = new ntsUser;
				$noteUser->setId( $noteUserId );
				$noteUserView = ntsView::objectTitle( $noteUser );
				$notesView[] = $noteUserView . ': ' . $noteText;
				}
			$notesView = join( ', ', $notesView );
			$thisEntry['notes'] = $notesView;
			}
		}

	if( $thisEntry )
		$viewEntries[] = $thisEntry;
	}
?>