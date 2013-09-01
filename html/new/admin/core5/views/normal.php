<?php
global $_NTS, $NTS_VIEW, $NTS_CURRENT_USER;
if( ! defined('NTS_HEADER_SENT') ){
	if( isset($NTS_VIEW['headFile']) && $NTS_VIEW['headFile'] && file_exists($NTS_VIEW['headFile']) )
		require( $NTS_VIEW['headFile'] );
	else
		require( dirname(__FILE__) . '/head.php' );
	define( 'NTS_NEED_FOOTER', 1 );
	}
?>

<!-- HEADER -->
<?php if( isset($NTS_VIEW['headerFile']) && file_exists($NTS_VIEW['headerFile']) ) : ?>
	<?php require( $NTS_VIEW['headerFile'] ); ?>
<?php endif; ?>

<div id="nts">

<?php if( ntsView::isAdminAnnounce() ) : ?>
	<ul id="nts-admin-announce">
<?php 
		$text = ntsView::getAdminAnnounceText();
		if( ! is_array($text) )
			$text = array( $text );
?>
	<?php foreach( $text as $t ) : ?>
		<li><?php echo $t[0]; ?></li>
	<?php endforeach; ?>
	</ul>
	<?php ntsView::clearAdminAnnounce(); ?>
<?php endif; ?>

<?php if( preg_match('/^admin/', $_NTS['CURRENT_PANEL']) ) : ?>
<?php require( dirname(__FILE__) . '/admin-header.php' ); ?>
<?php endif; ?>

<!-- USER ACCOUNT INFO  -->
<?php require( dirname(__FILE__) . '/user-info.php' ); ?>

<!-- MENU  -->
<?php if( $NTS_VIEW['menu1'] ) : ?>
	<ul id="nts-menu1">
	<?php foreach( $NTS_VIEW['menu1'] as $m ) : ?>
		<?php
		$currentOne = false;
		if(
			( ($m['panel'] == substr($_NTS['CURRENT_PANEL'], 0, strlen($m['panel']))) && ( (strlen($_NTS['CURRENT_PANEL']) == $m['panel']) || (substr($_NTS['CURRENT_PANEL'], strlen($m['panel']), 1) == '/') ) )
			||
			( ($m['panel'] == substr($_NTS['CURRENT_PANEL'], 0, strlen($m['panel']))) && ( (strlen($_NTS['CURRENT_PANEL']) == $m['panel']) || (substr($_NTS['CURRENT_PANEL'], strlen($m['panel']), 1) == '/') ) )
			||
			( $m['panel'] == $_NTS['CURRENT_PANEL'] )	
			){
			$currentOne = true;
			}
		$link = ntsLink::makeLink($m['panel'], '', $m['params'], false, true);
		?>
		<li><a<?php if ( $currentOne ){ echo ' class="current"'; } ?> href="<?php echo $link; ?>"><?php echo $m['title']; ?></a></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php 
	if( 
		isset($NTS_VIEW['subHeaderFile']) && 
		$NTS_VIEW['subHeaderFile'] && 
		file_exists($NTS_VIEW['subHeaderFile']) &&
		$NTS_VIEW['menu2'] || $NTS_VIEW['menu3']
		) : 
?>
<?php 	require( dirname(__FILE__) . '/normal-subheader.php'); ?>

<?php else : ?>

<?php if( $NTS_VIEW['menu2'] ) : ?>
	<div class="nts-menu2">
	<ul>
<?php	foreach( $NTS_VIEW['menu2'] as $m ) : ?>
<?php 
			if( isset($m['directLink']) )
				$link = $m['directLink'];
			else {
				$link = ntsLink::makeLink( $m['panel'], '', $m['params'], false );
				}
		$currentOne = false;
		if( 
			($m['panel'] == substr($_NTS['CURRENT_PANEL'], 0, strlen($m['panel'])) ) ||
			($m['panel'] == substr($_NTS['CURRENT_PANEL'], 0, strlen($m['panel'])) )
			){
			$currentOne = true;
			}
?>
		<li<?php if ( $currentOne ){ echo ' class="selected"'; } ?>><a href="<?php echo $link; ?>"><?php echo $m['title']; ?></a></li>
<?php	endforeach; ?>
	</ul>
	</div>
<?php endif; ?>

<!-- ANNOUNCE IF ANY -->
<?php 	if( ntsView::isAnnounce() ) : ?>
	<ul id="nts-announce">
	<?php $text = ntsView::getAnnounceText();	?>
	<?php foreach( $text as $t ) : ?>
	<?php if( $t[1] == 'error' ) : ?>
		<li class="error">
	<?php else : ?>
		<li>
	<?php endif; ?>
		<?php echo $t[0]; ?>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php ntsView::clearAnnounce(); ?>
<?php 	endif; ?>

<!-- DISPLAY PAGE -->
<?php
if( file_exists($NTS_VIEW['displayFile']) )
	require( $NTS_VIEW['displayFile'] );
?>

<?php endif; ?>

<!-- FOOTER IF ANY -->
<?php if( isset($NTS_VIEW['footerFile']) && file_exists($NTS_VIEW['footerFile']) ) : ?>
	<?php require( $NTS_VIEW['footerFile'] ); ?>
<?php endif; ?>

</div><!-- end of #nts -->

<?php
if( ( ! NTS_APP_WHITELABEL) && ( ! ( preg_match('/^admin/', $_NTS['CURRENT_PANEL']) ) ) ){
	echo '<!-- for stats -->' . "\n" . '<div id="ntsCredit"><a href="http://www.hitappoint.com">Appointment scheduling software by hitAppoint</a></div>';
	}
?>
<?php
if( defined('NTS_NEED_FOOTER') )
	require( dirname(__FILE__) . '/footer.php' );
?>