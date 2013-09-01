<?php
$ntsdb =& dbWrapper::getInstance();
$invoiceId = $object->getId();

/* delete transactions */
$where = array(
	'invoice_id'	=> array('=', $invoiceId)
	);
$transactions = ntsObjectFactory::find( 'transaction', $where );
reset( $transactions );
foreach( $transactions as $tra ){
	$this->runCommand( $tra, 'delete' );
	}
?>