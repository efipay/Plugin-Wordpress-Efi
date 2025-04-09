<?php
/**
 * Plugin helpers.
 *
 * @package  Gerencianet_Oficial
 */

add_action( 'wp_ajax_gn_check_order_status', 'gn_check_order_status' );
add_action( 'wp_ajax_nopriv_gn_check_order_status', 'gn_check_order_status' );

/**
 * Retorna o status atual do pedido.
 */
function gn_check_order_status() {
    // Verifica se veio um order_id via POST
    if ( ! isset( $_POST['order_id'] ) ) {
        wp_send_json_error( [ 'message' => 'Parâmetros inválidos.' ], 400 );
    }

    $order_id = absint( $_POST['order_id'] );
    $order    = new WC_Order( $order_id );

    if ( ! $order ) {
        wp_send_json_error( [ 'message' => 'Pedido não encontrado.' ], 404 );
    }

    // Pega status atual do pedido (sem "wc-")
    $current_status = $order->get_status();

    // Se quiser retornar também o status completo e qualquer info adicional
    // Ex.: $order_data = $order->get_data();
    // mas aqui vamos só mandar o status
    wp_send_json_success( [ 'current_status' => $current_status ] );
}


/**
 * Register autoloader
 *
 * All classes must follow WP class naming convention and be in the same folder as this file
 */
spl_autoload_register(
	function( $required_file ) {

		// Transform file name from class based to file based
		$fixed_name = strtolower( str_ireplace( '_', '-', $required_file ) );
		$file_path  = explode( '\\', $fixed_name );
		$last_index = count( $file_path ) - 1;
		$file_name  = "class-{$file_path[$last_index]}.php";

		// Get fully qualified path
		$fully_qualified_path = trailingslashit( dirname( __FILE__ ), 2 );
		for ( $key = 1; $key < $last_index; $key++ ) {
			$fully_qualified_path .= trailingslashit( $file_path[ $key ] );
		}
		$fully_qualified_path .= $file_name;

		// Include the file
		if ( stream_resolve_include_path( $fully_qualified_path ) ) {
			include_once $fully_qualified_path;
		}

	}
);

/**
 * Check if the plugin template part loader is already loaded
 */
if ( ! function_exists( 'otkp_get_template_part' ) ) {

	/**
	 * Retrieves a template part
	 *
	 * Hacked from the WP function that allows a template part to be loaded
	 *
	 * @param string $slug
	 * @param string $name Optional. Default null
	 * @param bool   $load   Optional. Default true
	 *
	 * @uses  otkp_locate_template()
	 * @uses  load_template()
	 */
	function otkp_get_template_part( $slug, $name = null, $load = true ) {

		// Check that slug is a string
		if ( ! is_string( $slug ) ) {
			return '';
		}

		// Execute code for this part
		do_action( 'get_template_part_' . $slug, $slug, $name );

		// Setup possible parts
		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = $slug . '-' . $name . '.php';
		}
		$templates[] = $slug . '.php';

		// Allow template parts to be filtered
		$templates = apply_filters( 'otkp_get_template_part', $templates, $slug, $name );

		// Return the part that is found
		return otkp_locate_template( $templates, $load, false );
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
	 * inherit from a parent theme can just overload one file. If the template is
	 * not found in either of those, it looks in the plugin folder last.
	 *
	 * Hacked from the WP function that allows a template part to be located
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load If true the template file will be loaded if it is found.
	 * @param bool         $require_once Whether to require_once or require. Default true.
	 *                                    Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	function otkp_locate_template( $template_names, $load = false, $require_once = true ) {
		// No file found yet
		$located = '';

		// Try to find a template file
		foreach ( (array) $template_names as $template_name ) {

			// Continue if template is empty
			if ( empty( $template_name ) ) {
				continue;
			}

			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );

			// Current plugin dir name
			$dir_name = basename( dirname( __FILE__, 2 ) );

			if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $dir_name . '/' . $template_name ) ) {  // Check child theme first
				$located = trailingslashit( get_stylesheet_directory() ) . $dir_name . '/' . $template_name;
				break;
			} elseif ( file_exists( trailingslashit( get_template_directory() ) . $dir_name . '/' . $template_name ) ) {  // Check parent theme next
				$located = trailingslashit( get_template_directory() ) . $dir_name . '/' . $template_name;
				break;
			} elseif ( file_exists( trailingslashit( otkp_get_templates_dir() ) . $template_name ) ) {  // Check plugin templates folder last
				$located = trailingslashit( otkp_get_templates_dir() ) . $template_name;
				break;
			}
		}

		if ( ( true === $load ) && ! empty( $located ) ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	/**
	 * Retrieve the path to the plugin template directory
	 *
	 * @return string
	 */
	function otkp_get_templates_dir() {
		return trailingslashit( dirname( __FILE__, 2 ) ) . 'templates/';
	}
}
