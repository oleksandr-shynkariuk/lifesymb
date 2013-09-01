<TABLE>
<tr>
	<th><?php echo M('Label'); ?> *</th>
	<TD>
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'label',
			'default'	=> 'Credit Card by 2Checkout',
			'attr'		=> array(
				'size'	=> 32,
				),
			'required'	=> 1,
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
	</TD>
</TR>

<tr>
	<th>Test Mode</th>
	<TD>
	<?php
	echo $this->makeInput (
	/* type */
		'checkbox',
	/* attributes */
		array(
			'id'	=> 'test',
			)
		);
	?>
	</TD>
</TR>

<tr>
	<th>2Checkout Account # *</th>
	<TD>
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'account_id',
			'default'	=> '',
			'attr'		=> array(
				'size'	=> 24,
				),
			'required'	=> 1,
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
	</TD>
</TR>

<tr>
	<th>2Checkout Secret Word *</th>
	<TD>
<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'secret_word',
			'default'	=> '',
			'attr'		=> array(
				'size'	=> 24,
				),
			'required'	=> 1,
			'default'	=> 'tango',
			'help'		=> 'In <b>Account -&gt; Site Management</b> of your 2Checkout control panel',
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
	</TD>
</TR>
</table>

<p>
<h2>Important!</h2>
<p>
<i style="text-decoration: underline;">
Set this in your 2Checkout merchant account control panel.
</i>

<table>
<tr>
	<th>Account -&gt; Site Management -&gt; Direct Return</th>
	<TD>
	Header Redirect (Your URL) 
	</TD>
</TR>
</TABLE>
<p>
<strong>Also please note that your domain name (<?php echo $_SERVER['HTTP_HOST']; ?>) MUST match the domain that is listed on your 2Checkout.com account.</strong>
