<?php
class ntsAppointment extends ntsObject {
	private $_invoices = NULL;

	function ntsAppointment(){
		parent::ntsObject( 'appointment' );
		}

	function getInvoice(){
		return null;
		}

	function getOrder(){
		$return = NULL;
		$orders = $this->getChildren( 'order' );
		if( $orders && isset($orders[0]) )
			$return = $orders[0];
		return $return;
		}

	function getCost(){
		$cost = $this->getProp('price');
		return $cost;
		}

	function doRefund(){
		$ntsdb =& dbWrapper::getInstance();
		$objId = $this->getId();

		$where = array(
			'obj_class'		=> array('=', 'invoice'),
			'meta_name'		=> array('=', '_appointment'),
			'meta_value'	=> array('=', $objId),
			);
		$what = array(
			'meta_data'	=> 0
			);
		$ntsdb->update( 'objectmeta', $what, $where );

		$pm =& ntsPaymentManager::getInstance();
		$invoices = $this->getInvoices();
		reset( $invoices );
		foreach( $invoices as $ia ){
			list( $invoiceId, $myNeededAmount, $due ) = $ia;
			$invoice = ntsObjectFactory::get( 'invoice' );
			$invoice->setId( $invoiceId );
			$pm->updateInvoice( $invoice );
			}
		}

	function redoRefund(){
		$ntsdb =& dbWrapper::getInstance();
		$objId = $this->getId();
		$cost = $this->getCost();

		$where = array(
			'obj_class'		=> array('=', 'invoice'),
			'meta_name'		=> array('=', '_appointment'),
			'meta_value'	=> array('=', $objId),
			);
		$what = array(
			'meta_data'	=> $cost
			);
		$ntsdb->update( 'objectmeta', $what, $where );

		$pm =& ntsPaymentManager::getInstance();
		$invoices = $this->getInvoices();
		reset( $invoices );
		foreach( $invoices as $ia ){
			list( $invoiceId, $myNeededAmount, $due ) = $ia;
			$invoice = ntsObjectFactory::get( 'invoice' );
			$invoice->setId( $invoiceId );
			$pm->updateInvoice( $invoice );
			}
		}

	function getTotalAmount(){
		$return = 0;
		$invoices = $this->getInvoices();
		reset( $invoices );
		foreach( $invoices as $ia ){
			list( $invoiceId, $myNeededAmount, $due ) = $ia;
			$return += $myNeededAmount;
			}
		return $return;
		}

	function getInvoices(){
		if( ! is_array($this->_invoices) )
		{
			$this->_invoices = array();

			$ntsdb =& dbWrapper::getInstance();
			$objId = $this->getId();

			$invoices = array();

			$where1 = array(
				'obj_class'		=> array('=', 'invoice'),
				'meta_name'		=> array('=', '_appointment'),
				'meta_value'	=> array('=', $objId),
				);
			$where2 = array(
				'obj_class'		=> array('=', 'invoice'),
				'meta_name'		=> array('=', '_tax'),
				'meta_value'	=> array('=', 'appointment:' . $objId),
				);
			$where = array( $where1, $where2 );

			$join = array(
				array( 'invoices', array('objectmeta.obj_id' => array('=', '{PRFX}invoices.id', 1)) )
				);
			$addon = 'ORDER BY {PRFX}invoices.due_at ASC';
			$result = $ntsdb->select( array('meta_name', 'meta_data', 'obj_id', '{PRFX}invoices.due_at AS due_at'), 'objectmeta', $where, $addon, $join );

			$invoiceIds = array();
			$invoiceData = array(
				'amount'	=> 0,
				'taxrate'	=> 0,
				'due_at'	=> 0
				);

			while( $i = $result->fetch() )
			{
				if( ! in_array($i['obj_id'], $invoiceIds) )
				{
					$invoiceIds[] = $i['obj_id'];
					$invoiceData[ $i['obj_id'] ] = array(
						'amount'	=> 0,
						'taxrate'	=> 0,
						'due_at'	=> $i['due_at']
						);
				}
				switch( $i['meta_name'] )
				{
					case '_tax':
						$invoiceData[ $i['obj_id'] ]['taxrate'] = $i['meta_data'];
						break;
					default:
						$invoiceData[ $i['obj_id'] ]['amount'] = $i['meta_data'];
						break;
				}
			}

			reset( $invoiceIds );
			foreach( $invoiceIds as $iid )
			{
				$amount = $invoiceData[$iid]['amount'];
				$tax = ntsLib::calcTax( $amount, $invoiceData[$iid]['taxrate'] );
				$total = $amount + $tax;
				$this->_invoices[] = array( $iid, $total, $invoiceData[$iid]['due_at'] );
			}
		}
		return $this->_invoices;
		}

	function getPaidAmount(){
		$return = 0;

		$invoices = $this->getInvoices();
		reset( $invoices );
		foreach( $invoices as $ia ){
			list( $invoiceId, $myNeededAmount, $due ) = $ia;

			$invoice = ntsObjectFactory::get( 'invoice' );
			$invoice->setId( $invoiceId );

			$invoiceTotalAmount = $invoice->getTotalAmount();
			$invoicePaidAmount = $invoice->getPaidAmount();
			if( $invoiceTotalAmount ){
				$myPaidAmount = $invoicePaidAmount * ( $myNeededAmount / $invoiceTotalAmount );
				$return += $myPaidAmount;
				}
			}
		return $return;
		}

	function getStatus(){
		$alert = 0;
		$cssClass = '';
		$message = '';
		$return = array( $alert, $cssClass, $message );
		
		$completed = $this->getProp('completed');
		if( $completed ){
			switch( $completed ){
				case HA_STATUS_COMPLETED:
					$alert = 0;
					$cssClass = 'ntsCompleted';
					$message = M('Completed');
					break;
				case HA_STATUS_CANCELLED:
					$alert = 1;
					$cssClass = 'ntsCancelled';
					$message = M('Cancelled');
					break;
				case HA_STATUS_NOSHOW:
					$alert = 1;
					$cssClass = 'ntsNoshow';
					$message = M('No Show');
					break;
				}
			}
		else {
			if( $this->getProp('approved') ){
				$alert = 0;
				$cssClass = 'ntsApproved';
				$message = M('Approved');
				}
			else {
				$alert = 1;
				$cssClass = 'ntsPending';
				$message = M('Pending');
				}
			}

		$return = array( $alert, $cssClass, $message );
		return $return;
		}
	}
?>