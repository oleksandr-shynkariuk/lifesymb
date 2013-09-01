<?php
$cal = ntsLib::getVar( 'admin/manage:cal' );
$tm2 = ntsLib::getVar( 'admin::tm2' );
$t = $NTS_VIEW['t'];

$fixCustomer = ntsLib::getVar( 'admin/company/payments/orders/create::fixCustomer' );
$fixPack = ntsLib::getVar( 'admin/company/payments/orders/create::fixPack' );

$cid = $this->getValue( 'customer_id' );
if( $cid ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'customer_id',
			'value'	=> $cid,
			)
		);
	}
$pid = $this->getValue( 'pack_id' );
if( $pid ){
	echo $this->makeInput (
	/* type */
		'hidden',
	/* attributes */
		array(
			'id'	=> 'pack_id',
			'value'	=> $pid,
			)
		);
	}
?>
<table class="ntsForm">
<?php
if( $this->formAction == 'display' ){
	$displayPack = ( ! $fixPack );
	if( $displayPack ){
		require( dirname(__FILE__) . '/form-pack.php' );
		}

	$displayCustomer = ( ! $fixCustomer );
	if( $displayCustomer && $cid ){
		require( dirname(__FILE__) . '/form-customer.php' );
		}
  
	$displayConfirm = ( $cid );
	if( $displayConfirm ){
		require( dirname(__FILE__) . '/form-confirm.php' );
		}
	}
else {
	require( dirname(__FILE__) . '/form-pack.php' );
	require( dirname(__FILE__) . '/form-confirm.php' );
	}
?>
</table>