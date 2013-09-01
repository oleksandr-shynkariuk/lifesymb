<?php
$paymentOkUrl = ntsLink::makeLink( 'customer/invoices/view', '', array('refno' => $invoiceRefNo, 'display' => 'ok', 'offline' => 1) );
$paymentFailedUrl = ntsLink::makeLink( 'customer/invoices/view', '', array('refno' => $invoiceRefNo, 'display' => 'fail') );

//$url = $paymentOk ? $paymentOkUrl : $paymentFailedUrl;
$url = $paymentOkUrl;
ntsView::redirect( $url );
?>