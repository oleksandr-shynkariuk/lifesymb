<?php
$conf =& ntsConf::getInstance();

/* price formatting */
$confValue = $conf->get('priceFormat');
$default = array(
	'sign-before'	=> $confValue[0],
	'format'		=> $confValue[1] . '||' . $confValue[2],
	'sign-after'	=> $confValue[3]
	);
/* currency */
$currency = $conf->get('currency');
$default['currency'] = $currency;
$default['taxTitle'] = $conf->get('taxTitle');
$default['taxRate'] = $conf->get('taxRate');
$default['invoiceHeader'] = $conf->get('invoiceHeader');
$default['invoiceFooter'] = $conf->get('invoiceFooter');

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $default );

switch( $action ){
	case 'reset':
		$result = $conf->reset( 'priceFormat' );

		if( $result ){
			ntsView::setAnnounce( M('Reset To Defaults'), 'ok' );

		/* continue to return options form */
			$forwardTo = ntsLink::makeLink( '-current-' );
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
			ntsView::setAnnounce( M('Settings') . ': ' . M('Update') . ': ' . M('Failed') . ': Database Error', 'error' );
			}
		break;

	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

		/* price format */
			$setting = explode( '||', $formValues['format'] );
			array_unshift( $setting, $formValues['sign-before'] );
			$setting[] = $formValues['sign-after'];
			$conf->set( 'priceFormat', $setting );

		/* currency */
			$setting = $formValues['currency'];
			$conf->set( 'currency', $setting );

		/* tax */
			$setting = $formValues['taxTitle'];
			$conf->set( 'taxTitle', $setting );

			$setting = $formValues['taxRate'];
			$conf->set( 'taxRate', $setting );

		/* invoice header and footer */
			$setting = $formValues['invoiceHeader'];
			$conf->set( 'invoiceHeader', $setting );

			$setting = $formValues['invoiceFooter'];
			$conf->set( 'invoiceFooter', $setting );

			if( ! ($error = $conf->getError()) ){
				ntsView::setAnnounce( M('Settings') . ': ' . M('Update') . ': ' . M('OK'), 'ok' );

			/* continue to delivery options form */
				$forwardTo = ntsLink::makeLink( '-current-' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
				echo '<BR>Database error:<BR>' . $error . '<BR>';
				}
			}
		else {
		/* form not valid, continue to create form */
			}

		break;
	}
?>