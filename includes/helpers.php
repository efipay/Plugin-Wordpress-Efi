<?php
/**
 * Plugin helpers.
 *
 * @package  Gerencianet_Oficial
 */

/**
 * Register autoloader
 *
 * All classes must follow WP class naming convention and be in the same folder as this file
 */
spl_autoload_register(function ($class) {

    $baseDir = __DIR__;

    $files = find_files($baseDir);
    foreach ($files as $file) {
        require_once $file;
    }
});
// Função recursiva para buscar arquivos em subpastas
    function find_files($dir) {
        $files = glob($dir . '/*.php');
        foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subdir) {
			if(!str_contains($subdir, 'lib/gerencianet')){
				$files = array_merge($files, find_files($subdir));
			}	
        }
		
        return $files;
    }

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
