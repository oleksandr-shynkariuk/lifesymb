<?php
$packs = ntsObjectFactory::getAllIds( 'pack' );
if( count($packs) > 0 ){
	$title = M('Add Package');
	$sequence = 33;
	}
?>