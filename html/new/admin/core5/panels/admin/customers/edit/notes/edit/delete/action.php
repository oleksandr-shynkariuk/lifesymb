<?php
$ntsdb =& dbWrapper::getInstance();
$ff =& ntsFormFactory::getInstance();
$noteId = ntsLib::getVar( 'admin/customers/edit/notes/edit::noteId' );

$where = array(
	'id'	=> array('=', $noteId)
	);

$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile );

switch( $action ){
	case 'delete':
		$result = $ntsdb->delete('objectmeta', $where );

		if( $result ){
			ntsView::setAnnounce( M('Note') . ': '. M('Delete') . ': ' . M('OK'), 'ok' );
			}
		else {
			$errorText = $cm->printActionErrors();
			ntsView::addAnnounce( $errorText, 'error' );
			}
	/* continue to the list with anouncement */
		ntsView::getBack();
		exit;
		break;
	}
?>