<?php
class ntsAdminPermissionsManager {
	var $keys = array();

	function ntsAdminPermissionsManager(){
		$this->keys = array(
			array( 'admin/customers', M('Customers'), 1 ),
				array( 'admin/customers/browse',	M('View') ),
				array( 'admin/customers/edit', 		M('Edit') ),
				array( 'admin/customers/create',	M('Create') ),
				array( 'admin/customers/notified',	M('Get Notified') ),
				//array( 'admin/customers/import',	M('Import') ),

			array( 'admin/company/resources', M('Bookable Resources'), 1 ),
				array( 'admin/company/resources/browse',	M('View') ),
				array( 'admin/company/resources/edit', 		M('Edit') ),
				array( 'admin/company/resources/create',	M('Create') ),

			array( 'admin/company/services', M('Services'), 1 ),
				array( 'admin/company/services/browse',	M('View') ),
				array( 'admin/company/services/edit',	M('Edit') ),
				array( 'admin/company/services/create',	M('Create') ),
				array( 'admin/company/services/cats',	M('Categories') ),
				array( 'admin/company/services/packs',	M('Packages') ),
				array( 'admin/company/services/promotions', M('Promotions and Coupons') ),

			array( 'admin/company/locations', M('Locations'), 1 ),
				array( 'admin/company/locations/browse',	M('View') ),
				array( 'admin/company/locations/edit', 		M('Edit') ),
				array( 'admin/company/locations/create',	M('Create') ),
				array( 'admin/company/locations/travel',	M('Travel Time') ),

			array( 'admin/company/staff', M('Administrative Users'), 1 ),
				array( 'admin/company/staff/browse',	M('View') ),
				array( 'admin/company/staff/edit', 		M('Edit') ),
				array( 'admin/company/staff/create',	M('Create') ),

			array( 'admin/company/payments', M('Payments'), 1 ),
				array( 'admin/company/payments/invoices',		M('Invoices') ),
				array( 'admin/company/payments/transactions', 	M('Transactions') ),
				array( 'admin/company/payments/orders', 		M('Package Orders') ),

			array( 'admin/forms', M('Forms'), 1 ),
				array( 'admin/forms/customers', M('Customers') ),
				array( 'admin/forms/appointments', M('Appointments') ),

			/*array( 'admin', M('Synchronization'), 1 ),
			array( 'admin/sync', M('Synchronization') ),

			array( 'admin/promo', M('Promotion'), 1 ),
				array( 'admin/promo/newsletter', M('Newsletter') ),

			array( 'admin/conf', M('Settings'), 1 ),
				array( 'admin/conf/customers', M('Customers') ),
				array( 'admin/conf/email_settings', M('Email') ),
				array( 'admin/conf/email_templates', M('Notifications') ),
				array( 'admin/conf/reminders', M('Reminders') ),
				array( 'admin/conf/cron', M('Automatic Actions') ),
				array( 'admin/conf/terminology', M('Terminology') ),
				array( 'admin/conf/datetime', M('Date and Time') ),
				array( 'admin/conf/currency', M('Currency') ),
				array( 'admin/conf/payment_gateways', M('Payment Gateways') ),
				array( 'admin/conf/languages', M('Languages') ),
				array( 'admin/conf/flow', M('Appointment Flow') ),
				array( 'admin/conf/themes', M('Themes') ),
				array( 'admin/conf/plugins', M('Plugins') ),
				array( 'admin/conf/misc', M('Misc') ),
				array( 'admin/conf/upgrade', M('Info') ),
				array( 'admin/conf/backup', M('Backup') ),*/
			);

	/* add panels from plugins */
		$plm =& ntsPluginManager::getInstance();
		$activePlugins = $plm->getActivePlugins();
		reset( $activePlugins );
		foreach( $activePlugins as $plg ){
			$panels = $plm->getPanels( $plg );
			reset( $panels );
			foreach( $panels as $p ){
				$this->keys[] = $p;
				}
			}
		}

	function getPanels(){
		$return = array();
		reset( $this->keys );
		foreach( $this->keys as $ka ){
			if( isset($ka[2]) && $ka[2] )
				continue;
			$return[] = $ka[0];
			}
		return $return;
		}

	function getPanelsDetailed(){
		return $this->keys;
		}

	// Singleton stuff
	static function &getInstance(){
		return ntsLib::singletonFunction( 'ntsAdminPermissionsManager' );
		}
	}
?>