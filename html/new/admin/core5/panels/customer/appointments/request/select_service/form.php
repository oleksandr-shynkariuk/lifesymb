<?php
global $NTS_AR;
$entries = $NTS_VIEW['entries'];
$categories = $NTS_VIEW['categories']; 
$cat2service = $NTS_VIEW['cat2service'];
$bundles = $NTS_VIEW['bundles'];

$conf =& ntsConf::getInstance();
$currentIndexes = array_keys( $entries );

$addFlowFlow = array( 'service', array() );

foreach( $currentIndexes as $i ){
	$objectOptions = array();

	if( $bundles && (count($currentIndexes) == 1) ){
		$objectOptions[] = array( M('Bundles') );
		foreach( $bundles as $b ){
			$bServices = $b->getProp('services');
			$bServices = explode( '-', $bServices );

			$bTitle = array();
			reset( $bServices );
			foreach( $bServices as $bsid ){
				$bservice = ntsObjectFactory::get('service');
				$bservice->setId( $bsid );
				$bTitle[] = ntsView::objectTitle($bservice);
				}
			$bTitle = join( ' + ', $bTitle );
			$thisIds = join('-', $bServices);
			$objectOptions[] = array( $thisIds, $bTitle );
			}
		}
	
	if( $categories[$i] ){
		foreach( $categories[$i] as $cat ){
			if( is_object($cat) ){
				$catId = $cat->getId();
				$catTitle = ntsView::objectTitle($cat);
				$catDescription = $cat->getProp('description');
				}
			else {
				list( $catId, $catTitle ) = $cat;
				$catDescription = '';
				}

			$objectOptions[] = array( $catTitle );
			$myEntries = $cat2service[$i][$catId];
			foreach( $myEntries as $s ){
				$linkView = ntsView::objectTitle($s);
				if( strlen($s->getProp('price')) )
					$linkView .= ' - ' . ntsCurrency::formatServicePrice($s->getProp('price'));
				$objectOptions[] = array( $s->getId(), $linkView );
				}
			}
		}
	else {
		foreach( $entries[$i] as $l ){
			$objectOptions[] = array( $l->getId(), ntsView::objectTitle($l) );
			}
		}

	$default = $NTS_AR->getSelectedValue( $i, 'service' );
	if( $objectOptions ){
		array_unshift( $objectOptions, array( '', ' - ' . M('Select') . ' - ' ) );
		$addFlowFlow[1][] = $this->makeInput (
		/* type */
			'select',
		/* attributes */
			array(
				'id'		=> 'id_' . $i,
				'options'	=> $objectOptions,
				'attr'		=> array(
					),
				'default'	=> $default,
				),
		/* validators */
			array(
				array(
					'code'		=> 'notEmpty.php', 
					'error'		=> M('Required field'),
					),
				)
			);
		}
	else {
		$addFlowFlow[1][] = '<span class="alert">' . M('Not Available') . '</span>';
		}
	}
$NTS_VIEW['flowFlow'][] = $addFlowFlow;
?>
<?php require( dirname(__FILE__) . '/../common/flow.php' ); ?>

<p>
<?php echo $this->makePostParams('-current-', 'select' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Continue'); ?>">
