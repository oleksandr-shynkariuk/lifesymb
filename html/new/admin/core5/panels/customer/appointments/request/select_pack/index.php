<?php
$packs = $NTS_VIEW['packs'];
reset( $packs );
?>
<div id="nts-selector">
<?php if( $packs ) : ?>
	<a href="<?php echo ntsLink::makeLink('-current-/..'); ?>"><?php echo M('Schedule Now'); ?></a>
<?php endif; ?>

<h2><?php echo M('Packages'); ?></h2>
	<ul>
	<?php foreach( $packs as $p ) : ?>
		<li>
		<h3><?php echo $p->getFullTitle(); ?></h3>
<?php if( $p->getServiceType() == 'fixed' ) : ?>
<?php
$services = $p->getGroupedServices();
reset( $services );
$serviceView = array();
foreach( $services as $sa )
	$serviceView[] = $sa[1] . 'x ' . ntsView::objectTitle($sa[0]);
$serviceView = join( ', ', $serviceView );
?>
<ul>
<li><?php echo $serviceView; ?></li>
</ul>
<?php endif; ?>

<?php
$expiresIn = $p->getProp('expires_in');
if( $expiresIn ){
	list( $qty, $measure ) = explode( ' ', $expiresIn );
	$expiresInText = $qty;
	$tag = ( $qty > 1 ) ? $measure : substr($measure, 0, -1);
	$tag = ucfirst( $tag );
	$expiresInText .= ' ' . M($tag);
	}
else {
	$expiresInText = '';
	}

$resourceId = $p->getProp('resource_id');
if( $resourceId ){
	$resource = ntsObjectFactory::get('resource');
	$resource->setId( $resourceId );
	$resourceView = ntsView::objectTitle($resource);
	}
else {
	$resourceView = ' - ' . M('Any') . ' - ';
	}
?>
		<ul>
<?php if( $resourceView ) : ?>
			<li><?php echo M('Bookable Resource'); ?>: <?php echo $resourceView; ?></li>
<?php endif; ?>

<?php if( $expiresInText ) : ?>
			<li><?php echo M('Expires In'); ?>: <?php echo $expiresInText; ?></li>
<?php endif; ?>

			<li>
			<a href="<?php echo ntsLink::makeLink('-current-/../confirm-pack', '', array('pack' => $p->getId()) ); ?>"><?php echo M('Buy Now'); ?> [<?php echo ntsCurrency::formatServicePrice($p->getProp('price')); ?>]</a>
			</li>
		</ul>
		</li>
	<?php endforeach; ?>
	</ul>
</div>
