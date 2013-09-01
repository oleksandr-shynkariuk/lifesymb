<?php
$alias = 'admin/manage/schedules';

$object = ntsLib::getVar( 'admin/company/resources/edit::OBJECT' );
$objId = $object->getId();

/* redefine calendar */
$cal = '';
ntsLib::setVar( 'admin/manage/schedules:cal', $cal );

$ress = array( $objId );
ntsLib::setVar( 'admin::ress', $ress );

$tm2 = ntsLib::getVar( 'admin::tm2' );
$tm2->setResource( $objId );
ntsLib::setVar( 'admin::tm2', $tm2 );

ntsView::setBack( ntsLink::makeLink('admin/company/resources/edit/schedules', '', array('_id' => $objId)) );

$groupId = $_NTS['REQ']->getParam( 'gid' );
if( $groupId ){
	ntsView::setPersistentParams( array('gid' => $groupId), 'admin/company/resources/edit/schedules/edit' );

	$blocks = $tm2->getBlocksByGroupId( $groupId );
	ntsLib::setVar( 'admin/manage/schedules/edit::blocks', $blocks );
	ntsLib::setVar( 'admin/manage/schedules/edit::groupId', $groupId );
	}
?>