<?php
$ntsConf =& ntsConf::getInstance();
$sendCcForAppointment = $ntsConf->get('sendCcForAppointment');
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );

if( (! is_array($object)) && $sendCcForAppointment ){
	$cc = $object->getProp('_cc');
	reset( $cc );
	$cc_count = 0;
	foreach( $cc as $cc_to )
	{
		if( trim($cc_to) )
			$cc_count++;
	}

	$title = M('CC') . ' [' . $cc_count . ']';
	$sequence = 25;
	}
?>