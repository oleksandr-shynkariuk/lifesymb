<?php
global $NTS_VIEW;
$flowCount = isset($NTS_VIEW['flowFlow']) ? count( $NTS_VIEW['flowFlow'] ) : 0;
$appsCount = isset( $NTS_VIEW['flowFlow'][0][1] ) ? count($NTS_VIEW['flowFlow'][0][1]) : 1;
$selectedNotAvailable = isset($NTS_VIEW['selectedNotAvailable']) ? $NTS_VIEW['selectedNotAvailable'] : array();
?>
<?php if( $flowCount ) : ?>

<?php if( $appsCount > 0 ) : ?>
<div id="nts-appointment-list">
	<table>

<?php if( $flowCount > 1 ) : ?>
	<tr>
	<th style="width: 2em;">&nbsp;</th>
	<?php for( $j = 0; $j < $flowCount; $j++ ) : ?>
	<th><?php echo $NTS_VIEW['flowTitles'][ $NTS_VIEW['flowFlow'][$j][0] ]; ?></th>
	<?php endfor; ?>
	<th>&nbsp;</th>
	</tr>
<?php endif; ?>

<?php for( $i = 0; $i < $appsCount; $i++ ) : ?>
<?php
if( in_array(($i+1), $selectedNotAvailable) ){
	$entryClass = 'alert';
	}
else {
	$entryClass = '';
	}
?>
	<tr class="<?php echo $entryClass; ?>">

<?php if( $appsCount > 1 ) : ?>
	<td><?php echo ($i + 1); ?></td>
<?php endif; ?>

<?php 	for( $j = 0; $j < $flowCount; $j++ ) : ?>
	<td class="ntsFormValue"><?php if( strlen($NTS_VIEW['flowFlow'][$j][1][$i]) ){echo $NTS_VIEW['flowFlow'][$j][1][$i];} else {echo '&nbsp;';} ?></td>
<?php 	endfor; ?>

<?php if( $appsCount > 1 ) : ?>
	<td class="ntsFormControl">
<?php 	if( ! in_array(($i+1), $selectedNotAvailable) ) : ?>
	[<a href="<?php echo ntsLink::makeLink('-current-/../control/delete', '', array('what' => ($i + 1) )); ?>" class="alert"><?php echo M('Delete'); ?></a>]
<?php 	else : ?>
	&nbsp;
<?php 	endif; ?>
	</td>
<?php endif; ?>
	</tr>
<?php endfor; ?>

	</table>
</div>
<?php endif; ?>

<?php endif; ?>