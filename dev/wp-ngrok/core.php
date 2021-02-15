<?php
/**
 * Created by PhpStorm.
 * Project :  wp-ngrok.
 * User: hadie MacBook
 * Date: 02/12/20
 * Time: 20.02
 */

namespace WPNgrok;

class WP_REMOTE_NGROK
{


    /**
     * Hooking WordPress
     */
    public function make_it_relative()
    {
        add_action('registered_taxonomy', array($this, 'buffer_start_relative_url'));
        add_action('shutdown', array($this, 'buffer_end_relative_url'));
        add_filter('stylesheet_directory_uri', array($this, 'style_uri'), 10, 1);
        add_filter('stylesheet_directory', array($this, 'style_uri'), 10, 1);
        add_filter('template_directory_uri', array($this, 'style_uri'), 10, 1);
        add_filter('plugins_url', array($this, 'get_ngrok_url'), 100, 1);
        add_filter('the_content', array($this, 'filter_content'), 10, 1);
        add_filter('wp_get_attachment_url', array($this, 'get_ngrok_url'), 10, 1);
    }


    /**
     * @param $content
     * @return string|string[]|null
     * Filter WordPress Image URL in the COntent
     */
    public function filter_content($content)
    {
        $regex = '#src=("|\')' .
            '(/images/(19|20)(0-9){2}/(0|1)(0-9)/[^.]+\.(jpg|png|gif|bmp|jpeg))' .
            '("|\')#';
        $replace = 'src="' . get_site_url() . '$2';


        return preg_replace($regex, $replace, $content);
    }


    /**
     * @param $stylesheet_dir_uri
     * @return string
     * Filter Style URL
     */
    public function style_uri($stylesheet_dir_uri)
    {
        return $this->get_ngrok_url($stylesheet_dir_uri);
    }

    /**
     * @param $template_dir_uri
     * @return string
     * Get URL from Ngrok
     */
    public function get_ngrok_url($template_dir_uri)
    {

        if (isset($_SERVER['HTTP_X_ORIGINAL_HOST']) && strpos($_SERVER['HTTP_X_ORIGINAL_HOST'], 'ngrok') !== FALSE) {
            $xhost = explode('/wp-content/', $template_dir_uri);
            $original = end($xhost);
            $template_dir_uri = '/wp-content/' . $original;
        }
        return $template_dir_uri;
    }


    /**
     * define WP_SITEURL, WP_HOME and LOCAL_TUNNER_ACTIVE
     */
    public function defining()
    {
        if (isset($_SERVER['HTTP_X_ORIGINAL_HOST'])) {

            if (strpos($_SERVER['HTTP_X_ORIGINAL_HOST'], 'ngrok') !== FALSE) {

                if (
                    isset($_SERVER['HTTP_X_ORIGINAL_HOST']) &&
                    $_SERVER['HTTP_X_ORIGINAL_HOST'] === "https"
                ) {
                    $server_proto = 'https://';
                } else {
                    $server_proto = 'http://';
                }

                if (!defined('WP_SITEURL')) {
                    define('WP_SITEURL', $server_proto . $_SERVER['HTTP_HOST']);
                }
                if (!defined('WP_HOME')) {
                    define('WP_HOME', $server_proto . $_SERVER['HTTP_HOST']);

                }
                if (!defined('WPNGROK_TUNNEL_ACTIVE')) {
                    define('WPNGROK_TUNNEL_ACTIVE', true);

                }

                define('WP_PLUGIN_DIR', $server_proto . $_SERVER['HTTP_HOST']);

            }
        }
    }

    /**
     * @param $page_html
     * @return string|string[]
     */
    private function callback_relative_url($page_html)
    {
        if (defined('WPNGROK_TUNNEL_ACTIVE')) {
            if (WPNGROK_TUNNEL_ACTIVE === true) {
                $wp_home_url = esc_url(home_url('/'));
                $rel_home_url = wp_make_link_relative($wp_home_url);

                $esc_home_url = str_replace('/', '\/', $wp_home_url);
                $rel_esc_home_url = str_replace('/', '\/', $rel_home_url);

                $rel_page_html = str_replace($wp_home_url, $rel_home_url, $page_html);
                $esc_page_html = str_replace($esc_home_url, $rel_esc_home_url, $rel_page_html);

                $page_html = $esc_page_html;
            }
        }
        return $page_html;
    }


    /**
     * Start Change URLS Dynamically
     */
    public function buffer_start_relative_url()
    {
        if (defined('WPNGROK_TUNNEL_ACTIVE')) {
            if (WPNGROK_TUNNEL_ACTIVE === true) {
                ob_start(array($this, 'callback_relative_url'));
            }
        }
    }

    /**
     * End Change URLS Dynamically
     */
    public function buffer_end_relative_url()
    {
        if (defined('WPNGROK_TUNNEL_ACTIVE')) {
            if (WPNGROK_TUNNEL_ACTIVE === true) {
                @ob_end_flush();
            }
        }
    }


}
