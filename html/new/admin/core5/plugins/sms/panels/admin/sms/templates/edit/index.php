<?php
require( NTS_APP_DIR . '/panels/admin/conf/email_templates/_keys.php' );

// find title
$keyTitle = '';
reset( $matrix );
foreach( $matrix as $class => $classArray ){
	reset( $classArray );
	foreach( $classArray as $to => $toArray ){
		reset( $toArray );
		foreach( $toArray as $keyArray ){
			if( $keyArray[0] == $NTS_VIEW['key'] ){
				$keyTitle = $keyArray[1];
				break;
				}
			}
		}
	}
?>
<H2><?php echo M('SMS Notifications Templates'); ?></H2>
<H3><?php echo $keyTitle; ?></H3>

<?php
$ff =& ntsFormFactory::getInstance();
$form =& $ff->makeForm( dirname(__FILE__) . '/form' );
$form->display();
?>