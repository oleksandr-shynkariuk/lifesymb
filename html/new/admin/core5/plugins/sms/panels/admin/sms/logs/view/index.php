<?php
$e = $NTS_VIEW['e'];
?>
<table>
<tr>
	<th style="width: 10em;"><?php echo M('Sent At'); ?></th>
	<td>
	<?php
	$t = new ntsTime( $e['sent_at'] );
	$cellView = $t->formatWeekdayShort() . ', ' . $t->formatDate() . ' ' . $t->formatTime();
	echo $cellView;
	?>
	</td>
</tr>
<tr>
	<th><?php echo M('Status'); ?></th>
	<td>
	<?php if( $e['success'] ) : ?>
		<span class="ok">OK</span>
	<?php else : ?>
		<span class="alert"><?php echo M('Failed'); ?></span>
	<?php endif; ?>
	</td>
</tr>
<tr>
	<th><?php echo M('To'); ?></th>
	<td><?php echo $e['to_number']; ?></td>
</tr>
<tr>
	<th><?php echo M('From'); ?></th>
	<td><?php echo $e['from_number']; ?></td>
</tr>
<tr>
	<th><?php echo M('Message'); ?></th>
	<td>
	<?php echo nl2br($e['message']); ?>
	</td>
</tr>
<tr>
	<th><?php echo M('Response'); ?></th>
	<td>
	<?php echo $e['response']; ?>
	</td>
</tr>
</table>