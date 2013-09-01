<?php
class ntsCurrency {
	static function formatPrice( $amount ){
		$conf =& ntsConf::getInstance();
		$formatConf = $conf->get( 'priceFormat' );
		list( $beforeSign, $decPoint, $thousandSep, $afterSign ) = $formatConf;

		$amount = number_format( $amount, 2, $decPoint, $thousandSep );
		$return = $beforeSign . $amount . $afterSign;
		return $return;
		}

	static function formatServicePrice( $amount, $defaultAmount = '' ){
		$return = '';
		if( strlen($amount) ){
			if( $amount > 0 ){
				$price = ntsCurrency::formatPrice( $amount );
				$return = $price;
				}
			else {
				$return = M('Free');
				}
			}
		if( strlen($defaultAmount) && ($defaultAmount != $amount) ){
			$return .= ' <span style="text-decoration: line-through;">' . ntsCurrency::formatPrice( $defaultAmount ) . '</span>';
			}
		return $return;
		}
	}
?>