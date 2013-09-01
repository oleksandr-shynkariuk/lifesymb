<?php
$ntsdb =& dbWrapper::getInstance();

/* add 'until_closed' for session */
$sql = "ALTER TABLE {PRFX}sessions ADD COLUMN `until_closed` tinyint NOT NULL DEFAULT 0";
$result = $ntsdb->runQuery( $sql );

/* change timezone values */
/* global */
$conf =& ntsConf::getInstance();
$currentTz = $conf->get('companyTimezone');

$newTz = '';
$changes = array(
	'-9'	=> 'America/Anchorage',
	'-8'	=> 'America/Los_Angeles',
	'-7'	=> 'America/Phoenix',
	'-6'	=> 'America/Chicago',
	'-5'	=> 'America/Detroit',
	'-11'	=> 'Pacific/Samoa',
	'-10'	=> 'Pacific/Honolulu',
	'-9.5'	=> 'Pacific/Marquesas',
	'-4'	=> 'America/St_Johns',
	'-3.5'	=> 'America/St_Johns',
	'-3'	=> 'America/Buenos_Aires',
	'-2'	=> 'America/Sao_Paulo',
	'-1'	=> 'Atlantic/Azores',
	'0'		=> 'Europe/London',
	'1'		=> 'Europe/Berlin',
	'2'		=> 'Europe/Vilnius',
	'3'		=> 'Europe/Moscow',
	'3.5'	=> 'Asia/Tehran',
	'4'		=> 'Asia/Dubai',
	'4.5'	=> 'Asia/Kabul',
	'5'		=> 'Asia/Karachi',
	'5.5'	=> 'Asia/Calcutta',
	'5.75'	=> 'Asia/Katmandu',
	'6'		=> 'Asia/Almaty',
	'6.5'	=> 'Asia/Rangoon',
	'7'		=> 'Asia/Bangkok',
	'8'		=> 'Asia/Hong_Kong',
	'8.75'	=> 'Australia/Eucla',
	'9'		=> 'Asia/Tokyo',
	'9.5'	=> 'Australia/Darwin',
	'10'	=> 'Australia/Queensland',
	'10.5'	=> 'Australia/South',
	'11'	=> 'Australia/Sydney',
	'11.5'	=> 'Pacific/Norfolk',
	'12'	=> 'Pacific/Fiji',
	'13'	=> 'Pacific/Auckland',
	'14'	=> 'Pacific/Kiritimati',
	);

if( isset($changes[$currentTz]) )
	$newTz = $changes[$currentTz];
else {
	if( preg_match('/^[\d\.\-]$/', $currentTz) ){
		$currentTz = ($currentTz >= 0) ? '+' . $currentTz : $currentTz;
		$newTz = "Etc/GMT$currentTz";
		}
	}

if( $newTz ){
	$conf->set( 'companyTimezone', $newTz );
	}

/* users */
reset( $changes );
foreach( $changes as $from => $to ){
	$sql =<<<EOT
	UPDATE
		{PRFX}objectmeta
	SET
		meta_value = "$to"
	WHERE
		meta_name = "_timezone" AND
		meta_value = $from
EOT;
//echo nl2br($sql) . '<br><br>';
	$result = $ntsdb->runQuery( $sql );
	}
?>