<?php /*?><?php if( $_SERVER['SERVER_NAME'] == 'localhost' ) : ?>
<br>
<?php ntsLib::printCurrentExecutionTime(); ?>
<br>
<?php
$ntsdb =& dbWrapper::getInstance();
echo $ntsdb->_queryCount . ' queries';
?>
<br>memory: <?php echo number_format(memory_get_usage()); ?><br>
<?php endif; ?><?php */?>
</body>
</html>