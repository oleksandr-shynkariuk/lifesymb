<link rel="stylesheet" type="text/css" href="http://www.lifesymb.com/new/admin/css/style.css" />

<div id="nts-mainholder">
<?php if( $NTS_VIEW['menu2'] && $NTS_VIEW['menu3'] ) : ?>
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

<div id="nts-subheader">
<?php	require( $NTS_VIEW['subHeaderFile'] ); ?>
</div>

<div id="nts-mainpage"> 

<?php
$thisMenu = '';
if( $NTS_VIEW['menu3'] ){
	if( count($NTS_VIEW['menu3']) > 1 )
		$thisMenu = 'menu3';
	}
elseif( $NTS_VIEW['menu2'] ){
	$thisMenu = 'menu2';
	}
?>

<?php if( $thisMenu ) : ?>
	<div class="nts-<?php echo $thisMenu; ?>">
	<ul>
	<?php foreach( $NTS_VIEW[$thisMenu] as $m ) : ?>
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

		if( preg_match('/(\<i.+\>\<\/i\>\s+)(.+)/', $m['title'], $ma) )
		{
			$linkTitle = $ma[2];
			$linkIcon = $ma[1];
			$linkLabel = '';
			$linkLabel = $linkTitle;
		}
		else
		{
			$linkTitle = $m['title'];
			$linkIcon = '';
			$linkLabel = $m['title'];
		}
?>
		<li class="<?php echo $class; ?>"><a href="<?php echo $link; ?>" title="<?php echo $linkTitle; ?>"><?php echo $linkIcon; ?><?php echo $linkLabel; ?></a></li>
	<?php endforeach; ?>
	</ul>
	</div>
<?php endif; ?>

<!-- ANNOUNCE IF ANY -->
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

<!-- DISPLAY PAGE -->
<?php
if( file_exists($NTS_VIEW['displayFile']) )
	require( $NTS_VIEW['displayFile'] );
?>
</div>

<div style="float: none; clear: both;"></div>
</div>