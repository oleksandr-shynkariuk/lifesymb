<?php
$cm =& ntsCommandManager::getInstance();
$ff =& ntsFormFactory::getInstance();
/* transactions */
$invoice = ntsLib::getVar( 'admin/company/payments/transactions::invoice' );
$customer = $invoice->getCustomer();
$ntsdb =& dbWrapper::getInstance();

$addon = 'ORDER BY created_at DESC';
$where = array();

$entries = array();
$transactionsAmount = 0;

if( $invoice ){
	$limit = 0;
	$pm =& ntsPaymentManager::getInstance();
	$entries = $pm->getTransactionsOfInvoice( $invoice->getId() );
	$count = count($entries);
	}
else {
	$limit = 50;
	$count = $ntsdb->count( 'transactions', $where );
	if( $limit && ($count > $limit) ){
		$addon .= ' LIMIT ' . $limit;
		}

	$result = $ntsdb->select( 'id', 'transactions', $where, $addon );
	$ids = array();
	while( $i = $result->fetch() ){
		$ids[] = $i['id'];
		}
	ntsObjectFactory::preload( 'transaction', $ids );
	reset( $ids );
	foreach( $ids as $id ){
		$e = ntsObjectFactory::get( 'transaction' );
		$e->setId( $id );
		$entries[] = $e;
		}
	}

reset( $entries );
foreach( $entries as $e ){
	$transactionsAmount += $e->getProp('amount');
	}

ntsLib::setVar( 'admin/company/payments/transactions::totalCount', $count );
ntsLib::setVar( 'admin/company/payments/transactions::limit', $limit );

ntsLib::setVar( 'admin/company/payments/transactions::entries', $entries );
ntsLib::setVar( 'admin/company/payments/transactions::transactionsAmount', $transactionsAmount );

$customerLink = ntsLink::makeLinkFull(NTS_FRONTEND_WEBPAGE, 'system/invoice', '', array('refno' => $object->getProp('refno')));

$NTS_VIEW['customerLink'] = $customerLink;

$formParams = $object->getByArray();
$formParams['sendLink'] = $customerLink;

$sendFormFile = dirname( __FILE__ ) . '/formSend';
$NTS_VIEW['formSend'] =& $ff->makeForm( $sendFormFile, $formParams );

switch( $action ){
	case 'changecost':
		$ff =& ntsFormFactory::getInstance();
		$form = $ff->makeForm( dirname(__FILE__) . '/formUnitCost' );

		if( $form->validate() ){
			$formValues = $form->getValues();
			$invoice->updateItemCost( $formValues['item'], $formValues['item_id'], $formValues['cost'] );
			$msg = array( ntsView::objectTitle($invoice), M('Update'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );
			}
		$forwardTo = ntsLink::makeLink( '-current-' );
		ntsView::redirect( $forwardTo );
		exit;
		break;

	case 'changediscount':
		$ff =& ntsFormFactory::getInstance();
		$form = $ff->makeForm( dirname(__FILE__) . '/formDiscount' );

		if( $form->validate() ){
			$formValues = $form->getValues();
			$invoice->updateItemDiscount( $formValues['item'], $formValues['item_id'], $formValues['discount'] );
			$msg = array( ntsView::objectTitle($invoice), M('Update'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );
			}
		$forwardTo = ntsLink::makeLink( '-current-' );
		ntsView::redirect( $forwardTo );
		exit;
		break;

	case 'send':
		if( $NTS_VIEW['formSend']->validate() ){
			$formValues = $NTS_VIEW['formSend']->getValues();

		/* send */
			$cm->runCommand( $customer, 'email', array('body' => $formValues['body'], 'subject' => $formValues['subject']) );

			if( $cm->isOk() ){
				$title = M('Customer') . ': ' . '<b>' . $customer->getProp('first_name') . ' ' . $customer->getProp('last_name') . '</b>';
				ntsView::setAnnounce( $title . ': ' . M('Send Email') . ': ' . M('OK'), 'ok' );

			/* continue to the list with anouncement */
				$forwardTo = ntsLink::makeLink( '-current-' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			else {
				$errorText = $cm->printActionErrors();
				ntsView::addAnnounce( $errorText, 'error' );
				}
			}
		else {
		/* form not valid, continue to edit form */
			}
		break;
	}
?>