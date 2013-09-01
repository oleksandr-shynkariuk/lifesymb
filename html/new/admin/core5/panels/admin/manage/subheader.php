<?php
global $NTS_CURRENT_USER, $_NTS;

$locs = ntsLib::getVar( 'admin::locs' );
$ress = ntsLib::getVar( 'admin::ress' );
$sers = ntsLib::getVar( 'admin::sers' );

$locs2 = ntsLib::getVar( 'admin::locs2' );
$ress2 = ntsLib::getVar( 'admin::ress2' );
$sers2 = ntsLib::getVar( 'admin::sers2' );

$filter = ntsLib::getVar( 'admin/manage:filter' );
$tm2 = ntsLib::getVar( 'admin::tm2' );
if( count($filter) || (count($locs2) > 1) || (count($ress2) > 1) || (count($sers2) > 1) ){
	$showFilter = true;
	}
else {
	$showFilter = false;
	}
?>
<?php if( $showFilter ) : ?>
<ul class="nts-filter">
<?php for( $fi = 0; $fi < count($filter); $fi++ ) : ?>
<?php
	$thisFilter = $filter;
	unset( $thisFilter[$fi] );
	$thisFilter = array_values( $thisFilter );

	$fp = $filter[$fi];
	$fclass = substr( $fp, 0, 1 );
	$fid = substr( $fp, 1 );

	$classes = array(
		's'	=> array( 'service', 'Service' ),
		'r'	=> array( 'resource', 'Bookable Resource' ),
		'l'	=> array( 'location', 'Location' ),
		'c'	=> array( 'user', 'Customer' ),
		);
	if( ! isset($classes[$fclass]) )
		continue;
	$className = $classes[$fclass][0];
	$title = M( $classes[$fclass][1] );

	switch( $className ){
		case 'user':
			$obj = new ntsUser();
			$obj->setId( $fid );
			$objectView = '' . M('Customer') . '' . ': ' . '<b>' . ntsView::objectTitle($obj) . '</b>';
			break;
		default:
			$obj = ntsObjectFactory::get( $className );
			$obj->setId( $fid );
//			$objectView = $title . ': ' . '<b>' . ntsView::objectTitle($obj) . '</b>';
			$objectView = '<b>' . ntsView::objectTitle($obj) . '</b>';
			break;
		}
?>
	<li class="option"><?php echo $objectView; ?> <a class="ntsDeleteControl2" href="<?php echo ntsLink::makeLink('-current-', '', array('nts-filter' => join('-', $thisFilter) )); ?>">[x]</a></li>
<?php endfor; ?>

<?php if( (count($locs2) > 1) || (count($ress2) > 1) || (count($sers2) > 1) ) : ?>
	<li>
		<a href="#" class="nts-sublist-expander"><?php echo M('Add Filter'); ?></a>
		<ul class="nts-sublist">

<?php if( count($locs2) > 1 ) : ?>
			<li>
				<a href="#" class="nts-sublist-expander"><?php echo M('Locations'); ?></a>
				<ul class="nts-sublist">
<?php			foreach( $locs2 as $objId ) : ?>
<?php
					$obj = ntsObjectFactory::get( 'location' );
					$obj->setId( $objId );
					$thisFilter = array_merge( $filter, array('l' . $obj->getId()) );
					$thisFilter = array_unique($thisFilter);
?>
					<li><a href="<?php echo ntsLink::makeLink('-current-', '', array('nts-filter' => join('-', $thisFilter) )); ?>"><?php echo ntsView::objectTitle($obj); ?></a></li>
<?php			endforeach; ?>
				</ul>
			</li>
<?php endif; ?>

<?php if( count($ress2) > 1 ) : ?>
			<li>
				<a href="#" class="nts-sublist-expander"><?php echo M('Bookable Resources'); ?></a>
				<ul class="nts-sublist">
<?php			foreach( $ress2 as $objId ) : ?>
<?php
					$obj = ntsObjectFactory::get( 'resource' );
					$obj->setId( $objId );
					$thisFilter = array_merge( $filter, array('r' . $obj->getId()) );
					$thisFilter = array_unique($thisFilter);
?>
					<li><a href="<?php echo ntsLink::makeLink('-current-', '', array('nts-filter' => join('-', $thisFilter) )); ?>"><?php echo ntsView::objectTitle($obj); ?></a></li>
<?php			endforeach; ?>
				</ul>
			</li>
<?php endif; ?>

<?php if( count($sers2) > 1 ) : ?>
			<li>
				<a href="#" class="nts-sublist-expander"><?php echo M('Services'); ?></a>
				<ul class="nts-sublist">
<?php			foreach( $sers2 as $objId ) : ?>
<?php
					$obj = ntsObjectFactory::get( 'service' );
					$obj->setId( $objId );
					$thisFilter = array_merge( $filter, array('s' . $obj->getId()) );
					$thisFilter = array_unique($thisFilter);
?>
					<li><a href="<?php echo ntsLink::makeLink('-current-', '', array('nts-filter' => join('-', $thisFilter) )); ?>"><?php echo ntsView::objectTitle($obj); ?></a></li>
<?php			endforeach; ?>
				</ul>
			</li>
<?php endif; ?>

		</ul>
	</li>
<?php endif; ?>
<li style="float: none; clear: both; font-size: 0px; border-width: 0px; margin: 0 0; padding: 0 0;"></li>
</ul>
<?php endif; ?>