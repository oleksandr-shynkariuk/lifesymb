<?php
/* current language */
$conf =& ntsConf::getInstance();
$activeLanguages = $conf->get('languages');

/* language manager */
$lm =& ntsLanguageManager::getInstance();
$languages = $lm->getLanguages();

$active = array();
$available = array();

reset( $languages );
foreach( $languages as $l ){
	if( in_array($l, $activeLanguages) )
		$active[] = $l;
	else
		$available[] = $l;
	}

/* default language  */
$defaultLanguageConf = $lm->getLanguageConf( 'languageTemplate' );
?>

<p>
<h3><?php echo M('Active'); ?></h3>

<ul>
<?php foreach( $active as $lo ) : ?>
	<?php	$lConf = $lm->getLanguageConf( $lo );	?>
	<li>
	<b><?php echo $lo; ?> (<?php echo $lConf['language']; ?>)</b><br>
	<?php if( $lo != 'en-builtin' ) : ?>
		<?php echo M('Last Update'); ?>: <b><?php echo $lConf['lastUpdate']; ?></b>
		<?php echo M('Author'); ?>: <b><?php echo $lConf['author']; ?></b>
		<br>
	<?php endif; ?>

	<?php
	/* check if any missing strings */
	if( $lo != 'en-builtin' ){
		$missingInterface = array_diff( array_keys($defaultLanguageConf['interface']), array_keys($lConf['interface']) );
		$missingTemplates = array_diff( array_keys($defaultLanguageConf['templates']), array_keys($lConf['templates']) );
		}
	else {
		$missingInterface = array();
		$missingTemplates = array();
		}
	?>
	<?php echo M('Status'); ?>:
	<?php if( $missingInterface ) : ?>
		<a href="<?php echo ntsLink::makeLink('admin/conf/languages/view', '', array('language' => $lo) ); ?>"><?php echo M('String(s) missing'); ?>: <?php echo count($missingInterface); ?></a>
	<?php elseif( $missingTemplates ) : ?>
		<a href="<?php echo ntsLink::makeLink('admin/conf/languages/view', '', array('language' => $lo) ); ?>"><?php echo M('String(s) missing'); ?>: <?php echo count($missingTemplates); ?></a>
	<?php else : ?>
		<b>OK</b>
	<?php endif; ?>

	<?php if( count($active) > 1 ) : ?>
		<br><a href="<?php echo ntsLink::makeLink('admin/conf/languages', 'disable', array('language' => $lo) ); ?>"><?php echo M('Disable'); ?></a>
	<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>

<?php if( count($available) > 0 ) : ?>
	<p>
	<h3><?php echo M('Available'); ?></h3>
	<ul>
	<?php foreach( $available as $lo ) : ?>
		<li>
		<?php	$lConf = $lm->getLanguageConf( $lo ); ?>
		<?php if( $lConf['error'] ) : ?>
			<b><?php echo $lo; ?></b><br>
			<?php echo M('Status'); ?>: <b>XML File Error</b><br>
			<i><?php echo $lConf['error']; ?></i>
			
		<?php else : ?>
			<b><?php echo $lo; ?> (<?php echo $lConf['language']; ?>)</b><br>
			<?php if( $lo != 'en-builtin' ) : ?>
				<?php echo M('Last Update'); ?>: <b><?php echo $lConf['lastUpdate']; ?></b>
				<?php echo M('Author'); ?>: <b><?php echo $lConf['author']; ?></b>
				<br>
				<?php
				/* check if any missing strings */
				$missingInterface = array_diff( array_keys($defaultLanguageConf['interface']), array_keys($lConf['interface']) );
				$missingTemplates = array_diff( array_keys($defaultLanguageConf['templates']), array_keys($lConf['templates']) );
				?>
			<?php else : ?>
				<?php	
				$missingInterface = array();
				$missingTemplates = array();
				?>
			<?php endif; ?>

			<?php echo M('Status'); ?>:
			<?php if( $missingInterface ) : ?>
				<a href="<?php echo ntsLink::makeLink('admin/conf/languages/view', '', array('language' => $lo) ); ?>"><?php echo M('String(s) missing'); ?>: <?php echo count($missingInterface); ?></a> 
				<a href="<?php echo ntsLink::makeLink('admin/conf/languages', 'activate', array('language' => $lo) ); ?>"><?php echo M('Activate'); ?></a>
			<?php elseif( $missingTemplates ) : ?>
				<a href="<?php echo ntsLink::makeLink('admin/conf/languages/view', '', array('language' => $lo) ); ?>"><?php echo M('String(s) missing'); ?>: <?php echo count($missingTemplates); ?></a>
				<a href="<?php echo ntsLink::makeLink('admin/conf/languages', 'activate', array('language' => $lo) ); ?>"><?php echo M('Activate'); ?></a>
			<?php else : ?>
				<b>OK</B> <a href="<?php echo ntsLink::makeLink('admin/conf/languages', 'activate', array('language' => $lo) ); ?>"><?php echo M('Activate'); ?></a>
			<?php endif; ?>
		<?php endif; ?>
		<br>
		</li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>