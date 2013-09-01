<?php
global $NTS_VIEW;
?>
<?php /* ?>
<p>
<i><?php echo $_SERVER["REQUEST_URI"]; ?></i><br><br>
<?php */ ?>

<?php 
$showMenu3 = false;
if( $NTS_VIEW['menu3'] )
	$showMenu3 = true;
?>
<?php if( $showMenu3 ) : ?>
	<?php	require( $NTS_VIEW['subHeaderFile'] ); ?>

	<div class="nts-menu3">
	<ul style="margin: 0.25em 0; padding: 0 0;">
	<?php foreach( $NTS_VIEW['menu3'] as $m ) : ?>
		<?php
			if( isset($m['directLink']) ){
				$link = $m['directLink'];
				}
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
		$class = array();
		if( $currentOne ){
			$class[] = $m['alert'] ? 'selected-alert' : 'selected';
			}
		else {
			$class[] = $m['alert'] ? 'alert' : '';
			}
		$class = join( ' ', $class );
		?>
		<li class="<?php echo $class; ?>"><a href="<?php echo $link; ?>" title="<?php echo $m['title']; ?>"><?php echo $m['title']; ?></a></li>
	<?php endforeach; ?>
	</ul>
	</div>
<?php endif; ?>

<?php if( ntsView::isAnnounce() ) : ?>
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
<?php endif; ?>

<?php
if( file_exists($NTS_VIEW['displayFile']) )
	require( $NTS_VIEW['displayFile'] );
?>