<?php
$appEdit = ntsLib::getVar( 'admin/manage:appEdit' );
$objects = ntsLib::getVar( 'admin/manage/appointments/edit_class::objects' );
$obj = $objects[0];

$locationId = $obj->getProp('location_id');
$resourceId = $obj->getProp('resource_id');
$serviceId = $obj->getProp('service_id');
$startsAt = $obj->getProp('starts_at'); 
$duration = $obj->getProp('duration'); 

$iCanEdit = in_array($resourceId, $appEdit) ? true : false;

$service = ntsObjectFactory::get( 'service' );
$service->setId( $serviceId ); 
$location = ntsObjectFactory::get( 'location' );
$location->setId( $locationId );
$resource = ntsObjectFactory::get( 'resource' );
$resource->setId( $resourceId );

$NTS_VIEW['t']->setTimestamp( $startsAt );
$dateView = $NTS_VIEW['t']->formatWeekdayShort() . ', ' . $NTS_VIEW['t']->formatDate();

$timeView = $NTS_VIEW['t']->formatTime();
$NTS_VIEW['t']->modify( '+' . $duration . ' seconds' );
$timeView .= ' - ' . $NTS_VIEW['t']->formatTime();
?>

<table class="ntsForm">

<tr>
<td class="ntsFormLabel"><?php echo M('Location'); ?></td>
<td class="ntsFormValue"><?php echo $location->getProp('title'); ?></td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Bookable Resource'); ?></td>
<td class="ntsFormValue"><?php echo $resource->getProp('title'); ?></td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Service'); ?></td>
<td class="ntsFormValue"><?php echo $service->getProp('title'); ?></td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Date'); ?></td>
<td class="ntsFormValue"><?php echo $dateView; ?></td>
</tr>

<tr>
<td class="ntsFormLabel"><?php echo M('Time'); ?></td>
<td class="ntsFormValue"><?php echo $timeView; ?></td>
</tr>

</table>