<?php
$transId = $_NTS['REQ']->getParam('transid');
ntsView::setPersistentParams( array('transid' => $transId), 'admin/company/payments/edit' );

ntsLib::setVar( 'admin/company/payments/edit::transId', $transId );
?>