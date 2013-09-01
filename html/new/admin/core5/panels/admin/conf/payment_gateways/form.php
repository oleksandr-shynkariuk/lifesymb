<?php
$percentOptions = range( 0, 100, 10 );
$prepayOptions = array();
foreach( $percentOptions as $p ){
	$prepayOptions[] = array( $p . '%', $p . '%' );
	}
/* check if they are common for all services */
$ntsdb =& dbWrapper::getInstance();
$options = array();
$res = $ntsdb->select( 'prepay', 'services' );
while( $o = $res->fetch() )
{
	if( ! $o['prepay'] )
		$o['prepay'] = 0;
	if( ! in_array($o['prepay'], $options) )
		$options[] = $o['prepay'];
}

$defaultOnlineDeposit = 0;
if( count($options) == 1 )
{
	$defaultOnlineDeposit = $options[0];
}
?>
<?php
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'prepay',
			'options'	=> $prepayOptions,
			'default'	=> $defaultOnlineDeposit
			)
		);
?>
 <a id="nts-set-deposit" href="<?php echo ntsLink::makeLink('-current-', 'prepay'); ?>"><?php echo M('Set For All Services'); ?></a>
