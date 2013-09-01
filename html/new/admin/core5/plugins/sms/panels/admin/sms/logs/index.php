<?php 
if( ! count($NTS_VIEW['entries']) ){
	echo M('None');
	return;
	}
?>
<?php
include_once( NTS_BASE_DIR . '/lib/view/ntsPager.php' );
$pagerParams = array();

$pager = new ntsPager( $NTS_VIEW['totalCount'], $NTS_VIEW['showPerPage'], 10 );
$pager->setPage( $NTS_VIEW['currentPage'] );

$pages = $pager->getPages();
reset( $pages );
?>

[<?php echo $NTS_VIEW['showFrom']; ?> - <?php echo $NTS_VIEW['showTo']; ?> of <?php echo $NTS_VIEW['totalCount']; ?>]
&nbsp;&nbsp;<?php echo M('Pages'); ?>: 
<?php foreach( $pages as $pi ): ?>
	<?php if( $NTS_VIEW['currentPage'] != $pi['number'] ) : ?>
		<?php $pagerParams['p'] = $pi['number']; ?>
		<a href="<?php echo ntsLink::makeLink('-current-', '', $pagerParams ); ?>"><?php echo $pi['title']; ?></a>
	<?php else : ?>
		<b><?php echo $pi['title']; ?></b>
	<?php endif; ?>
<?php endforeach; ?>

<table class="nts-listing">
<tr class="listing-header">
	<th><?php echo M('Sent At'); ?></th>
	<th><?php echo M('Status'); ?></th>
	<th><?php echo M('To'); ?></th>
	<th><?php echo M('From'); ?></th>
	<th><?php echo M('Message'); ?></th>
	<th><?php echo M('Gateway'); ?></th>
	<th><?php echo M('Response'); ?></th>
</tr>

<?php $count = 0; ?>
<?php foreach( $NTS_VIEW['entries'] as $e ) : ?>
<tr class="<?php echo ($count % 2) ? 'even' : 'odd'; ?>">
	<td>
	<?php
	$t = new ntsTime( $e['sent_at'] );
	$cellView = $t->formatWeekdayShort() . ', ' . $t->formatDate() . ' ' . $t->formatTime();
	echo $cellView;
	?>
	</td>

	<td>
	<?php if( $e['success'] ) : ?>
		<span class="ok">OK</span>
	<?php else : ?>
		<span class="alert"><?php echo M('Failed'); ?></span>
	<?php endif; ?>
	</td>

	<td><?php echo $e['to_number']; ?></td>

	<td><?php echo $e['from_number']; ?></td>

	<td>
	<a href="<?php echo ntsLink::makeLink('-current-/view', '', array('id' => $e['id']) ); ?>">
	<?php echo substr( $e['message'], 0, 40 ) . ' ... '; ?>
	</a>
	</td>

	<td><?php echo $e['gateway']; ?></td>

	<td>
	<a href="<?php echo ntsLink::makeLink('-current-/view', '', array('id' => $e['id']) ); ?>">
<?php 
	$response = htmlentities($e['response']);
	echo substr( $response, 0, 40 ) . ' ... ';
?>
	</a>
	</td>
</tr>
<?php $count++; ?>
<?php endforeach; ?>
</table>