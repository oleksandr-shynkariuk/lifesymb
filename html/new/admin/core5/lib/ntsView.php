<?php
class ntsHtmlTable
{
	var $header;
	var $rows;
	var $config;

	function __construct()
	{
		$this->header = array();
		$this->rows = array();
	}

	function configView( $config )
	{
		$this->config = $config;
	}

	function prepareView( $e )
	{
		$return = array();

		global $NTS_VIEW;
		$t = $NTS_VIEW['t'];

		foreach( $e as $k => $fieldValue )
		{
			if( ! isset($this->config[$k]) )
				continue;
			$fieldType = $this->config[$k];
			switch( $fieldType ){
				case 'date':
					$t->setTimestamp( $fieldValue );
					$returnView = $t->formatDate();
					break;
				case 'date_never':
					if( $fieldValue ){
						$t->setTimestamp( $fieldValue );
						$returnView = $t->formatDate();
						}
					else {
						$returnView = M('Never Expires');
						}
					break;
				case 'price':
					$returnView = ntsCurrency::formatPrice( $fieldValue );
					break;
				default:
					$returnView = $fieldValue;
					break;
				}
			$return[ $k ] = $returnView;
		}
		return $return;
	}

	function setHeader( $header )
	{
		$this->header = $header;
	}

	function addRow( $row )
	{
		$this->rows[] = $row;
	}
	function display(){
?>

<table>
<?php if( $this->header ) : ?>
	<?php reset( $this->header ); ?>
	<tr>
	<?php foreach( $this->header as $h ) : ?>
		<th><?php echo $h; ?></th>
	<?php endforeach; ?>
	</tr>
<?php endif; ?>

<?php reset( $this->rows ); ?>
<?php foreach( $this->rows as $row ) : ?>
<tr>
<?php reset( $row ); ?>
<?php 	foreach( $row as $r ) : ?>
<?php		if( is_array($r) ) : ?>
<?php
				if( isset($r['value']) ){
					$value = $r['value'];
					unset( $r['value'] );
					reset( $r );
					$props = array();
					foreach( $r as $k => $v ){
						$props[] = $k . '="' . $v . '"';
						}
					$props = join( ' ', $props );
					}
				else {
					$value = '';
					}
?>
				<td <?php echo $props; ?>><?php echo $value; ?></td>
<?php		else : ?>
				<td><?php echo $r; ?></td>
<?php		endif; ?>
<?php 	endforeach; ?>
</tr>
<?php endforeach; ?>

</table>

<?php
		}
	}

class ntsListingTable {
	var $entries;
	var $fields;

	function ntsListingTable( $fields, $entries ){
		$this->fields = $fields;
		$this->entries = $entries;
		}

	function displayField( $fn, $e ){
		global $NTS_VIEW;
		$t = $NTS_VIEW['t'];

		$return = '';
		$fieldType = $this->fields[$fn][0];
		$fieldValue = is_object($e) ? $e->getProp($fn) : $e[$fn];
		switch( $fieldType ){
			case 'date':
				$t->setTimestamp( $fieldValue );
				$return = $t->formatDate();
				break;
			case 'date_never':
				if( $fieldValue ){
					$t->setTimestamp( $fieldValue );
					$return = $t->formatDate();
					}
				else {
					$return = M('Never Expires');
					}
				break;
			case 'price':
				$return = ntsCurrency::formatPrice( $fieldValue );
				break;
			default:
				$return = $fieldValue;
				break;
			}
		return $return;
		}
	function display(){
?>

<table>
<?php reset( $this->fields ); ?>
<tr>
<?php foreach( $this->fields as $fn => $fa ) : ?>
	<th><?php echo $fa[1]; ?></th>
<?php endforeach; ?>
</tr>

<?php reset( $this->entries ); ?>
<?php foreach( $this->entries as $e ) : ?>
<tr>
<?php reset( $this->fields ); ?>
<?php 	foreach( $this->fields as $fn => $fa ) : ?>
	<td><?php echo $this->displayField($fn, $e); ?></td>
<?php 	endforeach; ?>
</tr>
<?php endforeach; ?>

</table>

<?php
		}
	}
?>
<?php
class ntsLink {
	var $target = NTS_ROOT_WEBPAGE;
	var $prefix = '';

	function ntsLink(){
		$this->setTarget( NTS_ROOT_WEBPAGE );
		$this->prefix = '';
		}

	function setTarget( $trg ){
		$this->target = $trg;
		}

	function prepare( $panel = '', $action = '', $params = array() ){
		$this->prefix = ntsView::makeGetParams( $panel, $action, $params );
		}

	function make( $panel = '', $action = '', $params = array()  ){
		$joiner = ( strpos($this->target, '?' ) === false ) ? '?' : '&'; 
		$link = $this->target . $joiner . ntsView::makeGetParams( $panel, $action, $params );
		return $link;
		}
	function append( $p, $v ){
		$joiner = ( strpos($this->target, '?' ) === false ) ? '?' : '&'; 
		$link = $this->target . $joiner . $this->prefix . '&' . $p . '=' . urlencode($v);
		return $link;
		}
	static function makeLink( $panel = '', $action = '', $params = array(), $return = false, $skipSaveOn = false ){
		return ntsLink::makeLinkFull( NTS_ROOT_WEBPAGE, $panel, $action, $params, $skipSaveOn );
		}
	static function makeLinkFull( $target, $panel = '', $action = '', $params = array(), $skipSaveOn = false ){
		$addOn = '';
		if( isset($params['#']) ){
			$addOn = '#' . $params['#'];
			unset( $params['#'] );
			}
		$rootWebPage = $target;
		$joiner = ( strpos($rootWebPage, '?') === false ) ? '?' : '&';
		$getParams = ntsView::makeGetParams( $panel, $action, $params, $skipSaveOn );
		if( $getParams )
			$link =  $rootWebPage . $joiner . $getParams;
		else
			$link =  $rootWebPage;
		if( $addOn )
			$link .= $addOn;
		return $link;
		}

	static function printLink( $p = array(), $showIfDisabled = false ){
		global $NTS_CURRENT_USER;
		$return = '';
		if( ! isset($p['action']) )
			$p['action'] = '';
		if( ! isset($p['params']) )
			$p['params'] = array();
		if( ! isset($p['return']) )
			$p['return'] = false;
		if( ! isset($p['skipSaveOn']) )
			$p['skipSaveOn'] = false;

		$panel = ntsView::parsePanel( $p['panel'] );
		$attrLine = ' ';
		if( isset($p['attr']) ){
			$attrs = array();
			foreach( $p['attr'] as $pk => $pv ){
				$attrs[] = $pk . '="' . $pv . '"';
				}
			$attrLine = ' ' . join( ' ', $attrs );
			}
		
		if( ! $NTS_CURRENT_USER->isPanelDisabled($panel) ){
			$link = ntsLink::makeLink( $panel, $p['action'], $p['params'], $p['return'], $p['skipSaveOn'] );
			$return = '<a' . $attrLine . ' href="' . $link . '">' . $p['title'] . '</a>';
			}
		elseif( $showIfDisabled ){
			$return = '<span' . $attrLine . '>' . $p['title'] . '</span>';
			}
		return $return;
		}
	}

class ntsView {
	static function printStatus( $object, $msg = true ){
		$out = '';
		$message = '&nbsp;';
		$className = $object->getClassName();
		switch( $className ){
			case 'order':
				$cssClass = '';
				if( $isActive = $object->getProp('is_active') ){
					$cssClass = 'ntsApproved';
					}
				else {
					$cssClass = 'ntsPending';
					}
				break;
			case 'appointment':
				$ntsConf =& ntsConf::getInstance();
				$customerAcknowledge = $ntsConf->get( 'customerAcknowledge' );

				$cssClass = '';
				$startsAt = $object->getProp('starts_at');
				if( $startsAt > 0 ){
					if( $completed = $object->getProp('completed') ){
						switch( $completed ){
							case HA_STATUS_COMPLETED:
								if( $customerAcknowledge && (! $object->getProp('_ack')) )
								{
									$cssClass = 'ntsCompleted2_NotAck';
									$message = M('Completed') . ', ' . M('Not Acknowledged By Customer');
								}
								else
								{
									$cssClass = 'ntsCompleted2';
									$message = M('Completed');
								}
								break;
							case HA_STATUS_CANCELLED:
								$cssClass = 'ntsCancelled2'; 
								$message = M('Cancelled');
								break;
							case HA_STATUS_NOSHOW:
								$cssClass = 'ntsNoShow2'; 
								$message = M('No Show');
								break;
							}
						}
					else {
						if( $object->getProp('approved') ){
							$cssClass = 'ntsApproved2'; 
							$message = M('Approved');
							}
						else {
							$cssClass = 'ntsPending2';
							$message = M('Pending');
							}
						}
					}
				else {
					$message = M('Not Scheduled');
					}
				break;
			}

		if( $cssClass ){
			if( ! $msg ){
				$message = '&nbsp;';
				}
			$out = '<span class="' . $cssClass . '">' . $message . '</span>';
			}
		return $out;
		}

	static function setTitle( $title ){
		global $NTS_PAGE_TITLE_ARRAY, $NTS_PAGE_TITLE;
		if( ! $NTS_PAGE_TITLE_ARRAY )
			$NTS_PAGE_TITLE_ARRAY = array();
		$NTS_PAGE_TITLE_ARRAY[] = $title;
		$NTS_PAGE_TITLE = join( ' - ', $NTS_PAGE_TITLE_ARRAY ); // for backward compatibility
		}

	static function getTitle(){
		global $NTS_PAGE_TITLE_ARRAY;
		if( ! $NTS_PAGE_TITLE_ARRAY )
			$NTS_PAGE_TITLE_ARRAY = array();
		$return = join( ' - ', $NTS_PAGE_TITLE_ARRAY );
		return $return;
		}

	static function setNextAction( $panel, $action = '' ){
		global $_NTS;
		$panel = ntsView::parsePanel( $panel );
		$_NTS['REQUESTED_PANEL'] = $panel;
		$_NTS['REQUESTED_ACTION'] = $action;
		}

	static function objectTitle( $object ){
		global $NTS_VIEW;
		if( ! $object )
			return;
		$className = $object->getClassName();
		switch( $className ){
			case 'invoice':
				$return = M('Invoice') . ' ' . $object->getProp('refno');
				break;

			case 'order':
				$packId = $object->getProp( 'pack_id' );
				$pack = ntsObjectFactory::get( 'pack' );
				$pack->setId( $packId );
				$return = ntsView::objectTitle($pack);
				break;

			case 'pack':
				$return = $object->getProp('title');
				if( ! $return )
				{
					$return = M('Package') . ' #' . $object->getId();
				}
				break;

			case 'service_cat':
				if( $object->getId() )
					$return = $object->getProp( 'title' );
				else
					$return = M('Uncategorized');
				break;

			case 'service':
				$conf =& ntsConf::getInstance();
				$showSessionDuration = $conf->get('showSessionDuration');

				$return = $object->getProp( 'title' );
				$durationView = '';
//				if( $showSessionDuration ){
//					$durationView .= ntsTime::formatPeriod($object->getProp('duration'));
//					}
				if( $durationView ){
					$return .= ' [' . $durationView . ']';
					}
				break;

			case 'appointment':
				$conf =& ntsConf::getInstance();
				$showEndTime = $conf->get('showEndTime');

				$service = ntsObjectFactory::get( 'service' );
				$service->setId( $object->getProp('service_id') );
				$serviceView = ntsView::objectTitle( $service );

				$startsAt = $object->getProp('starts_at');
				if( $startsAt ){
					$NTS_VIEW['t']->setTimestamp( $startsAt ); 
					$dateView = $NTS_VIEW['t']->formatWeekdayShort() . ', ' . $NTS_VIEW['t']->formatDate();
					$timeView = $showEndTime ? $NTS_VIEW['t']->formatTime( $object->getProp('duration') ) : $NTS_VIEW['t']->formatTime();
					$return = $dateView . ' ' . $timeView . ' ' . $serviceView;
					}
				else {
					$timeView = M('Not Scheduled');
					$return = $serviceView . ' [' . $timeView . ']';
					}
				break;

			case 'user':
				$return = array();
				if( $object->getProp( 'first_name' ) )
					$return[] = $object->getProp( 'first_name' );
				if( $object->getProp( 'last_name' ) )
					$return[] = $object->getProp( 'last_name' );
			
				if( $return )
					$return = join( ' ', $return );
				else
					$return = M('Customer') . ' #' . $object->getId();
				break;

			default:
				$return = $object->getProp( 'title' );
				break;
			}

		$className = $object->getClassName();
	/* plugin files */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		reset( $activePlugins );
		$viewFiles = array();
		foreach( $activePlugins as $plg ){
			$viewFiles[] = $plm->getPluginFolder( $plg ) . '/views/' . $className . '/title.php';
			}
		reset( $viewFiles );
		foreach( $viewFiles as $vf ){
			if( file_exists($vf) ){
				require( $vf );
				break;
				}
			}
		return $return;
		}

	static function appServiceView( $a, $noPrice = FALSE ){
		$return = '';

		$service = ntsObjectFactory::get( 'service' );
		$service->setId( $a->getProp('service_id') );

		$seats = $a->getProp('seats');
		$duration = $a->getProp('duration');

		$return .= ntsView::serviceView( $service, $seats, $duration );

		if( ! $noPrice )
		{
			$thisPrice = $a->getProp('price');
			$priceView = ntsCurrency::formatServicePrice($thisPrice);
			if( strlen($priceView) ){
				$return .= "\n" . M('Price') . ': ' . $priceView;
				}
		}
		return $return;
		}

	static function serviceView( $service, $seats, $duration ){
		$return = '';

		$conf =& ntsConf::getInstance();
		$showSessionDuration = $conf->get('showSessionDuration');

		$return .= $service->getProp('title');
		if( $showSessionDuration ){
			$return .= "\n [" . ntsTime::formatPeriod($duration) . ']';
			}

		return $return;
		}

	static function setBack( $to, $alsoAjax = false ){
		global $NTS_VIEW;
		if( isset($NTS_VIEW[NTS_PARAM_VIEW_MODE]) && ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax') ){
			$_SESSION['nts-get-back-ajax'] = $to;
			}
		else {
			$_SESSION['nts-get-back'] = $to;
			if( $alsoAjax ){
				$_SESSION['nts-get-back-ajax'] = $to;
				}
			}
		}

	static function redirect( $to, $force = false, $parent = false ){
		global $NTS_VIEW;
		$html = '';
		if( isset($NTS_VIEW[NTS_PARAM_VIEW_MODE]) && ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax') ){
			if( $force ){
			$html =<<<EOT

<script language="JavaScript">
document.location.href="$to";
</script>

EOT;
				}
			else {
/* check if we have another parent nts-ajax-container */
			$randomId = ntsLib::generateRand(6, array('caps' => false));

			$html =<<<EOT
<span id="nts-$randomId"></span>

EOT;

			if( $parent ){
				$html .=<<<EOT

<script language="JavaScript">
var myGrandparent = jQuery('#nts-$randomId').closest( '.nts-ajax-return' ).parents( '.nts-ajax-return' );

EOT;
				}
			else {
				$html .=<<<EOT

<script language="JavaScript">
var myGrandparent = jQuery('#nts-$randomId').closest( '.nts-ajax-return' );

EOT;
				}

$viewModeParam = NTS_PARAM_VIEW_MODE;
			$html .=<<<EOT
if( myGrandparent.length > 0 ){
	myGrandparent = myGrandparent.first();
	var thisFormData = '$viewModeParam=ajax';
	jQuery.ajax({
		type: "GET",
		url: "$to",
		data: thisFormData
		})
		.done( function(msg){
			myGrandparent.html( msg );
			});
	}
else{
	document.location.href="$to";
	}
</script>

EOT;
				}
			echo $html;
			exit;
			}
		else {
			if( ! headers_sent() ){
				header( 'Location: ' . $to );
				exit;
				}
			else {
//			$html = "<META http-equiv=\"refresh\" content=\"0;URL=$to\">";
				$html = "<a href=\"$to\">$to</a>";
				echo $html;
				}
			}
		}

	static function getBackLink( $force = false, $parent = false ){
		global $NTS_VIEW;

		$to = '';
		if( $force ){
			if( isset($_SESSION['nts-get-back']) && $_SESSION['nts-get-back'] ){
				$to = $_SESSION['nts-get-back'];
				unset( $_SESSION['nts-get-back'] );
				}
			}
		else {
			if( isset($NTS_VIEW[NTS_PARAM_VIEW_MODE]) && ($NTS_VIEW[NTS_PARAM_VIEW_MODE] == 'ajax') ){
				if( isset($_SESSION['nts-get-back-ajax']) && $_SESSION['nts-get-back-ajax'] ){
					$to = $_SESSION['nts-get-back-ajax'];
					unset( $_SESSION['nts-get-back-ajax'] );
					}
				}
			else {
				if( isset($_SESSION['nts-get-back']) && $_SESSION['nts-get-back'] ){
					$to = $_SESSION['nts-get-back'];
					unset( $_SESSION['nts-get-back'] );
					}
				}
			}

		if( ! $to ){
			$to = ntsLink::makeLink('-current-');
			}
		return $to;
		}

	static function getBack( $force = false, $parent = false ){
		$to = ntsView::getBackLink( $force, $parent );
		ntsView::redirect( $to, $force, $parent );
		}

	static function resetPersistentParams( $rootPanel = '' ){
		global $NTS_PERSISTENT_PARAMS;
		if( ! $rootPanel )
			$rootPanel = '/';
		if( ! $NTS_PERSISTENT_PARAMS )
			$NTS_PERSISTENT_PARAMS = array();
		$NTS_PERSISTENT_PARAMS[ $rootPanel ] = array();
		}

	static function setPersistentParams( $pNames, $rootPanel = '' ){
		global $NTS_PERSISTENT_PARAMS;

		if( ! $rootPanel )
			$rootPanel = '/';

		if( ! $NTS_PERSISTENT_PARAMS )
			$NTS_PERSISTENT_PARAMS = array();
		if( ! isset($NTS_PERSISTENT_PARAMS[ $rootPanel ]) )
			$NTS_PERSISTENT_PARAMS[ $rootPanel ] = array();

		foreach( $pNames as $pName => $pValue ){
			if( is_array($pValue) || strlen($pValue) )
				$NTS_PERSISTENT_PARAMS[ $rootPanel ][ $pName ] = $pValue;
			}
		}

	static function parsePanel( $panel ){
		global $_NTS;
		$currentTag = '-current-';
		if( substr($panel, 0, strlen($currentTag)) == $currentTag ){
			$replaceFrom = '-current-';
			$replaceTo = $_NTS['CURRENT_PANEL'];

			if( strpos($panel, '..') !== false ){
				if( strlen($replaceTo) ){
					$downCount = substr_count( $panel, '/..' );
					$re = "/^(.+)(\/[^\/]+){" . $downCount. "}$/U";
					preg_match($re, $replaceTo, $ma);

					$replaceFrom = '-current-' . str_repeat('/..', $downCount);
					$replaceTo = $ma[1];
					$panel = str_replace( $replaceFrom, $replaceTo, $panel );
					}
				else {
					$panel = '';
					}
				}
			else {
				$panel = str_replace( $replaceFrom, $replaceTo, $panel );
				}
			}
		return $panel;
		}

	static function makeGetParams( $panel = '', $action = '', $params = array(), $skipSaveOn = false ){
		global $NTS_PERSISTENT_PARAMS, $_NTS;
		$panel = ntsView::parsePanel( $panel );

		if( isset($params['-skipSaveOn-']) && $params['-skipSaveOn-'] ){
			unset( $params['-skipSaveOn-'] );
			$skipSaveOn = true;
			}

		if( $panel )
			$params[ NTS_PARAM_PANEL ] = $panel;
		if( $action )
			$params[ NTS_PARAM_ACTION ] = $action;

		if( $NTS_PERSISTENT_PARAMS && (! $skipSaveOn) ){
			reset( $NTS_PERSISTENT_PARAMS );
		/* global */
			if( isset($NTS_PERSISTENT_PARAMS['/']) ){
				reset( $NTS_PERSISTENT_PARAMS['/'] );
				foreach( $NTS_PERSISTENT_PARAMS['/'] as $p => $v ){
					if( ! isset($params[$p]) )
						$params[ $p ] = $v;
					}
				}
		/* above panel */
			$setIn = array();
			reset( $NTS_PERSISTENT_PARAMS );
			foreach( $NTS_PERSISTENT_PARAMS as $pan => $pampam ){
				if( substr($panel, 0, strlen($pan) ) != $pan )
					continue;
				reset( $pampam );
				foreach( $pampam as $p => $v ){
					if( 
//						( isset($setIn[$p]) && (strlen($pan) > strlen($setIn[$p])) ) OR
						( ! isset($params[$p]) )
						){
						$params[ $p ] = $v;
						$setIn[ $p ] = $pan;
						}
					}
				}
			}

		reset( $params );
		$linkParts = array();
		foreach( $params as $p => $v ){
			if( $v || ($v === 0) || ($v === '0') ){
				if( is_array($v) )
					$v = join( '-', $v );
				elseif( is_object($v) ){
					$v = $v->getId();
					}
				if( $v == '-reset-' )
					continue;

				$realP = ntsView::setRealName( $p );
				$linkParts[] = $realP . '=' . urlencode($v);
				}
			}

		if( $linkParts )
			$link = join( '&', $linkParts );
		else
			$link = '';
		return $link;
		}

	static function setRealName( $pName ){
		$return = $pName;
		$pref = 'nts-';
		if( substr($pName, 0, strlen($pref)) != $pref ){
			$return = $pref . $return;
			}
		return $return;
		}
	static function getRealName( $pName ){
		$return = $pName;
		$pref = 'nts-';
		if( substr($pName, 0, strlen($pref)) ==  $pref ){
			$return = substr($pName, strlen($pref));
			}
		return $return;
		}

	static function prepareUrlParams( $params = array() ){
		reset( $params );
		$linkParts = array();
		foreach( $params as $p => $v ){
			if( $v )
				$linkParts[] = $p . '=' . urlencode($v);
			}
		$link = join( '&', $linkParts );
		return $link;
		}

	static function addAdminAnnounce( $msg, $type = 'ok' ){
	// type might be 'error' or 'ok'
		if( ! isset($_SESSION['announce_text_admin']) ){
			$_SESSION['announce_text_admin'] = array();
			}
		$_SESSION['announce_text_admin'][] = array( $msg, $type );
		}
	static function setAdminAnnounce( $msg, $type = 'ok' ){
	// type might be 'error' or 'ok'
		$_SESSION['announce_text_admin'] = array( array( $msg, $type ) );
		}
	static function isAdminAnnounce(){
		$return = ( isset($_SESSION['announce_text_admin']) )? true : false;
		return $return;
		}
	static function getAdminAnnounceText(){
		if( isset($_SESSION['announce_text_admin']) ){
			$return = $_SESSION['announce_text_admin'];
			}
		else {
			$return = '';
			}
		return $return;
		}
	static function clearAdminAnnounce(){
		unset( $_SESSION['announce_text_admin'] );
		}

	static function addAnnounce( $msg, $type = 'ok', $order = 50 ){
	// type might be 'error' or 'ok'
		if( ! isset($_SESSION['announce_text']) ){
			$_SESSION['announce_text'] = array();
			}
		$_SESSION['announce_text'][] = array( $msg, $type, $order );
		}

	static function setAnnounce( $msg, $type = 'ok' ){
		ntsView::addAnnounce( $msg, $type );
		}
	static function isAnnounce(){
		$return = ( isset($_SESSION['announce_text']) )? true : false;
		return $return;
		}

	static function getAnnounceText(){
		if( isset($_SESSION['announce_text']) ){
			$return = $_SESSION['announce_text'];

		/* SORT BY ORDER */
			usort( $return, create_function('$a, $b', 'return ntsLib::numberCompare($a[2], $b[2]);' ) );
			}
		else {
			$return = '';
			}
		return $return;
		}

	static function getAnnounceType(){
		$return = ( isset($_SESSION['announce_type']) )? $_SESSION['announce_type'] : '';
		return $return;
		}

	static function clearAnnounce(){
		unset( $_SESSION['announce_text'] );
		unset( $_SESSION['announce_type'] );
		}
	}
?>