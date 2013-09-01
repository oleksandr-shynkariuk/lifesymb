<?php
$ff =& ntsFormFactory::getInstance();

$formFile = dirname( __FILE__ ) . '/searchForm';
$NTS_VIEW['searchForm'] =& $ff->makeForm( $formFile );

if( $NTS_VIEW['searchForm']->validate() ){
	$formValues = $NTS_VIEW['searchForm']->getValues();
	$params = array();
	if( $formValues['search'] )
		$params['search'] = trim($formValues['search']);

	$forwardTo = ntsLink::makeLink( '-current-', '', $params );
	ntsView::redirect( $forwardTo, false );
	exit;
	}
else {
	}
?>