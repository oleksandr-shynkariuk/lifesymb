<?php
switch( $inputAction ){
	case 'display':
		$input .= '<TEXTAREA ID="' . $conf['htmlId'] . '" NAME="' . $conf['id'] . '"';
	/* fix old bug with columns vs. cols */	
		if( isset($conf['attr']['columns']) ){
			$conf['attr']['cols'] = $conf['attr']['columns'];
			unset( $conf['attr']['columns'] );
			}

		$inputParams = $this->_makeInputParams( $conf['attr'] );
		if( $inputParams )
			$input .= ' ' . $inputParams;

		if( isset($conf['readonly']) && $conf['readonly'] )
			$input .= ' READONLY DISABLED CLASS="readonly"';

		$input .= '>';
		$input .= htmlspecialchars( $conf['value'] );
		$input .= '</TEXTAREA>';
		break;

	case 'submit':
		$input = $_NTS['REQ']->getParam( $handle );
		break;

	case 'check_submit':
		$input = isset( $_POST[$handle] ) ? true : false;
		break;
	}
?>