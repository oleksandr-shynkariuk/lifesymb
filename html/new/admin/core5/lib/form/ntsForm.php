<?php
class ntsFormFactory {
	function ntsFormFactory(){
		$this->forms = array();
		$this->_fileCache = array();
		}

	function &makeForm( $formFile, $defaults = array(), $key = '', $idPrefix = '' ){
		$formFile = str_replace( '\\', '/', $formFile );
		$index = ( strlen($key) ) ? $formFile . $key : $formFile;	
		if( ! isset($this->forms[$index]) ){
			$form = new ntsForm( $formFile, $defaults, $idPrefix );
			$this->forms[$index] = $form;
			}
		return $this->forms[$index];
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsFormFactory' );
		}
	}

class ntsForm {
	function ntsForm( $formFile, $defaults = array(), $idPrefix = '' ){
		$this->formFile = $formFile;

		$this->defaults = $defaults;
		$this->inputs = array();

		$this->errors = array();
		$this->values = array();
		$this->params = array();

		$this->requiredFields = 0;
		$this->formAction = 'display';
		$this->readonly = false;

		$this->skipRequiredAlert = false;
		$this->valid = true;

		$this->_controlsCache = array();
		$this->useCache = false;
		$this->formId = '';
		$this->idPrefix = $idPrefix;
		$this->noprint = FALSE;
		}

	function setParams( $params ){
		$this->params = $params;
		}

/* Builds the set of hidden fields for panel, action, and params */
	function makePostParams( $panel, $action = '', $params = array() ){
		global $_NTS, $NTS_PERSISTENT_PARAMS;

		if( preg_match('/^\-current\-/', $panel) ){
			$replaceFrom = '-current-';
			$replaceTo = $_NTS['CURRENT_PANEL'];

			if( strlen($replaceTo) && preg_match('/\/\.\./', $panel) ){
				$downCount = substr_count( $panel, '/..' );
				$re = "/^(.+)(\/[^\/]+){" . $downCount. "}$/U";
				preg_match($re, $replaceTo, $ma);
				$replaceFrom = '-current-' . str_repeat('/..', $downCount);
				$replaceTo = $ma[1];
				}
			$panel = str_replace( $replaceFrom, $replaceTo, $panel );
			}

		$params[ NTS_PARAM_PANEL ] = $panel;
		if( strlen($action) )
			$params[ NTS_PARAM_ACTION ] = $action;

		if( $NTS_PERSISTENT_PARAMS ){
			reset( $NTS_PERSISTENT_PARAMS );
		/* global */
			if( isset($NTS_PERSISTENT_PARAMS['/']) ){
				reset( $NTS_PERSISTENT_PARAMS['/'] );
				foreach( $NTS_PERSISTENT_PARAMS['/'] as $p => $v ){
					$params[ $p ] = $v;
					}
				}
		/* above panel */
			reset( $NTS_PERSISTENT_PARAMS );
			foreach( $NTS_PERSISTENT_PARAMS as $pan => $pampam ){
				if( substr($panel, 0, strlen($pan) ) != $pan )
					continue;
				reset( $pampam );
				foreach( $pampam as $p => $v )
					$params[ $p ] = $v;
				}
			}

		reset( $params );
		$postParts = array();
		foreach( $params as $p => $v ){
			$realP = ntsView::setRealName( $p );
			if( is_array($v) ){
				foreach( $v as $va )
					$postParts[] = '<INPUT TYPE="hidden" NAME="' . $realP . '[]" VALUE="' . $va . '">';
				}
			else
				$postParts[] = '<INPUT TYPE="hidden" NAME="' . $realP . '" VALUE="' . $v . '">';
			}

		$post = join( "\n", $postParts );
		return $post;
		}

	function getName(){
		return $this->formId;
		}

	function display( $vars = array(), $skipEnd = false, $skipStart = false ){
		global $_NTS, $NTS_VIEW, $NTS_CURRENT_USER;
		$this->requiredFields = 0;
		$this->formAction = 'display';

	// START UP HTML
		$this->formId = 'nts_form_' . $this->idPrefix . ntsLib::generateRand(6, array('caps' => false));
		$startUp = '';

	// NOW DISPLAY CONTROLS
		$displayFile = $this->formFile . '.php';
		ob_start();
		require( $displayFile );
		$formContent = ob_get_contents();
	// SHOW REQUIRED TEXT
		if( $this->requiredFields > 0 && ( ! $this->skipRequiredAlert) ){
			$formContent = "\n<P>" . '<i>' . '* ' . M('Required field') . '</i></p>' . $formContent;
			}
		ob_end_clean();

		if( ! $skipStart ){
			$startUp .= "\n" . '<FORM METHOD="post"';
			if( defined('NTS_ROOT_WEBPAGE') ){
				$startUp .= ' ACTION="' . NTS_ROOT_WEBPAGE . '"';
				}
			$startUp .=  ' ENCTYPE="multipart/form-data"';
			$startUp .= ' NAME="' . $this->formId . '"';
			$startUp .= ' ID="' . $this->formId . '"';
			$startUp .= ">\n";
			}

		if( $this->noprint ){
			ob_start();
			}

		echo $startUp;
		echo $formContent;

	// END HTML
		if( ! $skipEnd ){
			$end = '';
			$end .= '</FORM>';
			echo $end;
			}

		if( $this->noprint ){
			$return = ob_get_contents();
			ob_end_clean();
			return $return;
			}
		}

/* registers input */
	function registerInput( $type, $inputArray, $validators = array() ){
		$conf = array_merge( $this->_inputDefaults(), $inputArray );
		$conf[ 'type' ] = $type;
		$conf[ 'validators' ] = $validators;
		$this->inputs[] = $conf;
		}

/* builds input HTML code */
	function makeInput( $type, $inputArray, $validators = array() ){
		$return = '';

		if( $this->readonly )
			$inputArray[ 'readonly' ] = 1;

		if( $this->formAction == 'validate' ){
			return $this->registerInput( $type, $inputArray, $validators );
			}

		$conf = array_merge( $this->_inputDefaults(), $inputArray );

		if( $type == 'radio' ){
			$conf['groupValue'] = $this->getValue( $conf['id'], $conf['default'] );
			}

		if( ! isset($conf['value']) ){
			$conf['value'] = $this->getValue( $conf['id'], $conf['default'] );
			}

	/* if it is one entry only */
		reset( $validators );
		foreach( $validators as $va ){
			$shortValidatorName = basename( $va['code'], '.php' );
			if( ($shortValidatorName == 'oneEntryOnly') && ( strlen($conf['value']) > 0 ) ){
				$conf['attr']['readonly'] = 'readonly';
				$conf['attr']['disabled'] = 'disabled';
				}
			}

		$conf['error'] = $this->_getErrorForInput( $conf['id'] );
		$input = '';
		$inputAction = 'display';
		if( ! isset($conf['htmlId']) ){
			$conf['htmlId'] = $this->formId . $conf['id'];
			}

	// INCLUDE THE RIGHT INPUT FILE
		$inputFile = 'lib/form/inputs/' . $type . '.php';
		$inputFile = ntsLib::fileInCoreDirs( $inputFile );

		if( ! $inputFile ){
			echo $shortName . ' file does not exist!<BR';
			return;
			}

		$conf['id'] = ntsView::setRealName( $conf['id'] );
		if( $this->useCache ){
			if( ! isset($this->_controlsCache[$inputFile]) ){
				$code2run = file_get_contents( $inputFile );
				$code2run = str_replace( '<?php', '', $code2run );
				$code2run = str_replace( '?>', '', $code2run );
				$this->_controlsCache[$inputFile] = $code2run;
				}
			$code2run = $this->_controlsCache[$inputFile];
			eval( $code2run );
			}
		else {
			require( $inputFile );
			}

		if( $conf['help'] )
			$input .= '<br /><i>' . $conf['help'] . '</i>';

	// COMPILE OUTPUT
		if( $conf['error'] )
			$return .= '<strong class="alert">' . $conf['error'] . '</strong><br />';
		$return .= $input;

		if( $conf['required'] )
			$this->requiredFields++;

		return $return;
		}

/* Validates form */
	function validate( $removeValidation = array(), $keepErrors = FALSE ){
		global $_NTS, $NTS_VIEW;
		$formValid = true;

		$this->inputs = array();
		if( ! $keepErrors )
			$this->errors = array();
		if( $this->errors )
			$formValid = false;
		$this->values = array();

		ob_start();
		$formFile = $this->formFile . '.php';
		$this->formAction = 'validate';
		require( $formFile );
		ob_end_clean();

	// NOW GRAB
		reset( $this->inputs );
		$supplied = array();
		foreach( $this->inputs as $controlConf ){
			$suppliedValue = $this->grabValue( $controlConf['id'], $controlConf['type'], $controlConf );
			$this->values[ $controlConf['id'] ] = $suppliedValue;
			$supplied[] = $controlConf['id'];
			}

	// NOW VALIDATE
		reset(  $this->inputs );
		$formValues = array_merge( $this->defaults, $this->values );

		$val = new ntsValidator;
		$val->formValues = array_merge( $this->defaults, $this->values );

		foreach(  $this->inputs as $controlConf ){
			$val->reset();
			$val->controlConf = $controlConf;
			$val->checkValue = $this->values[ $controlConf['id'] ];

			if( ! in_array($controlConf['id'], $supplied) )
				continue;
 			$checkValue = $this->values[ $controlConf['id'] ];

		/* built-in control validation */
			$inputAction = 'validate';
			$validationFailed = false;
			$validationError = '';

			$shortName = 'inputs/' . $controlConf['type'] . '.php';
			$handle = $controlConf['id'];

			$inputFile = 'lib/form/inputs/' . $controlConf['type'] . '.php';
			$inputFile = ntsLib::fileInCoreDirs( $inputFile );
			if( $inputFile )
			{
				require( $inputFile );
			}
			else
				echo $shortName . ' file does not exist!<BR';

			if( $validationFailed ){
				$this->errors[ $controlConf['id'] ] = $validationError;
				$formValid = false;
				break;
				}

			if( ! isset($controlConf['validators']) )
				continue;
	
		/* external validation */
			$val->reset();
			$val->controlConf = $controlConf;
			$val->checkValue = $this->values[ $controlConf['id'] ];
			if( (! $removeValidation) || (! in_array($controlConf['id'],$removeValidation) ) ){
				reset( $controlConf['validators'] );
				foreach( $controlConf['validators'] as $validatorInfo ){
					$val->validationParams = ( isset($validatorInfo['params']) ) ? $validatorInfo['params'] : array();

					if( ! $val->run($validatorInfo['code']) ){
						$validationError = $val->getError();
						if( ! $validationError )
							$validationError = $validatorInfo['error'];

						$this->errors[ $controlConf['id'] ] = $validationError;

						$formValid = false;
						break;
						}
					}
				}
			}

		$this->valid = $formValid;
		return $formValid;
		}

	function setValue( $ctlId, $ctlValue ){
		$this->values[ $ctlId ] = $ctlValue;
		}

	function getValues(){
		return $this->values;
		}

/* Prefills an input attributes */
	function _inputDefaults(){
		$def = array(
			'id'		=> ntsLib::generateRand(6, array('caps' => false)),
			'label'		=> '',
			'default'	=> '',
			'help'		=> '',
			'attr'		=> array(),
			'required'	=> 0,
			);
		return $def;
		}

	function getDefaults(){
		return $this->defaults;
		}

/* Checks if a value has been supplied, or returns default otherwise */
	function getValue( $name, $defaultValue = '' ){
		if( isset($this->values[$name]) ){
			$value = $this->values[$name];
			}
		elseif( isset($this->defaults[$name]) ){
			$value = $this->defaults[$name];
			}
		else {
			$value = $defaultValue;
			}
		return $value;
		}

/* Checks if a validation error happend for this input */
	function _getErrorForInput( $name ){
		$error = ( isset($this->errors[$name]) ) ? $this->errors[$name] : '';
		return $error;
		}

/* Builds HTML string with input attributes */
	function _makeInputParams( $params = array() ){
		$paramsCode = array();
		reset( $params );
		foreach( $params as $key => $value ){
			if( $key == '_class' )
				continue;
			if( is_array($value) )
				continue;
			$paramsCode[] = $key . '="' . htmlspecialchars($value) . '"';
			}
		$return = join( ' ', $paramsCode );
		return $return;
		}

/* Grabs an input value - actual code in the input file */
	function grabValue( $handle, $type = '', $conf = array() ){
		global $_NTS, $NTS_VIEW;

		$input = '';
		$inputAction = 'submit';

	// INCLUDE THE RIGHT INPUT FILE
		$shortName = 'inputs/' . $type . '.php';

		$inputFile = 'lib/form/inputs/' . $type . '.php';
		$inputFile = ntsLib::fileInCoreDirs( $inputFile );
		if( $inputFile )
			require( $inputFile );
		else
			echo $shortName . ' file does not exist!<BR';

	/* if not admin then strip HTML tags */
		global $NTS_CURRENT_USER;
		if( ! $NTS_CURRENT_USER->hasRole('admin') ){
			if( is_array($input) ){
				}
			else {
				$input = strip_tags( $input );
				}
			}

		return $input;
		}

/* Checks if an input has been really supplied - actual code in the input file */
	function inputSupplied( $handle, $type = '' ){
		$input = '';
		$inputAction = 'check_submit';

	// INCLUDE THE RIGHT INPUT FILE
		$shortName = 'inputs/' . $type . '.php';

		$inputFile = 'lib/form/inputs/' . $type . '.php';
		$inputFile = ntsLib::fileInCoreDirs( $inputFile );
		$handle = ntsView::setRealName( $handle );
		if( $inputFile )
			require( $inputFile );
		else
			echo $shortName . ' file does not exist!<BR';

		return $input;
		}
	}
?>