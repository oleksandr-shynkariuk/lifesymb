<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );
$email = $object->getProp('email');
if( $email ){
	$title = '<i class="icon-envelope"></i> ' . M('Send Email');
	$sequence = 60;
	}
?>