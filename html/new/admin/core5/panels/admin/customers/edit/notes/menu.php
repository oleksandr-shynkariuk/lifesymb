<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$notes = $object->getProp('_note');

$title = M('Notes') . ' [' . count($notes) . ']';
$sequence = 50;
?>