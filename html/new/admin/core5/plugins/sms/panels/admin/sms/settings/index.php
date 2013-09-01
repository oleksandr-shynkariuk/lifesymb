<?php
$plm =& ntsPluginManager::getInstance();
$defaults = $plm->getPluginSettings( 'sms' );

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile, $defaults );

$form->display();
?>