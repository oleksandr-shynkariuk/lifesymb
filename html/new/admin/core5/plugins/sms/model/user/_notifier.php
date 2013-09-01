<?php
if( isset($params['_silent']) && $params['_silent'] )
	return;

if( ! NTS_ENABLE_REGISTRATION ){
	if( $mainActionName == 'require_approval' )
		return;
	}

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

require( dirname(__FILE__) . '/_notifier_customer.php' );
?>