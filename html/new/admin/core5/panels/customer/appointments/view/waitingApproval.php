<?php
$lm =& ntsLanguageManager::getInstance();
$etm =& ntsEmailTemplateManager::getInstance();
$om =& objectMapper::getInstance();

$defaultLanguage = $lm->getDefaultLanguage();
$userLang = $defaultLanguage;

$key = 'appointment-require_approval-customer';
$templateInfo = $etm->getTemplate( $userLang, $key );

reset( $object );
?>
<?php foreach( $object as $obj ) : ?>
<?php
	$tags = $om->makeTags_Appointment( $obj, 'external' );
	$subject = str_replace( $tags[0], $tags[1], $templateInfo['subject'] );
	$body = str_replace( $tags[0], $tags[1], $templateInfo['body'] );
?>
	<p>
	<H2><?php echo nl2br( $subject ); ?></H2>

	<p>
	<?php echo nl2br( $body ); ?>
<?php endforeach; ?>
