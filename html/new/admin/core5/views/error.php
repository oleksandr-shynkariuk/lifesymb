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
