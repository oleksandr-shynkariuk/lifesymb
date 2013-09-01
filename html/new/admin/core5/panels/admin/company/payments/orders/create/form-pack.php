<tbody>
<tr>
<td class="ntsFormLabel"><?php echo M('Package'); ?></td>

<td class="ntsFormValue">
<?php
$packs = ntsObjectFactory::getAll( 'pack' );
$packOptions = array();
reset( $packs );
foreach( $packs as $pack ){
	$packOptions[] = array( $pack->getId(), $pack->getFullTitle() );
	}
array_unshift( $packOptions, array('', ' - ' . M('Select') . ' - ') );
?>
<?php
echo $this->makeInput (
/* type */
	'select',
/* attributes */
	array(
		'id'		=> 'pack_id',
		'options'	=> $packOptions
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
</tbody>