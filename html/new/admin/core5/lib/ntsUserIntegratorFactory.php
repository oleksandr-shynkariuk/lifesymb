<?php
class ntsUserIntegratorFactory {
	var $integrator;

	function ntsUserIntegratorFactory(){
		if( defined('NTS_REMOTE_INTEGRATION') ){
			$authIntegratorFile = NTS_REMOTE_INTEGRATION . '/' . NTS_REMOTE_INTEGRATION . '.php';
			$authIntegratorClass = NTS_REMOTE_INTEGRATION . 'Integrator';
			include_once( NTS_APP_DIR . '/integration/' . $authIntegratorFile );
			$this->integrator = new $authIntegratorClass;
			}
		else {
			include_once( NTS_BASE_DIR . '/lib/ntsUserIntegratorBuiltin.php' );
			$this->integrator = new ntsUserIntegratorBuiltin;
			}
		}

	function &getIntegrator(){
		return $this->integrator;
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsUserIntegratorFactory' );
		}
	}
?>