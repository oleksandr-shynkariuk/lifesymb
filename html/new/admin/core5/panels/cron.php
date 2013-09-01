<?php
$conf =& ntsConf::getInstance();
$now = time();
$cronLastRun = $conf->get( 'cronLastRun' );
if( 
	(! $cronLastRun) OR
	( ($now - $cronLastRun) > 60 * 60 )
	)
{
	$cronDir = dirname(__FILE__) . '/../cron';
	require( $cronDir . '/auto-reject.php' );
	require( $cronDir . '/reminder.php' );
	require( $cronDir . '/auto-complete.php' );
	require( $cronDir . '/auto-reject2.php' );

	$conf->set( 'cronLastRun', $now );
}
?>