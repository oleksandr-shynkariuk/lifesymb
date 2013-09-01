<?php
$schView = ntsLib::getVar( 'admin/manage:schView' );
$schEdit = ntsLib::getVar( 'admin/manage:schEdit' );

if( ! ($schView && $schEdit) ){
	return;
	}
$fromOptions = array();
reset( $schView );
foreach( $schView as $rid ){
	if( (count($schEdit) <= 1) && in_array($rid, $schEdit) ){
		continue;
		}
	$resource = ntsObjectFactory::get( 'resource' );
	$resource->setId( $rid );
	$fromOptions[] = array( $rid, ntsView::objectTitle($resource) );
	}

$toOptions = array();
reset( $schEdit );
foreach( $schEdit as $rid ){
	$resource = ntsObjectFactory::get( 'resource' );
	$resource->setId( $rid );
	$toOptions[] = array( $rid, ntsView::objectTitle($resource) );
	}

$showDuplicate = count($fromOptions) && count($toOptions);
?>

<?php if( $showDuplicate ) : ?>
<a href="#" id="<?php echo $this->getName(); ?>duplicate-form"><?php echo M('Schedules') . ': ' . M('Duplicate'); ?></a>

<p>
<div id="<?php echo $this->getName(); ?>duplicate-wrapper">
<table class="ntsForm">
<tr>
<td class="ntsForValue">
<?php if( count($fromOptions) > 1 ) : ?>
<?php
		array_unshift( $fromOptions, array('', ' - ' . M('Select') . ' - ') );

		echo $this->makeInput (
		/* type */
			'select',
		/* attributes */
			array(
				'id'		=> 'from-resource',
				'options'	=> $fromOptions,
				),
		/* validators */
			array(
				array(
					'code'		=> 'notEmpty.php', 
					'error'		=> M('Required Field'),
					),
				)
			);
?>
<?php elseif(count($fromOptions) == 1 ) : ?>
<?php
		echo $this->makeInput (
		/* type */
			'hidden',
		/* attributes */
			array(
				'id'	=> 'from-resource',
				'value'	=> $fromOptions[0][0],
				),
		/* validators */
			array(
				array(
					'code'		=> 'notEmpty.php', 
					'error'		=> M('Required Field'),
					),
				)
			);
?>
<strong><?php echo $fromOptions[0][1]; ?></strong>
<?php endif; ?>
</td>

<td>
&gt;&gt;
</td>

<td>
<?php if( count($toOptions) > 1 ) : ?>
<?php
		array_unshift( $toOptions, array('', ' - ' . M('Select') . ' - ') );
		echo $this->makeInput (
		/* type */
			'select',
		/* attributes */
			array(
				'id'		=> 'to-resource',
				'options'	=> $toOptions,
				),
		/* validators */
			array(
				array(
					'code'		=> 'notEmpty.php', 
					'error'		=> M('Required Field'),
					),
				array(
					'code'		=> 'notEqualTo.php', 
					'error'		=> "Can't duplicate schedules to the same resource",
					'params'	=> array(
						'compareWithField' => 'from-resource',
						),
					)
				)
			);
?>
<?php elseif(count($toOptions) == 1 ) : ?>
<?php
		echo $this->makeInput (
		/* type */
			'hidden',
		/* attributes */
			array(
				'id'	=> 'to-resource',
				'value'	=> $toOptions[0][0],
				)
			);
?>
<strong><?php echo $toOptions[0][1]; ?></strong>
<?php endif; ?>
</td>

<td>
<?php echo $this->makePostParams('-current-', 'duplicate'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Duplicate'); ?>">
</td>

</tr>
</table>
</div>
<?php endif; ?>


<script language="JavaScript">
<?php if( $this->valid ) : ?>
jQuery(document).ready( function(){
	jQuery("#<?php echo $this->getName(); ?>duplicate-wrapper").hide();
	});
<?php endif; ?>
jQuery("#<?php echo $this->getName(); ?>duplicate-form").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>duplicate-wrapper").toggle();
	return false;
	});
</script>
