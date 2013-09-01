<?php
//echo 'action = ' . $mainActionName . '<br>';

if( isset($params['_silent']) && $params['_silent'] )
	return;

$customerId = $object->getProp('customer_id');
if( ! $customerId )
	return;

$customer = new ntsUser;
$customer->setId( $customerId );
if( $customer->notFound() )
	return;

$conf =& ntsConf::getInstance();
$etm =& ntsEmailTemplateManager::getInstance();
$om =& objectMapper::getInstance();

$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

$lm =& ntsLanguageManager::getInstance();
$defaultLanguage = $lm->getDefaultLanguage();

$ntsdb =& dbWrapper::getInstance();
require( dirname(__FILE__) . '/_notifier_customer.php' );
require( dirname(__FILE__) . '/_notifier_admin.php' );
?>