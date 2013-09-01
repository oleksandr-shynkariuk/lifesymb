<?php
/* order */
$id = $_NTS['REQ']->getParam( 'id' );
$object = ntsObjectFactory::get( 'order' );
$object->setId( $id );

$apps = array();
$items = $object->getItems();
foreach( $items as $item )
{
	$className = $item->getClassName();
	if( $className != 'appointment' )
	{
		continue;
	}
	$apps[] = $item;
}

ntsLib::setVar( 'customer/orders/view::OBJECT', $object );
ntsLib::setVar( 'customer/orders/view::deps', $apps );
?>