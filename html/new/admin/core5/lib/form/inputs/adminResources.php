<?php
$fields = array('appointments_view', 'appointments_edit', 'appointments_notified', 'schedules_view', 'schedules_edit');

$allResources = ntsObjectFactory::getAll( 'resource', 'ORDER BY title ASC' );

switch( $inputAction ){
	case 'display':
		if( ! $conf['value'] )
			$conf['value'] = array();
		$input .= '<table>';
		$input .= '<tr><th></th><th colspan="3">' . M('Appointments') . '</th><th colspan="2">' . M('Schedules') . '</th></tr>';
		$input .= '<tr><td></td><td>' . M('View') . '</td><td>' . M('Edit') . '</td><td>' . M('Get Notified') . '</td><td>' . M('View') . '</td><td>' . M('Edit') . '</td></tr>';

		$count = 0;
		reset( $allResources );
		foreach( $allResources as $e ){
			$id = $e->getId();
			$resTitle = trim( ntsView::objectTitle($e) );

			$trClass = (($count++) % 2) ? 'even' : 'odd';
			$input .= '<tr class="' . $trClass . '">';
			$input .= '<td>' . '<b>' . $resTitle . '</b>' . '</td>';

			reset( $fields );
			foreach( $fields as $f ){
				$input .= '<td>';
				$input .= $this->makeInput (
				/* type */
					'checkbox',
				/* attributes */
					array(
						'id'		=> $conf['id'] . '_' . $f . '_' . $id,
						'default'	=> isset($conf['value'][$id][$f]) ? $conf['value'][$id][$f] : 0,
						)
					);
				$input .= '</td>';
				}
			$input .= '</tr>';
			}
		$input .= '</table>';
		break;

	case 'submit':
		$input = array();
		reset( $allResources );
		foreach( $allResources as $e ){
			$id = $e->getId();
			reset( $fields );
			foreach( $fields as $f ){
				$checkHandle = $handle . '_' . $f . '_' . $id;
				$input[ $id ][ $f ] = ( $_NTS['REQ']->getParam($checkHandle) ) ? 1 : 0;
				}
			}
		break;

	case 'check_submit':
		$input = true;
		break;
	}
?>