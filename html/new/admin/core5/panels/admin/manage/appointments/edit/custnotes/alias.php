<?php
$alias = 'admin/customers/edit/notes';
$id = $_NTS['REQ']->getParam( '_id' );

$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$customerId = $object->getProp( 'customer_id' );

$customer = new ntsUser();
$customer->setId( $customerId );
ntsLib::setVar( 'admin/customers/edit::OBJECT', $customer );

ntsView::setBack( ntsLink::makeLink('admin/manage/appointments/edit/custnotes', '', array('_id' => $id)) );

$noteId = $_NTS['REQ']->getParam('noteid');
ntsView::setPersistentParams( array('noteid' => $noteId), 'admin/manage/appointments/edit/custnotes' );
ntsLib::setVar( 'admin/customers/edit/notes/edit::noteId', $noteId );

$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$resourceId = $object->getProp('resource_id');
$iCanEdit = in_array($resourceId, $appEdit) ? true : false;
ntsLib::setVar( 'admin/customers/edit/notes::iCanEdit', $iCanEdit );
?>