<?php
$objects = ntsLib::getVar( 'admin/manage/appointments/edit_class::objects' );
$objectView = ntsView::objectTitle( $objects[0] );
?>
<h2><?php echo $objectView; ?></h2>
