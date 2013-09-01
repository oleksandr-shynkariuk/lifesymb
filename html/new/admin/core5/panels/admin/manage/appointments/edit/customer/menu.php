<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
if( ! is_array($object) ){
	$title = M('Customer');
	$sequence = 20;
	}
?>