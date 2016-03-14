<?php
/**
 * Plugin Name: WooCommerce Gerencianet Oficial
 * Plugin URI: https://wordpress.org/plugins/woo-gerencianet-oficial/
 * Description: Gateway de pagamento Gerencianet para WooCommerce.
 * Author: Gerencianet
 * Author URI: http://www.gerencianet.com.br
 * Version: 0.1.0
 * License: GPLv2 or later
 * Text Domain: woo-gerencianet-oficial
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCGerencianetOficial' ) ) :

/**
 * Woocommerce Gerencianet Oficial main class.
 */
class WCGerencianetOficial {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '0.1.0';

	/**
	 * Integration id.
	 *
	 * @var string
	 */
	protected static $gateway_id = 'gerencianet_oficial';

	protected static $textDomain = "woo-gerencianet-oficial";

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin actions.
	 */
	public function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			
			include_once 'includes/class-wc-gerencianet-oficial-gateway.php';
			include_once 'includes/lib/GerencianetIntegration.php';

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

			add_action( 'wp_ajax_woocommerce_gerencianet_get_installments', array( $this, 'woocommerce_gerencianet_get_installments' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_gerencianet_get_installments', array( $this, 'woocommerce_gerencianet_get_installments' ) );
			add_action( 'wp_ajax_woocommerce_gerencianet_pay_billet', array( $this, 'woocommerce_gerencianet_pay_billet' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_gerencianet_pay_billet', array( $this, 'woocommerce_gerencianet_pay_billet' ) );
			add_action( 'wp_ajax_woocommerce_gerencianet_pay_card', array( $this, 'woocommerce_gerencianet_pay_card' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_gerencianet_pay_card', array( $this, 'woocommerce_gerencianet_pay_card' ) );
			add_action( 'wp_ajax_woocommerce_gerencianet_create_charge', array( $this, 'woocommerce_gerencianet_create_charge' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_gerencianet_create_charge', array( $this, 'woocommerce_gerencianet_create_charge' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object 
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Return ajax request
	 *
	 * @return string
	 */
	public function woocommerce_gerencianet_get_installments() {
		$gnGateway = new WC_Gerencianet_Oficial_Gateway();
		echo $gnGateway->gerencianet_get_installments();
		die();
	}

	/**
	 * Return ajax request
	 *
	 * @return string
	 */
	public function woocommerce_gerencianet_pay_billet() {
		$gnGateway = new WC_Gerencianet_Oficial_Gateway();
		echo $gnGateway->gerencianet_pay_billet();
		die();
	}

	/**
	 * Return ajax request
	 *
	 * @return string
	 */
	public function woocommerce_gerencianet_pay_card() {
		$gnGateway = new WC_Gerencianet_Oficial_Gateway();
		echo $gnGateway->gerencianet_pay_card();
		die();
	}

	/**
	 * Return ajax request
	 *
	 * @return string
	 */
	public function woocommerce_gerencianet_create_charge() {
		$gnGateway = new WC_Gerencianet_Oficial_Gateway();
		echo $gnGateway->gerencianet_create_charge();
		die();
	}

	/**
	 * Return the gateway id
	 *
	 * @return string
	 */
	public static function get_gateway_id() {
		return self::$gateway_id;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), WCGerencianetOficial::getTextDomain() );

		load_textdomain( WCGerencianetOficial::getTextDomain(), trailingslashit( WP_LANG_DIR ) . 'woo-gerencianet-oficial/woo-gerencianet-oficial-' . $locale . '.mo' );
		load_plugin_textdomain( WCGerencianetOficial::getTextDomain(), false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array 
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Gerencianet_Oficial_Gateway';

		return $methods;
	}


	/**
	 * Return the textDomain.
	 *
	 * @return string TextDomain variable.
	 */
	public static function getTextDomain(){
		return self::$textDomain;
	}


	/**
	 * WooCommerce missing notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'Gerencianet Gateway depends on the last version of %s to work!', WCGerencianetOficial::getTextDomain() ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
	}
}

add_action( 'plugins_loaded', array( 'WCGerencianetOficial', 'get_instance' ), 0 );

endif;

/**
 * Adds support to notification
 *
 * @return void
 */
function WCGerencianetOficial_legacy_ipn() {
	
	if ( isset( $_POST['notification'] ) ) {
		
		global $woocommerce;
		$woocommerce->payment_gateways();
		
		do_action( 'woocommerce_api_WC_Gerencianet_Oficial_Gateway' );
		
	}
}

add_action( 'init', 'WCGerencianetOficial_legacy_ipn' );
