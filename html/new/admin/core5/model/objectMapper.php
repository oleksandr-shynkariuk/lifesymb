<?php
include_once( NTS_BASE_DIR . '/lib/ntsObjectMapper.php' );

class objectMapper extends ntsObjectMapper {
	function objectMapper(){
		$conf =& ntsConf::getInstance();

		parent::ntsObjectMapper();
	/* registerProp: (className, pName, isCore, isArray, default) */
		$this->registerClass( 'user', 'users' );
		if( ! NTS_EMAIL_AS_USERNAME )
			$this->registerProp( 'user',	'username' );
		$this->registerProp( 'user',	'email' );
		$this->registerProp( 'user',	'password' );
		$this->registerProp( 'user',	'first_name' );
		$this->registerProp( 'user',	'last_name' );
		$this->registerProp( 'user',	'lang' );
		$this->registerProp( 'user',	'created' );
	/* meta */
		$this->registerProp( 'user',	'_role',				false,	1,	array('customer') );
		$this->registerProp( 'user',	'_disabled_panels',		false,	1,	array() );
		$this->registerProp( 'user',	'_resource_schedules',	false,	2,	array() ); // array( '2' => 'edit', '1' => 'view', '4' => 'none' );
		$this->registerProp( 'user',	'_resource_apps',		false,	2,	array() ); // array( '2' => 4 ) // view=1, edit=2, notified=4

		$this->registerProp( 'user',	'_restriction',	false,	1,	array() );
		$this->registerProp( 'user',	'_timezone',	false,	0,	NTS_COMPANY_TIMEZONE );
		$this->registerProp( 'user',	'_lang',		false,	0,	'' );
		$this->registerProp( 'user',	'_auth_code',	false,	0,	'' );
		$this->registerProp( 'user',	'_created_by',	false,	0,	0 );

		$this->registerProp( 'user',	'_note',				false,	1,	array() );
		$this->registerProp( 'user',	'_agenda_fields',		false,	1,	array('service', 'customer') );
		$this->registerProp( 'user',	'_calendar_field',		false,	0,	'' );
		$this->registerProp( 'user',	'_default_calendar',	false,	0,	0 );
		$this->registerProp( 'user',	'_default_apps_view',	false,	0,	'calendar' );

		$this->registerClass( 'form', 'forms' );
		$this->registerProp( 'form',	'title' );
		$this->registerProp( 'form',	'class' );
		$this->registerProp( 'form',	'details' );

		$this->registerClass( 'form_control', 'form_controls' );
		$this->registerProp( 'form_control',	'form_id' );
		$this->registerProp( 'form_control',	'name' );
		$this->registerProp( 'form_control',	'type' );
		$this->registerProp( 'form_control',	'title' );
		$this->registerProp( 'form_control',	'description' );
		$this->registerProp( 'form_control',	'show_order' );
		$this->registerProp( 'form_control',	'ext_access' );
		$this->registerProp( 'form_control',	'attr' );
		$this->registerProp( 'form_control',	'validators' );
		$this->registerProp( 'form_control',	'default_value' );

		$this->registerClass( 'service', 'services' );
		$this->registerProp( 'service',	'title' );
		$this->registerProp( 'service',	'description' );
		$this->registerProp( 'service',	'recur_total', true, 0, 1 );
		$this->registerProp( 'service',	'recur_options', true, 0, 'd-2d-w' );
		$this->registerProp( 'service',	'min_cancel',	true, 0, 1 * 24 * 60 * 60 ); // 1 day
		$this->registerProp( 'service',	'show_order' );
		$this->registerProp( 'service',	'class_type', true, 0, 0 );
		$this->registerProp( 'service',	'blocks_location', true, 0, 0 );
		$this->registerProp( 'service',	'duration' );
		$this->registerProp( 'service',	'lead_in' );
		$this->registerProp( 'service',	'lead_out' );
		$this->registerProp( 'service',	'price' );
		$this->registerProp( 'service',	'prepay' );
		$this->registerProp( 'service',	'return_url' );
		$this->registerProp( 'service',	'pack_only',	true, 0, 0 );
		$this->registerProp( 'service',	'allow_queue', true, 0, 0 );
		$this->registerProp( 'service',	'_permissions', false,	1,	array( 'group-1:allowed', 'group0:auto_confirm', 'group-2:auto_confirm' ) );
		$this->registerProp( 'service',	'_form', false,	false,	0 );
		$this->registerProp( 'service',	'_service_cat', false,	1,	array() );
		$this->registerProp( 'service',	'_auto_reject', false,	0,	'' );
		$this->registerProp( 'service',	'_package',	false,	2,	array() ); // array( '5' => '25', '10' => 45 );

		$this->registerClass( 'service_cat', 'service_cats' );
		$this->registerProp( 'service_cat',	'title' );
		$this->registerProp( 'service_cat',	'description' );
		$this->registerProp( 'service_cat',	'show_order' );

		$this->registerClass( 'location', 'locations' );
		$this->registerProp( 'location',	'title' );
		$this->registerProp( 'location',	'description' );
		$this->registerProp( 'location',	'show_order' );
		$this->registerProp( 'location',	'capacity',	true, 0, 0 );
		$this->registerProp( 'location',	'_travel',	false,	2,	array() );

		$this->registerClass( 'resource', 'resources' );
		$this->registerProp( 'resource',	'title' );
		$this->registerProp( 'resource',	'description' );
		$this->registerProp( 'resource',	'show_order' );
		$this->registerProp( 'resource',	'_internal',	false,	0,	0 );
		$this->registerProp( 'resource',	'_restriction',	false,	1,	array() );
		$this->registerProp( 'resource',	'_disabled_location',	false,	1,	array() );
		$this->registerProp( 'resource',	'_disabled_service',	false,	1,	array() );

		$this->registerClass( 'timeoff', 'timeoffs' );
		$this->registerProp( 'timeoff',	'location_id' );
		$this->registerProp( 'timeoff',	'resource_id' );
		$this->registerProp( 'timeoff',	'starts_at' );
		$this->registerProp( 'timeoff',	'ends_at' );
		$this->registerProp( 'timeoff',	'description' );

		$this->registerClass( 'appointment', 'appointments' );
		$this->registerProp( 'appointment',	'service_id' );
		$this->registerProp( 'appointment',	'resource_id' );
		$this->registerProp( 'appointment',	'customer_id' );
		$this->registerProp( 'appointment',	'location_id' );
		$this->registerProp( 'appointment',	'seats', true, 0, 1 );
		$this->registerProp( 'appointment',	'created_at' );
		$this->registerProp( 'appointment',	'starts_at' );
		$this->registerProp( 'appointment',	'duration' );
		$this->registerProp( 'appointment',	'lead_in' );
		$this->registerProp( 'appointment',	'lead_out' );
		$this->registerProp( 'appointment',	'approved', true, 0, 0 );
		$this->registerProp( 'appointment',	'completed', true, 0, 0 );

		$this->registerProp( 'appointment',	'auth_code' );
		$this->registerProp( 'appointment',	'need_reminder', true, 0, 0 );
		$this->registerProp( 'appointment',	'price' );

		$this->registerProp( 'appointment',	'_note',	false,	3,	array() );
		$this->registerProp( 'appointment',	'_order',	false,	1,	array() );
		$this->registerProp( 'appointment',	'_cc',		false,	1,	array() );
		$this->registerProp( 'appointment',	'_promotion',	false,	2, array() );
		$this->registerProp( 'appointment',	'_ack',	false,	0, 0 ); // customer acknowledge

		$this->registerClass( 'order', 'orders' );
		$this->registerProp( 'order',	'created_at' );
		$this->registerProp( 'order',	'is_active', true, false, 0 );
		$this->registerProp( 'order',	'valid_from' );
		$this->registerProp( 'order',	'valid_to' );
		$this->registerProp( 'order',	'pack_id' );
		$this->registerProp( 'order',	'customer_id' );

		$this->registerProp( 'order',	'location_id' );
		$this->registerProp( 'order',	'resource_id' );
		$this->registerProp( 'order',	'service_id' );

		$this->registerProp( 'order',	'qty' );
		$this->registerProp( 'order',	'amount' );
		$this->registerProp( 'order',	'duration' );
		$this->registerProp( 'order',	'rule' );

		$this->registerClass( 'invoice', 'invoices' );
		$this->registerProp( 'invoice',	'refno' );
		$this->registerProp( 'invoice',	'amount' );
		$this->registerProp( 'invoice',	'currency' );
		$this->registerProp( 'invoice',	'created_at' );
		$this->registerProp( 'invoice',	'due_at' );
		$this->registerProp( 'invoice',	'_appointment',	false,	2,	array() );
		$this->registerProp( 'invoice',	'_order',	false,	2,	array() );
		$this->registerProp( 'invoice',	'_item',	false,	2,	array() );
		$this->registerProp( 'invoice',	'_tax',	false,	2,	array() );
		$this->registerProp( 'invoice',	'_discount',	false,	2,	array() );

		$this->registerClass( 'transaction', 'transactions' );
		$this->registerProp( 'transaction',	'from_account' );
		$this->registerProp( 'transaction',	'to_account' );
		$this->registerProp( 'transaction',	'amount' );
		$this->registerProp( 'transaction',	'amount_net' );
		$this->registerProp( 'transaction',	'invoice_id' );
		$this->registerProp( 'transaction',	'created_at' );
		$this->registerProp( 'transaction',	'pgateway' );
		$this->registerProp( 'transaction',	'pgateway_ref' );
		$this->registerProp( 'transaction',	'pgateway_response' );

		$this->registerClass( 'pack', 'packs' );
		$this->registerProp( 'pack',	'title' );
		$this->registerProp( 'pack',	'service_id' );
		$this->registerProp( 'pack',	'location_id' );
		$this->registerProp( 'pack',	'resource_id' );
		$this->registerProp( 'pack',	'qty' );
		$this->registerProp( 'pack',	'duration' );
		$this->registerProp( 'pack',	'amount' );
		$this->registerProp( 'pack',	'price' );
		$this->registerProp( 'pack',	'expires_in' );
		$this->registerProp( 'pack',	'show_order' );
		$this->registerProp( 'pack',	'rule' );

		$this->registerClass( 'promotion', 'promotions' );
		$this->registerProp( 'promotion', 'rule' );
		$this->registerProp( 'promotion', 'price' );
		$this->registerProp( 'promotion', 'title' );

		$this->registerClass( 'coupon', 'coupons' );
		$this->registerProp( 'coupon', 'code' );
		$this->registerProp( 'coupon', 'use_limit' );
		$this->registerProp( 'coupon', 'promotion_id' );
		}

	function makeTags_Appointment( $object, $access = 'external' ){
		$conf =& ntsConf::getInstance();
		$showSessionDuration = $conf->get('showSessionDuration');
		$showEndTime = $conf->get('showEndTime');

		$enableTimezones = $conf->get('enableTimezones');
		$changes = $object->getChanges();

		$allInfo = '';

		/* time */
		$customerId = $object->getProp( 'customer_id' );
		$customer = new ntsUser();
		$customer->setId( $customerId );

		$resourceId = $object->getProp( 'resource_id' );
		$resource = ntsObjectFactory::get( 'resource' );
		$resource->setId( $resourceId );

		$ts = $object->getProp('starts_at');
		$t = new ntsTime( $ts );
		if( $access == 'external' )
			$t->setTimezone( $customer->getProp('_timezone') );

		$showTimezone = ( $enableTimezones == -1 ) ? 0 : 1;

		if( $ts > 0 ){
			if( $showEndTime ){
				$timeFormatted = $t->formatDate() . ' ' . $t->formatTime($object->getProp('duration'), $showTimezone);
				if( isset($changes['duration']) && (! isset($changes['starts_at'])) ){
					$t->setTimestamp( $object->getProp('starts_at') );
					$oldTimeFormatted = $t->formatDate() . ' ' . $t->formatTime($changes['duration'][1], $showTimezone);
					$timeFormatted .= ' (' . M('Old') . ': ' . $oldTimeFormatted . ')';
					}
				}
			else
				$timeFormatted = $t->formatDate() . ' ' . $t->formatTime(0, $showTimezone);
			}
		else {
			$timeFormatted = M('Not Scheduled');
			}

		if( isset($changes['starts_at']) ){
			$t->setTimestamp( $changes['starts_at'][1] );
			$oldTimeFormatted = $t->formatDate() . ' ' . $t->formatTime();
			$timeFormatted .= ' (' . M('Old') . ': ' . $oldTimeFormatted . ')';
			}

		$tags[0][] = '{APPOINTMENT.STARTS_AT}';
		$tags[1][] = $timeFormatted;
		$allInfo .= $timeFormatted . "\n";

		/* service */
		$serviceView = ntsView::appServiceView( $object, TRUE );
		if( isset($changes['service_id']) ){
			$oldService = ntsObjectFactory::get('service');
			$oldService->setId( $changes['service_id'][1] );
			$oldServiceView = ntsView::objectTItle( $oldService );
			$serviceView .= ' (' . M('Old') . ': ' . $oldServiceView . ')';
			}

		$tags[0][] = '{APPOINTMENT.SERVICE}';
		$tags[1][] = $serviceView;
		$allInfo .= $serviceView . "\n";

	/* add service description */
		$service = new ntsObject( 'service' );
		$service->setId( $object->getProp('service_id') );
		$tags[0][] = '{APPOINTMENT.SERVICE.DESCRIPTION}';
		$tags[1][] = $service->getProp('description');

		$tags[0][] = '{APPOINTMENT.SEATS}';
		$tags[1][] = $object->getProp('seats');

	/* price */
		$invoices = $object->getInvoices();
		if( $invoices )
		{
			$price = $object->getTotalAmount();
			$amount = $object->getPaidAmount();
			$paidView = ntsCurrency::formatPrice( $amount );
		}
		else
		{
			$price = $object->getProp('price');
			$paidView = '';
		}

		$priceView = ntsCurrency::formatServicePrice($price);
		if( strlen($priceView) )
		{
			$allInfo .= M('Price') . ': ' . $priceView . "\n";
		}
		else
		{
			$priceView = M('N/A');
		}

		if( $paidView )
		{
			$allInfo .= M('Paid Amount') . ': ' . $paidView . "\n";
		}

		$tags[0][] = '{APPOINTMENT.PRICE}';
		$tags[1][] = $priceView;
		$tags[0][] = '{APPOINTMENT.PAID_AMOUNT}';
		$tags[1][] = $paidView;

	/* status */
		if( $completed = $object->getProp('completed') ){
			switch( $completed ){
				case HA_STATUS_COMPLETED:
					$statusView = M('Completed');
					break;
				case HA_STATUS_CANCELLED:
					$statusView = M('Cancelled');
					break;
				case HA_STATUS_NOSHOW:
					$statusView = M('No Show');
					break;
				}
			}
		else {
			if( $object->getProp('approved') ){
				$statusView = M('Approved');
				}
			else {
				$statusView = M('Pending');
				}
			}
		
		$tags[0][] = '{APPOINTMENT.STATUS}';
		$tags[1][] = $statusView;
		$allInfo .= M('Status') . ': ' . $statusView . "\n";

	/* location */
		$locationId = $object->getProp( 'location_id' );
		$location = new ntsObject( 'location' );
		$location->setId( $locationId );
		$locationTitle = ntsView::objectTitle($location);
		if( isset($changes['location_id']) ){
			$oldLocation = ntsObjectFactory::get('location');
			$oldLocation->setId( $changes['location_id'][1] );
			$oldLocationView = ntsView::objectTItle( $oldLocation );
			$locationTitle .= ' (' . M('Old') . ': ' . $oldLocationView . ')';
			}

		$tags[0][] = '{APPOINTMENT.LOCATION}';
		$tags[1][] = $locationTitle;
		$tags[0][] = '{APPOINTMENT.LOCATION.DESCRIPTION}';
		$tags[1][] = $location->getProp('description');

		if( ! NTS_SINGLE_LOCATION ){
			$allInfo .= M('Location') . ': ' . $locationTitle . "\n";
			}

		/* resource */
		$resourceTitle = $resource->getProp('title');
		if( isset($changes['resource_id']) ){
			$oldResource = ntsObjectFactory::get('resource');
			$oldResource->setId( $changes['resource_id'][1] );
			$oldResourceView = ntsView::objectTItle( $oldResource );
			$resourceTitle .= ' (' . M('Old') . ': ' . $oldResourceView . ')';
			}

		$tags[0][] = '{APPOINTMENT.RESOURCE}';
		$tags[1][] = $resourceTitle;
		$tags[0][] = '{APPOINTMENT.RESOURCE.DESCRIPTION}';
		$tags[1][] = $resource->getProp('description');

		if( ! NTS_SINGLE_RESOURCE ){
			$allInfo .= M('Bookable Resource') . ': ' . $resourceTitle . "\n";
			}

		/* order */
		$orderInfo = '';
		$order = $object->getOrder();
		if( $order ){
			$orderInfo = ntsView::objectTitle($order) .': ' . $order->getUsageText();
			}
		$tags[0][] = '{APPOINTMENT.PACKAGE_ORDER}';
		$tags[1][] = $orderInfo;
		if( $order ){
			$allInfo .= $orderInfo . "\n";
			}

		/* customer */
		if( $access == 'external' ){
			$fields = $this->getFields( 'customer', 'external' );
			}
		else {
			$fields = $this->getFields( 'customer', 'internal' );
			}
		$customerId = $object->getProp( 'customer_id' );
		$customer = new ntsUser();
		$customer->setId( $customerId );

		$allCustomerInfo = '';
		foreach( $fields as $f ){
			$value = $customer->getProp( $f[0] );
			if( $f[2] == 'checkbox' ){
				$value = $value ? M('Yes') : M('No');
				}

			$tags[0][] = '{APPOINTMENT.CUSTOMER.' . strtoupper($f[0]) . '}';
			$tags[1][] = $value;

			$allCustomerInfo .= M($f[1]) . ': ' . $value . "\n";
			}
		$tags[0][] = '{APPOINTMENT.CUSTOMER.-ALL-}';
		$tags[1][] = $allCustomerInfo;

		/* custom fields */
		$om =& objectMapper::getInstance();
		$otherDetails = array(
			'service_id'	=> $object->getProp('service_id'),
			);

		$fields = $om->getFields( 'appointment', $access, $otherDetails );
		reset( $fields );
		foreach( $fields as $fArray ){
			$value = $object->getProp($fArray[0]);
			if( $fArray[2] == 'checkbox' ){
				$value = $value ? M('Yes') : M('No');
				}

			$c = $this->getControl( 'appointment', $fArray[0], false );
			if( $c[2]['description'] ){
				$value .= ' (' . $c[2]['description'] . ')';
				}

			$allInfo .= $fArray[1] . ': ' . $value . "\n";

			$tags[0][] = '{APPOINTMENT.' . strtoupper($fArray[0]) . '}';
			$tags[1][] = $value;
			}

		$tags[0][] = '{APPOINTMENT.-ALL-}';
		$tags[1][] = $allInfo;
		return $tags;
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'objectMapper' );
		}
	}
?>
