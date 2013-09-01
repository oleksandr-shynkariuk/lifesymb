<?php
class ntsConf {
	var $rawValues;
	var $arrayType = array();
	var $codeGet = '';
	var $codeSet = '';
	var $_cache;
	var $error;

	function ntsConf(){
		$this->rawValues = array();
		$this->arrayType = array(
			'disabledNotifications',
			);

		$codeFile = NTS_BASE_DIR . '/model/confGet.php';
		$code2run = file_get_contents( $codeFile );
		$code2run = str_replace( '<?php', '', $code2run );
		$code2run = str_replace( '?>', '', $code2run );
		$this->codeGet = $code2run;

		$codeFile = NTS_BASE_DIR . '/model/confSet.php';
		$code2run = file_get_contents( $codeFile );
		$code2run = str_replace( '<?php', '', $code2run );
		$code2run = str_replace( '?>', '', $code2run );
		$this->codeSet = $code2run;
		$this->_cache = array();
		$this->load();
		$this->error = '';
		}

	function getError(){
		return $this->error;
		}

	function load(){
		$this->rawValues = array();
		$ntsdb =& dbWrapper::getInstance();

		$sql = "SELECT name, value FROM {PRFX}conf";
		$result = $ntsdb->runQuery( $sql );

		if( $result ){
			while( $oInfo = $result->fetch() ){
				if( in_array($oInfo['name'], $this->arrayType)){
					if( ! isset($this->rawValues[ $oInfo['name'] ]) ){
						$this->rawValues[ $oInfo['name'] ] = array();
						}
					$this->rawValues[ $oInfo['name'] ][] = $oInfo['value'];
					}
				else {
					if( isset($this->rawValues[$oInfo['name']]) ){
						if( ! is_array($this->rawValues[ $oInfo['name'] ]) )
							$this->rawValues[ $oInfo['name'] ] = array( $this->rawValues[ $oInfo['name'] ] );
						$this->rawValues[ $oInfo['name'] ][] = $oInfo['value'];
						}
					else {
						$this->rawValues[ $oInfo['name'] ] = $oInfo['value'];
						}
					}
				}
			}
		else {
			return;
			}
		}

	function getLoadedNames(){
		$return = array_keys( $this->rawValues );
		return $return;
		}

	function get( $name ){
		if( ! isset($this->_cache[$name]) ){
			if( in_array($name, $this->arrayType) || (isset($this->rawValues[$name]) && is_array($this->rawValues[$name])) ){
				$rawValue = isset($this->rawValues[$name]) ? $this->rawValues[$name] : array();
				}
			else {
				$rawValue = isset($this->rawValues[$name]) ? $this->rawValues[$name] : '';
				$rawValue = trim( $rawValue );
				}
			$return = $rawValue;

		/* actual code file */
			eval( $this->codeGet );

			$this->_cache[$name] = $return;
			}
		$return = $this->_cache[$name];
		return $return;
		}

	function set( $name, $value ){
		$return = $value;

	/* actual code file */
		eval( $this->codeSet );

		$this->saveProp( $name, $return );
		return $return;
		}

	function reset( $name ){
		$ntsdb =& dbWrapper::getInstance();
		$result = $ntsdb->delete( 
			'conf',
			array(
				'name' => array('=', $name)
				)
			);
		return $result;
		}

	function saveProp( $name, $newValue ){
		$ntsdb =& dbWrapper::getInstance();
		if( is_array($newValue) || in_array($name, $this->arrayType) ){
			$result = $ntsdb->delete( 
				'conf',
				array(
					'name' => array('=', $name)
					)
				);
			reset( $newValue );
			foreach( $newValue as $nv ){
				$result = $ntsdb->insert( 'conf', array('value' => $nv, 'name' => $name) );
				}
			}
		else {
			$sql = "SELECT value FROM {PRFX}conf WHERE name = '$name'";
			$result = $ntsdb->runQuery( $sql );
			$update = ( $oInfo = $result->fetch() ) ? true : false;

		/* update */
			if( $update ){
				$result = $ntsdb->update(
					'conf',
					array('value' => $newValue),
					array(
						'name' => array('=', $name)
						)
					);
				}
		/* insert */
			else {
				$result = $ntsdb->insert( 'conf', array('value' => $newValue, 'name' => $name) );
				}
			}

		$this->error = $result ? '' : $ntsdb->getError();
		if( ! $this->error ){
			unset( $this->_cache[$name] );
			$this->rawValues[$name] = $newValue;
			}
		return $result;
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsConf' );
		}
	}
?>