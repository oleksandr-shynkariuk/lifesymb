<?php
$discount = $this->getValue('discount');
$costView = $this->getValue('discountView');
$itemObj = $this->getValue('itemObj');
$discounts = $this->getValue('discounts'); 
if( (! $discount) && (! $this->readonly ) )
	$costView = M('Add');
?>
<span id="<?php echo $this->formId; ?>showDiscount">
<?php if( ! $this->readonly ) : ?>
	<a href="#" id="<?php echo $this->formId; ?>toggleDiscount"><?php echo $costView; ?></a>
<?php
$addView = '';
if( ($discounts) && is_object($itemObj) )
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
<span id="<?php echo $this->formId; ?>changeDiscount" style="display: none;">
<?php
echo $this->makeInput (
/* type */
	'text',
/* attributes */
	array(
		'id'	=> 'discount',
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
<?php echo $this->makePostParams('-current-', 'changediscount'); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Update'); ?>">
<a href="#" id="<?php echo $this->formId; ?>cancelDiscount"><?php echo M('Cancel'); ?></a>
</span>

<script type="text/javascript">
jQuery("#<?php echo $this->formId; ?>toggleDiscount").live("click", function() {
	jQuery("#<?php echo $this->formId; ?>changeDiscount").show();
	jQuery("#<?php echo $this->formId; ?>showDiscount").hide();
	return false;
	});
jQuery("#<?php echo $this->formId; ?>cancelDiscount").live("click", function() {
	jQuery("#<?php echo $this->formId; ?>changeDiscount").hide();
	jQuery("#<?php echo $this->formId; ?>showDiscount").show();
	return false;
	});
</script>
<?php endif; ?>