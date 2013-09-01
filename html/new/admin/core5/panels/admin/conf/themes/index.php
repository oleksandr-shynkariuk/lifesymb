<?php
$conf =& ntsConf::getInstance();

/* current theme */
$currentTheme = $conf->get('theme');

/* get themes */
$available = array();
if( $currentTheme != 'default' )
	$available[] = 'default';

$themeDir = NTS_EXTENSIONS_DIR . '/themes';
$folders = ntsLib::listSubfolders( $themeDir );
reset( $folders );
foreach( $folders as $f ){
	if( $f == '_copy_from' )
		continue;
	if( $f != $currentTheme )
		$available[] = $f;
	}
?>
<p>
<h3><?php echo M('Active'); ?></h3>
<ul>
<li>
	<b><?php echo $currentTheme; ?></b>
<?php
	$missing = false;
	if( $currentTheme != 'default' ){
		$themeFolder = NTS_EXTENSIONS_DIR . '/themes/' . $currentTheme;
		if( ! file_exists($themeFolder) )
			$missing = true;
		}
?>
	<?php if( $missing ) : ?>
		<b class="alert">Theme folder missing! Default theme is used.</b>
	<?php endif; ?>
	
	</li>
</ul>

<?php if( count($available) > 0 ) : ?>
	<p>
	<h3><?php echo M('Available'); ?></h3>
	<ul>
	<?php foreach( $available as $av ) : ?>
		<li>
		<b><?php echo $av; ?></b>
		<a href="<?php echo ntsLink::makeLink('-current-', 'activate', array('theme' => $av) ); ?>"><?php echo M('Activate'); ?></a>
		</li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
