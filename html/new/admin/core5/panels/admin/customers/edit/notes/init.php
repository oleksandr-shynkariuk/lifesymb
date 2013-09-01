<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$objId = $object->getId();

$iCanEdit = true;
ntsLib::setVar( 'admin/customers/edit/notes::iCanEdit', $iCanEdit );

ntsView::setBack( ntsLink::makeLink('admin/customers/edit/notes', '', array('_id' => $objId)) );
?>