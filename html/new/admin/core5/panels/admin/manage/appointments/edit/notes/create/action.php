<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$objId = $object->getId();

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$NTS_VIEW['form'] =& $ff->makeForm( $formFile );

switch( $action ){
	case 'create':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

			$now = time();
			$metaData = $now . ':' . NTS_CURRENT_USERID;

			$ntsdb =& dbWrapper::getInstance();
			$newValues = array(
				'obj_class'		=> 'appointment',
				'obj_id'		=> $objId,
				'meta_name'		=> '_note',
				'meta_value'	=> $formValues['note'],
				'meta_data'		=> $metaData,
				);
			$result = $ntsdb->insert('objectmeta', $newValues );

			if( $result ){
				$id = $object->getId();

				$msg = array( M('Note'), M('Add'), M('OK') );
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