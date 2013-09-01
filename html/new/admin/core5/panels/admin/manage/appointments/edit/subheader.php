<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$objectView = ntsView::objectTitle( $object );
list( $alert, $cssClass, $message ) = $object->getStatus();

$serviceId = $object->getProp('service_id');
$service = ntsObjectFactory::get( 'service' );
$service->setId( $serviceId );
$type = $service->getType();

$noheader = $_NTS['REQ']->getParam( 'noheader' );
$showHeader = ( ($type != 'class') && (! $noheader) );
?>
<?php if( $showHeader ) : ?>
<h2><?php echo ntsView::printStatus($object, false); ?> <?php echo $objectView; ?></h2>
<?php endif; ?>