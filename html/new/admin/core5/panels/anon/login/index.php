<div id="nts-login-form">
<H2><?php echo M('Please login'); ?></H2>

<?php
$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$form =& $ff->makeForm( $formFile );

$user = $NTS_VIEW['user'];
?>
<?php $form->display( array('user' => $user) ); ?>
</div>

<?php if( NTS_ENABLE_REGISTRATION ) : ?>
	<div id="nts-register-form">
	<H2><?php echo M('New to our site? Please take a moment to register!'); ?></H2>
	<a href="<?php echo ntsLink::makeLink('anon/register' ); ?>"><?php echo M('Register'); ?></a> 
	</div>
<?php endif; ?>
<div style="float: none; clear: both;"></div>
