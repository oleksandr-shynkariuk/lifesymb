<?php
$conf['options'] = array(
	array('write',	M('View and Update') ),
	array('read',	M('View Only') ),
	array('hidden',	M('Hidden') ),
	);
require( NTS_BASE_DIR . '/lib/form/inputs/select.php' );
?>