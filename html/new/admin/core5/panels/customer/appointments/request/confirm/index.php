<?php
$NTS_SHOW_READY = true;
global $NTS_AR;

require_once( dirname(__FILE__) . '/../common/prepareViews.php' );

$ready = $NTS_AR->getReady();
$msgHeader = ( count($ready) > 1 ) ? M('Confirm Appointments') : M('Confirm Appointment');
$reschedule = $NTS_AR->getReschedule();
$msgHeader = '';
if( ! $reschedule )
	$msgHeader = ( count($ready) > 1 ) ? M('Confirm Appointments') : M('Confirm Appointment');

$ntsConf =& ntsConf::getInstance();
$maxAppsInCart = $ntsConf->get('appsInCart');
?>
<?php if( $msgHeader ) : ?>
	<h2><?php echo $msgHeader; ?></h2>
<?php endif; ?>

<?php if( (! ($reschedule)) && ($maxAppsInCart > count($ready) ) ) : ?>
<p>
<span class="ok">[+] <a href="<?php echo ntsLink::makeLink( $NTS_AR->getPanel() . '/control/add' ); ?>" class="ok"><?php echo M('Add Another Appointment'); ?></a></span>
<?php endif; ?>

<p>
<?php
require( dirname(__FILE__) . '/../common/flow-header.php' );
?>
<p>
<?php
if( ! isset($NTS_VIEW['form']) ){
	$reguireLoginForm = NTS_ENABLE_REGISTRATION ? TRUE : FALSE;

	$ff =& ntsFormFactory::getInstance();
	$formFile = dirname(__FILE__) . '/form';
	$formParams = array(
		'reguireLoginForm'		=> $reguireLoginForm,
		'reguireRegisterForm'	=> true
		);
	$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );
	}
$NTS_VIEW['form']->display();
?>