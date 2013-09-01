<?php
global $NTS_OBJECT_CACHE, $NTS_OBJECT_PROPS_CONFIG, $NTS_ALL_IDS;

class ntsObjectFactory {
	static function clearCache( $className, $id = 0 ){
		global $NTS_OBJECT_CACHE, $NTS_ALL_IDS;
		if( $id )
			unset( $NTS_OBJECT_CACHE[$className][$id] );
		else {
			if( isset($NTS_OBJECT_CACHE[$className]) ){
				reset( $NTS_OBJECT_CACHE[$className] );
				$keys = array_keys($NTS_OBJECT_CACHE[$className]);
				foreach( $keys as $k ){
					unset( $NTS_OBJECT_CACHE[$className][$k] );
					}
				}
			if( isset($NTS_ALL_IDS[$className]) ){
				unset( $NTS_ALL_IDS[$className] );
				}
			}
		}

	static function preloadMeta( $className, $ids = array() ){
		global $NTS_OBJECT_PROPS_CONFIG;
		if( ! isset($NTS_OBJECT_PROPS_CONFIG[$className]) ){
			$om =& objectMapper::getInstance();
			$om->initPropsConfig( $className );
			}

		$ntsdb =& dbWrapper::getInstance();
		$return = array();
		$metaClass = $className;

		$splitBy = 100;
		$splitSteps = ceil( count($ids) / $splitBy );
		for( $s = 0; $s < $splitSteps; $s++ ){
			$idsString = join( ',', array_slice($ids, $s * $splitBy, $splitBy) );
			$sql =<<<EOT
SELECT 
		meta_name, meta_value, meta_data, obj_id
FROM 
		{PRFX}objectmeta 
WHERE
		obj_class = "$metaClass" AND obj_id IN ($idsString)
EOT;

			$result = $ntsdb->runQuery( $sql );
			if( $result ){
				while( $n = $result->fetch() ){
					$n['meta_data'] = trim( $n['meta_data'] );

					if( isset($return[$n['obj_id']][$n['meta_name']]) ){
						if( isset($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]) && $NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] ){
							if( ! is_array($return[$n['obj_id']][$n['meta_name']]) )
								$return[$n['obj_id']][$n['meta_name']] = array( $return[$n['obj_id']][$n['meta_name']] );

							if( $NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 3 ){
								$return[$n['obj_id']][$n['meta_name']][] = array($n['meta_value'], $n['meta_data'] );
								}
							elseif( strlen($n['meta_data']) && ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 2) ){
								$return[$n['obj_id']][$n['meta_name']][ $n['meta_value'] ] = $n['meta_data'];
								}
							else {
								if( ! in_array($n['meta_value'], $return[$n['obj_id']][$n['meta_name']] ) ) 
									$return[$n['obj_id']][$n['meta_name']][] = $n['meta_value'];
								}
							}
						}
					else {
						if( isset($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]) && ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 3) ){
							$return[$n['obj_id']][$n['meta_name']] = array( array($n['meta_value'], $n['meta_data']) );
							}
						elseif( (isset($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]) && ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 2) ) ){
							$return[$n['obj_id']][ $n['meta_name'] ] = array( $n['meta_value'] => $n['meta_data'] );
							}
						else {
							$return[$n['obj_id']][ $n['meta_name'] ] = $n['meta_value'];
							}
						}
					}
				}
			}
		return $return;
		}

	static function preload( $className, $ids = array() ){
		global $NTS_OBJECT_CACHE, $NTS_OBJECT_PROPS_CONFIG;

		$oldIds = $ids;
		$ids = array();
		foreach( $oldIds as $id ){
			if( ! isset($NTS_OBJECT_CACHE[$className][$id]) ){
				$ids[] = $id;
				}
			}
		if( ! $ids )
			return;

		$metaInfo = ntsObjectFactory::preloadMeta( $className, $ids );

		switch( $className ){
			case 'user':
				$uif =& ntsUserIntegratorFactory::getInstance();
				$integrator =& $uif->getIntegrator();
				$usersInfo = $integrator->loadUser( $ids );
				reset( $usersInfo );
				foreach( $usersInfo as $u ){
					if( isset( $metaInfo[$u['id']] ) ){
						$u = array_merge( $u, $metaInfo[$u['id']] );
						}
					$NTS_OBJECT_CACHE[$className][ $u['id'] ] = $u;
					}
				break;

			default:
				$ntsdb =& dbWrapper::getInstance();
				$om =& objectMapper::getInstance();
				$tblName = $om->getTableForClass( $className );

				$splitBy = 100;
				$splitSteps = ceil( count($ids) / $splitBy );
				for( $s = 0; $s < $splitSteps; $s++ ){
					$thisIds = array_slice($ids, $s * $splitBy, $splitBy);
					$where = array(
						'id'	=> array('IN', $thisIds)
						);
					$result = $ntsdb->select( '*', $tblName, $where );
					while( $u = $result->fetch() ){
						if( isset( $metaInfo[$u['id']] ) ){
							$u = array_merge( $u, $metaInfo[$u['id']] );
							}
						$NTS_OBJECT_CACHE[$className][ $u['id'] ] = $u;
						}
					}
				break;
			}
		}

	static function getAllIds( $className, $addonString = '' ){
		global $NTS_ALL_IDS;

		if( ! isset($NTS_ALL_IDS[$className]) ){
			$NTS_ALL_IDS[$className] = array();

			$ntsdb =& dbWrapper::getInstance();
			$om =& objectMapper::getInstance();
			$tblName = $om->getTableForClass( $className );

			if( (! $addonString) && $om->isPropRegistered($className, 'show_order') )
				$addonString .= ' ORDER BY show_order ASC';

			$result = $ntsdb->select( 'id', $tblName, array(), $addonString );
			if( $result ){
				while( $u = $result->fetch() ){
					$NTS_ALL_IDS[$className][] = $u['id'];
					}
				}
			}

		return $NTS_ALL_IDS[$className];
		}

	static function find( $className, $where, $addonString = '' ){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();
		$om =& objectMapper::getInstance();
		$tblName = $om->getTableForClass( $className );
		
		if( (! $addonString) && $om->isPropRegistered($className, 'show_order') )
			$addonString .= ' ORDER BY show_order ASC';

		$ids = array();
		$result = $ntsdb->select( 'id', $tblName, $where, $addonString );
		if( $result ){
			while( $u = $result->fetch() ){
				$ids[] = $u['id'];
				}
			}
		if( $ids ){
			ntsObjectFactory::preload( $className, $ids );
			reset( $ids );
			foreach( $ids as $id ){
				$o = ntsObjectFactory::get( $className );
				$o->setId( $id );
				$return[] = $o;
				}
			}
		return $return;
		}

	static function getAll( $className, $addonString = '', $returnById = FALSE ){
		$return = array();

		$ids = ntsObjectFactory::getAllIds( $className, $addonString );
		ntsObjectFactory::preload( $className, $ids );
		reset( $ids );
		foreach( $ids as $id ){
			$o = ntsObjectFactory::get( $className );
			$o->setId( $id );
			if( $returnById )
				$return[$id] = $o;
			else
				$return[] = $o;
			}
		return $return;
		}

	static function get( $className, $id = 0 ){
		static $classes;
		if( ! isset($classes[$className]) ){
			$classes[$className] = '';
			$customClassName = 'nts' . ucfirst( $className );
			$customClassFileName = $customClassName . '.php';
			$realClassFileName = ntsLib::fileInCoreDirs( '/objects/' . $customClassFileName );
			if( $realClassFileName ){
				include_once( $realClassFileName );
				$classes[$className] = $customClassName;
				}
			}

		$customClassName = $classes[$className];
		if( $customClassName ){
			$return = new $customClassName;
			}
		else {
			$return = new ntsObject( $className );
			}
		if( $id ){
			$return->setId( $id );
			}
		return $return;
		}
	}

class ntsObject {
	var $className;
	var $props = array();
	var $updatedProps = array();
	var $id = 0;
	var $notFound = false;

	function ntsObject( $className ){
		$this->className = $className;
		$this->id = 0;
		$this->props = array();
		$this->notFound = false;

		global $NTS_OBJECT_PROPS_CONFIG;
		$myClasses = $this->getMyClasses();
		reset( $myClasses );
		foreach( $myClasses as $myClass ){
			if( ! isset($NTS_OBJECT_PROPS_CONFIG[$myClass]) ){
				$om =& objectMapper::getInstance();
				$om->initPropsConfig( $myClass );
				}
			}
		$this->resetUpdatedProps();
		}

	function getParents(){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();
		$myId = $this->getId();
		$myClassName = $this->getClassName();

		$where = array(
			'meta_name'		=> array( '=', '_' . $myClassName ),
			'meta_value'	=> array( '=', $myId ),
			);
		$result = $ntsdb->select( array('obj_class', 'obj_id'), 'objectmeta', $where );

		if( $result ){
			while( $pInfo = $result->fetch() ){
				$p = ntsObjectFactory::get( $pInfo['obj_class'] );
				$p->setId( $pInfo['obj_id'] );
				$return[] = $p;
				}
			}
		return $return;
		}

	function getChildren( $filterClass = '' ){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();
		$myId = $this->getId();
		$myClassName = $this->getClassName();

		$where = array(
			'obj_id'	=> array( '=', $myId ),
			'obj_class'	=> array( '=', $myClassName ),
			);
		if( $filterClass )
			$where['meta_name']	= array( '=', '_' . $filterClass );
		else
			$where['meta_name']	= array( 'LIKE', '_%' );

		$result = $ntsdb->select( array('meta_name', 'meta_value'), 'objectmeta', $where );

		if( $result ){
			while( $pInfo = $result->fetch() ){
				$childClass = substr( $pInfo['meta_name'], 1 );
				$p = ntsObjectFactory::get( $childClass );
				$p->setId( $pInfo['meta_value'] );
				$return[] = $p;
				}
			}
		return $return;
		}

	function getMyClasses(){
		$myClasses = ( $this->className == 'user' ) ? array('customer', 'user') : array($this->className);
		return $myClasses;
		}
		
	function getDefaultProp( $pName ){
		global $NTS_OBJECT_PROPS_CONFIG;
		$return = null;

		$myClasses = ( $this->className == 'user' ) ? array('customer', 'user') : array($this->className);
		reset( $myClasses );
		foreach( $myClasses as $myClass ){
			if( isset($NTS_OBJECT_PROPS_CONFIG[$myClass][$pName]) ){
				$return = $NTS_OBJECT_PROPS_CONFIG[$myClass][$pName]['default'];
				break;
				}
			}
		return $return;
		}
	
	function getProp( $pName, $unserialize = FALSE ){
		global $NTS_OBJECT_PROPS_CONFIG;
		$return = null;
		if( isset($this->props[$pName]) ){
			if(
				isset($NTS_OBJECT_PROPS_CONFIG[$this->className][$pName]) && 
				$NTS_OBJECT_PROPS_CONFIG[$this->className][$pName]['isArray'] && 
				( ! is_array($this->props[$pName]) )
				){
				$this->props[$pName] = trim( $this->props[$pName] );
				if( $this->props[$pName] )
					$this->props[$pName] = array( $this->props[$pName] );
				else
					$this->props[$pName] = array();
				}
			$return = $this->props[$pName];
			}
		else {
			$myClasses = ( $this->className == 'user' ) ? array('customer', 'user') : array($this->className);
			reset( $myClasses );
			foreach( $myClasses as $myClass ){
				if( isset($NTS_OBJECT_PROPS_CONFIG[$myClass][$pName]) ){
					$return = $NTS_OBJECT_PROPS_CONFIG[$myClass][$pName]['default'];
					break;
					}
				}
			}
		if( $unserialize )
		{
			$return = unserialize( $return );
		}
		return $return;
		}

	function setId( $id, $load = true ){
		if( preg_match("/[^\d]/", $id) )
			return;
		$this->id = $id;
		if( ($id > 0) && $load ){
			$this->load();
			}
		}

	function notFound(){
		return $this->notFound;
		}

	function getId(){
		return $this->id;
		}

	function getClassName(){
		return $this->className;
		}

	function resetUpdatedProps(){
		$this->updatedProps = array();
		}

	function getMetaClass(){
		$useMetaIn = array( 'user', 'service', 'appointment', 'location', 'resource', 'order', 'invoice', 'transaction' );

		$return = '';
		$className = $this->getClassName();
		if( in_array($className, $useMetaIn) )
			$return = $className;

		return $return;
		}

	function load(){
		global $NTS_OBJECT_CACHE;
		$className = $this->getClassName();
		$id = $this->getId();
		if( ! $id )
			return;

		switch( $className ){
			case 'user':
//				echo "<h3>LOADING: $id</h3>";
				if( isset($NTS_OBJECT_CACHE[$className][$id]) ){
					$userInfo = $NTS_OBJECT_CACHE[$className][$id];
					}
				else {
					$uif =& ntsUserIntegratorFactory::getInstance();
					$integrator =& $uif->getIntegrator();
					$userInfo = $integrator->getUserById( $id );
					$NTS_OBJECT_CACHE[$className][$id] = $userInfo;
					}
				if( $userInfo ){
					$this->setByArray( $userInfo );
					$this->resetUpdatedProps();
					}
				else {
					$this->notFound = true;
					}
				break;

			default:
				$ntsdb =& dbWrapper::getInstance();
				$className = $this->getClassName();

				if( isset($NTS_OBJECT_CACHE[$className][$id]) ){
					$this->setByArray( $NTS_OBJECT_CACHE[$className][$id], true );
					$this->resetUpdatedProps();
					}
				else {
					$om =& objectMapper::getInstance();
					$tblName = $om->getTableForClass( $className );

					$sql = "SELECT * FROM {PRFX}$tblName WHERE id = $id";

					$result = $ntsdb->runQuery( $sql );
					if( $result && ($u = $result->fetch()) ){
						$metaClass = $this->getMetaClass();
					/* load meta as well */
						$metaInfo = $this->loadMeta();
						$u = array_merge( $u, $metaInfo );
//_print_r( $u );
						$this->setByArray( $u, true );
						$this->resetUpdatedProps();

						$NTS_OBJECT_CACHE[$className][$id] = $u;
						}
					else {
						$this->notFound = true;
						}
					}
				break;
			}
		}

	function loadMeta(){
		global $NTS_OBJECT_PROPS_CONFIG;
		$return = array();
		$objId = $this->getId();
		if( ! $objId )
			return;
		$metaClass = $this->getMetaClass();
		if( ! $metaClass )
			return $return;

		$ntsdb =& dbWrapper::getInstance();
		$sql =<<<EOT
SELECT 
	meta_name, meta_value, meta_data
FROM 
	{PRFX}objectmeta 
WHERE
	obj_id = $objId AND obj_class = "$metaClass"
EOT;

		$result = $ntsdb->runQuery( $sql );
		if( $result ){
			while( $n = $result->fetch() ){
				$n['meta_data'] = trim( $n['meta_data'] );
				if( isset($return[$n['meta_name']]) ){
					if( isset($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]) && $NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] ){
						if( ! is_array($return[$n['meta_name']]) )
							$return[$n['meta_name']] = array( $return[$n['meta_name']] );
						if( strlen($n['meta_data']) && ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 2) )
							$return[$n['meta_name']][ $n['meta_value'] ] = $n['meta_data'];
						elseif( ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 3) )
							$return[$n['meta_name']][] = array( $n['meta_value'], $n['meta_data'] );
						else {
							if( ! in_array($n['meta_value'], $return[$n['meta_name']] ) ) 
								$return[$n['meta_name']][] = $n['meta_value'];
							}
						}
					}
				else {
					if( strlen($n['meta_data']) && (isset($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]) && ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 2) ) ){
						$return[ $n['meta_name'] ] = array( $n['meta_value'] => $n['meta_data'] );
						}
					elseif( isset($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]) && ($NTS_OBJECT_PROPS_CONFIG[$metaClass][$n['meta_name']]['isArray'] == 3)  ){
						$return[ $n['meta_name'] ] = array( array($n['meta_value'], $n['meta_data']) );
						}
					else {
						$return[ $n['meta_name'] ] = $n['meta_value'];
						}
					}
				}
			}
		return $return;
		}

	function deleteProp( $pName, $pValue ){
		if( ! isset($this->props[$pName]) )
			return;
			
		if( ! is_array($this->props[$pName]) )
			return;
			
		$result = array();
		reset( $this->props[$pName] );
		foreach( $this->props[$pName] as $v ){
			if( $v == $pValue )
				continue;
			$result[] = $v;
			}
		$this->props[$pName] = $result;
		}

	function setProp( $pName, $pValue, $fromStorage = false ){
		if( $pValue === 0 )
			$pValue = '0';

		global $NTS_OBJECT_PROPS_CONFIG;
	/* if updated */
		if( ! $fromStorage ){
			if( 
				(! isset($this->props[$pName])) OR 
				($pValue != $this->props[$pName]) OR 
				( (! is_array($pValue)) && (strlen($pValue) != strlen($this->props[$pName])) )
				)
				{
				if( isset($this->props[$pName]) )
					$this->updatedProps[$pName] = $this->props[$pName];
				else
					$this->updatedProps[$pName] = null;
				}
			}

		if( isset($NTS_OBJECT_PROPS_CONFIG[$this->className][$pName]) ){
			if( 
				$NTS_OBJECT_PROPS_CONFIG[$this->className][$pName]['isCore'] && 
				$NTS_OBJECT_PROPS_CONFIG[$this->className][$pName]['isArray'] 
				){
				if( $fromStorage ){
					$pValue = trim($pValue);
					if( strlen($pValue) )
						$pValue = unserialize( $pValue );
					else
						$pValue = array();
					$this->props[$pName] = $pValue;
					}
				else {
					if( is_array($pValue) ){
						$this->props[$pName] = $pValue;
						}
					else {
						if( ! isset($this->props[$pName]) )
							$this->props[$pName] = array();
						$pValue = trim($pValue);
						if( strlen($pValue) )
							$this->props[$pName][] = $pValue;
						}
					}
				}
			else {
				$this->props[$pName] = $pValue;
				}
			}
		else {
			$this->props[$pName] = $pValue;
			}
		}

	function setByArray( $array, $fromStorage = false ){
		reset( $array );
		foreach( $array as $pName => $pValue ){
			$this->setProp( $pName, $pValue, $fromStorage );
			if( $pName == 'id' )
				$this->setId( $pValue, false );
			}
		}

	function getChanges(){
		$return = array();
		reset( $this->updatedProps );
		foreach( $this->updatedProps as $upn => $upv ){
			$return[ $upn ] = array( $this->getProp($upn), $upv );
			}

		return $return;
		}
		
	function getByArray( $split = false, $updated = false ){
		global $NTS_OBJECT_PROPS_CONFIG;
		if( $updated ){
			$props = array();
			reset( $this->updatedProps );
			foreach( $this->updatedProps as $upn => $upv ){
				$props[ $upn ] = $this->getProp( $upn );
				}
			}
		else {
//			$props = $this->props;
			reset( $this->props );
			foreach( $this->props as $k => $v ){
				$props[ $k ] = $this->getProp( $k );
				}

		/* check if any default props missing */
			reset( $NTS_OBJECT_PROPS_CONFIG[$this->className] );
			foreach( $NTS_OBJECT_PROPS_CONFIG[$this->className] as $pName => $pConfig ){
				if( ! isset($props[$pName]) )
					$props[$pName] = $NTS_OBJECT_PROPS_CONFIG[$this->className][$pName]['default'];
				}
			}

		if( $split ){
			$core = array();
			$meta = array();

			$om =& objectMapper::getInstance();
			list( $coreProps, $metaProps ) = $om->getPropsForClass( $this->getClassName() );
			$corePropsNames = array_keys( $coreProps );

			reset( $props );
			foreach( $props as $k => $v ){
				if( in_array($k, $corePropsNames) )
					$core[ $k ] = $v;
				else
					$meta[ $k ] = $v;
				}
			$return = array( $core, $meta );
			}
		else {
			$return = $props;
			}
		return $return;
		}
	}
?>