<?php
if( isset($params['_silent']) && $params['_silent'] )
	return;
	
$conf =& ntsConf::getInstance();
$stm =& ntsSmsTemplateManager::getInstance();
$om =& objectMapper::getInstance();

$lm =& ntsLanguageManager::getInstance();
$defaultLanguage = $lm->getDefaultLanguage();

$plm =& ntsPluginManager::getInstance();

$plugin = 'sms';

/* --- RETURN IF SMS DISABLED --- */
$smsDisabled = $plm->getPluginSetting($plugin, 'disabled');
if( $smsDisabled )
	return;

if( $mainActionName == 'remind' ){
	if( ! $object->getProp( 'really_need_reminder' ) )
		return;
	}

require( dirname(__FILE__) . '/_notifier_customer.php' );
require( dirname(__FILE__) . '/_notifier_provider.php' );
?>