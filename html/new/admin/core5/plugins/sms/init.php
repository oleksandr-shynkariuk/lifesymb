<?php
include_once( dirname(__FILE__) . '/lib/ntsSmsTemplateManager.php' );

$om =& objectMapper::getInstance();
$om->registerProp( 'user',	'mobile_phone',		false,	0,	'' );
?>