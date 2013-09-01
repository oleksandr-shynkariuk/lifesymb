<?php
if( ! isset($returnBackAsRequest) )
	$returnBackAsRequest = 1;

$ntsdb =& dbWrapper::getInstance();

$display = $_NTS['REQ']->getParam( 'display' ); 
$invoiceRefNo = $_NTS['REQ']->getParam( 'refno' );
$offline = $_NTS['REQ']->getParam( 'offline' );
if( ! $invoiceRefNo ){
	echo "invoiceRefNo required!";
	exit;
	}

$invoiceId = 0;
$result = $ntsdb->select( 'id', 'invoices', array('refno' => array('=', $invoiceRefNo)) );
if( $i = $result->fetch() ){
	$invoiceId = $i['id'];
	}

if( ! $invoiceId ){
	echo "invoice '$invoiceRefNo' not found!";
	exit;
	}

$invoice = ntsObjectFactory::get( 'invoice' );
$invoice->setId( $invoiceId );
$invoiceId = $invoice->getId();
$invoiceInfo = $invoice->getByArray();
$invoiceInfo['totalAmount'] = $invoice->getTotalAmount();
$invoiceInfo['object'] = $invoice;

/* payments for this invoice */
$NTS_VIEW['paidAmount'] = $invoice->getPaidAmount();
$invoiceId = $invoiceInfo['id'];

if( ($display == 'ok') && (! $offline) && ($NTS_VIEW['paidAmount'] <= 0) )
{
	$display = 'fail';
	$_REQUEST['nts-display'] = $display;
}

/* find dependants */
if( $display == 'ok' ){
	$deps = $invoice->getItemsObjects();
	$packName = '';
	$appIds = array();
	if( $deps ){
		reset( $deps );
		foreach( $deps as $dep ){
			$className = $dep->getClassName();
			switch( $className ){
				case 'appointment':
					if( count($deps) == 1 ){
						$appId = $deps[0]->getId();
						$forwardTo = ntsLink::makeLink( 'customer/appointments/view', '', array('id' => $appId, 'request' => $returnBackAsRequest) );
						$_REQUEST['nts-request'] = 1;
						$_REQUEST['nts-id'] = $appId;
						$nextPanel = 'customer/appointments/view';

						$appointment = ntsObjectFactory::get( 'appointment' );
						$appointment->setId( $appId );
						$customerId = $appointment->getProp( 'customer_id' );
						if( ! isset($_SESSION['temp_customer_id']) )
							$_SESSION['temp_customer_id'] = $customerId;
						ntsView::setNextAction( $nextPanel );
						return;
						}
					else {
						$appIds[] = $dep->getId();
						}
					break;

				case 'order':
					$pack = ntsObjectFactory::get( 'pack' );
					$pack->setId( $dep->getProp('pack_id') );
					$packName = $pack->getFullTitle();
					break;
				}
			}
		}
	if( $packName ){
		$invoiceInfo['item_name'] = $packName;
		}
	else {
		if( $appIds ){
			$id2view = join( '-', $appIds );
			$forwardTo = ntsLink::makeLink( 'customer/appointments/view', '', array('id' => $id2view, 'request' => $returnBackAsRequest) );
			ntsView::redirect( $forwardTo );
			}
		}
	}

$NTS_VIEW['invoiceInfo'] = $invoiceInfo;
?>