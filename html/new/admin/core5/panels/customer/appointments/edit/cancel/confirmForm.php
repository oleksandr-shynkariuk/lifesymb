<?php
$return = $this->getValue('return');
$params['return'] = $return;

$id = $this->getValue('id');
$params['_id'] = $id;

$conf =& ntsConf::getInstance();
$requireCancelReason = $conf->get( 'requireCancelReason' );
?>
<?php if( $requireCancelReason ) : ?>
<?php echo M('Please provide the cancel reason'); ?><br>
<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'reason',
			'attr'		=> array(
				'cols'	=> 48,
				'rows'	=> 4,
				),
			'default'	=> '',
			),
	/* validators */
		array(
			)
		);
?>
<?php endif; ?>
<p>
<?php echo $this->makePostParams('-current-', 'cancel', $params ); ?>
<input type="submit" VALUE="<?php echo M('Cancel'); ?>"> <A HREF="javascript:history.go(-1);"><?php echo M('Go Back'); ?></A>
