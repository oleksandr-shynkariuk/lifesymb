<?php
$ntsdb =& dbWrapper::getInstance();

$id = $_NTS['REQ']->getParam('id');

/* info */
$sql =<<<EOT
SELECT
	*
FROM
	{PRFX}smslog
WHERE
	id = $id
EOT;

$result = $ntsdb->runQuery( $sql );
$NTS_VIEW['e'] = array();
if( $result ){
	if( $e = $result->fetch() ){
		$NTS_VIEW['e'] = $e;
		}
	}
?>