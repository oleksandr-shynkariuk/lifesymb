<?php
// payment types
$gateways = array();
$ntsdb =& dbWrapper::getInstance();
$result = $ntsdb->select( 'DISTINCT(pgateway)', 'transactions' );
while( $i = $result->fetch() )
{
	$gtw = trim( $i['pgateway'] );
	if( $gtw )
		$gateways[] = array( $gtw, $gtw );
}
?>
<?php if( $gateways ) : ?>
	<?php echo M('Paid Through'); ?>: 
	<?php
	array_unshift( $gateways, array('', ' - ' . M('Any') . ' - ') );
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> 'gateway',
			'options'	=> $gateways
			)
		);
	?>
<?php endif; ?>

<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'from',
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
 - 
<?php
echo $this->makeInput (
/* type */
	'date/Calendar',
/* attributes */
	array(
		'id'		=> 'to',
		),
/* validators */
	array(
		array(
			'code'		=> 'notEmpty.php', 
			'error'		=> M('Required field'),
			),
		)
	);
?>
<?php 
echo $this->makePostParams('-current-', 'dates');
?>

<INPUT TYPE="submit" VALUE="<?php echo M('Go'); ?>">
&nbsp;&nbsp;
<?php
$params = array(
	'from'		=> $this->getValue('from'),
	'to'		=> $this->getValue('to'),
	'gateway'	=> $this->getValue('gateway'),
	);
?>
<a href="<?php echo ntsLink::makeLink('-current-', 'export', $params ); ?>"><?php echo M('CSV Export'); ?></a>