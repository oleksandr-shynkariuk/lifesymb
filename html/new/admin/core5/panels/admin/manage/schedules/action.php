<?php
$tm2 = ntsLib::getVar('admin::tm2');
$cal = ntsLib::getVar( 'admin/manage/schedules:cal' );

$ff =& ntsFormFactory::getInstance();
$formFile = dirname( __FILE__ ) . '/form';
$fParams = array();
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $fParams );

switch( $action ){
	case 'duplicate':
		if( $NTS_VIEW['form']->validate() ){
			$formValues = $NTS_VIEW['form']->getValues();

			$schView = ntsLib::getVar( 'admin/manage:schView' );
			$schEdit = ntsLib::getVar( 'admin/manage:schEdit' );

			if( ! in_array($formValues['from-resource'],$schView) ){
				$msg = M('Schedules') . ': ' . M('View') . ': ' . M('Permission Denied');
				ntsView::addAnnounce( $msg, 'error' );

				/* continue */
				$forwardTo = ntsLink::makeLink( '-current-' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			if( ! in_array($formValues['to-resource'],$schEdit) ){
				$msg = M('Schedules') . ': ' . M('Edit') . ': ' . M('Permission Denied');
				ntsView::addAnnounce( $msg, 'error' );

				/* continue */
				$forwardTo = ntsLink::makeLink( '-current-' );
				ntsView::redirect( $forwardTo );
				exit;
				}
			
			/* ok duplicate schedules */
			// first delete everything of to resource
			$whereTo = array(
				'resource_id'	=> array('=', $formValues['to-resource'] ),
				);
			$tm2->deleteBlocksByWhere( $whereTo );

			// now get ones of from resource
			$whereFrom = array(
				'resource_id'	=> array('=', $formValues['from-resource'] ),
				);
			$blocks = $tm2->getBlocksByWhere( $whereFrom );
			reset( $blocks );
			$newBlocks = array();
			foreach( $blocks as $b ){
				unset($b['group_id']);
				$b['resource_id'] = array($formValues['to-resource']);
				$newBlocks[] = $b;
				}
			reset( $newBlocks );
			foreach( $newBlocks as $b ){
				$tm2->addBlock( $b );
				}

			ntsView::addAnnounce( M('Schedules') . ': ' . M('Duplicate') . ': ' . M('OK'), 'ok' );
			$forwardTo = ntsLink::makeLink('-current-');
			ntsView::redirect( $forwardTo );
			exit;
			}
		else {
		/* form not valid, continue */
			}
		break;

	default:
		break;
	}

$tmBlocks = $tm2->getBlocks( $cal, true );
$blocks = array();
if( ! $cal ){
	for( $di = 0; $di <= 6; $di++ ){
		$blocks[ $di ] = array();
		}
	}
else {
	$t = new ntsTime();
	$t->setDateDb( $cal );
	$di = $t->getWeekday();
	$blocks[ $di ] = array();
	}

reset( $tmBlocks );
foreach( $tmBlocks as $b ){
	if( ! isset($blocks[$b['applied_on']]) )
		$blocks[$b['applied_on']] = array();

	$gid = $b['group_id'];
	if( ! isset($blocks[$b['applied_on']][$gid]) )
		$blocks[$b['applied_on']][$gid] = array();

	$blocks[$b['applied_on']][$gid][] = $b;
	}
reset( $blocks );

foreach( array_keys($blocks) as $di ){
	uasort( $blocks[$di], create_function(
		'$a, $b', 
		'
		if( $a[0]["starts_at"] != $b[0]["starts_at"] ){
			$return = ($a[0]["starts_at"] - $b[0]["starts_at"]);
			}
		else {
			$return = ($a[0]["valid_from"] - $b[0]["valid_from"]);
			}
		return $return;
		'
		) 
	);
	}

ntsLib::setVar( 'admin/manage/schedules:blocks', $blocks );
?>