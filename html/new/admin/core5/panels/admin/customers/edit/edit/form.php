<?php
$object = ntsLib::getVar( 'admin/customers/edit::OBJECT' );

$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();

$id = $this->getValue('id');
/* form params - used later for validation */
$this->setParams(
	array(
		'myId'	=> $id,
		)
	);

$className = 'customer';

$om =& objectMapper::getInstance();
$fields = $om->getFields( $className, 'internal' );
reset( $fields );

/* status */
list( $alert, $cssClass, $message ) = $object->getStatus();
$class = $alert ? 'alert' : 'ok';
$restrictions = $object->getProp( '_restriction' );
?>
<table class="ntsForm">
<tr>
<td class="ntsFormValue">
ID: <?php echo $object->getId(); ?>
</td>
<td class="ntsFormValue">
<b class="<?php echo $class; ?>"><?php echo $message; ?></b>
&nbsp;
<?php if( $restrictions ) : ?>
	<a class="ok" href="<?php echo ntsLink::makeLink('-current-', 'activate'); ?>"><?php echo M('Activate'); ?></a>
<?php else : ?>
	<a class="alert" href="<?php echo ntsLink::makeLink('-current-', 'suspend'); ?>"><?php echo M('Suspend'); ?></a>
<?php endif; ?>
</td>
</tr>

<?php foreach( $fields as $f ) : ?>
<?php $c = $om->getControl( 'customer', $f[0], false ); ?>
<tr>
	<td class="ntsFormLabel"><?php echo $c[0]; ?></td>
	<td class="ntsFormValue">
	<?php
	if( defined('NTS_REMOTE_INTEGRATION') && (NTS_REMOTE_INTEGRATION == 'wordpress') && ($c[2]['id'] == 'username') ){
		$c[1] = 'labelData';
		}
	echo $this->makeInput (
		$c[1],
		$c[2],
		$c[3]
		);
	?>
	<?php if( NTS_ALLOW_NO_EMAIL && ($c[2]['id'] == 'email') ) : ?>
		<?php
		echo $this->makeInput (
		/* type */
			'checkbox',
		/* attributes */
			array(
				'id'	=> 'noEmail',
				)
			);
		?><?php echo M('No Email?'); ?>
	<?php endif; ?>
	<?php if( NTS_ALLOW_DUPLICATE_EMAILS && ($c[2]['id'] == 'email') ) : ?>
<?php // 	check if there're duplicates
			$checkEmail = $this->getValue('email');
			$countDuplicates = 0;
			if( strlen($checkEmail) ){
				$myWhere = array();
				$myWhere['email'] = array('=', $checkEmail);
				$myWhere['id'] = array('<>', $id);
				$countDuplicates = $integrator->countUsers( $myWhere );
				}
?>
		<?php if( $countDuplicates ) : ?>
			<br>Also <a href="<?php echo ntsLink::makeLink('admin/customers/browse', 'search', array('email' => $checkEmail) ); ?>"><?php echo $countDuplicates; ?> other user(s)</a> with this email
		<?php endif; ?>
	<?php endif; ?>
	</td>
</tr>
<?php endforeach; ?>

<?php if( NTS_ENABLE_TIMEZONES > 0 ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Timezone'); ?></td>
	<td>
	<?php
	$timezoneOptions = ntsTime::getTimezones();
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> '_timezone',
			'options'	=> $timezoneOptions,
			)
		);
	?>
	</td>
</tr>
<?php endif; ?>

<?php
$lm =& ntsLanguageManager::getInstance();
$languages = $lm->getActiveLanguages();
$currentLanguage = $object->getLanguage();
?>
<?php if( count($languages) > 1 ) : ?>
<tr>
	<td class="ntsFormLabel"><?php echo M('Language'); ?></td>
	<td>
<?php
	$languageOptions = array();
	reset( $languages );
	foreach( $languages as $lng )
	{
		$languageOptions[] = array( $lng, $lng );
	}
	echo $this->makeInput (
	/* type */
		'select',
	/* attributes */
		array(
			'id'		=> '_lang',
			'options'	=> $languageOptions,
			)
		);
?>
	</td>
</tr>
<?php endif; ?>
<tr>
<td></td>
<td>
<?php echo $this->makePostParams('-current-', 'update' ); ?>
<INPUT TYPE="submit" VALUE="Update">
</td>
</tr>
</table>

<?php if( NTS_ALLOW_NO_EMAIL ) : ?>
<script language="JavaScript">
jQuery(document).ready( function(){
	if( jQuery("#<?php echo $this->getName(); ?>noEmail").is(":checked") ){
		jQuery("#<?php echo $this->getName(); ?>email").hide();
		}
	else {
		jQuery("#<?php echo $this->getName(); ?>email").show();
		}
	});
jQuery("#<?php echo $this->getName(); ?>noEmail").live( 'click', function(){
	jQuery("#<?php echo $this->getName(); ?>email").toggle();
	});
</script>
<?php endif; ?>
