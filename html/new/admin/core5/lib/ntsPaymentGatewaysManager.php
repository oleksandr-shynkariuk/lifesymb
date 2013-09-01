<?php
class ntsPaymentGatewaysManager {
	var $dir;
	var $gateways;

	function ntsPaymentGatewaysManager(){
		$this->dir = NTS_BASE_DIR . '/payment';
		$this->gateways = array();
		$this->allCurrencies = array(
			array( 'usd', 'USD U.S. Dollar' ),
			array( 'eur', 'EUR Euro' ),
			array( 'gbp', 'GBP Pound Sterling' ),
			array( 'aud', 'AUD Australian Dollar' ),
			array( 'cad', 'CAD Canadian Dollar'),
			array( 'jpy', 'JPY Japanese Yen' ),
			array( 'nzd', 'NZD New Zealand Dollar' ),
			array( 'chf', 'CHF Swiss Franc' ),
			array( 'hkd', 'HKD Hong Kong Dollar'),
			array( 'sgd', 'SGD Singapore Dollar' ),
			array( 'sek', 'SEK Swedish Krona' ),
			array( 'dkk', 'DKK Danish Krone' ),
			array( 'pln', 'PLN Polish Zloty' ),
			array( 'nok', 'NOK Norwegian Krone' ),
			array( 'huf', 'HUF Hungarian Forint' ),
			array( 'czk', 'CZK Czech Koruna' ),
			array( 'ils', 'ILS Israeli Shekel' ),
			array( 'mxn', 'MXN Mexican Peso' ),
			array( 'brl', 'BRL Brazilian Real' ),
			array( 'myr', 'MYR Malaysian Ringgits' ),
			array( 'php', 'PHP Philippine Pesos' ),
			array( 'twd', 'TWD Taiwan New Dollars' ),
			array( 'thb', 'THB Thai Baht' ),
			array( 'vef', 'VEF Venezuelan Bolivar' ),
			array( 'xcd', 'XCD East Caribbean Dollar' ),
			array( 'ars', 'ARS Argentine Peso' ),
			array( 'inr', 'INR Indian Rupee' ),
			array( 'ltl', 'LTL Lithuanian Litas' ),
			array( 'ron', 'RON Romanian New Leu' ),
			array( 'rub', 'RUB Russian Ruble' ),
			array( 'zar', 'ZAR South African Rand' ),
			array( 'try', 'TRY Turkish Lira' ),
			array( 'aed', 'AED United Arab Emirates Dirham' ),
			);
		}

	function getAllCurrencies(){
		return $this->allCurrencies;
		}

	function gatewayActivate( $newGateway ){
		$conf =& ntsConf::getInstance();
		$setting = $conf->get( 'paymentGateways' );

		$gatewayAdded = '';
		if( ! in_array($newGateway, $setting) ){
			$setting[] = $newGateway;
			$gatewayAdded = $newGateway;
			}
		return $setting;
		}

	function gatewayDisable( $disableGateway ){
		$conf =& ntsConf::getInstance();
		$setting = $conf->get( 'paymentGateways' );

		$newSetting = array();
		reset( $setting );
		foreach( $setting as $s ){
			if( $s == $disableGateway )
				continue;
			$newSetting[] = $s;
			}

		return $newSetting;
		}

	function getActiveGateways(){
		$active = array();
		$conf =& ntsConf::getInstance();
		$gateways = $this->getGateways();
		$activeGateways = $conf->get('paymentGateways');

		reset( $gateways );
		foreach( $gateways as $g ){
			if( in_array($g, $activeGateways) )
				$active[] = $g;
			}
		return $active;
		}

	function getGateways(){
		$gateways = array();

		$folders = ntsLib::listSubfolders( $this->dir );
		reset( $folders );
		foreach( $folders as $f ){
			$gateways[] = $f;
			}

		return $gateways;
		}

	function getGatewayName( $gtw ){
		$return = $gtw;
		return $return;
		}

	function getGatewayCurrencies( $gtw ){
		$return = array();

		$file = $this->getGatewayFolder( $gtw ) . '/currencies.php';
		if( file_exists($file) ){
			require( $file );
			if( isset($currencies) )
				$return = $currencies;
			}
		else {
			reset( $this->allCurrencies );
			foreach( $this->allCurrencies as $c )
				$return[] = $c[0];
			}
		return $return;
		}

	function getActiveCurrencies(){
		$allowedCurrencies = array();
		$paymentGateways = $this->getActiveGateways();
		reset( $paymentGateways );
		$count = 0;
		foreach( $paymentGateways as $pg ){
			$thisCurrencies = $this->getGatewayCurrencies( $pg );
			// first one, init
			if( ! $count ){
				$allowedCurrencies = $thisCurrencies;
				}
			else {
				$allowedCurrencies = array_intersect( $allowedCurrencies, $thisCurrencies );
				}
			$count++;
			}
		$return = array_unique( $allowedCurrencies );
		return $return;
		}

	function getGatewayFolder( $gtw ){
		$folderName = $gtw;
		$fullFolderName = $this->dir . '/' . $folderName;
		return $fullFolderName;
		}

	function getGatewaySettings( $gtw ){
		$return = array();
		$conf =& ntsConf::getInstance();

		$confPrefix = 'payment-gateway-' . $gtw . '-';
		$allSettingsNames = $conf->getLoadedNames();
		reset( $allSettingsNames );
		foreach( $allSettingsNames as $confName ){
			if( substr($confName, 0, strlen($confPrefix)) == $confPrefix ){
				$shortName = substr($confName, strlen($confPrefix));
				$confValue = $conf->get( $confName );
				$return[ $shortName ] = $confValue;
				}
			}
		return $return;
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsPaymentGatewaysManager' );
		}
	}
?>