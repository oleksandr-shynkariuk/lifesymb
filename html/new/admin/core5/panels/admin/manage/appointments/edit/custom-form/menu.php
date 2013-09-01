<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$serviceId = $object->getProp( 'service_id' );

$om =& objectMapper::getInstance();
$formId = $om->isFormForService( $serviceId );

if( $formId ){
	$form = ntsObjectFactory::get( 'form' );
	$form->setId( $formId );
	$formTitle = $form->getProp('title');

	$title = M('Custom Form') . ': ' . $formTitle;
	$sequence = 15;
	}
?>