<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$objId = $object->getId();

ntsView::setBack( ntsLink::makeLink('admin/manage/appointments/edit/notes', '', array('_id' => $objId)) );
?>