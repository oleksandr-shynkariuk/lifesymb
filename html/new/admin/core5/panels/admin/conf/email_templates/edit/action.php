<?php
$conf =& ntsConf::getInstance();
$lm =& ntsLanguageManager::getInstance();

switch( $action ){
	case 'save':
		$ff =& ntsFormFactory::getInstance();
		$formFile = dirname( __FILE__ ) . '/form';

		$NTS_VIEW['key'] = $_NTS['REQ']->getParam( 'key' );
		$NTS_VIEW['lang'] = $_NTS['REQ']->getParam( 'lang' );
		$formParams = array(
			'key'		=> $NTS_VIEW['key'],
			'lang'		=> $NTS_VIEW['lang'],
			);

		$form =& $ff->makeForm( $formFile, $formParams );

		if( $form->validate() ){
			$formValues = $form->getValues();

			$NTS_VIEW['key'] = $_NTS['REQ']->getParam( 'key' );

			$lm =& ntsLanguageManager::getInstance();
			$tm =& ntsEmailTemplateManager::getInstance();

			$languages = $lm->getActiveLanguages();
			if( ! $NTS_VIEW['lang'] )
				$NTS_VIEW['lang'] = $languages[0];
			if( $NTS_VIEW['lang'] == 'en-builtin' )
				$NTS_VIEW['lang'] = 'en';

			$subject = $formValues['subject'];
			$body = $formValues['body'];

			$result = $tm->save( $NTS_VIEW['lang'], $NTS_VIEW['key'], $subject, $body );
			if( $result ){
				ntsView::setAnnounce( M('Template') . ': ' . M('Update') . ': ' . M('OK'), 'ok' );
			/* continue  */
				$forwardTo = ntsLink::makeLink( '-current-', '', array('key' => $NTS_VIEW['key'], 'lang' => $NTS_VIEW['lang']) );
				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
				echo '<BR>Database error:<BR>' . $tm->getError() . '<BR>';
				}
			}
		else {
		/* form not valid, continue to edit form */
			}
		break;

	case 'reset':
		$tm =& ntsEmailTemplateManager::getInstance();

		$NTS_VIEW['lang'] = $_NTS['REQ']->getParam( 'lang' );
		$NTS_VIEW['key'] = $_NTS['REQ']->getParam( 'key' );

		$result = $tm->reset( $NTS_VIEW['lang'], $NTS_VIEW['key'] );
		if( $result ){
			ntsView::setAnnounce( M('Template') . ': ' . M('Reset To Defaults') . ': ' . M('OK'), 'ok' );
		/* continue  */
			$forwardTo = ntsLink::makeLink( '-current-', '', array('key' => $NTS_VIEW['key'], 'lang' => $NTS_VIEW['lang']) );
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
			echo '<BR>Database error:<BR>' . $tm->getError() . '<BR>';
			}
		break;

	default:
		$lm =& ntsLanguageManager::getInstance();
		$tm =& ntsEmailTemplateManager::getInstance();

		$NTS_VIEW['key'] = $_NTS['REQ']->getParam( 'key' );

		$languages = $lm->getActiveLanguages();
		$NTS_VIEW['lang'] = $_NTS['REQ']->getParam( 'lang' );
		if( ! $NTS_VIEW['lang'] )
			$NTS_VIEW['lang'] = $languages[0];

		if( $NTS_VIEW['lang'] == 'en-builtin' )
			$NTS_VIEW['lang'] = 'en';

		if( $NTS_VIEW['lang'] != 'en' ){
			$languageConf = $lm->getLanguageConf( $NTS_VIEW['lang'] );
			if( isset($languageConf['charset']) ){
				header( 'Content-Type: text/html; charset=' . $languageConf['charset'] );
				}
			}

		$template = $tm->getTemplate( $NTS_VIEW['lang'], $NTS_VIEW['key'] );

	/* prepare form */
		$ff =& ntsFormFactory::getInstance();
		$formParams = array(
			'key'		=> $NTS_VIEW['key'],
			'lang'		=> $NTS_VIEW['lang'],
			'subject'	=> $template['subject'],
			'body'		=> $template['body'],
			);
		$form =& $ff->makeForm( dirname(__FILE__) . '/form', $formParams );
		break;
	}
?>