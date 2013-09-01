<?php
$conf =& ntsConf::getInstance();
$commonHeader = $conf->get('emailCommonHeader');
$commonFooter = $conf->get('emailCommonFooter');

$key = $this->getValue('key');

/* templates manager */
$tm =& ntsEmailTemplateManager::getInstance();

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
	<TH><?php echo M('Subject'); ?> *</TH>

<th style="vertical-align: top;">
<?php echo M('Tags'); ?>
</th>
</tr>

<tr>
	<TD>
	<?php
	echo $this->makeInput (
	/* type */
		'text',
	/* attributes */
		array(
			'id'		=> 'subject',
			'attr'		=> array(
				'size'	=> 48,
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

<td style="vertical-align: top;" rowspan="3">
<?php foreach( $tags as $t ) : ?>
	<?php echo $t; ?><br>
<?php endforeach; ?>
</td>

</TR>

<TR>
	<TH style="vertical-align: top;"><?php echo M('Message'); ?> *</TH>
</tr>
<tr>
	<TD>
	<a href="<?php echo ntsLink::makeLink('admin/conf/email_settings'); ?>"><?php echo M('Header'); ?>: <?php echo M('Edit'); ?></a>
	<br>
	<?php echo $commonHeader; ?>
	<br>
	<?php
	echo $this->makeInput (
	/* type */
		'textarea',
	/* attributes */
		array(
			'id'		=> 'body',
			'attr'		=> array(
				'cols'	=> 56,
				'rows'	=> 16,
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
	<br>
	<?php echo nl2br($commonFooter); ?>
	<br>
	<a href="<?php echo ntsLink::makeLink('admin/conf/email_settings'); ?>"><?php echo M('Footer'); ?>: <?php echo M('Edit'); ?></a>
	</TD>	

</TR>

<tr>
<td colspan="2">
<?php echo $this->makePostParams('-current-', 'save', array('key' => $key)); ?>
<INPUT TYPE="submit" VALUE="<?php echo M('Save'); ?>">
&nbsp; <a href="<?php echo ntsLink::makeLink('-current-', 'reset', array('lang' => $NTS_VIEW['lang'], 'key' => $NTS_VIEW['key']) ); ?>"><?php echo M('Reset To Defaults'); ?></a>
</td>
</tr>
</table>