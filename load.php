<?php

/**
 * Load the SI application
 * (function called at the bottom of this page)
 * 
 * @package Sprout_Invoices
 * @return void
 */
function sprout_invoices_load() {
	if ( class_exists( 'Sprout_Invoices' ) ) {
		si_deactivate_plugin();
		return; // already loaded, or a name collision
	}

	do_action( 'sprout_invoices_preload' );

	//////////
	// Load //
	//////////

	// Master class
	require_once SI_PATH.'/Sprout_Invoices.class.php';

	// base classes
	require_once SI_PATH.'/models/_Model.php';
	require_once SI_PATH.'/controllers/_Controller.php';
	do_action( 'si_require_base_classes' );

	// models
	require_once SI_PATH.'/models/Client.php';
	require_once SI_PATH.'/models/Estimate.php';
	require_once SI_PATH.'/models/Invoice.php';
	require_once SI_PATH.'/models/Notification.php';
	require_once SI_PATH.'/models/Payment.php';
	require_once SI_PATH.'/models/Record.php';
	do_action( 'si_require_model_classes' );

	/////////////////
	// Controllers //
	/////////////////

	// settings
	require_once SI_PATH.'/controllers/admin/Settings.php';
	require_once SI_PATH.'/controllers/admin/Settings_API.php';

	// json api
	require_once SI_PATH.'/controllers/api/JSON_API.php';
	
	// checkouts
	require_once SI_PATH.'/controllers/checkout/Checkouts.php';

	// clients
	require_once SI_PATH.'/controllers/clients/Clients.php';

	// developer logs
	require_once SI_PATH.'/controllers/developer/Logs.php';

	// Estimates
	require_once SI_PATH.'/controllers/estimates/Estimate_Submission.php';
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/estimates/Estimate_Submission_Premium.php' ) ) {
		require_once SI_PATH.'/controllers/estimates/Estimate_Submission_Premium.php';
	}
	require_once SI_PATH.'/controllers/estimates/Estimates.php';
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/estimates/Estimates_Premium.php' ) ) {
		require_once SI_PATH.'/controllers/estimates/Estimates_Premium.php';
	}
	
	// invoices	
	require_once SI_PATH.'/controllers/invoices/Invoices.php';
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/invoices/Invoices_Premium.php' ) ) {
		require_once SI_PATH.'/controllers/invoices/Invoices_Premium.php';
	}

	// notifications
	require_once SI_PATH.'/controllers/notifications/Notifications_Control.php';
	require_once SI_PATH.'/controllers/notifications/Notifications.php';
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/notifications/Notifications_Premium.php' ) ) {
		require_once SI_PATH.'/controllers/notifications/Notifications_Premium.php';
	}
	require_once SI_PATH.'/controllers/notifications/Notifications_Admin_Table.php';

	// payment processing
	require_once SI_PATH.'/controllers/payment-processing/Payment_Processors.php';
	require_once SI_PATH.'/controllers/payment-processing/Credit_Card_Processors.php';
	require_once SI_PATH.'/controllers/payment-processing/Offsite_Processors.php';

	// payment processors
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/payment-processing/processors/SI_Paypal_EC.php' ) ) {
		require_once SI_PATH.'/controllers/payment-processing/processors/SI_Paypal_EC.php';
	}
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/payment-processing/processors/SI_Paypal_Pro.php' ) ) {
		require_once SI_PATH.'/controllers/payment-processing/processors/SI_Paypal_Pro.php';
	}
	require_once SI_PATH.'/controllers/payment-processing/processors/SI_Checks.php';
	require_once SI_PATH.'/controllers/payment-processing/processors/SI_Admin_Payment.php';
	do_action( 'si_payment_processors_loaded' );

	// payments
	require_once SI_PATH.'/controllers/payments/Payments.php';
	require_once SI_PATH.'/controllers/payments/Payments_Admin_Table.php';

	// internal records
	require_once SI_PATH.'/controllers/records/Internal_Records.php';
	require_once SI_PATH.'/controllers/records/Records_Admin_Table.php';

	// reporting
	require_once SI_PATH.'/controllers/reporting/Reporting.php';
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/reporting/Reporting_Premium.php' ) ) {
		require_once SI_PATH.'/controllers/reporting/Reporting_Premium.php';
	}
	require_once SI_PATH.'/controllers/templating/Templating.php';

	// updates
	if ( !SI_FREE_TEST && file_exists( SI_PATH.'/controllers/updates/Updates.php' ) ) {
		require_once SI_PATH.'/controllers/updates/Updates.php';
	}
	
	// importers
	require_once SI_PATH.'/importers/Importer.php';
	require_once SI_PATH.'/importers/Freshbooks.php';
	require_once SI_PATH.'/importers/Harvest.php';
	require_once SI_PATH.'/importers/WP-Invoice.php';
	do_action( 'si_importers_loaded' );

	// all done
	do_action( 'si_require_controller_classes' );

	// Template tags
	require_once SI_PATH.'/template-tags/estimates.php';
	require_once SI_PATH.'/template-tags/clients.php';
	require_once SI_PATH.'/template-tags/forms.php';
	require_once SI_PATH.'/template-tags/invoices.php';
	require_once SI_PATH.'/template-tags/ui.php';
	require_once SI_PATH.'/template-tags/utility.php';

	// addons
	require_once SI_PATH.'/add-ons/Addons.php';

	///////////////////
	// init() models //
	///////////////////
	do_action( 'si_models_init' );
	SI_Post_Type::init(); // _Model

	SI_Record::init();
	SI_Notification::init();
	SI_Invoice::init();
	SI_Estimate::init();
	SI_Client::init();
	SI_Payment::init();

	/////////////////////////
	// init() controllers //
	/////////////////////////
	do_action( 'si_controllers_init' );
	SI_Controller::init();
	SI_Settings_API::init();
	SI_Templating_API::init();

	// updates
	if ( !SI_FREE_TEST && method_exists( 'SI_Updates', 'init' ) ) {
		SI_Updates::init();
	}
	
	// api
	SI_JSON_API::init();

	// reports
	SI_Reporting::init();
	if ( !SI_FREE_TEST && method_exists( 'SI_Reporting_Premium', 'init' ) ) {
		SI_Reporting_Premium::init();
	}

	// records and logs
	SI_Internal_Records::init();
	SI_Dev_Logs::init();

	// settings
	SI_Admin_Settings::init();

	// payments and processing
	SI_Payment_Processors::init();
	SI_Payments::init();

	// notifications
	SI_Notifications::init(); // Hooks come before parent class.
	if ( !SI_FREE_TEST && method_exists( 'SI_Notifications_Premium', 'init' ) ) {
		SI_Notifications_Premium::init();
	}
	SI_Notifications_Control::init();

	// clients
	SI_Clients::init();

	// estimates
	SI_Estimates::init();
	if ( !SI_FREE_TEST && method_exists( 'SI_Estimates_Premium', 'init' ) ) {
		SI_Estimates_Premium::init();
	}
	if ( !SI_FREE_TEST && method_exists( 'SI_Estimates_Submission_Premium', 'init' ) ) {
		SI_Estimates_Submission_Premium::init();
	}
	SI_Estimate_Submissions::init();

	// checkouts
	SI_Checkouts::init();

	// invoices
	SI_Invoices::init();
	if ( !SI_FREE_TEST && method_exists( 'SI_Invoices_Premium', 'init' ) ) {
		SI_Invoices_Premium::init();
	}

	// importer
	SI_Importer::init();

	// addons
	if ( method_exists( 'SA_Addons', 'init' ) ) {
		SA_Addons::init();
	}
	do_action( 'sprout_invoices_loaded' );
}

/**
 * Minimum supported version of WordPress
 */
define( 'SI_SUPPORTED_WP_VERSION', version_compare( get_bloginfo( 'version' ), '3.7', '>=' ) );
/**
 * Minimum supported version of PHP
 */
define( 'SI_SUPPORTED_PHP_VERSION', version_compare( phpversion(), '5.2.4', '>=' ) );

/**
 * Compatibility check
 */
if ( SI_SUPPORTED_WP_VERSION && SI_SUPPORTED_PHP_VERSION ) {
	add_action( 'plugins_loaded', 'sprout_invoices_load', 1000 );
} else {
	/**
	 * Disable SI and add fail notices if compatibility check fails
	 * @return string inserted within the WP dashboard
	 */
	si_deactivate_plugin();
	add_action( 'admin_head', 'si_fail_notices' );
	function si_fail_notices() {
		if ( !SI_SUPPORTED_WP_VERSION ) {
			printf( '<div class="error"><p><strong>Sprout Invoices</strong> requires WordPress %s or higher. Please upgrade WordPress and activate the Sprout Invoices Plugin again.</p></div>', SI_SUPPORTED_WP_VERSION );
		}
		if ( !SI_SUPPORTED_PHP_VERSION ) {
			printf( '<div class="error"><p><strong>Sprout Invoices</strong> requires PHP version %s or higher to be installed on your server. Talk to your web host about using a secure version of PHP.</p></div>', SI_SUPPORTED_PHP_VERSION );
		}
	}
}