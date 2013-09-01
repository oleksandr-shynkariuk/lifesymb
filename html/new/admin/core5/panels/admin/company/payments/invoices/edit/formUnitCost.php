<?php
$costView = $this->getValue('costView');
$itemObj = $this->getValue('itemObj');
$discounts = $this->getValue('discounts'); 
?>
<span id="<?php echo $this->formId; ?>showUnitCost">
<?php if( ! $this->readonly ) : ?>
	<a href="#" id="<?php echo $this->formId; ?>toggleUnitCost"><?php echo $costView; ?></a>
<?php
$addView = '';
if( (! $discounts) && is_object($itemObj) )
{
	$promotions = $itemObj->getProp('_promotion');
	if( is_array($promotions) )
	{
		$tooltipInfo = array();
		reset( $promotions );
		foreach( $promotions as $promId => $couponCode )
		{
			$prom = ntsObjectFactory::get('promotion');
			$prom->setId( $promId );
			if( ! $prom->notFound() )
			{
				$tooltipInfo[] = $prom->getModificationView() . ': ' . $prom->getTitle();
			}
		}
		$tooltipInfo = join( '; ', $tooltipInfo );
		if( $tooltipInfo )
			$addView = ' <a class="nts-tooltip" title="' . $tooltipInfo . '"><span title="?"> ? </span></a>';
		echo $addView;
	}
}
?>

<?php else : ?>
	<?php echo $costView; ?>
<?php endif; ?>
</span>

<?php if( ! $this->readonly ) : ?>
<span id="<?php echo $this->formId; ?>changeUnitCost" style="display: none;">
<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'	=> 'cost',
		'attr'		=> array(
			'size'	=> 4,
			),
		)
	);
echo $this->makeInput (
/* type */
	'hidden',
/* attributes */
	array(
		'id'	=> 'item',
		)
	);
echo $this->makeInput (
/* type */
	'hidden',
/* attributes */
	array(
		'id'	=> 'item_id',
		)
	);
?>
<?php echo $this->makePostParams('-current-', 'changecost'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
<a href="#" id="<?php echo $this->formId; ?>cancelUnitCost"><?php echo M('Cancel'); ?></a>
</span>

<script type="text/javascript">
jQuery("#<?php echo $this->formId; ?>toggleUnitCost").live("click", function() {
	jQuery("#<?php echo $this->formId; ?>changeUnitCost").show();
	jQuery("#<?php echo $this->formId; ?>showUnitCost").hide();
	return false;
	});
jQuery("#<?php echo $this->formId; ?>cancelUnitCost").live("click", function() {
	jQuery("#<?php echo $this->formId; ?>changeUnitCost").hide();
	jQuery("#<?php echo $this->formId; ?>showUnitCost").show();
	return false;
	});
</script>
<?php endif; ?>