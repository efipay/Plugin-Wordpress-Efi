<?php
namespace GN_Includes;

require_once __DIR__ . '/lib/gerencianet-logger.php';
require_once __DIR__ . '/lib/class-gerencianet-integration.php';
require_once __DIR__ . '/lib/class-gerencianet-validate.php';
require_once __DIR__ . '/payment-methods/class-wc-gerencianet-boleto.php';
require_once __DIR__ . '/payment-methods/class-wc-gerencianet-cartao.php';
require_once __DIR__ . '/payment-methods/class-wc-gerencianet-pix.php';
require_once __DIR__ . '/payment-methods/class-wc-gerencianet-open-finance.php';
require_once __DIR__ . '/payment-methods/subscriptions/class-gerencianet-planos.php';
require_once __DIR__ . '/payment-methods/subscriptions/class-gerencianet-assinaturas.php';
require_once __DIR__ . '/payment-methods/subscriptions/class-wc-gerencianet-assinaturas-boleto.php';
require_once __DIR__ . '/payment-methods/subscriptions/class-wc-gerencianet-assinaturas-cartao.php';
require_once __DIR__ . '/utils/class-gerencianet-hpos.php';
require_once __DIR__ . '/class-gerencianet-i18n.php';

use Gerencianet_Assinaturas;
use Gerencianet_Planos;
use Gerencianet_Hpos;
use Gerencianet_Integration;

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
class Gerencianet_Oficial
{

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
	public function __construct()
	{
		$this->version = GERENCIANET_OFICIAL_VERSION;
		$this->plugin_name = 'gerencianet-oficial';

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		$this->loader = new Gerencianet_Loader();
		$this->gerencianetSDK = new Gerencianet_Integration();

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// Gerencianet Boleto Actions and Filters
		add_action('plugins_loaded', 'init_gerencianet_boleto');
		add_action('plugins_loaded', 'init_gerencianet_cartao');
		add_action('plugins_loaded', 'init_gerencianet_pix');
		add_action('plugins_loaded', 'init_gerencianet_open_finance');
		add_action('plugins_loaded', 'init_gerencianet_assinaturas_cartao');
		add_action('plugins_loaded', 'init_gerencianet_assinaturas_boleto');

		// Ajax card retry
		add_action( 'wp_ajax_woocommerce_gerencianet_card_retry', array('wc_gerencianet_cartao', 'card_retry'));
		add_action( 'wp_ajax_woocommerce_gerencianet_assinatura_retry', array('wc_gerencianet_assinaturas_cartao', 'assinatura_retry'));

		// Add gateway to woocommerce options
		$this->loader->add_filter('woocommerce_payment_gateways', $this, 'gerencianet_add_gateway_class');
		$this->loader->add_filter('plugin_action_links_woo-gerencianet-official/gerencianet-oficial.php', $this, 'gn_settings_link' );

		// Hook download logs
		add_action('admin_post_gn_download_logs', array($this, 'gn_download_logs'));

		// Hook register webhook
		add_action('admin_post_register_webhook', array($this, 'registerWebhook'));

		add_action('admin_enqueue_scripts', array($this, 'setting_enqueue_scripts'));

		$settingsAssinaturasBoleto = maybe_unserialize(get_option('woocommerce_wc_gerencianet_assinaturas_boleto_settings'));
        $settingsAssinaturasCartao = maybe_unserialize(get_option('woocommerce_wc_gerencianet_assinaturas_cartao_settings'));

        $hasBoleto = false;
        $hasCartao = false;

        if(isset($settingsAssinaturasBoleto['gn_billet_banking']))
            $hasBoleto = $settingsAssinaturasBoleto['gn_billet_banking'] == 'yes' ? true : false;
        if(isset($settingsAssinaturasCartao['gn_credit_card']))
            $hasCartao = $settingsAssinaturasCartao['gn_credit_card'] == 'yes' ? true : false;

        if ($hasBoleto || $hasCartao) {
			new Gerencianet_Assinaturas();
			new Gerencianet_Planos();
		}

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gerencianet_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Gerencianet_I18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		// WC_GERENCIANET_PIX
		// Hook to recieve payment notifications
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks()
	{

		add_action('woocommerce_thankyou', array($this, 'show_thankyou'));

		add_action('wp_enqueue_scripts', array($this, 'public_scripts'), 1000);

		add_filter('woocommerce_available_payment_gateways', array($this, 'show_hide_payment_methods'));

	}

	public function public_scripts()
	{

		if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
			return;
		}
		wp_enqueue_script('gn-vmask', plugins_url('assets/js/vanilla-masker.min.js', plugin_dir_path(__FILE__)), array('jquery'), '1.1.1', true);
		wp_enqueue_script('gn-fields', plugins_url('assets/js/checkout-fields.js', plugin_dir_path(__FILE__)), array('jquery', 'gn-vmask'), '1.0.0', true);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gerencianet_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	// Register Gerencianet Payment Methods
	public function gerencianet_add_gateway_class($gateways)
	{
		$gateways[] = GERENCIANET_BOLETO_ID;
		$gateways[] = GERENCIANET_CARTAO_ID;
		$gateways[] = GERENCIANET_PIX_ID;
		$gateways[] = GERENCIANET_OPEN_FINANCE_ID;
		$gateways[] = GERENCIANET_ASSINATURAS_BOLETO_ID;
		$gateways[] = GERENCIANET_ASSINATURAS_CARTAO_ID;
		return $gateways;
	}

	public function show_thankyou($order_id)
	{
		include dirname(__FILE__) . '/../templates/gerencianet-thankyou.php';
	}

	function show_hide_payment_methods($available_gateways)
	{

		if (!is_checkout()) {
			return;
		}

		$cardEnabled = '';

		$boletoSettings = maybe_unserialize(get_option('woocommerce_' . GERENCIANET_BOLETO_ID . '_settings'));
		$cardSettings = maybe_unserialize(get_option('woocommerce_' . GERENCIANET_CARTAO_ID . '_settings'));

		if (isset($boletoSettings['gn_billet_banking'])) {
			$boletoEnabled = $boletoSettings['gn_billet_banking'];
		}

		if (isset($cardSettings['gn_credit_card'])) {
			$cardEnabled = $cardSettings['gn_credit_card'];
		}


		$current_shipping_method = WC()->session->get('chosen_shipping_methods');
		$shippingCost = 0;
		foreach (WC()->cart->get_shipping_packages() as $package_id => $package) {
			if (WC()->session->__isset('shipping_for_package_' . $package_id)) {
				foreach (WC()->session->get('shipping_for_package_' . $package_id)['rates'] as $shipping_rate_id => $shipping_rate) {
					if ($shipping_rate->get_id() == $current_shipping_method[0]) {
						$shippingCost = intval($shipping_rate->get_cost());
					}
				}
			}
		}

		if (isset(WC()->cart->subtotal) && ((WC()->cart->subtotal + $shippingCost) < 3) && isset($cardEnabled)) {
			wc_clear_notices();
			if ($cardEnabled == 'yes') {
				wc_add_notice('O pagamento via Cartão de Crédito só está disponível em pedidos acima de R$3,00', 'notice');
				unset($available_gateways[GERENCIANET_CARTAO_ID]);
			}
		}

		$found = false;
		if (isset(WC()->cart)) {

			foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
				$product = $values['data'];
				if (Gerencianet_Hpos::get_meta($product->get_id(), '_habilitar_recorrencia', true) == 'yes')
					$found = true;
			}
			if ($found) {
				foreach ($available_gateways as $key) {
					if (($key->id != GERENCIANET_ASSINATURAS_CARTAO_ID) && ($key->id != GERENCIANET_ASSINATURAS_BOLETO_ID)) {
						unset($available_gateways[$key->id]);
					}
				}
			} else {
				unset($available_gateways[GERENCIANET_ASSINATURAS_CARTAO_ID]);
				unset($available_gateways[GERENCIANET_ASSINATURAS_BOLETO_ID]);
			}
		}

		return $available_gateways;
	}

	// Adiciona links a Efi na página de plugins
	function gn_settings_link( $links ) {
		// Link Configurações
		$url = esc_url( add_query_arg(
			array(
				'page' => 'wc-settings',
				'tab' => 'checkout'
			),
			get_admin_url() . 'admin.php'
		));
		// Create the link.
		$settings_link = "<a href='$url'>" . __( 'Configurações', Gerencianet_I18n::getTextDomain() ) . '</a>';
		
		// Link Baixar Logs.
		$urlLogs = esc_url(add_query_arg(
			array(
				'action' => 'gn_download_logs'
			),
			admin_url('admin-post.php?action=gn_download_logs&log=versions')
		));
		// Create the link.
		$logs_link = "<a href='$urlLogs'>" . __( 'Baixar Logs', Gerencianet_I18n::getTextDomain() ) . '</a>';
		
		// Adds the link to the end of the array.
		array_push(
			$links,
			$settings_link,
			$logs_link
		);
		return $links;
	}

	function gn_download_logs($log){

		if(isset($_GET['log'])){
			$log = sanitize_text_field($_GET['log']);
		}

		$log_files = array(
			'versions' => 'efi-versions.log',
			'wc_gerencianet_boleto' => 'efi-boletos.log',
			'wc_gerencianet_pix' => 'efi-pix.log',
			'wc_gerencianet_cartao' => 'efi-cartao.log',
			'wc_gerencianet_open_finance' => 'efi-open-finance.log',
			'wc_gerencianet_assinaturas_boleto' => 'efi-assinaturas-boleto.log',
			'wc_gerencianet_assinaturas_cartao' => 'efi-assinaturas-cartao.log'
		);

		$logs_location = WP_CONTENT_DIR.'/';

		try {
			if (file_exists($logs_location.$log_files[$log])) {
				$this->send_download_log($logs_location, $log_files[$log]);
			} else {
				gn_log('Sem logs Registrados', $log);
				$this->send_download_log($logs_location, $log_files[$log]);
			}
		} catch (\Throwable $th) {
			gn_log($th);
		}
	}

	function send_download_log($logs_location, $log_file ){
		header('Content-Description: File Transfer');
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="'.$log_file.'"');
		readfile($logs_location.$log_file);
		exit;
	}

	

	function setting_enqueue_scripts($hook) {
		// Verifica se estamos na página de configurações do WooCommerce
		if (!isset($_GET['page']) || $_GET['page'] !== 'wc-settings' || !isset($_GET['tab']) || $_GET['tab'] !== 'checkout') {
			return;
		}

		// Verifica se é o nosso gateway específico
		if (!isset($_GET['section']) || !in_array($_GET['section'], ['wc_gerencianet_pix', 'wc_gerencianet_boleto', 'wc_gerencianet_cartao', 'wc_gerencianet_open_finance', 'wc_gerencianet_assinaturas_boleto', 'wc_gerencianet_assinaturas_cartao'])) {
			return;
		}

		// Enfileira o estilo CSS personalizado
		wp_enqueue_style(
			'efi-settings-css',
			plugin_dir_url(__FILE__) . '../assets/css/settings.css', // Caminho para o arquivo CSS
			array(),
			'1.0.0'
		);

		// Enfileira o script JavaScript personalizado
		wp_enqueue_script(
			'efi-settings-js',
			plugin_dir_url(__FILE__) . '../assets/js/settings.js', // Caminho para o arquivo JS
			array('jquery'), // Dependência do jQuery (opcional)
			'1.0.0',
			true // Carregar no final da página
		);
	}

}
