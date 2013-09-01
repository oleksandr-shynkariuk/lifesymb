<?php
$noteId = $_NTS['REQ']->getParam('noteid');
ntsView::setPersistentParams( array('noteid' => $noteId), 'admin/manage/appointments/edit/notes/edit' );

ntsLib::setVar( 'admin/manage/appointments/edit/notes/edit::noteId', $noteId );
?>