<?php
$cm =& ntsCommandManager::getInstance();
$ntsdb =& dbWrapper::getInstance();

/* CUSTOMER AND PROVIDER - create field for mobile phone no */
$formIds = array();

$sql =<<<EOT
	SELECT
		*
	FROM
		{PRFX}forms
	WHERE
		class = 'provider'
	LIMIT 1
EOT;
$result = $ntsdb->runQuery( $sql );
if( $formInfo = $result->fetch() ){
	$formIds[] = $formInfo['id'];
	}
else {
	$form = new ntsObject( 'form' );
	$form->setProp( 'title', 'Provider Form' );
	$form->setProp( 'class', 'provider' );
	$form->setProp( 'details', '' );

	$cm->runCommand( $form, 'create' );
	$providerFormId = $form->getId();
	$formIds[] = $providerFormId;
	}

/* customer forms */
$sql =<<<EOT
	SELECT
		*
	FROM
		{PRFX}forms
	WHERE
		class = 'customer'
	LIMIT 1
EOT;
$result = $ntsdb->runQuery( $sql );
while( $formInfo = $result->fetch() )
	$formIds[] = $formInfo['id'];


/* add phone field */	
reset( $formIds );
foreach( $formIds as $formId ){
	$sql =<<<EOT
SELECT
	id
FROM
	{PRFX}form_controls
WHERE
	name = 'mobile_phone' AND
	form_id = $formId
LIMIT 1
EOT;
	$result = $ntsdb->runQuery( $sql );
	$fieldInfo = $result->fetch();

	if( ! $fieldInfo ){
		/* field */
		$field = new ntsObject( 'form_control' );
		$fieldValues = array(
			'name'			=> 'mobile_phone',
			'title'			=> 'Mobile Phone For SMS Notifications',
			'ext_access'	=> 'write',
			'type'			=> 'mobilephone',
			'attr'			=> array( 'size' => 24 ),
			'description'	=> "Please make sure that you include the country code. For example, the US number (248) 123-7654 becomes +12481237654, the UK number 07777123456 becomes +447777123456.",
			);

		$field->setByArray( $fieldValues );
		$field->setProp( 'form_id', $formId );
		$cm->runCommand( $field, 'create' );
		}
	}

/* TABLE FOR LOGS */
$sql =<<<EOT
CREATE TABLE IF NOT EXISTS `{PRFX}smslog` (
	`id` int(11) NOT NULL auto_increment,
	`sent_at` int(11),

	`to_number` VARCHAR(16),
	`from_number` VARCHAR(16),
	`message` TEXT,
	`success` TINYINT DEFAULT 0,
	`gateway` VARCHAR(16),
	`response` TEXT,

	PRIMARY KEY  (`id`)
	);
EOT;
$result = $ntsdb->runQuery( $sql );
?>