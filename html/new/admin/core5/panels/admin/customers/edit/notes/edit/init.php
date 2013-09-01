<?php
$noteId = $_NTS['REQ']->getParam('noteid');
ntsView::setPersistentParams( array('noteid' => $noteId), 'admin/customers/edit/notes/edit' );

ntsLib::setVar( 'admin/customers/edit/notes/edit::noteId', $noteId );
?>