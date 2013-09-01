<?php
$ntsdb =& dbWrapper::getInstance();
$ff =& ntsFormFactory::getInstance();
$noteId = ntsLib::getVar( 'admin/customers/edit/notes/edit::noteId' );

$where = array(
	'id'	=> array('=', $noteId)
	);
$result = $ntsdb->select('meta_value', 'objectmeta', $where );

$data = $result->fetch();
$formParams['note'] = $data['meta_value'];

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

switch( $action ){
	case 'update':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

			$now = time();
			$metaData = $now . ':' . NTS_CURRENT_USERID;

			$newValues = array(
				'meta_value'	=> $formValues['note'],
				'meta_data'		=> $metaData,
				);

			$ntsdb =& dbWrapper::getInstance();
			$result = $ntsdb->update('objectmeta', $newValues, $where );

			if( $result ){
				$msg = array( M('Note'), M('Update'), M('OK') );
				$msg = join( ': ', $msg );
				ntsView::addAnnounce( $msg, 'ok' );

			/* continue to the list with anouncement */
				ntsView::getBack();
				exit;
				}
			else {
				$errorText = $cm->printActionErrors();
				ntsView::addAnnounce( $errorText, 'error' );
				}
			}
		else {
		/* form not valid, continue to create form */
			}

		break;
	default:
		break;
	}
?>