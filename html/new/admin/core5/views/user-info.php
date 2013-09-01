<!-- HERE YOU CAN PLACE YOUR HEADER HTML CODE -->

<div id="tophead">
			<a href="index.php"><img src="images/logo-ontwerpen.png" id="logo"  alt="Logolabs" /></a>
             <ul id="nts-user-menu">
<?php if( NTS_CURRENT_USERID ) : ?>
	<?php if( $NTS_CURRENT_USER->hasRole('admin') ) : ?>
		<?php require( dirname(__FILE__) . '/user-info-admin.php' ); ?>
	<?php elseif( $NTS_CURRENT_USER->hasRole('customer') ) : ?>
		<?php require( dirname(__FILE__) . '/user-info-customer.php' ); ?>
	<?php endif; ?>
<?php else: ?>
	<?php require( dirname(__FILE__) . '/user-info-anon.php' ); ?>
<?php endif; ?>
<?php require( dirname(__FILE__) . '/user-info-lang.php' ); ?>
</ul>
		</div>