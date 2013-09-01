<?php
$ntsdb =& dbWrapper::getInstance();

$id = $_NTS['REQ']->getParam( 'order_id' );
$object = ntsObjectFactory::get( 'order' );
$object->setId( $id );

$formParams = $object->getByArray();

$rule = $object->getRule();
$formParams = array_merge($formParams, $rule);
if( isset($rule['time']) )
{
	$formParams['time_all'] = 0;
	$formParams['from_time'] = $rule['time'][0];
	$formParams['to_time'] = $rule['time'][1];
}

if( isset($rule['date']) )
{
	$formParams['date_all'] = 0;
	if( isset($rule['date']['from']) )
	{
		$formParams['date_type'] = 'range';
		$formParams['from_date'] = $rule['date']['from'];
		$formParams['to_date'] = $rule['date']['to'];
	}
	else
	{
		$formParams['date_type'] = 'fixed';
		$formParams['fixed_date'] = $rule['date'];
	}
}

$formParams['service_id'] = $object->getServiceId();
$formParams['order_id'] = $id;
$formParams['pack_type'] = $object->getType();
$formParams['service_type'] = $object->getServiceType();
if( $formParams['service_type'] == 'fixed' )
	$formParams['fixed_service_id'] = $formParams['service_id'];
else
{
	if( in_array(0, $formParams['service_id']) )
		$formParams['service_id_all'] = 1;
}

$formFile = NTS_APP_DIR . '/app/forms/pack';

$ff =& ntsFormFactory::getInstance();
$NTS_VIEW['form'] =& $ff->makeForm( $formFile, $formParams );

switch( $action ){
	case 'save':
		$removeValidation = array();
		$removeValidation[] = 'title';

		if( $_NTS['REQ']->getParam( 'time_all' ) )
		{
			$removeValidation[] = 'from_time';
			$removeValidation[] = 'to_time';
		}

		if( $_NTS['REQ']->getParam( 'date_all' ) )
		{
			$removeValidation[] = 'from_date';
			$removeValidation[] = 'to_date';
			$removeValidation[] = 'fixed_date';
		}
		else
		{
			$dateType = $_NTS['REQ']->getParam( 'date_type' );
			switch( $dateType )
			{
				case 'fixed':
					$removeValidation[] = 'from_date';
					$removeValidation[] = 'to_date';
					break;
				case 'range':
					$removeValidation[] = 'fixed_date';
					break;
			}
		}

		$serviceType = $_NTS['REQ']->getParam( 'service_type' );
		$packType = $_NTS['REQ']->getParam( 'pack_type' );
		switch( $serviceType ){
			case 'fixed':
				$removeValidation[] = 'qty';
				$removeValidation[] = 'duration';
				$removeValidation[] = 'amount';
				$removeValidation[] = 'service_id';
				break;
			case 'one':
				$removeValidation[] = 'fixed_service_id';
				switch( $packType ){
					case 'unlimited':
						$removeValidation[] = 'qty';
						$removeValidation[] = 'duration';
						$removeValidation[] = 'amount';
						break;
					case 'qty':
						$removeValidation[] = 'duration';
						$removeValidation[] = 'amount';
						break;
					case 'duration':
						$removeValidation[] = 'qty';
						$removeValidation[] = 'amount';
						break;
					case 'amount':
						$removeValidation[] = 'qty';
						$removeValidation[] = 'duration';
						break;
					}
				$serviceAll = $_NTS['REQ']->getParam( 'service_id_all' );
				if( $serviceAll ){
					$removeValidation[] = 'service_id';
					}
				break;
			}

		$removeValidation[] = 'price';

		if( $NTS_VIEW['form']->validate($removeValidation) ){
			$formValues = $NTS_VIEW['form']->getValues();

		/* build rules */
			$rule = array();
			$grab = array('weekday');
			foreach( $grab as $gr )
			{
				if( isset($formValues[$gr]) && (! in_array(-1, $formValues[$gr])) )
				{
					$rule[$gr] = $formValues[$gr];
				}
			}
			if( (! isset($formValues['time_all'])) OR (! $formValues['time_all']) )
			{
				$rule['time'] = array($formValues['from_time'], $formValues['to_time']);
			}

			if( (! isset($formValues['date_all'])) OR (! $formValues['date_all']) )
			{
				$dateType = $formValues['date_type'];
				switch( $dateType )
				{
					case 'fixed':
						$rule['date'] = $formValues['fixed_date'];
						break;
					case 'range':
						$rule['date'] = array(
							'from'	=> $formValues['from_date'],
							'to'	=> $formValues['to_date'],
							);
						break;
				}
			}

			switch( $serviceType ){
				case 'fixed':
					$formValues['qty'] = 0;
					$formValues['amount'] = 0;
					$formValues['duration'] = 0;
					$formValues['service_id'] = $formValues['fixed_service_id'];
					if( strpos($formValues['service_id'], '-') === FALSE ){
						$formValues['qty'] = 1;
						}
					break;

				case 'one':
					if( ! is_array($formValues['service_id']) )
						$formValues['service_id'] = array($formValues['service_id']);
					if( $serviceAll )
						$formValues['service_id'] = array(0);
					$formValues['service_id'] = join(',', $formValues['service_id'] );

					switch( $packType ){
						case 'unlimited':
							$formValues['qty'] = 0;
							$formValues['amount'] = 0;
							$formValues['duration'] = 0;
							break;
						case 'qty':
							$formValues['amount'] = 0;
							$formValues['duration'] = 0;
							break;
						case 'duration':
							$formValues['qty'] = 0;
							$formValues['amount'] = 0;
							break;
						case 'amount':
							$formValues['qty'] = 0;
							$formValues['duration'] = 0;
							break;
						}
					break;
				}

			if( $formValues['neverExpires'] ){
				$formValues['expires_in'] = 0;
				}
			if( isset($formValues['service_type']) )
				unset($formValues['service_type']);
			if( isset($formValues['fixed_service_id']) )
				unset($formValues['fixed_service_id']);

			//expires_in
			if( $formValues['expires_in'] ){
				$now = time();
				$validFrom = $object->getProp('valid_from');
				if( ! $validFrom ){
					$validFrom = $now;
					$formValues['valid_from'] = $validFrom;
					}

				$t = new ntsTime;
				$t->setTimestamp( $validFrom );
				$t->modify( '+' . $formValues['expires_in'] );
				$validTo = $t->getTimestamp();
				$formValues['valid_to'] = $validTo;
				}
			else {
				$formValues['valid_to'] = 0;
				}
			$object->setByArray( $formValues );
			$object->setRule( $rule );

			$cm =& ntsCommandManager::getInstance();
			$cm->runCommand( $object, 'update' );

			$msg = array( M('Package Order'), M('Update'), M('OK') );
			$msg = join( ': ', $msg );
			ntsView::addAnnounce( $msg, 'ok' );

			/* continue to list */
			$forwardTo = ntsLink::makeLink( '-current-/../..' );
			ntsView::redirect( $forwardTo, true );
			exit;
			break;
			}
		else {
			/* form not valid, get back */
			}
	}
?>