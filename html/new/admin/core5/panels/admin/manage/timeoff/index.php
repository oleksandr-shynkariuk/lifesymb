<?php
$entries = ntsLib::getVar( 'admin/manage/timeoff:entries' );
$cal = ntsLib::getVar( 'admin/manage/timeoff:cal' );
$schEdit = ntsLib::getVar( 'admin/manage:schEdit' );
$ress = ntsLib::getVar( 'admin::ress' );

$showDate = true;
$t = $NTS_VIEW['t'];

if( $cal ){
	$t->setDateDb( $cal );
	$dayTitleView = $t->formatWeekdayShort() . ', ' . $t->formatDate();
	}
else {
	$t->setNow();
	}

$showCols = array(
	'time'	=> 1,
	'resource'	=> 1,
	'description'	=> 1,
	);

$totalCols = 1;
reset( $showCols );
foreach( $showCols as $k => $v ){
	if( $v )
		$totalCols++;
	}

$displayCreateLink = true;
$showNone = $displayCreateLink;
if( ! ( $schEdit && array_intersect($ress, $schEdit) ) )
	$displayCreateLink = false;	
?>
<?php require( dirname(__FILE__) . '/submenu.php' ); ?>

<?php if( $cal ) : ?>
<p><h3><?php echo $dayTitleView; ?></h3>
<?php endif; ?>

<?php if( (count($entries) <= 0) && $showNone ) : ?>
<?php echo M('None'); ?>
<?php endif; ?>

<?php if( count($entries) > 0 ) : ?>
<table class="nts-listing">
<thead>
<tr>
<th style="width: 1em;">&nbsp;</th>

<?php if( $showCols['time'] ) : ?>
	<th><?php echo M('Time'); ?></th>
<?php endif; ?>
<?php if( $showCols['resource'] ) : ?>
	<th><?php echo M('Bookable Resource'); ?></th>
<?php endif; ?>
<?php if( $showCols['description'] ) : ?>
	<th><?php echo M('Description'); ?></th>
<?php endif; ?>
</tr>
</thead>
<?php $count = 0; ?>
<?php 	foreach( $entries as $b ) : ?>
<?php
			$iCanEdit = in_array($b['resource_id'], $schEdit );
			$editLink = ntsLink::makeLink( 
				'-current-/edit',
				'',
				array(
					'_id' => $b['id']
					)
				);
			$deleteLink = ntsLink::makeLink( 
				'-current-/edit/delete',
				'',
				array(
					'_id' => $b['id']
					)
				);

			$t->setTimestamp( $b['starts_at'] );
			$validFrom = $t->formatDate_Db();
			$view['time'] = $t->formatDate() . ' ' . $t->formatTime();
			$t->setTimestamp( $b['ends_at'] );
			$validTo = $t->formatDate_Db();
			$view['time'] .= ' - ' . $t->formatDate() . ' ' . $t->formatTime();

			$resource = ntsObjectFactory::get('resource');
			$resource->setId( $b['resource_id'] );
			$view['resource'] = ntsView::objectTitle( $resource );

			$view['description'] = $b['description'];
?>
<tbody class="nts-ajax-parent">
<tr class="<?php echo (($count++) % 2) ? 'even' : 'odd'; ?>">

<td style="width: 1em;">
<?php if( $iCanEdit ) : ?><a class="alert nts-ajax-loader nts-bold" href="<?php echo $deleteLink; ?>" title="<?php echo M('Delete'); ?>">[x]</a><?php endif; ?>
</td>

<?php if( $showCols['time'] ) : ?>
	<td style="white-space: nowrap;">
<?php if( $iCanEdit ) : ?><a href="<?php echo $editLink; ?>" class="nts-ajax-loader"><?php endif; ?><span class="nts-bold"><?php echo $view['time']; ?></span><?php if( $iCanEdit ) : ?></a><?php endif; ?>
	</td>
<?php endif; ?>

<?php if( $showCols['resource'] ) : ?>
	<td>
	<?php echo $view['resource']; ?>
	</td>
<?php endif; ?>

<?php if( $showCols['description'] ) : ?>
	<td>
	<?php echo $view['description']; ?>
	</td>
<?php endif; ?>

</tr>

<tr>
<td colspan="<?php echo $totalCols; ?>" class="nts-ajax-container nts-child"></td>
</tr>

</tbody>

<?php 	endforeach; ?>
</table>
<?php endif; ?>
