<?php
define( 'NTS_LANGUAGE_COOKIE_NAME', 'rtr-language' );
require_once( NTS_BASE_DIR . '/lib/xml/xml-simple.php' );

function M( $str, $params = array(), $skipCustom = false ){
	if( ! isset($GLOBALS['NTS_CURRENT_LANGUAGE']) ){
		$lm =& ntsLanguageManager::getInstance();
		}

	$return = '';
	/* get in database is any custom tweaks */
	if( ! $skipCustom ){
		$conf =& ntsConf::getInstance();
		$realPropName = 'text-' . $str;
		$return = $conf->get( $realPropName );
		}

	if( ! $return ){
		if( $GLOBALS['NTS_CURRENT_LANGUAGE'] == 'en-builtin' ){
			$return = $str;
			}
		else {
			$languageConf = $GLOBALS['NTS_CURRENT_LANGUAGE_CONF'];

		/* replace html if any */
			$str = preg_replace( '/\<(.+)\>/U', '[\\1]', $str );

			if( isset($languageConf['interface'][$str]) )
				$return = $languageConf['interface'][$str];
			else
				$return = $str;
			}
		}

	/* put back html if any */
	$return = preg_replace( '/\[(.+)\]/U', '<\\1>', $return );

	if( $params ){
		reset( $params );
		foreach( $params as $key => $value ){
			$return = str_replace( '{' . $key . '}', $value, $return );
			}
		}

	return $return;
	}

function M2( $str, $params = array() ){
	return M( $str, $params );
	}

class ntsLanguageManager {
	var $dir;
	var $languages;

	function ntsLanguageManager(){
		$this->dir = NTS_EXTENSIONS_DIR . '/languages';
		$this->languages = array();
		$this->init();
		}

	function init()
	{
		global $NTS_CURRENT_USER;
		if( isset($NTS_CURRENT_USER) && $NTS_CURRENT_USER )
		{
			$this->setLanguage( $NTS_CURRENT_USER->getLanguage() );
		}
		else
		{
			$this->setLanguage( $this->getDefaultLanguage() );
		}
	}


	function setLanguage( $lng )
	{
		$currentLanguage = $lng;
		$languageConf = $this->getLanguageConf( $currentLanguage );

	/* problem with this file */
		while( isset($languageConf['error']) && $languageConf['error'] )
		{
		/* remove from active languages */
			$newSetting = $this->languageDisable( $currentLanguage );
			$conf->set( 'languages', $newSetting );
			if( $newSetting )
				$currentLanguage = $newSetting[0];
			else
				$currentLanguage = 'en-builtin';
			$languageConf = $this->getLanguageConf( $currentLanguage );
		}

	/* file ok */
		$GLOBALS['NTS_CURRENT_LANGUAGE'] = $currentLanguage;
		$GLOBALS['NTS_CURRENT_LANGUAGE_CONF'] = $languageConf;
	}

	function languageActivate( $newLanguage ){
		$conf =& ntsConf::getInstance();
		$setting = $conf->get( 'languages' );

		$languageAdded = '';
		if( ! in_array($newLanguage, $setting) ){
			$setting[] = $newLanguage;
//			$setting = array( $newLanguage );
			$languageAdded = $newLanguage;
			}

		if( $languageAdded == 'en' ){
			$temp = $setting;
			$setting = array();
			reset( $temp );
			foreach( $temp as $t ){
				if( $t != 'en-builtin' ){
					$setting[] = $t;
//					$setting = array( $newLanguage );
					}
				}
			}
		if( $languageAdded == 'en-builtin' ){
			$temp = $setting;
			$setting = array();
			reset( $temp );
			foreach( $temp as $t ){
				if( $t != 'en' ){
					$setting[] = $t;
//					$setting = array( $newLanguage );
					}
				}
			}
		return $setting;
		}

	function languageDisable( $disableLanguage ){
		$conf =& ntsConf::getInstance();
		$setting = $conf->get( 'languages' );

		$newSetting = array();
		reset( $setting );
		foreach( $setting as $s ){
			if( $s == $disableLanguage )
				continue;
			$newSetting[] = $s;
			}

		return $newSetting;
		}

	function getDefaultLanguage(){
		$activeLanguages = $this->getActiveLanguages();
		if( ! $activeLanguages )
			$activeLanguages = array( 'en-builtin' );
		$lng = $activeLanguages[0];
		return $lng;
		}

	function getActiveLanguages(){
		$active = array();
		$conf =& ntsConf::getInstance();
		$languages = $this->getLanguages();
		$activeLanguages = $conf->get('languages');

		reset( $languages );
		foreach( $languages as $l ){
			if( in_array($l, $activeLanguages) )
				$active[] = $l;
			}

		if( ! $active )
			$active = array( 'en-builtin' );

		return $active;
		}

	function getLanguages(){
		$languages = array();

		$folders = ntsLib::listSubfolders( $this->dir );
		reset( $folders );
		foreach( $folders as $folder ){
			$fileName = $this->dir . '/' . $folder . '/interface.xml';
			if( file_exists($fileName) ){
				$languages[] = $folder;
				}
			}

		array_unshift( $languages, 'en-builtin' );
		return $languages;
		}

	function getLanguageConf( $lng ){
		if( $lng == 'en-builtin' ){
			$return = array(
				'language'	=> 'English Built-In',
				'error'		=> '',
				'charset'	=> 'utf-8',
				);
			return $return;
			}

		if( ! isset($this->languages[$lng]) ){
			$this->loadLanguageFile( $lng );
			}
		return $this->languages[$lng];
		}

	function loadLanguageFile( $lng ){
		$f = $lng . '/interface.xml';

		if( $lng == 'languageTemplate' )
			$fullFileName = NTS_APP_DIR . '/defaults/language/interface.xml';
		else
			$fullFileName = $this->dir . '/' . $f;
//echo "loading lang file $fullFileName<br>";

		if( ($lng != 'en-builtin') && file_exists($fullFileName) ){
			$thisLangStrings = array();
			$xmlCode = ntsLib::fileGetContents( $fullFileName );

			/* get first line to see if encoding is defined */
			$firstLine = ntsLib::fileGetFirstLine( $fullFileName );
			$re = '/encoding\s*=\s*[\'|\"](.+)[\'|\"]/U';
			if( preg_match($re, $firstLine, $ma) ){
				$encoding = $ma[1];
				$parser = new xml_simple( $encoding );
				}
			else {
				$parser = new xml_simple();
				}

			$languageConf = $parser->parse( $xmlCode );
			$languageConf['error'] = '';
			$thisLangStrings = array();

			if( ! $parser->error ){
				if( (! isset($languageConf['string'][0])) && is_array($languageConf['string']) ){
					$languageConf['string'] = array( $languageConf['string'] );
					}
				reset( $languageConf );
				foreach( $languageConf['string'] as $a ){
					$thisLangStrings[ $a['original'] ] = $a['translate'];
					}
				}
			else {
				$languageConf['error'] = $parser->error;
				}

			/* if more than one country is enabled, add the country names as well */
			if( $lng == 'languageTemplate' ){
				$conf =& ntsConf::getInstance();
				$currentCountries = $conf->get('countries');
				if( count($currentCountries) > 1 ){
					require( NTS_APP_DIR . '/helpers/countries.php' );
					reset( $currentCountries );
					foreach( $currentCountries as $ccode )
						$thisLangStrings[ $countries[$ccode] ] = $countries[$ccode];
					}
				}

			$languageConf['interface'] = $thisLangStrings;

			/* also add templates */
			$tm =& ntsEmailTemplateManager::getInstance();
			$templateLng = ( $lng == 'languageTemplate' ) ? 'en' : $lng;
			$templateKeys = $tm->getKeys( $templateLng );

			$languageConf['templates'] = array();
			reset( $templateKeys );
			foreach( $templateKeys as $tk )
				$languageConf['templates'][ $tk ] = '';

			$this->languages[ $lng ] = $languageConf;
			}
		else {
			echo "language file '$fullFileName' doesn't exist!";
			}
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsLanguageManager' );
		}
	}
?>