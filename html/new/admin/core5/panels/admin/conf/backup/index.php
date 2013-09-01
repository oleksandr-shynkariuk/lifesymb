<table>
<tr>
<td style="vertical-align: top;">
	<table>
	<tr>
	<td><a href="<?php echo ntsLink::makeLink('-current-', 'make' ); ?>"><?php echo M('Download Backup'); ?></a></td>
	</tr>
	<tr>
	<td><a href="<?php echo ntsLink::makeLink('-current-/upload', '' ); ?>"><?php echo M('Restore From Backup'); ?></a></td>
	</tr>
	</table>
</td>
<td style="vertical-align: top;">
<?php
$conf =& ntsConf::getInstance();
$params = array(
	'remindOfBackup',
	);
$default = array();
reset( $params );
foreach( $params as $p ){
	$default[ $p ] = $conf->get( $p );
	}
$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile, $default );
$form->display();
?>
</td>
</tr>
</table>