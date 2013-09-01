<?php
$conf =& ntsConf::getInstance();
$commonHeader = $conf->get('emailCommonHeader');
$commonFooter = $conf->get('emailCommonFooter');

$refno = $this->getValue( 'refno' );
$sendLink = $this->getValue( 'sendLink' );
$sendLink = '<a href="' . $sendLink . '">' . $sendLink . '</a>';

$text = M('Your invoice #{REFNO} is ready, to review please click {SEND_LINK}', array('REFNO' => $refno, 'SEND_LINK' => $sendLink))
?>
<table class="ntsForm">
<tr>
	<td class="ntsFormLabel"><?php echo M('Subject'); ?></td>
	<td>
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'subject',
			'attr'		=> array(
				'size'	=> 48,
				),
			'required'	=> 1,
			'default'	=> M('Invoice') . ' ' . $refno,
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
	</td>
</tr>

<tr>
	<td class="ntsFormLabel"><?php echo M('Message'); ?></td>
	<td>
	<?php echo $commonHeader; ?>
	<br>
<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'body',
			'attr'		=> array(
				'cols'	=> 56,
				'rows'	=> 16,
				),
			'required'	=> 1,
			'default'	=> $text
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

	<br>
	<?php echo nl2br($commonFooter); ?>
	</td>
</tr>

<tr>
<td></td>
<td>
<?php echo $this->makePostParams('-current-', 'send', array('display' => 'send') ); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Send'); ?>">
</td>
</tr>
</table>