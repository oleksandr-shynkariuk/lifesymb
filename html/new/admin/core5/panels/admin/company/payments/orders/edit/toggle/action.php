<?php
$ntsdb =& dbWrapper::getInstance();

$id = $_NTS['REQ']->getParam( 'order_id' );
$object = ntsObjectFactory::get( 'order' );
$object->setId( $id );

$cm =& ntsCommandManager::getInstance();
$isActive = $object->getProp('is_active');
$isActive = $isActive ? 0 : 1;
$object->setProp('is_active', $isActive );
$actionTitle = $isActive ? M('Activate') : M('Disable');

$cm->runCommand( $object, 'update' );

ntsView::setAnnounce( ntsView::objectTitle($object) . ': ' . $actionTitle . ': ' . M('OK'), 'ok' );

/* continue to list */
ntsView::getBack();
exit;
?>