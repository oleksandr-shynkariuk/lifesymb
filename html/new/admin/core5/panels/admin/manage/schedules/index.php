<?php
global $NTS_TIME_WEEKDAYS;
$blocks = ntsLib::getVar( 'admin/manage/schedules:blocks' );
$cal = ntsLib::getVar( 'admin/manage/schedules:cal' );
$schEdit = ntsLib::getVar( 'admin/manage:schEdit' );
$ress = ntsLib::getVar( 'admin::ress' );

$allSids = ntsObjectFactory::getAllIds('service');
$allLids = ntsObjectFactory::getAllIds('location');

$showDate = true;
$t = $NTS_VIEW['t'];

if( $cal ){
	$activeCheckWith = $cal;
	}
else {
	$t->setNow();
	$activeCheckWith = $t->formatDate_Db();
	}

$shownWeekdays = array();
reset( $blocks );
$t->setDateDb( '20120416' );
$t->setStartDay();
$startDay = $t->getTimestamp();

$showCols = array(
	'date'	=> 1,
	'time'	=> 1,
	'location'	=> 1,
	'resource'	=> 1,
	'service'	=> 1,
	'capacity'	=> 1,
	);

$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

if( count($locs) <= 1 )
	$showCols['location'] = 0;
if( count($ress) <= 1 )
	$showCols['resource'] = 0;
if( count($sers) <= 1 )
	$showCols['service'] = 0;
if( $cal )
	$showCols['date'] = 0;

$showCapacity = false;
/* check capacity */
reset( $blocks );
foreach( $blocks as $i1 => $k1 ){
	reset( $k1 );
	foreach( $k1 as $i2 => $k2 ){
		reset( $k2 );
		foreach( $k2 as $k3 ){
			if( $k3['capacity'] > 1 ){
				$showCapacity = true;
				break;
				}
			}
		if( $showCapacity )
			break;
		}
	if( $showCapacity )
		break;
	}
if( ! $showCapacity ){
	$showCols['capacity'] = 0;
	}
	
$totalCols = 0;
reset( $showCols );
foreach( $showCols as $k => $v ){
	if( $v )
		$totalCols++;
	}

$ntsConf =& ntsConf::getInstance();
$weekStartsOn = $ntsConf->get('weekStartsOn');
$dis = array();
for( $i = 0; $i < 7; $i++ ){
	$di = $weekStartsOn + $i;
	$di = $di % 7;
	$dis[] = $di;
	}
reset( $dis );
?>
<?php require( dirname(__FILE__) . '/submenu.php' ); ?>

<?php $NTS_VIEW['form']->display(); ?>

<p>
<?php foreach( $dis as $di ) : ?>
<?php
		if( ! isset($blocks[$di]) )
			continue;
		$ba = $blocks[$di];
?>
<?php	if( ! $cal ) : ?>
		<h3><?php echo $NTS_TIME_WEEKDAYS[$di]; ?></h3>
<?php 	// elseif( count($ba) > 0 ) : ?>
<?php 	else : ?>
<?php
			$t->setDateDb( $cal );
			$dayTitleView = $t->formatWeekday() . ', ' . $t->formatDate();
?>
		<h3><?php echo $dayTitleView; ?></h3>
<?php 	endif; ?>

<?php if( count($ba) > 0 ) : ?>
<table class="nts-listing">
<thead>
<tr>
<?php if( $showCols['date'] ) : ?>
	<th><?php echo M('Dates'); ?></th>
<?php endif; ?>
<?php if( $showCols['time'] ) : ?>
	<th><?php echo M('Time'); ?></th>
<?php endif; ?>
<?php if( $showCols['resource'] ) : ?>
	<th><?php echo M('Bookable Resource'); ?></th>
<?php endif; ?>
<?php if( $showCols['location'] ) : ?>
	<th><?php echo M('Locations'); ?></th>
<?php endif; ?>
<?php if( $showCols['service'] ) : ?>
	<th><?php echo M('Services'); ?></th>
<?php endif; ?>
<?php if( $showCols['capacity'] ) : ?>
	<th><?php echo M('Capacity'); ?></th>
<?php endif; ?>
</tr>
</thead>
<?php $count = 0; ?>
<?php 	foreach( $ba as $b ) : ?>
<?php
			$iCanEdit = in_array($b[0]['resource_id'], $schEdit );
			$editLink = ntsLink::makeLink( 
				'-current-/edit',
				'',
				array(
					'gid' => $b[0]['group_id']
					)
				);

			$t->setDateDb( $b[0]['valid_from'] );
			$view['valid_from'] = $t->formatDate();

			$t->setDateDb( $b[0]['valid_to'] );
			$view['valid_to'] = $t->formatDate();

			$resource = ntsObjectFactory::get('resource');
			$resource->setId( $b[0]['resource_id'] );
			$view['resource'] = ntsView::objectTitle( $resource );

		/* services */
			$sids = array();
			reset( $b );
			foreach( $b as $bbb ){
				$sids[ $bbb['service_id'] ] = 1;
				}
			$sids = array_keys( $sids );

			if( count($sids) == 1 ){
				if( $sids[0] ){
					$service = ntsObjectFactory::get('service');
					$service->setId( $sids[0] );
					$view['service'] = ntsView::objectTitle( $service );
					}
				else {
					$view['service'] = ' - ' . M('All') . ' - ';
					}
				}
			else {
				$view['service'] = count($sids);
				}

		/* locations */
			$lids = array();
			reset( $b );
			foreach( $b as $bbb ){
				$lids[ $bbb['location_id'] ] = 1;
				}
			$lids = array_keys( $lids );

			if( count($lids) == 1 ){
				if( $lids[0] ){
					$location = ntsObjectFactory::get('location');
					$location->setId( $lids[0] );
					$view['location'] = ntsView::objectTitle( $location );
					}
				else {
					$view['location'] = ' - ' . M('All') . ' - ';
					}
				}
			else {
				$view['location'] = count($lids);
				}

			$t->setTimestamp( $startDay + $b[0]['starts_at'] );
			$view['time'] = $t->formatTime();

			if( $b[0]['selectable_every'] ){
				$t->setTimestamp( $startDay + $b[0]['ends_at'] );
				$view['time'] .= ' - ' . $t->formatTime();
				}
			else {
				}
			$view['capacity'] = $b[0]['capacity'];
			$nowActive = ( ($activeCheckWith >= $b[0]['valid_from']) && ($activeCheckWith <= $b[0]['valid_to']) ) ? 1 : 0;
?>
<tbody class="nts-ajax-parent">
<tr class="<?php echo (($count++) % 2) ? 'even' : 'odd'; ?> <?php echo ($nowActive) ? '' : 'supress'; ?>">

<?php if( $showCols['date'] ) : ?>
	<td>
	<?php echo $view['valid_from']; ?> - <?php echo $view['valid_to']; ?>
	</td>
<?php endif; ?>
<?php if( $showCols['time'] ) : ?>
	<td>
<?php if( $iCanEdit ) : ?><a href="<?php echo $editLink; ?>" class="nts-ajax-loader"><?php endif; ?><span class="nts-bold"><?php echo $view['time']; ?></span><?php if( $iCanEdit ) : ?></a><?php endif; ?>
<?php if( $b[0]['selectable_every'] > 0 ) : ?>
 / <?php echo ($b[0]['selectable_every']/60); ?> <?php echo M('Minutes'); ?>
<?php endif; ?>
	</td>
<?php endif; ?>

<?php if( $showCols['resource'] ) : ?>
	<td>
	<?php echo $view['resource']; ?>
	</td>
<?php endif; ?>

<?php if( $showCols['location'] ) : ?>
	<td>
	<?php echo $view['location']; ?>
	</td>
<?php endif; ?>
<?php if( $showCols['service'] ) : ?>
	<td>
	<?php echo $view['service']; ?>
	</td>
<?php endif; ?>
<?php if( $showCols['capacity'] ) : ?>
	<td>
	<?php echo $view['capacity']; ?>
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

<?php
$displayCreateLink = false;
if( ! $cal ){
	$displayCreateLink = true;
	}
elseif( count($ba) > 0 ){
	$displayCreateLink = true;
	}
else {
	$t->setDateDb( $cal );
	$calDi = $t->getWeekday();
	if( $calDi == $di ){
		$displayCreateLink = true;
		}
	}

$showNone = $displayCreateLink;
if( ! ( $schEdit && array_intersect($ress, $schEdit) ) )
	$displayCreateLink = false;
?>

<?php if( (count($ba) <= 0) && $showNone ) : ?>
<?php echo M('None'); ?>
<?php endif; ?>

<?php if( $displayCreateLink ) : ?>
<?php
		$createParams = array();
		if( $cal ){
			$createParams['cal'] = $cal;
			}
		else {
			$createParams['applied_on'] = $di;
			}
		$addLink = ntsLink::makeLink( 
			'-current-/create',
			'',
			$createParams
			);
?>
<ul class="nts-listing" style="margin: 0.5em 0 0 0;">
<li class="nts-ajax-parent">
<a class="nts-ajax-loader nts-button2 nts-ok" href="<?php echo $addLink; ?>">[+] <?php echo M('Add Timeslot'); ?></a>
<div class="nts-ajax-container" style="margin: 0.5em 0 1em 1em; padding: 0.5em 0.5em; border: #CCCCCC 1px solid;"></div>
</li>
</ul>
<?php endif; ?>

<?php endforeach; ?>