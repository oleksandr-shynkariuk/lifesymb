<?php
$ntsdb =& dbWrapper::getInstance();

$NTS_VIEW['showPerPage'] = 20;
$NTS_VIEW['currentPage'] = $_NTS['REQ']->getParam('p');
if( ! $NTS_VIEW['currentPage'] )
	$NTS_VIEW['currentPage'] = 1;
$limit = ( ($NTS_VIEW['currentPage'] - 1) * $NTS_VIEW['showPerPage'] ) . ',' . $NTS_VIEW['showPerPage'];

/* super count */
$NTS_VIEW['totalCount'] = $ntsdb->count('smslog');

/* entries */
$sql =<<<EOT
SELECT
	*
FROM
	{PRFX}smslog
ORDER BY
	sent_at DESC
LIMIT $limit
EOT;

$result = $ntsdb->runQuery( $sql );
$NTS_VIEW['entries'] = array();
if( $result ){
	while( $e = $result->fetch() ){
		$NTS_VIEW['entries'][] = $e;
		}
	}

/* pager info */
$NTS_VIEW['showFrom'] = 1 + ($NTS_VIEW['currentPage'] - 1) * $NTS_VIEW['showPerPage'];
$NTS_VIEW['showTo'] = $NTS_VIEW['showFrom'] + $NTS_VIEW['showPerPage'] - 1;
if( $NTS_VIEW['showTo'] > $NTS_VIEW['totalCount'] )
	$NTS_VIEW['showTo'] = $NTS_VIEW['totalCount'];
?>