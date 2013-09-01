<?php
global $NTS_AR, $NTS_CURRENT_USER;

$ntsconf =& ntsConf::getInstance();
$taxTitle = $ntsconf->get('taxTitle');

$reguireLoginForm = $this->getValue('reguireLoginForm');
$reguireRegisterForm = $this->getValue('reguireRegisterForm');

$ready = $NTS_AR->getReady();
$msgButton = ( count($ready) > 1 ) ? M('Confirm Appointments') : M('Confirm Appointment');

/* check if we have valid coupon */
$suppliedCoupon = $NTS_AR->getCoupon();
$ntspm =& ntsPaymentManager::getInstance();

/* check if we have coupons for this ready */
$showCoupon = FALSE;
$coupon_promotions = array();
reset( $ready );
foreach( $ready as $r )
{
	if( ! $showCoupon )
	{
		$have_coupon_promotions = $ntspm->getPromotions( $r, '', TRUE );
		if( $have_coupon_promotions )
		{
			$showCoupon = TRUE;
		}
	}

	$this_coupon_promotions = $ntspm->getPromotions( $r, $suppliedCoupon, TRUE );
	if( $this_coupon_promotions )
	{
		reset( $this_coupon_promotions );
		foreach( $this_coupon_promotions as $cp )
			$coupon_promotions[ $cp->getId() ] = $cp;
	}
}

// if we have several apps, check services and custom forms
$class = 'appointment';
$om =& objectMapper::getInstance();

$allForms = array();
$form2service = array();

reset( $ready );
foreach( $ready as $r ){
	$serviceId = $r['service'];
	$formId = $om->isFormForService( $serviceId );
	if( ! in_array($formId, $allForms) ){
		$allForms[] = $formId;
		if( ! isset($form2service[$formId]) )
			$form2service[ $formId ] = array();
		$form2service[ $formId ][] = $serviceId;
		}
	}
?>
<?php
$NTS_SHOW_READY = true;
require( dirname(__FILE__) . '/../common/flow.php' );
?>
<?php
$customerKnown = false;
if(	($NTS_CURRENT_USER->getId()) || isset( $_SESSION['temp_customer_id'] ) ){
	$customerKnown = true;
	}

$rowsShown = 0;	
?>
<table>
<?php
	if( 
		($NTS_CURRENT_USER->getId() < 1) && 
		(! $NTS_CURRENT_USER->hasRole('admin')) &&
		( 
		isset($_SESSION['temp_customer_id'])) || 
		( $_NTS['REQ']->getParam('email') && $_NTS['REQ']->getParam('first_name') && $_NTS['REQ']->getParam('last_name') )
		)
	:
?>
<?php 
	$tempCustomer = new ntsUser();
	if( isset($_SESSION['temp_customer_id']) ){
		$tempCustomer->setId( $_SESSION['temp_customer_id'] );
		}
	elseif( $_NTS['REQ']->getParam('email') && $_NTS['REQ']->getParam('first_name') && $_NTS['REQ']->getParam('last_name') ) {
		$noCookieFields = array( 'email', 'first_name', 'last_name' );
		foreach( $noCookieFields as $ncf ){
			$tempCustomer->setProp( $ncf, $_NTS['REQ']->getParam($ncf) );
			echo $this->makeInput (
				'hidden',
				array(
					'id'	=> $ncf,
					'value'	=> $_NTS['REQ']->getParam($ncf)
					)
				);
			}
		}
	$rowsShown++;
?>
	<tr>
		<td class="ntsFormLabel"><?php echo M('Your Name'); ?></td>
		<td>
			<b><?php echo $tempCustomer->getProp('first_name'); ?> <?php echo $tempCustomer->getProp('last_name'); ?></b>
			[<a href="<?php echo ntsLink::makeLink('-current-', 'reset_customer'); ?>" class="alert"><?php echo M('Not you?'); ?></a>]
		</td>
	</tr>
<?php endif; ?>

<?php // if( $customerKnown && $allForms ) : ?>
<?php if( $allForms ) : ?>
<?php foreach( $allForms as $formId ) : ?>
<?php
		if( ! $formId )
			continue;
		$otherDetails = array(
			'service_id'	=> $form2service[ $formId ][0],
			);
		$fields = $om->getFields( $class, 'external', $otherDetails );
		reset( $fields );
?>
<?php if( count($allForms) > 1 ) : ?>
<?php
$serviceTitles = array();
reset( $form2service[ $formId ] );
foreach( $form2service[ $formId ] as $si ){
	$thisService = ntsObjectFactory::get( 'service' );
	$thisService->setId( $si );
	$serviceTitles[] = ntsView::objectTitle( $thisService );
	}
	$rowsShown++;
?>
	<tr>
		<th colspan="2"><b><?php echo join( ', ', $serviceTitles); ?></b></th>
	</tr>
<?php endif; ?>

	<?php foreach( $fields as $f ) : ?>
	<?php $c = $om->getControl( $class, $f[0], false ); ?>
	<?php
	if( isset($f[4]) ){
		if( $f[4] == 'read' ){
			continue;
			}
		}
	?>
	<tr>
		<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
		<td>
		<?php
		echo $this->makeInput (
			$c[1],
			$c[2],
			$c[3]
			);
		?>
<?php	if( $c[2]['description'] ) : ?>
&nbsp;<i><?php echo $c[2]['description']; ?></i></td>
<?php	endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php
$prices = $NTS_AR->getPrices();
$showPrice = false;
$pm =& ntsPaymentManager::getInstance();

$totalFinalPrice = 0;
$totalBasePrice = 0;
$totalDueNow = 0;
$totalTax = 0;
$totalTaxNow = 0;

reset( $prices );

for( $ii = 0; $ii < count($prices); $ii++ ){
	$pi = $prices[$ii];
	if( strlen($pi[0]) ){
		$showPrice = true;

		$serviceId = $ready[$ii]['service'];
		$service = ntsObjectFactory::get('service');
		$service->setId( $serviceId );

		$taxRate = $pm->getTaxRate( $service );
		$finalPrice = $pi[1];
		$basePrice = $pi[3];
		$dueNow = $pi[2];
		$tax = ntsLib::calcTax( $finalPrice, $taxRate );
		$baseTax = ntsLib::calcTax( $basePrice, $taxRate );
		$taxNow = ntsLib::calcTax( $dueNow, $taxRate ); 

		$totalFinalPrice += ($finalPrice + $tax);
		$totalBasePrice += ($basePrice + $baseTax);
		$totalDueNow += ($dueNow + $taxNow);
		$totalTax += $tax;
		$totalTaxNow += $taxNow;
		}
	}
?>
<?php if( $showPrice ) : ?>
<?php	if( $totalDueNow != $totalFinalPrice ) : ?>
	<?php if( $totalTax ) : ?>
		<h3 style="margin: 0.5em 0 0 0;"><?php echo M('Subtotal'); ?>: <?php echo ntsCurrency::formatServicePrice($totalFinalPrice - $totalTax); ?></h3>
		<?php echo $taxTitle; ?>: <?php echo ntsCurrency::formatServicePrice($totalTax); ?><br>
	<?php endif; ?>
<p>
<table>
<tr>
<td class="ntsFormLabel">
<h3 style="margin: 0 0;"><?php echo M('Total'); ?></h3>
</td>
<td class="ntsFormValue">

<h3 style="margin: 0 0;">
<?php if( $totalBasePrice != $totalFinalPrice ) : ?>
	<span style="text-decoration: line-through;"><?php echo ntsCurrency::formatServicePrice($totalBasePrice); ?></span>
<?php endif; ?>
<?php echo ntsCurrency::formatServicePrice($totalFinalPrice); ?>

</h3>

</td>
</tr>

<?php if( $showCoupon ) : ?>
	<tr>
	<td class="ntsFormLabel"><?php echo M('Coupon Code'); ?>?</td>
	<td class="ntsFormValue">
<?php if( (! $suppliedCoupon) OR (! $coupon_promotions) ) : ?>
<?php
		if( $suppliedCoupon && (! $coupon_promotions) )
		{
			$this->errors['coupon'] = M('Not Valid');
		}
		echo $this->makeInput (
		/* type */
			'text',
		/* attributes */
			array(
				'id'		=> 'coupon',
				'attr'		=> array(
					'size'	=> 12,
					),
				'default'	=> $suppliedCoupon,
				)
			);
?> <a href="<?php echo ntsLink::makeLink('-current-'); ?>" id="nts-apply-coupon"><?php echo M('Apply'); ?></a>
<?php elseif($coupon_promotions) :  ?>
<?php 	echo $suppliedCoupon; ?> (
<strong><?php	foreach( $coupon_promotions as $cp ) : ?>
<?php 		echo $cp->getModificationView() . ': ' . $cp->getTitle(); ?> 
<?php	endforeach; ?>
</strong>)
<?php endif; ?>

	</td>
	</tr>
<?php endif; ?>
</table>

<?php	endif; ?>

<?php	if( $totalDueNow ) : ?>
	<?php if( $totalTaxNow ) : ?>
		<h3 style="margin: 0.5em 0 0 0;"><?php echo M('Subtotal Due Now'); ?>: <?php echo ntsCurrency::formatServicePrice($totalDueNow - $totalTaxNow); ?></h3>
		<?php echo $taxTitle; ?>: <?php echo ntsCurrency::formatServicePrice($totalTaxNow); ?><br>
	<?php endif; ?>
	<h3 style="margin: 0 0;"><?php echo M('Total Due Now'); ?>: <?php echo ntsCurrency::formatServicePrice($totalDueNow); ?></h3>
<?php	endif; ?>
<?php 	endif; ?>

<?php if( ! (isset($NTS_VIEW['selectedNotAvailable']) && $NTS_VIEW['selectedNotAvailable']) ) : ?>
<?php echo $this->makePostParams('-current-', 'confirm' ); ?>

<?php if( (! $customerKnown) ) : ?>
<?php 	if( $reguireLoginForm ) : ?>
	<p>
	<h2><?php echo M('Already have an account?'); ?></h2>
	<?php	require(dirname(__FILE__) . '/form_Login.php'); ?>
<?php 	endif; ?>

<?php 	if( $reguireRegisterForm ) : ?>
	<p>
<?php 		if( NTS_ENABLE_REGISTRATION ) : ?>
	<h2><?php echo M('Or please register'); ?></h2>
<?php endif; ?>
	<?php	require(dirname(__FILE__) . '/form_Register.php'); ?>
<?php 	endif; ?>
<?php endif; ?>

<?php if( $customerKnown ) : ?>
<p>
<INPUT TYPE="submit" VALUE="<?php echo $msgButton; ?>">
<?php endif; ?>
<?php endif; ?>

<script language="JavaScript">
jQuery('#nts-apply-coupon').live("click", function()
{
	var targetUrl = jQuery(this).attr('href');
	var couponCode = jQuery(this).closest('form').find('[name=nts-coupon]').val();
	targetUrl += '&nts-coupon=' + couponCode;
	document.location.href = targetUrl;
	return false;
});
</script>