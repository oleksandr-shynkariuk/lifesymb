<?php
ntsView::setTitle( M('Packages') );
/* packages */
$where = array(
	'price'	=> array('>', 0),
	);
$packs = ntsObjectFactory::find( 'pack', $where );
$NTS_VIEW['packs'] = $packs;
?>