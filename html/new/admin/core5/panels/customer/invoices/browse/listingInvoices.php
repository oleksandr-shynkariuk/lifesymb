<?php
class listingInvoices extends ntsListingTable {
	function displayField( $fn, $e ){
		$return = '';
		switch( $fn ){
			case 'amount':
				$totalAmount = $e->getTotalAmount();
				return $totalAmount;
				break;

			case 'status':
				$now = time();
				$totalAmount = $e->getTotalAmount();
				$paidAmount = $e->getPaidAmount();
				$balance = $paidAmount - $totalAmount;
				if( $balance > 0 ){
					$return = '<span class="nts-ok nts-bold">' . ntsCurrency::formatPrice($balance) . '</span>';
					}
				elseif( ($balance == 0) && ($paidAmount > 0)){
					$return = '<span class="nts-ok nts-bold">' . M('Paid') . '</span>';
					}
				elseif( $balance < 0 ){
					if( NTS_CAN_PAY_ONLINE )
						$return = '<a title="' . M('Pay Now') . '" href="' . ntsLink::makeLink('customer/invoices/pay', '', array('refno' => $e->getProp('refno'))) . '">';
					if( $now > $e->getProp('due_at') ){
						$return .= '<span class="nts-alert nts-bold">' . ntsCurrency::formatPrice($balance) . '</span>';
						}
					else {
						$return .= ntsCurrency::formatPrice($balance);
						}
					if( NTS_CAN_PAY_ONLINE )
						$return .= '</a>';
					}
				else {
					$return = '&nbsp;';
					}
				break;

			case 'details':
				$return = $e->getFullTitle();
				break;

			default:
				return parent::displayField( $fn, $e );
				break;
			}
		return $return;
		}
	}
?>
