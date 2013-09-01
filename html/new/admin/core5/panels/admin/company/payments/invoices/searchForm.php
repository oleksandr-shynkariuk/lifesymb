<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'	=> 'search',
		'attr'		=> array(
			'size'	=> 24,
			),
		)
	);
?>
<?php 
$params = array();
$params[NTS_PARAM_VIEW_MODE] = $NTS_VIEW[NTS_PARAM_VIEW_MODE];
echo $this->makePostParams('-current-', 'search', $params);
?>
<INPUT TYPE="submit" VALUE="<?php echo M('Search'); ?>">