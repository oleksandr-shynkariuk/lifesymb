<?php
ntsView::setTitle( M('Services') );
require_once( dirname(__FILE__) . '/_common.php' );
$currentIndexes = $NTS_AR->getCurrentIndexes();

$limitService = array();
if( ! $currentIndexes ){
	$limitService = $_NTS['REQ']->getParam( 'service' );
	if( strlen($limitService) && $limitService ){
		if( strpos($limitService, '-') !== false ){
			$limitService = explode( '-', $limitService );
			}
		else {
			$limitService = array( $limitService );
			}
		}
	}

/* CHOOSE ONE */
$entries = array();
$categories = array();
$cat2service = array();
$bundles = array();

reset( $currentIndexes );
foreach( $currentIndexes as $i ){
	$entries[$i] = array();
	$categories[$i] = array();
	$cat2service[$i] = array();
	$allServices = array();

	reset( $allValidIds[$i] );
	foreach( $allValidIds[$i] as $vid ){
		if( in_array($vid, $allServices) )
			continue;

		$validOne = ntsObjectFactory::get( 'service' );
		$validOne->setId( $vid );

	/* FILTER SERVICES */
		/* limit */
		if( $limitService ){
			if( ! in_array($vid, $limitService) )
				continue;
			}

	/* permissions */
		$skipService = false;
		if( ! ( $NTS_CURRENT_USER->hasRole('admin') ) ){
			$groupId = $NTS_CURRENT_USER->getId() ? 0 : -1;
			$permission = $validOne->getPermissionsForGroup( $groupId );
			switch( $permission ){
				case 'not_allowed':
					$skipService = true;
					break;
				case 'not_shown':
					$skipService = true;
					break;
				}
			}
		if( $skipService )
			continue;

		$entries[$i][] = $validOne;
		$allServices[] = $vid;
		}

	/* sort by show order */
	usort( $entries[$i], create_function('$a, $b', 'return ntsLib::numberCompare($a->getProp("show_order"), $b->getProp("show_order"));' ) );

	/* define which categories */
	$allCats = array();
	reset( $entries[$i] );
	foreach( $entries[$i] as $service ){
		$thisCats = $service->getProp( '_service_cat' );
		if( ! $thisCats )
			$thisCats = array( 0 );
		reset( $thisCats );
		foreach( $thisCats as $catId ){
			if( ! isset($cat2service[$i][$catId]) )
				$cat2service[$i][$catId] = array();
			$cat2service[$i][$catId][] = $service;
			}
		$allCats = array_merge( $allCats, $thisCats );
		}
	$allCats = array_unique( $allCats );

/* if bundles then add bundles */
	$plm =& ntsPluginManager::getInstance();
	$activePlugins = $plm->getActivePlugins();
	if( in_array('bundles', $activePlugins) ){
		$bundles = ntsObjectFactory::getAll( 'bundle' );
		if( ! $allCats )
			$allCats[] = 0;
		}

	if( (count($allCats) > 1) OR $bundles ){
		$addUncat = false;
		reset( $allCats );
		foreach( $allCats as $catId ){
			if( $catId > 0 ){
				$category = ntsObjectFactory::get( 'service_cat' );
				$category->setId( $catId );
				$categories[$i][] = $category;
				}
			else
				$addUncat = true;
			}
		/* sort by show order */
		usort( $categories[$i], create_function('$a, $b', 'return ntsLib::numberCompare($a->getProp("show_order"), $b->getProp("show_order"));' ) );

		if( $addUncat ){
			if( $bundles && ( count($allCats) == 1 ) ){
				$categories[$i][] = array(0, M('Single Services'));
				}
			else {
				$uncat = ntsObjectFactory::get( 'service_cat' );
				$uncat->setId( 0 );
				$categories[$i][] = $uncat;
				}
			}
		}
	}

$NTS_VIEW['entries'] = $entries;
$NTS_VIEW['categories'] = $categories;
$NTS_VIEW['cat2service'] = $cat2service;
$NTS_VIEW['bundles'] = $bundles;

/* packages */
$where = array(
	'price'	=> array('>', 0),
	);
$packs = ntsObjectFactory::find( 'pack', $where );
$NTS_VIEW['packs'] = $packs;
?>