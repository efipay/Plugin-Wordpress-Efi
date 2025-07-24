<?php

class Gerencianet_Hpos {

    public static function update_meta($id, $meta_key, $meta_value) {
        if (self::is_order($id) && self::is_hpos_enabled()) {
                $order = wc_get_order($id);
                if ($order) {
                    $order->update_meta_data($meta_key, $meta_value);
                    $order->save();
                    return true;
                }
                return false;
            } else {
                return update_post_meta($id, $meta_key, $meta_value);
            }
        
    }

    public static function get_meta($id, $meta_key, $single = true) {
        if (self::is_order($id) && self::is_hpos_enabled()) {
                $order = wc_get_order($id);
                if ($order) {
                    return $order->get_meta($meta_key, $single);
                }
                return null;
            } else {
                return get_post_meta($id, $meta_key, $single);
            }
    }

    private static function is_hpos_enabled() {
        return class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && 
               \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    private static function is_order($id) {
        if (self::is_hpos_enabled()) {
            return class_exists('Automattic\WooCommerce\Utilities\OrderUtil') &&
                   \Automattic\WooCommerce\Utilities\OrderUtil::is_order($id, wc_get_order_types());
        } else {
            return 'shop_order' === get_post_type($id);
        }
    }
}
