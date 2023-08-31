<?php
namespace GN_Includes;

require __DIR__ . '/lib/gerencianet-logger.php';
require __DIR__ . '/lib/class-gerencianet-integration.php';
require __DIR__ . '/lib/class-gerencianet-validate.php';
require __DIR__ . '/payment-methods/class-wc-gerencianet-boleto.php';
require __DIR__ . '/payment-methods/class-wc-gerencianet-cartao.php';
require __DIR__ . '/payment-methods/class-wc-gerencianet-pix.php';
require __DIR__ . '/payment-methods/class-wc-gerencianet-open-finance.php';
require __DIR__ . '/class-gerencianet-i18n.php';


/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.gerencianet.com.br
 *
 * @package    Gerencianet_Oficial
 * @subpackage Gerencianet_Oficial/includes
 * @author     Consultoria Técnica <consultoria@gerencianet.com.br>
 */
class Gerencianet_Oficial {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @access   protected
	 * @var      Gerencianet_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {
		$this->version     = GERENCIANET_OFICIAL_VERSION;
		$this->plugin_name = 'gerencianet-oficial';

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		$this->loader = new Gerencianet_Loader();

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// Gerencianet Boleto Actions and Filters
		add_action( 'plugins_loaded', 'init_gerencianet_boleto' );
		add_action( 'plugins_loaded', 'init_gerencianet_cartao' );
		add_action( 'plugins_loaded', 'init_gerencianet_pix' );
		add_action( 'plugins_loaded', 'init_gerencianet_open_finance' );

		// Add gateway to woocommerce options
		$this->loader->add_filter( 'woocommerce_payment_gateways', $this, 'gerencianet_add_gateway_class' );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gerencianet_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Gerencianet_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {

		// WC_GERENCIANET_PIX
		// Hook to recieve payment notifications
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {

		add_action( 'woocommerce_thankyou', array( $this, 'show_thankyou' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ), 1000 );

		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'show_hide_payment_methods' ) );

	}


	public function public_scripts() {

		if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
			return;
		}
		wp_enqueue_script( 'gn-vmask', plugins_url( 'assets/js/vanilla-masker.min.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), '1.1.1', true );
		wp_enqueue_script( 'gn-fields', plugins_url( 'assets/js/checkout-fields.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'gn-vmask' ), '1.0.0', true );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gerencianet_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	// Register Gerencianet Payment Methods

	public function gerencianet_add_gateway_class( $gateways ) {
		$gateways[] = GERENCIANET_BOLETO_ID;
		$gateways[] = GERENCIANET_CARTAO_ID;
		$gateways[] = GERENCIANET_PIX_ID;
		$gateways[] = GERENCIANET_OPEN_FINANCE_ID;
		return $gateways;
	}

	public function show_thankyou( $order_id ) {
		include dirname( __file__ ) . '/../templates/gerencianet-thankyou.php';
	}

	function show_hide_payment_methods( $available_gateways ) {

		if ( ! is_checkout() ) {
				return;
		}

		$boletoSettings = maybe_unserialize( get_option( 'woocommerce_' . GERENCIANET_BOLETO_ID . '_settings' ) );
		$cardSettings   = maybe_unserialize( get_option( 'woocommerce_' . GERENCIANET_CARTAO_ID . '_settings' ) );

		if(isset($boletoSettings['gn_billet_banking'])) {
			$boletoEnabled = $boletoSettings['gn_billet_banking'];
		}
		
		if(isset($cardSettings['gn_credit_card'])) {
			$cardEnabled   = $cardSettings['gn_credit_card'];
		}
		

		$current_shipping_method = WC()->session->get( 'chosen_shipping_methods' );
		$shippingCost            = 0;
		foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
			if ( WC()->session->__isset( 'shipping_for_package_' . $package_id ) ) {
				foreach ( WC()->session->get( 'shipping_for_package_' . $package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
					if ( $shipping_rate->get_id() == $current_shipping_method[0] ) {
						$shippingCost = intval( $shipping_rate->get_cost() );
					}
				}
			}
		}

		if ( isset( WC()->cart->subtotal ) && ( ( WC()->cart->subtotal + $shippingCost ) < 5 ) ) {
			wc_clear_notices();
			if ( $boletoEnabled == 'yes' && $cardEnabled == 'yes' ) {
				wc_add_notice( 'O pagamento via Boleto ou Cartão de Crédito só está disponível em pedidos acima de R$5,00', 'notice' );
				unset( $available_gateways[ GERENCIANET_BOLETO_ID ] );
				unset( $available_gateways[ GERENCIANET_CARTAO_ID ] );
			} elseif ( $boletoEnabled == 'yes' ) {
				wc_add_notice( 'O pagamento via Boleto só está disponível em pedidos acima de R$5,00', 'notice' );
				unset( $available_gateways[ GERENCIANET_BOLETO_ID ] );
			} elseif ( $cardEnabled == 'yes' ) {
				wc_add_notice( 'O pagamento via Cartão de Crédito só está disponível em pedidos acima de R$5,00', 'notice' );
				unset( $available_gateways[ GERENCIANET_CARTAO_ID ] );
			}
		}

		return $available_gateways;
	}

}
