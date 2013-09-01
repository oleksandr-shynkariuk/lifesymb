<?php
$requiredFields = $this->requiredFields;
$this->requiredFields = 0;

$conf =& ntsConf::getInstance();

$key = $this->getValue('key');

/* templates manager */
$tm =& ntsSmsTemplateManager::getInstance();

/* language options */
$lm =& ntsLanguageManager::getInstance();
$languageOptions = array();
$languages = $lm->getActiveLanguages();
foreach( $languages as $lo ){
	$lConf = $lm->getLanguageConf( $lo );
	if( $lo == 'en-builtin' ){
		$lo = 'en';
		$lConf['language'] = 'English';
		}
	$languageOptions[] = array( $lo, $lConf['language'] );
	}

/* tags */
$tags = $tm->getTags( $key );
?>

<p>
<TABLE>
<?php if( count($languageOptions) > 1 ) : ?>
	<TR>
		<TH><?php echo M('Language'); ?></TH>
		<TD>
		<?php
		echo $this->makeInput (
		/* type */
			'select',
		/* attributes */
			array(
				'id'		=> 'lang',
				'options'	=> $languageOptions,
				'attr'		=> array (
					'onChange'	=> "document.location.href='" . ntsLink::makeLink('-current-', '', array('key' => $this->getValue('key')) ) . "&nts-lang=' + this.value",
					),
				)
			);
		?>
		</TD>
		<td>&nbsp;</td>
	</TR>
<?php endif; ?>

<TR>
	<TD>
	<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'body',
			'attr'		=> array(
				'cols'	=> 36,
				'rows'	=> 6,
				),
			'required'	=> 1,
			),
	/* validators */
		array(
			array(
				'code'		=> 'notEmpty.php', 
				'error'		=> M('Required field'),
				),
			)
		);
	?>
	</TD>	

<td style="vertical-align: top;">
<?php foreach( $tags as $t ) : ?>
	<?php echo $t; ?><br>
<?php endforeach; ?>
</td>
</TR>

<tr>
<td>
<?php echo $this->makePostParams('-current-', 'save', array('key' => $key)); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
&nbsp;<a href="<?php echo ntsLink::makeLink('-current-', 'reset', array('lang' => $NTS_VIEW['lang'], 'key' => $NTS_VIEW['key']) ); ?>"><?php echo M('Reset To Defaults'); ?></a>
</td>
</tr>
</table>
<?php
$this->requiredFields = $requiredFields;
?>