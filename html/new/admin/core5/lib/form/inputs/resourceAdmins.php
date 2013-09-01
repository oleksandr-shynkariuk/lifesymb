<?php
$fields = array('appointments_view', 'appointments_edit', 'appointments_notified', 'schedules_view', 'schedules_edit');

$uif =& ntsUserIntegratorFactory::getInstance();
$integrator =& $uif->getIntegrator();
$admins = $integrator->getUsers( array('_role' => array('=', 'admin')) );

switch( $inputAction ){
	case 'display':
		if( ! $conf['value'] )
			$conf['value'] = array();
		$input .= '<table>';
		$input .= '<tr><th></th><th colspan="3">' . M('Appointments') . '</th><th colspan="2">' . M('Schedules') . '</th></tr>';
		$input .= '<tr><td></td><td>' . M('View') . '</td><td>' . M('Edit') . '</td><td>' . M('Get Notified') . '</td><td>' . M('View') . '</td><td>' . M('Edit') . '</td></tr>';

		$count = 0;
		reset( $admins );
		foreach( $admins as $i ){
			$e = new ntsUser;
			$e->setId( $i['id'] );

			$adminFullName = trim( ntsView::objectTitle($e) );
			$adminTitle = NTS_EMAIL_AS_USERNAME ? $e->getProp('email') : $e->getProp('username');
			$adminTitle = '<b>' . $adminTitle . '</b>';

			if( $adminFullName ){
				$adminTitle .= '&nbsp;(' . $adminFullName . ')';
				}

			$trClass = (($count++) % 2) ? 'even' : 'odd';
			$input .= '<tr class="' . $trClass . '">';
			$input .= '<td>' . $adminTitle . '</td>';

			reset( $fields );
			foreach( $fields as $f ){
				$input .= '<td>';
				$input .= $this->makeInput (
				/* type */
					'checkbox',
				/* attributes */
					array(
						'id'		=> $conf['id'] . '_' . $f . '_' . $i['id'],
						'default'	=> isset($conf['value'][$i['id']][$f]) ? $conf['value'][$i['id']][$f] : 0,
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
		reset( $admins );
		foreach( $admins as $i ){
			reset( $fields );
			foreach( $fields as $f ){
				$checkHandle = $handle . '_' . $f . '_' . $i['id'];
				$input[ $i['id'] ][ $f ] = ( $_NTS['REQ']->getParam($checkHandle) ) ? 1 : 0;
				}
			}
		break;

	case 'check_submit':
		$input = true;
		break;
	}
?>