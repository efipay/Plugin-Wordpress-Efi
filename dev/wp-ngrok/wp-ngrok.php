<?php
/*
Plugin Name: WP-Ngrok
Plugin URI: https://theme.id
Description: Expose your local WordPress to the world with Ngrok
Version: 1.1
Author: Theme.id
Author URI: https://theme.id
Contributors: themeid,hadie danker
Requires at least: 5.0
Tested up to: 5.5
Stable tag: 0.0.2
Requires PHP: 5.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
@source code :
*/


define('WPNGROK_FILE', __FILE__);
define('WPNGROK_PATH', plugin_dir_path(WPNGROK_FILE));

function WPNGROK_plugin_loaded()
{
       if (wpngrok_is_localhost()) {
        require_once(WPNGROK_PATH . 'core.php');
        $local = new \WPNgrok\WP_REMOTE_NGROK();
        $local->defining();
        $local->make_it_relative();
    }
}

add_action('plugins_loaded', 'WPNGROK_plugin_loaded');

if (!function_exists('wpngrok_is_localhost')){
    function wpngrok_is_localhost()
    {
        $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

        return isset($_SERVER['REMOTE_ADDR']) && in_array($ip, ['127.0.0.1', '::1']);

    }
}

if (!function_exists('wpngrok_notice_if_not_in_localhost')){
    function wpngrok_notice_if_not_in_localhost()
    {
        if (!wpngrok_is_localhost()) {
            $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
            $message = ' <div class="notice notice-error is-dismissible">';
            $message .= sprintf('<p>%s</p>', __('You are not in localhost'));
            $message .= sprintf('<p>Your IP is %s</p>', $ip);
            $message .= '</div>';
            echo $message;
        }
    }

    add_action('admin_notices', 'wpngrok_notice_if_not_in_localhost');

}

