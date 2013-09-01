<?php
global $NTS_AR;
$entries = $NTS_VIEW['entries'];
$currentIndexes = array_keys( $entries );

$addFlowFlow = array( 'resource', array() );
foreach( $currentIndexes as $i ){
	if( count($entries[$i]) > 1 ){
		$objectOptions = array();
		if( $NTS_VIEW['selectionMode'] == 'manualplus' )
			$objectOptions[] = array( 'auto', ' - ' . M("Don't have a particular preference") . ' - '  );
		foreach( $entries[$i] as $l ){
			$objectOptions[] = array( $l->getId(), ntsView::objectTitle($l) );
			}
		if( $objectOptions && (count($currentIndexes) > 1) ){
			$objectOptions[] = array(0, ' - ' . M('No Appointment') . ' - ');
			}

		$default = $NTS_AR->getSelectedValue( $i, 'resource' );

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
	else {
		$addFlowFlow[1][] = ntsView::objectTitle( $entries[1][0] );
		}
	}
$NTS_VIEW['flowFlow'][] = $addFlowFlow;
?>
<?php require( dirname(__FILE__) . '/../common/flow.php' ); ?>

<p>
<?php echo $this->makePostParams('-current-', 'select' ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Continue'); ?>">
