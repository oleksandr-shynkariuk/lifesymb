<?php
include_once( NTS_BASE_DIR . '/lib/ntsUserIntegrator.php' );

class ntsUserIntegratorBuiltin extends ntsUserIntegrator {
	function builtinIntegrator(){
		$this->init();
		}

	function dumpUsers(){
		$table = 'users';
		return $this->db->dumpTable( $table, true );
		}

/* DATABASE FUNCTIONS */
	function queryUsers( $whereB = array(), $orderB = array(), $limit = '' ){
		$ids = array();

		$whereString = ( $whereB ) ? 'WHERE ' . $this->db->buildWhere($whereB) : '';
		$orderString = ( $orderB ) ? 'ORDER BY ' . $this->buildOrder($orderB) : '';
		$limitString = ( $limit ) ? 'LIMIT ' . $limit : '';

	/* ids */
		$sql =<<<EOT
SELECT 
	id 
FROM 
	{PRFX}users 
$whereString 
$orderString 
$limitString 
EOT;

		$result = $this->db->runQuery( $sql );
		if( $result ){
			while( $u = $result->fetch() ){
				$ids[] = $u['id'];
	 			}
			}

	/* count */
		$sql =<<<EOT
SELECT 
	COUNT(id) AS count
FROM 
	{PRFX}users 
$whereString 
EOT;

		$result = $this->db->runQuery( $sql );
		if( $result ){
			$u = $result->fetch();
			$count = $u['count'];
			}
		else {
			$count = 0;
			}

		return array( $ids, $count );
		}

	function loadUser( $userId ){
		$return = array();
		if( ! is_array($userId) ){
			$sql =<<<EOT
SELECT 
	* 
FROM 
	{PRFX}users 
WHERE
	{PRFX}users.id = $userId
LIMIT 1
EOT;
			$result = $this->db->runQuery( $sql );
			if( $result ){
				if( $n = $result->fetch() )
					$return = $n;
				}
			}
		else {
			$splitBy = 100;
			$splitSteps = ceil( count($userId) / $splitBy );
			for( $s = 0; $s < $splitSteps; $s++ ){
				$searchUserId = '(' . join(',', array_slice($userId, $s * $splitBy, $splitBy)) . ')';
				$sql =<<<EOT
SELECT 
	* 
FROM 
	{PRFX}users 
WHERE
	{PRFX}users.id IN $searchUserId
EOT;
				$result = $this->db->runQuery( $sql );
				if( $result ){
					while( $n = $result->fetch() ){
						$return[] = $n;
						}
					}
				}
			}
		return $return;
		}

	function updateUser( $id, $info ){
		if( ! $info )
			return true;

		$info = $this->convertTo( $info );
		$result = $this->db->update( 
			'users',
			$info,
			array(
				'id' => array('=', $id)
				)
			);

		if( ! $result ){
			$this->setError( $this->db->getError() );
			}
		return $result;
		}

	function createUser( $info, $netaInfo = array() ){
		$info = $this->convertTo( $info );
		$result = $this->db->insert( 'users', $info );

		if( $result ){
			$newId = $this->db->getInsertId();
			}
		else {
			$newId = 0;
			$this->setError( $this->db->getError() );
			}
		return $newId;
		}

	function deleteUser( $id ){
		$sql =<<<EOT
DELETE FROM {PRFX}users
WHERE id = $id
EOT;

		$result = $this->db->runQuery( $sql );
		return $result;
		}

/* AUTHENTICATION FUNCTIONS */
	function checkPassword( $username, $password ){
		$return = false;
		if( NTS_EMAIL_AS_USERNAME )
			$userInfo = $this->getUserByEmail( $username );
		else
			$userInfo = $this->getUserByUsername( $username );

		if( $userInfo ){
			$myHash = md5($password);
			$storedHash = $userInfo['password'];
			if( $myHash == $storedHash ){
				$return = 1;
				}
			}
		return $return;
		}

	function currentUserId(){
		$return = 0;
		if( isset($_SESSION['userid']) ){
			$return = $_SESSION['userid'];
			}
		return $return;
		}

	function login( $userId, $userPass = '' ){
		$userInfo = $this->getUserById( $userId );
		if( $userInfo ){
			$_SESSION['userid'] = $userId;
			}
		}

	function logout(){
		unset( $_SESSION['userid'] );
		}
/* END OF SPECIFIC METHODS */
	}
?>