<?php
switch( $inputAction ){
	case 'display':
		$input .= '<INPUT TYPE="file" ID="' . $conf['id'] . '" NAME="' . $conf['id'] . '"';
		$inputParams = $this->_makeInputParams( $conf['attr'] );
		if( $inputParams )
			$input .= ' ' . $inputParams;
		if( isset($conf['readonly']) && $conf['readonly'] )
			$input .= ' READONLY DISABLED CLASS="readonly"';
		$input .= '>';
		break;

	case 'submit':
		$realHandle = ntsView::setRealName( $handle );
	// returns the upload file temp name
		if( is_uploaded_file($_FILES[ $realHandle ]['tmp_name']) ){
			$input = $_FILES[ $realHandle ]['tmp_name'];

			$tmpName = $_FILES[$realHandle]['tmp_name'];
			$submittedName = $_FILES[$realHandle]['name'];
			$size = $_FILES[$realHandle]['size'];

			$input = $_FILES[$realHandle];
			}
		else {
			$input = false;
			}
		break;

	case 'check_submit':
		$realHandle = ntsView::setRealName( $handle );
		if( isset($_FILES[$realHandle]) )
			$input = true;
		else
			$input = false;
		break;
	}
?>