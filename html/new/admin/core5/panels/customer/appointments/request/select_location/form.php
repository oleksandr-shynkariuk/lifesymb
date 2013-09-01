<?php
global $NTS_AR;
$entries = $NTS_VIEW['entries'];
$currentIndexes = array_keys( $entries );

$addFlowFlow = array( 'location', array() );
foreach( $currentIndexes as $i ){
	$objectOptions = array();
	if( $NTS_VIEW['selectionMode'] == 'manualplus' )
		$objectOptions[] = array( 'auto', ' - ' . M("Don't have a particular preference") . ' - '  );
	foreach( $entries[$i] as $l ){
		$objectOptions[] = array( $l->getId(), ntsView::objectTitle($l) );
		}
	$default = $NTS_AR->getSelectedValue( $i, 'location' );

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
			)
		);
	}
$NTS_VIEW['flowFlow'][] = $addFlowFlow;
?>
<?php require( dirname(__FILE__) . '/../common/flow.php' ); ?>

<p>
<?php echo $this->makePostParams('-current-', 'select' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Continue'); ?>">
