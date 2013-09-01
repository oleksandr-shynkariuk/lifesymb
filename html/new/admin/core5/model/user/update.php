<?php
$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

$userId = $object->getId();

if( defined('NTS_CURRENT_USERID') && (NTS_CURRENT_USERID == $userId) ){
	if( isset($object->props['_agenda_fields']) ){
		$agendaFields = $object->props['_agenda_fields'];
		if( $agendaFields && (! headers_sent()) ){
			$value = serialize($agendaFields);
			setcookie( 'nts_agenda_fields', $value, time() + 365*24*60*60 );
			}
		}
	if( isset($object->props['_calendar_field']) ){
		$calendarField = $object->props['_calendar_field'];
		if( $calendarField && (! headers_sent()) ){
			setcookie( 'nts_calendar_field', $calendarField, time() + 365*24*60*60 );
			}
		}
	if( isset($object->props['_default_calendar']) ){
		$calendarField = $object->props['_default_calendar'];
		if( strlen($calendarField) && (! headers_sent()) ){
			setcookie( 'nts_default_calendar', $calendarField, time() + 365*24*60*60 );
			}
		}
	if( isset($object->props['_default_apps_view']) ){
		$calendarField = $object->props['_default_apps_view'];
		if( strlen($calendarField) && (! headers_sent()) ){
			setcookie( 'nts_default_apps_view', $calendarField, time() + 365*24*60*60 );
			}
		}
	}

list( $objectInfo, $metaInfo ) = $object->getByArray( true, true );

if( isset($metaInfo['new_password']) ){
	$newPassword = $metaInfo['new_password'];
	if( $newPassword ){
		$objectInfo['new_password'] = $newPassword;
		unset( $metaInfo['new_password'] );
		}
	}

$result = $integrator->updateUser( $userId, $objectInfo, $metaInfo );
if( ! $result ){
	$actionResult = 0;
	$actionError = $integrator->getError();
	$actionStop = 1;
	return;
	}

$skipMainTable = true;
?>