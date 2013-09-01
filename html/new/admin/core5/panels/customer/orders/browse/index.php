<?php
$entries = ntsLib::getVar( 'customer/orders/browse::entries' );
$fields = array(
	'refno'			=> array( 'text', '' ),
	'created_at'	=> array( 'date', M('Created') ),
	'valid_to'		=> array( 'date_never', M('Valid Till') ),
	'is_active'		=> array( 'boolean', M('Is Active') ),
	'value'			=> array( 'text', M('Total Value') ),
	'resource'		=> array( 'text', M('Bookable Resource') ),
	'when'			=> array( 'text', M('When') ),
	'usage'			=> array( 'text', M('Usage') ),
	);
?>
<?php
class listingOrders extends ntsListingTable {
	function displayField( $fn, $e ){
		$return = '';
		switch( $fn ){
			case 'is_active':
				global $NTS_VIEW;
				$t = $NTS_VIEW['t'];
				$t->setNow();
				$today = $t->formatDate_Db();

				$validTo = $e->getProp('valid_to');
				$t->setTimestamp( $validTo );
				$validToDate = $t->formatDate_Db();

				$isActive = $e->getProp( 'is_active' );
				$return = $isActive ? M('Yes') : M('No');
				$expired = (($validTo > 0) && ($today > $validToDate)) ? 1 : 0;
				if( $expired ){
					$return = M('Expired');
					}
				break;

			case 'refno':
				$return = ntsView::objectTitle($e);
				break;

			case 'value':
				$return = $e->getFullTitle();
				break;

			case 'usage':
				$return = $e->getUsageText();
				if( $e->isAvailable() )
				{
					$scheduleLink = ntsLink::makeLink('customer', '', array('order' => $e->getId()));
					$return .= ' '. '<a href="' . $scheduleLink . '">' . M('Schedule Now') . '</a>';
				}
				break;

			case 'resource':
				$resourceId = $e->getProp('resource_id');
				if( $resourceId ){
					$resource = ntsObjectFactory::get('resource');
					$resource->setId( $resourceId );
					$return = ntsView::objectTitle($resource);
					}
				else {
					$return = ' - ' . M('Any') . ' - ';
					}
				break;

			case 'when':
				$thisView = array();
				$rule = $e->getRuleView();
				foreach( $rule as $r ){
					if( is_array($r) )
						$thisView[] = join( ': ', array( '<strong>' . $r[0] . '</strong>', join(', ', $r[1])) );
					else
						$thisView[] = '<strong>' . $r . '</strong>';
					}
				$return = join( '<br>', $thisView );
				break;

			default:
				return parent::displayField( $fn, $e );
				break;
			}
		return $return;
		}
	}
?>
<h2><?php echo M('My Packages'); ?></h2>
<?php
$listing = new listingOrders( $fields, $entries );
echo $listing->display();
?>