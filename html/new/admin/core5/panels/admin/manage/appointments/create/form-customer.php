<tr>
<td class="ntsFormLabel"><?php echo M('Customer'); ?></td>
<td class="ntsFormValue" id="<?php echo $this->formId; ?>_customers">
<?php
	$obj = new ntsUser();
	$obj->setId( $cid );
	$objView = ntsView::objectTitle( $obj );
?>
<ul class="nts-hori-list">
<li class="nts-hori-list-item selected" title="<?php echo $objView; ?>">
<?php 	echo $objView; ?> 
<a class="ntsDeleteControl2" href="<?php echo ntsLink::makeLink('-current-', '', array('customer_id' => '-reset-') ); ?>">[x]</a>
</li>
</ul>

<?php
$ntsConf =& ntsConf::getInstance();
$sendCcForAppointment = $ntsConf->get('sendCcForAppointment');
$ccTo = 3;
?>
<?php if( $sendCcForAppointment ) : ?>
<strong><?php echo M('CC'); ?></strong><br>
<ul class="nts-listing">
<?php 	for( $cc = 1; $cc <= $ccTo; $cc++ ) : ?>
	<li>
<?php echo M('Email'); ?>: 
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'cc_' . $cc,
			'attr'		=> array(
				'size'	=> 32,
				),
			'default'	=> '',
			),
	/* validators */
		array(
			array(
				'code'		=> 'email', 
				'error'		=> M('Valid email required'),
				),
			)
		);
?>
	</li>
<?php 	endfor; ?>
</ul>
<?php endif; ?>

</td>
</tr>