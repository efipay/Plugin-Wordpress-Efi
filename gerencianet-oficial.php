<?php
namespace Gerencianet_Oficial;

use GN_Includes\Gerencianet_Oficial;
use GN_Includes\Gerencianet_Activator;
use GN_Includes\Gerencianet_Deactivator;

/**
 * Plugin Name:       Efí Bank
 * Plugin URI:        https://wordpress.org/plugins/woo-gerencianet-official/
 * Description:       Gateway de pagamento Efi Bank para WooCommerce
 * Version:           3.0.2
 * Author:            Efi Bank
 * Author URI:        https://www.sejaefi.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gerencianet-oficial
 * Domain Path:       /languages
 * WC requires at least: 5.0.0
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GERENCIANET_OFICIAL_VERSION', '3.0.2' );
define( 'GERENCIANET_BOLETO_ID', 'wc_gerencianet_boleto' );
define( 'GERENCIANET_CARTAO_ID', 'wc_gerencianet_cartao' );
define( 'GERENCIANET_PIX_ID', 'wc_gerencianet_pix' );
define( 'GERENCIANET_OPEN_FINANCE_ID', 'wc_gerencianet_open_finance' );
define( 'GERENCIANET_ASSINATURAS_BOLETO_ID', 'wc_gerencianet_assinaturas_boleto' );
define( 'GERENCIANET_ASSINATURAS_CARTAO_ID', 'wc_gerencianet_assinaturas_cartao' );



/**
 * Define global path constants
 */
define( 'GERENCIANET_OFICIAL_PLUGIN_FILE', __FILE__ );
define( 'GERENCIANET_OFICIAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GERENCIANET_OFICIAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GERENCIANET_OFICIAL_PLUGIN_PATH . 'includes/helpers.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gerencianet-oficial-activator.php
 */
register_activation_hook( GERENCIANET_OFICIAL_PLUGIN_FILE, array( Gerencianet_Activator::class, 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gerencianet-oficial-deactivator.php
 */
register_deactivation_hook( GERENCIANET_OFICIAL_PLUGIN_FILE, array( Gerencianet_Deactivator::class, 'deactivate' ) );


add_action( 'before_woocommerce_init', function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_gerencianet_oficial() {

	$plugin = new Gerencianet_Oficial();
	$plugin->run();

}
run_gerencianet_oficial();
