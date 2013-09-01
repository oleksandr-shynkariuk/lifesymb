<?php
class ntsObjectMapper {
	var $tables;
	var $props;
	var $controls = array();
	var $service2form = array();
	var $fieldsCache = array(); 

	function ntsObjectMapper(){
		$this->tables = array();
		$this->props = array();
		$this->controls = array();
		$this->service2form = array();
		$this->fieldsCache = array();
		$this->init();

	// common props registration
		$this->registerClass( 'user', 'users' );
		if( ! NTS_EMAIL_AS_USERNAME )
			$this->registerProp( 'user',	'username' );
		$this->registerProp( 'user',	'email' );
		$this->registerProp( 'user',	'password' );
		$this->registerProp( 'user',	'first_name' );
		$this->registerProp( 'user',	'last_name' );
		$this->registerProp( 'user',	'lang' );
		$this->registerProp( 'user',	'created' );
		$this->registerProp( 'user',	'_restriction',	false,	1,	array() );
		$this->registerProp( 'user',	'_timezone',	false,	0,	NTS_COMPANY_TIMEZONE );
		$this->registerProp( 'user',	'_role',				false,	1,	array(NTS_DEFAULT_USER_ROLE) );
		$this->registerProp( 'user',	'_disabled_panels',		false,	1,	array() );

		$this->registerClass( 'form', 'forms' );
		$this->registerProp( 'form',	'title' );
		$this->registerProp( 'form',	'class' );
		$this->registerProp( 'form',	'details' );

		$this->registerClass( 'form_control', 'form_controls' );
		$this->registerProp( 'form_control',	'form_id' );
		$this->registerProp( 'form_control',	'name' );
		$this->registerProp( 'form_control',	'type' );
		$this->registerProp( 'form_control',	'title' );
		$this->registerProp( 'form_control',	'description' );
		$this->registerProp( 'form_control',	'show_order' );
		$this->registerProp( 'form_control',	'ext_access' );
		$this->registerProp( 'form_control',	'attr' );
		$this->registerProp( 'form_control',	'validators' );
		$this->registerProp( 'form_control',	'default_value' );
		}

	function initPropsConfig( $className ){
		global $NTS_OBJECT_PROPS_CONFIG;
		if( ! isset($NTS_OBJECT_PROPS_CONFIG[$className]) ){
			list( $coreProps, $metaProps ) = $this->getPropsForClass( $className );
			$NTS_OBJECT_PROPS_CONFIG[ $className ] = array_merge( $coreProps, $metaProps );
			}
		}

	function makeTags_Customer( $object, $access = 'external' ){
		$fields = $this->getFields( 'customer', $access );
		$tags = array( array(), array() );

		$allInfo = '';
		foreach( $fields as $f ){
			$value = $object->getProp( $f[0] );
			if( $f[2] == 'checkbox' ){
				$value = $value ? M('Yes') : M('No');
				}

			$c = $this->getControl( 'customer', $f[0], false );
			if( $c[2]['description'] ){
				$value .= ' (' . $c[2]['description'] . ')';
				}

			$tags[0][] = '{USER.' . strtoupper($f[0]) . '}';
			$tags[1][] = $value;

		/* build the -ALL- tag */
			$allInfo .= M($f[1]) . ': ' . $value . "\n";
			}

		if( NTS_EMAIL_AS_USERNAME ){
			$tags[0][] = '{USER.USERNAME}';
			$tags[1][] = $object->getProp( 'email' );
			}

		$tags[0][] = '{USER.PASSWORD}';
		$newPasssword = $object->getProp( 'new_password' );
		if( $newPasssword )
			$tags[1][] = $newPasssword;
		else
			$tags[1][] = M('Not Shown For Security Reasons');

		$tags[0][] = '{USER.-ALL-}';
		$tags[1][] = $allInfo;
		return $tags;
		}

	function registerClass( $className, $storeTable ){
		$this->tables[ $className ] = $storeTable;
		$this->props[ $className ] = array();
		$this->registerProp( $className, 'id', true, false, 0 );
		}

/* $pArray: (pName, isCore, isArray, default) */
	function registerProp( $className, $pName, $isCore = true, $isArray = false, $default = '' ){
		$this->props[ $className ][ $pName ] = array(
			'isCore'	=> $isCore,
			'isArray'	=> $isArray,
			'default'	=> $default,
			);
		}

	function getTableForClass( $className ){
		$return = '';
		if( isset($this->tables[ $className ]) )
			$return = $this->tables[ $className ];
		else
			echo "getTableForClass: Class '$className' is not registered!";
		return $return;
		}

	function isClassRegistered( $className ){
		$return = FALSE;
		if( isset($this->tables[ $className ]) )
			$return = TRUE;
		return $return;
		}
	
	function isPropRegistered( $className, $propName ){
		$return = false;
		list( $core, $meta ) = $this->getPropsForClass( $className );
		$propNames = array_merge( array_keys($core), array_keys($meta) );
		if( in_array($propName, $propNames) ){
			$return = true;
			}
		return $return;
		}

	function getPropsForClass( $className ){
		$core = array();
		$meta = array();
		$return = array( $core, $meta );

		if( isset($this->props[ $className ]) ){
			reset( $this->props[ $className ] );
			foreach( $this->props[ $className ] as $pName => $pa ){
				if( $pa['isCore'] )
					$core[ $pName ] = $pa;
				else
					$meta[ $pName ] = $pa;
				}
			$return = array( $core, $meta );
			}
		else {
//			echo "getPropsForClass: Class '$className' is not registered!";
			}

		return $return;
		}
		
	function isFormForService( $serviceId ){
		$return = isset($this->service2form[$serviceId]) ? $this->service2form[$serviceId] : 0;
		return $return;
		}

	/*
	$side = internal|external
	*/
	function getFields( $className, $side = 'internal', $otherProps = array() ){
//		$this->fieldsCache;

		$cacheString = $className . '-' . $side;
		if( $otherProps ){
			$cacheString .= '-' . serialize($otherProps);
			}

		if( ! isset($this->fieldsCache[$cacheString]) ){
			$return = array();
			if( isset($this->controls[$className]) ){
				uasort( $this->controls[$className], create_function('$a, $b', 'return ($a["show_order"] - $b["show_order"]);') );
				reset( $this->controls[$className] );
				foreach( $this->controls[$className] as $cName => $c ){
					if( $className == 'appointment' ){
						$serviceId = isset($otherProps['service_id']) ? $otherProps['service_id'] : 0;
						if( $serviceId != -1 ){
							$requireForm = $this->isFormForService( $serviceId );
							if( $requireForm != $c['form_id'] )
								continue;
							}
						}

					if( $side == 'external' ){
						if( $c['ext_access'] == 'hidden' )
							continue;
						}
					if( ($side == 'external') && ($c['ext_access'] == 'read') ){
						$accessLevel = 'read';
						}
					else {
						$accessLevel = 'write';
						}

					if( NTS_EMAIL_AS_USERNAME && ($c['name'] == 'username') )
						continue;
						
					$return[] = array( $c['name'], $c['title'], $c['type'], $c['default'], $accessLevel );
					}
				}
			$this->fieldsCache[$cacheString] = $return;
			}
		else {
//			echo "fields '$cacheString' already set<br>";
			}
		$return = $this->fieldsCache[$cacheString];
		return $return;
		}

	function getControl( $className, $name, $forSearch = false ){
		$return = array();

		if( ! isset($this->controls[$className][$name]) ){
			echo "Control '$name' not defined for '$className'!<br>";
			return $return;
			}

		$c = $this->controls[$className][$name];
		if( ! $forSearch ){
			if( isset($c['validators']) && $c['validators'] ){
				$c['title'] .= ' *';
				}
			}

		$attributes = array(
			'id'	=> $c['name'],
			'attr'	=> $c['attr']
			);

		$attributes = array();
		if( $c['type'] == 'select' )
		{
			$attributes['options'] = $c['attr']['options'];
			unset($c['attr']['options']);
			if( $forSearch )
			{
				array_unshift( $attributes['options'], array( '-any-', M('- Any -') ) );
			}
		}

		$c['attr']['_class'] = $className;
		$attributes['id'] = $c['name'];
		$attributes['attr'] = $c['attr'];
		if( isset($c['description']) )
			$attributes['description'] = $c['description'];
		else
			$attributes['description'] = '';

		$attributes['default'] = $c['default'];

		$return = array(
			$c['title'],
			$c['type'],
			$attributes,
			$c['validators']
			);

		return $return;
		}

	function prepareMeta( $objId, $metaClass, $metaInfo, $full = true ){
		global $NTS_OBJECT_PROPS_CONFIG;
		$return = array();
		reset( $metaInfo );
		foreach( $metaInfo as $k => $va ){
//			if( ! isset( $NTS_OBJECT_PROPS_CONFIG[$metaClass][$k] ) )
//				continue;

			if( ! is_array($va) )
				$va = array( $va );

			reset( $va );
			foreach( $va as $kk => $v ){
				if( ! strlen($v) )
					continue;
				$v = trim($v);
				if(
					isset( $NTS_OBJECT_PROPS_CONFIG[$metaClass][$k]['isArray'] ) && 
					( $NTS_OBJECT_PROPS_CONFIG[$metaClass][$k]['isArray'] == 2 ) 
					){
					$metaArray = array(
						'meta_name'		=> $k,
						'meta_value'	=> $kk,
						'meta_data'		=> $v,
						);
					}
				elseif( 
					isset( $NTS_OBJECT_PROPS_CONFIG[$metaClass][$k]['isArray'] ) && 
					( $NTS_OBJECT_PROPS_CONFIG[$metaClass][$k]['isArray'] == 3 ) 
					){
					$metaArray = array(
						'meta_name'		=> $k,
						'meta_value'	=> $kk,
						'meta_data'		=> $v,
						);
					}
				else {
					$metaArray = array(
						'meta_name'		=> $k,
						'meta_value'	=> $v,
						'meta_data'		=> '',
						);
					}
				if( $full ){
					$metaArray[ 'obj_id' ] = $objId;
					$metaArray[ 'obj_class' ] = $metaClass;
					}
				$return[] = $metaArray;
				}
			}
		return $return;
		}

	function deleteMeta( $object, $metaName, $metaValue = '' ){
		$result = true;
		$ntsdb =& dbWrapper::getInstance();
		$id = $object->getId();
		$metaClass = $object->getMetaClass();
		if( $metaClass ){
			if( $metaValue ){
				$result = $ntsdb->delete(
					'objectmeta',
					array(
						'obj_id'		=> array( '=', $id ),
						'obj_class'		=> array( '=', $metaClass ),
						'meta_name'		=> array( '=', $metaName ),
						'meta_value'	=> array( '=', $metaValue ),
						)
					);
				}
			else {
				$result = $ntsdb->delete(
					'objectmeta',
					array(
						'obj_id'		=> array( '=', $id ),
						'obj_class'		=> array( '=', $metaClass ),
						'meta_name'		=> array( '=', $metaName ),
						)
					);
				}
			}
		return $result;
		}

	function init(){
		$ntsdb =& dbWrapper::getInstance();
		$controls = array();

	// user's
		$userControls = array();
		if( ! NTS_EMAIL_AS_USERNAME )
			$userControls[] = array( 'username',		'Username',	 	'text',	array('size' => 24), array( array('code' => 'notEmpty', 'error' => 'Required field'), array('code' => 'checkUsername', 'error' => 'Already in use', 'params' => array('skipMe'	=> 1) ) ) );
		$userControls[] = array( 'email',			'Email',			'text',	array('size' => 32), array( array('code' => 'notEmpty', 'error' => 'Required field'), array('code' => 'checkUserEmail', 'error' => 'Already in use', 'params' => array('skipMe'	=> 1) ) ) );
		$userControls[] = array( 'first_name',	'First Name',	'text',	array('size' => 32), array( array('code' => 'notEmpty', 'error' => 'Required field') ) );
		$userControls[] = array( 'last_name',		'Last Name',		'text',	array('size' => 32), array( array('code' => 'notEmpty', 'error' => 'Required field') ) );

		$order = 1;
		foreach( $userControls as $c ){
			$cInfo['class'] = 'user';
			$cInfo['ext_access'] = 'read';
			$cInfo['name'] = $c[0];
			$cInfo['title'] = $c[1];
			$cInfo['type'] = $c[2];
			$cInfo['attr'] = $c[3];

			$cInfo['validators'] = array();
			foreach( $c[4] as $v ){
				$v['code'] = $v['code'] . '.php';
				$cInfo['validators'][] = $v;
				}

			$cInfo['show_order'] = $order++;
			$controls[] = $cInfo;
			}

		// provider's
		$providerControls = array();
		if( ! NTS_EMAIL_AS_USERNAME )
			$providerControls[] = array( 'username',		'Username',	 	'text',	array('size' => 24), array( array('code' => 'notEmpty', 'error' => 'Required field'), array('code' => 'checkUsername', 'error' => 'Already in use', 'params' => array('skipMe'	=> 1) ) ) );
		$providerControls[] = array( 'email',			'Email',		'text',	array('size' => 32), array( array('code' => 'notEmpty', 'error' => 'Required field'), array('code' => 'checkUserEmail', 'error' => 'Already in use', 'params' => array('skipMe'	=> 1) ) ) );
		$providerControls[] = array( 'first_name',	'First Name',	'text',	array('size' => 32), array( array('code' => 'notEmpty', 'error' => 'Please enter the first name') ) );
		$providerControls[] = array( 'last_name',		'Last Name',	'text',	array('size' => 32), array( array('code' => 'notEmpty', 'error' => 'Please enter the last name') ) );
//		$providerControls[] = array( '_description',	'Description',	'textarea',	array('cols' => 42, 'rows' => 4), array() );

		$order = 1;
		foreach( $providerControls as $c ){
			$cInfo['class'] = 'provider';
			$cInfo['ext_access'] = 'read';
			$cInfo['name'] = $c[0];
			$cInfo['title'] = $c[1];
			$cInfo['type'] = $c[2];
			$cInfo['attr'] = $c[3];

			$cInfo['validators'] = array();
			foreach( $c[4] as $v ){
				$v['code'] = $v['code'] . '.php';
				$cInfo['validators'][] = $v;
				}

			$cInfo['show_order'] = $order++;
			$controls[] = $cInfo;
			}

		global $NTS_CURRENT_VERSION_NUMBER;
		$columns = array( 'form_id', 'name', 'type', 'title', 'show_order', 'ext_access', 'attr', 'validators', 'default_value' );
		if( $NTS_CURRENT_VERSION_NUMBER >= 4501 ){
			$columns[] = 'description';
			}

		$selectColumnsString = join( ",\n", array_map( create_function('$a', 'return "{PRFX}form_controls.' . '$a' . '";'), $columns) );
	// LOAD FROM DATABASE
		$sql =<<<EOT
SELECT 
	$selectColumnsString,
	(
	SELECT class FROM {PRFX}forms WHERE {PRFX}forms.id = {PRFX}form_controls.form_id
	) AS class
FROM 
	{PRFX}form_controls
ORDER BY
	show_order ASC
EOT;

		$result = $ntsdb->runQuery( $sql );
		if( $result ){
			while( $c = $result->fetch() ){
				if( isset($c['attr']) && $c['attr'] )
					$c['attr'] = unserialize($c['attr']);
				else
					$c['attr'] = array();

				if( isset($c['validators']) && $c['validators'] ){
					$validators = @unserialize($c['validators']);
					if( $validators ){
						reset( $validators );
						$c['validators'] = array();
						foreach( $validators as $v ){
							$v['code'] = $v['code'] . '.php';
							$c['validators'][] = $v;
							}
						}
					}
				else {
					$c['validators'] = array();
					}

				$c['default'] = $c['default_value'];
				$controls[] = $c;
				}
			}

		reset( $controls );
		foreach( $controls as $c ){
			$className = $c['class'];
			if( ! isset($this->controls[$className]) )
				$this->controls[$className] = array();

			if( ! isset($c['type']) )
				$c['type'] = 'text';

		/* default */
			if( ! isset($c['default']) )
				$c['default'] = '';

			if( $c['type'] == 'select' ){
				if( isset($c['attr']['options']) ){
					$rawOptions = $c['attr']['options'];
					$c['attr']['options'] = array();
					$c['default'] = $rawOptions[0];
					reset( $rawOptions );
					foreach( $rawOptions as $ro ){
						if( substr($ro, 0, 1) == '*' ){
							$ro = substr($ro, 1);
							$c['default'] = $ro;
							}
						$c['attr']['options'][] = array( $ro, $ro );
						}
					}
				else {
					$c['attr']['options'] = array();
					}
				}

			$this->controls[$className][$c['name']] = $c;
			/* className, pName, isCore, isArray, default */
			$this->registerProp( $className, $c['name'], false, false, $c['default'] );

			if( $c['name'] == 'mobile_phone' )
			{
				$plm =& ntsPluginManager::getInstance();
				$plugin = 'sms';
				$sms_settings = $plm->getPluginSettings( $plugin );
				if( 
					isset($sms_settings['gateway']) &&
					($sms_settings['gateway'] == 'email2sms') &&
					isset($sms_settings['carriers']) && 
					$sms_settings['carriers']
					)
				{
					$carrier_options = array();
					reset( $sms_settings['carriers'] );
					foreach( $sms_settings['carriers'] as $carrier )
					{
						$carrier_options[] = array( $carrier, $carrier );
					}
					if( $carrier_options ) 
					{
						array_unshift( $carrier_options, array('', ' - ' . 'Select' . ' - ') );
						$c2 = $c;
						$c2['name'] = 'mobile_carrier';
						$c2['type'] = 'select';
						$c2['default'] = '';
						$c2['title'] = 'Mobile Carrier';
						$c2['validators'] = array(
							array(
								'code' => 'notEmpty.php',
								'error' => 'Required field'
								)
							);

						$c2['attr'] = array(
							'options'	=> $carrier_options,
							);

						$this->controls[$className][$c2['name']] = $c2;
						/* className, pName, isCore, isArray, default */
						$this->registerProp( $className, $c2['name'], false, false, $c2['default'] );

					}
				}
			}
			}

	/* services to forms relation */
		$sql =<<<EOT
SELECT 
	obj_id AS service_id, meta_value AS form_id
FROM
	{PRFX}objectmeta
WHERE
	obj_class = "service" AND 
	meta_name = "_form"
EOT;
		$result = $ntsdb->runQuery( $sql );
		if( $result ){
			while( $c = $result->fetch() ){
				$this->service2form[ $c['service_id'] ] = $c['form_id'];
				}
			}
		}
	}
?>