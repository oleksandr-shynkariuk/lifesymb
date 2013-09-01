<?php echo M('Timezone'); ?> 
<?php
$timezoneOptions = ntsTime::getTimezones();

echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'tz',
		'options'	=> $timezoneOptions,
		)
	);
?>
<?php echo $this->makePostParams('-current-', 'timezone'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
