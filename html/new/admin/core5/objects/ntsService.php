<?php
class ntsService extends ntsObject {
	function ntsService(){
		parent::ntsObject( 'service' );
		}

	function getPrepay(){
		$return = $this->getProp('prepay');
		return $return;
		}

	function checkApproval( $customerId, $amount ){
		$approvalRequired = true;

		$permissionFound = false;
		if( $amount ){
			// for paid apps
			$permission = $this->getPermissionsForGroup( -2 );
			if( $permission == 'keep_same' ){
				$permissionFound = false;
				}
			else {
				$permissionFound = true;
				if( $permission == 'auto_confirm' ){
					$approvalRequired = false;
					}
				}
			}

		if( ! $permissionFound ){
			// customer groups
			$myGroupsIds = array();

			if( $customerId ){
				$customer = new ntsUser();
				$customer->setId( $customerId );
				$restrictions = $customer->getProp('_restriction');

				if( $restrictions ){
					$myGroupsIds[] = -1;
					}
				else {
					$myGroupsIds[] = 0;
					}
				}
			else {
				$myGroupsIds[] = -1;
				}

			reset( $myGroupsIds );
			foreach( $myGroupsIds as $groupId ){
				$permission = $this->getPermissionsForGroup( $groupId );
				if( $permission == 'auto_confirm' ){
					$approvalRequired = false;
					break;
					}
				}
			}
		return $approvalRequired;
		}

	function getPackages( $forCustomer = false ){
		$return = array();
		$ntsdb =& dbWrapper::getInstance();
		$where = array(
			array(
				array(
					'service_id' => array('=', $this->getId()),
					),
				array(
					'service_id ' => array('=', 0),
					),
				)
			);
		$result = $ntsdb->select( 'id', 'packs', $where, 'ORDER BY qty ASC' );
		if( $result ){
			while( $e = $result->fetch() ){
				$pack = ntsObjectFactory::get( 'pack' );
				$pack->setId( $e['id'] );
				if( (! $forCustomer) || ($pack->getProp('price')))
					$return[] = $pack;
				}
			}
		return $return;
		}

	function getType(){
		$return = $this->getProp('class_type') ? 'class' : 'appointment';
		return $return;
		}

/* possible values - 'not_allowed, 'not_shown', 'allowed', 'auto_confirm' */
	function getPermissions(){
		$return = array(); 

		$defaultPermissions = $this->getDefaultProp( '_permissions' );
		$rawPermissions = $this->getProp( '_permissions' );

		$return1 = array();
		reset( $defaultPermissions );
		foreach( $defaultPermissions as $ps ){
			list( $pk, $pv ) = explode( ':', $ps );
			$return1[ $pk ] = $pv;
			}
		
		$return2 = array();
		reset( $rawPermissions );
		foreach( $rawPermissions as $ps ){
			list( $pk, $pv ) = explode( ':', $ps );
			$return2[ $pk ] = $pv;
			}
		$return = array_merge( $return1, $return2 );
		return $return;
		}

	function getPermissionsForGroup( $groupId ){
		$permissions = $this->getPermissions();
		$key = 'group' . $groupId;
		if( isset($permissions[$key]) )
			$return = $permissions[$key];
		else {
			echo "<br>Permissions for group id $groupId not defined!<br>";
			$return = 'not_allowed';
			}
		return $return;
		}
	}
?>