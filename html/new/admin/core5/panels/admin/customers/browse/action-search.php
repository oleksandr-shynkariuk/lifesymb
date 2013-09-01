<?php
$ff =& ntsFormFactory::getInstance();

$formFile = dirname( __FILE__ ) . '/searchForm';
$NTS_VIEW['searchForm'] =& $ff->makeForm( $formFile );

if( $NTS_VIEW['searchForm']->validate() ){
	$formValues = $NTS_VIEW['searchForm']->getValues();
	$params = array();
	if( $formValues['search'] )
		$params['search'] = $formValues['search'];
	if( $NTS_VIEW[NTS_PARAM_VIEW_MODE] )
		$params[NTS_PARAM_VIEW_MODE] = $NTS_VIEW[NTS_PARAM_VIEW_MODE];

	$forwardTo = ntsLink::makeLink( '-current-', '', $params );
//	ntsView::redirect( $forwardTo, false );
	ntsView::redirect( $forwardTo, false );
	exit;
	}
else {
	}
?>