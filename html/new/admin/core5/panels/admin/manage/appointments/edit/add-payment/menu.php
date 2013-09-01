<?php
$object = ntsLib::getVar( 'admin/manage/appointments/edit::OBJECT' );
$totalAmount = $object->getTotalAmount();
$paidAmount = $object->getPaidAmount(); 
$oweAmount = $totalAmount ? ($totalAmount - $paidAmount) : 0;

if( $oweAmount > 0 )
{
	$invoiceId = 0;
	$invoices = $object->getInvoices();

	reset( $invoices );
	foreach( $invoices as $ia )
	{
		$iid = $ia[0];
		$invoice = ntsObjectFactory::get( 'invoice' );
		$invoice->setId( $iid );
		$invoiceTotal = $invoice->getTotalAmount();
		$invoicePaid = $invoice->getPaidAmount();
		if( $invoicePaid >= $invoiceTotal )
		{
			continue;
		}
		
		$invoiceId = $iid;
		if( ($invoiceTotal - $invoicePaid) > $oweAmount )
			$addAmount = $oweAmount;
		else
			$addAmount = ($invoiceTotal - $invoicePaid);
		break;
	}

	if( $invoiceId )
	{
		$sequence = 210;
		$title = M('Add Payment');
		$params = array( 
			'default' => $addAmount,
			'invoice' => $invoiceId,
			);
	}
}
?>