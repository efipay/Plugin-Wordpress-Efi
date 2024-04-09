<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

/* Classe usada para manter a compatibilidade com
 * os modos de armazenamento do Woocommerce
 * */
class Hpos_compatibility {

	public static function isHpos() {
        if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
            return true;
        } else {
            return false;
        }
	}

    public static function get_meta($order_id, $meta_field, $single=true){
        if (self::isHpos()){
            $order = wc_get_order( $order_id );
            return $order->get_meta( $meta_field, $single );
        }else{
            return get_post_meta( $order_id, $meta_field, $single );
        }
    }

    public static function update_meta($order_id, $meta_field, $meta_value){
        if (self::isHpos()){
            $order = wc_get_order( $order_id );
            $order->update_meta_data( $meta_field, $meta_value );
            $order->save();
        }else{
            update_post_meta( $order_id, $meta_field, $meta_value );
        }
    }
}
