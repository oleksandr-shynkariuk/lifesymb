<?php
switch( $name ){
	case 'taxRate':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'taxTitle':
		if( strlen($rawValue) == 0 )
			$return = 'Tax';
		else
			$return = $rawValue;
		break;

	case 'taxInclude':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'htmlTitle':
		if( ! isset($this->rawValues[$name]) )
			$return = 'Appointment Scheduler';
		else
			$return = $rawValue;
		break;

	case 'remindOfBackup':
		if( strlen($rawValue) == 0 )
			$return = 7 * 24 * 60 * 60;
		else
			$return = $rawValue;
		break;

	case 'backupLastRun':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'attachIcal':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'remindBefore':
		if( ! $rawValue )
			$return = 3600;
		else
			$return = $rawValue;
		break;

	case 'autoComplete':
		if( ! $rawValue )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'autoReject':
		if( ! $rawValue )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'disabledNotifications':
		if( ! $rawValue ){
			$return = array();
			}
		else
			$return = $rawValue;
		break;

	case 'currency':
		if( ! $rawValue ){
			$return = 'usd';
			}
		break;

	case 'userEmailConfirmation':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'emailAsUsername':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'allowNoEmail':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'userAdminApproval':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'userLoginRequired':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'enableRegistration':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'enableTimezones':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'emailSentFrom':
		if( ! $rawValue ){
			$return = 'your@email.here';
			}
		break;

	case 'emailSentFromName':
		if( ! $rawValue ){
			$return = 'Automated Mailer';
			}
		break;

	case 'emailDebug':
		if( ! $rawValue ){
			$return = 0;
			}
		break;

	case 'disablePrice':
		if( ! $rawValue )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'priceFormat':
		if( ! $rawValue ){
			$return = array( '$', '.', ',', '' );
			}
		else
			$return = explode( '||', $rawValue );
		break;

	case 'languages':
		if( ! $rawValue ){
			$return = array( 'en-builtin' );
			}
		else
			$return = explode( '||', $rawValue );
		break;

	case 'weekStartsOn':
		if( ! strlen($rawValue) )
			$return = 0;
		elseif( $rawValue == 7 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'dateFormat':
		if( ! $rawValue )
			$return = 'j M Y';
		else
			$return = $rawValue;
		break;

	case 'timeFormat':
		if( ! $rawValue )
			$return = 'g:i A';
		else
			$return = $rawValue;
		break;

	case 'companyTimezone':
		if( ! $rawValue )
			$return = 'America/Los_Angeles';
		break;

	case 'theme':
		if( ! $rawValue )
			$return = 'default';
		break;

	case 'appointmentFlow':
		if( ! $rawValue ){
			$return = array(
				array( 'service',	'manual' ),
				array( 'seats',		'manual' ),
				array( 'time',		'manual' ),
				array( 'location',	'manual' ),
				array( 'resource',	'manual' ),
				);
			}
		else {
			$raw = explode( '|', $rawValue );
			reset( $raw );
			$return = array();
			foreach( $raw as $rr ){
				$r = explode( ':', $rr ); 
				$return[] = $r;
				if( $r[0] == 'service' )
					$return[] = array( 'seats', 'manual' );
				}
			}
		break;

	case 'paymentGateways':
		if( ! $rawValue ){
			$return = array();
			}
		else
			$return = explode( '||', $rawValue );
		break;

	case 'plugins':
		if( ! $rawValue ){
			$return = array();
			}
		else
			$return = explode( '||', $rawValue );
		break;

	case 'monthsToShow':
		if( ! $rawValue )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'monthsToShowAdmin':
		if( ! $rawValue )
			$return = 3;
		else
			$return = $rawValue;
		break;

	case 'daysToShowCustomer':
		if( ! $rawValue )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'limitTimeMeasure':
		if( ! $rawValue )
			$return = 'hour';
		else
			$return = $rawValue;
		break;

	case 'csvDelimiter':
		if( ! $rawValue )
			$return = ',';
		else
			$return = $rawValue;
		break;

	case 'showSessionDuration':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'showEndTime':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'requireCancelReason':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'selectStyle':
		if( ! $rawValue )
			$return = 'list';
		break;

	case 'sosCode':
		if( ! $rawValue )
			$return = '';
		break;

	case 'allowDuplicateEmails':
		if( ! $rawValue )
			$return = 0;
		break;

	case 'timeUnit':
		if( ! $rawValue )
			$return = 15;
		break;

	case 'timeStarts':
		if( ! strlen($rawValue) )
			$return = 9 * 60 * 60;
		break;

	case 'appsInCart':
		if( ! strlen($rawValue) )
			$return = 3;
		break;

	case 'timeEnds':
		if( ! $rawValue )
			$return = 18 * 60 * 60;
		break;

	case 'useCaptcha':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'strongPassword':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'showCompletedAppsAdmin':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'sendCcForAppointment':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'autoActivatePackage':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'customerCanCancel':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'customerCanReschedule':
		if( strlen($rawValue) == 0 )
			$return = 1;
		else
			$return = $rawValue;
		break;

	case 'customerAcknowledge':
		if( strlen($rawValue) == 0 )
			$return = 0;
		else
			$return = $rawValue;
		break;

	case 'invoiceHeader':
		if( ! isset($this->rawValues[$name]) )
			$return =<<<EOT
<strong>Our Company</strong>
Our Address
http://www.oursite.com
EOT;
		else
			$return = $rawValue;
		break;
	}
?>