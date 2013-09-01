<?php
class ntsRequest {
	var $sanitizer;
	var $reset;
	var $explicit;

	function ntsRequest(){
		$this->sanitizer = array();
		$this->reset = array();
		$this->explicit = array();
		}

	function isAjax()
	{
		global $NTS_VIEW;
		$return = FALSE;
		if( isset($NTS_VIEW[NTS_PARAM_VIEW_MODE]) && ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax') )
		{
			$return = TRUE;
		}
		echo "is ajax: ";
		echo $return ? "TRUE" : "FALSE";
		echo '<br>';
		return $return;
	}

	function addSanitizer( $param, $re ){
		$this->sanitizer[ $param ] = $re;
		}

	function resetParam( $pName ){
		$this->reset[] = $pName;
		}

	function getRequestedAction(){
		$return = '';
		if( ! $return ){
			$return = $this->getParam( NTS_PARAM_ACTION );
			}
		return $return;
		}

	function setParam( $pName, $pValue ){
		$this->explicit[$pName] = $pValue;
		}

	function getParam( $pName, $convert = TRUE ){
		if( in_array($pName, $this->reset) )
			return null;

		if( isset($this->explicit[$pName]) ){
			$return = $this->explicit[$pName];
			return $return;
			}

		$return = '';
		$realName = $convert ? ntsView::setRealName($pName) : $pName;
		if( isset($_REQUEST[$realName]) ){
			$return = $_REQUEST[$realName];
			if( get_magic_quotes_gpc() ){
				if( ! is_array($return) )
					$return = stripslashes( $return );
				}
			}

	/* now check in sanitizer */
		if( isset($this->sanitizer[$pName]) ){
			$re = $this->sanitizer[$pName];
			if( is_array($return) ){
				reset( $return );
				foreach( $return as $r ){
					if( ! preg_match($re, $r) ){
					/* sanitizer failed */
						echo "invalid value for '$pName' detected";
						exit;
						}
					}
				}
			else {
				if( ! preg_match($re, $return) ){
				/* sanitizer failed */
					echo "invalid value for '$pName' detected";
					exit;
					}
				}
			}

		return $return;
		}

	function getGetParams(){
		$return = array();
		reset( $_GET );
		foreach( $_GET as $k => $v ){
			if( $k == NTS_PARAM_PANEL )
				continue;
			if( get_magic_quotes_gpc() )
				$v = stripslashes( $v );
			$realName = ntsView::getRealName($k);
			$return[ $realName ] = $v;
			}
		return $return;
		}

	function getPostParams(){
		$return = array();
		reset( $_POST );
		foreach( $_POST as $k => $v ){
			if( $k == NTS_PARAM_PANEL )
				continue;
			if( get_magic_quotes_gpc() )
				$v = stripslashes( $v );
			$realName = ntsView::getRealName($k);
			$return[ $realName ] = $v;
			}
		return $return;
		}
	}
?>