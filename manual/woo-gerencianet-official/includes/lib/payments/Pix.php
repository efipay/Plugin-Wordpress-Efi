<?php
/**
 * Pix payment method
 */


class Pix {

    /**
     * Return ajax request
     *
     * @return string
     */
    public static function woocommerce_gerencianet_pay_pix() {
        echo Pix::gerencianet_pay_pix('checkout_page', null, null);
        die();
    }

    /**
     * Return certificate file path
     *
     * @return string
     */
    public static function getCertPath() {
        return get_temp_dir() . 'gerencianet/cert.pem';
    }

    /**
     * Create gerencianet data config
     *
     * @return array
     */
    private static function get_gn_api_credentials($credential) {
        $credential['pix_cert'] = Pix::getCertPath();
        return $credential;
    }

    /**
     * Request Gerencianet API pay charge with Pix.
     *
     * @return string
     */
    public function gerencianet_pay_pix($checkout_type, $order_id, $charge_id, $cpf_cnpj = null) {
        if (count($_POST) < 1) {
            $errorResponse = array(
                'message' => __('An error occurred during your request. Please, try again.', WCGerencianetOficial::getTextDomain())
            );

            return json_encode($errorResponse);
        }

        $arrayDadosPost = array();
        foreach ($_POST as $key => $value) {
            $arrayDadosPost[$key] = $value;
        }

        $gateway = new WC_Gerencianet_Oficial_Gateway();

        //get order data
        $post_order_id = isset($arrayDadosPost['order_id']) ? $arrayDadosPost['order_id'] : $order_id;
        $order = wc_get_order($post_order_id);
        $full_name = $order->get_formatted_billing_full_name();

        $totalOrder = strval($gateway->calculatePixDiscount());
        $discountPix = (float)WC()->cart->get_cart_contents_total() - $gateway->calculatePixDiscount();
        $document = isset($cpf_cnpj) ? preg_replace('/[^0-9]/', '', $cpf_cnpj) : $arrayDadosPost['cpf_cnpj'];
        $docType = (strlen($document) == 11) ? 'cpf' : 'cnpj';

        $body = [
            'calendario' => [ 'expiracao' => (int)$gateway->expiration_pix * 3600 ],
            'devedor' => [
                $docType => $document,
                'nome' => $full_name
            ],
            'valor' => [ 'original' => $totalOrder ],
            'chave' => ($gateway->sandbox == 'yes') ? $gateway->pix_key_dev : $gateway->pix_key_prod,
            'solicitacaoPagador' => 'Pagamento #' . $post_order_id
        ];

        $credential = Pix::get_gn_api_credentials($gateway->gnIntegration->get_gn_api_credentials());
        $gnApiResult = GerencianetIntegration::pay_pix($credential, $body);
		$resultCheck = json_decode($gnApiResult, true);

		if (isset($resultCheck['txid']) && isset($resultCheck['loc']['id'])) {
            $gnApiQrCode = GerencianetIntegration::generate_qrcode($credential, $resultCheck['loc']['id']);
            $resultQrCode = json_decode($gnApiQrCode, true);
            $resultCheck['charge_id'] = $post_order_id;

            if(isset($resultQrCode['imagemQrcode'])) {
                $resultCheck['imagemQrcode'] = $resultQrCode['imagemQrcode'];
    			global $wpdb;

    			if ($order->get_status() != 'failed' && !isset($meta_discount_value_array[0])) {
    				$wpdb->insert($wpdb->prefix . 'woocommerce_order_items', array(
    					'order_item_name' => __('Discount of ', WCGerencianetOficial::getTextDomain()) . str_replace(".", ",", $gateway->discountPix) . __('% Pix', WCGerencianetOficial::getTextDomain()),
    					'order_item_type' => 'fee',
    					'order_id' => intval($post_order_id)
    				));
    				$lastid = $wpdb->insert_id;

    				$wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', array(
    					'order_item_id' => $lastid,
    					'meta_key'      => '_tax_class',
    					'meta_value'    => '0'
    				));

    				$wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', array(
    					'order_item_id' => $lastid,
    					'meta_key'      => '_line_total',
    					'meta_value'    => '-' . number_format(intval(floor(($gateway->gn_price_format($totalOrder)) * (((float)$discountPix / 100)))) / 100, 2, '.', '')
    				));

    				$wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', array(
    					'order_item_id' => $lastid,
    					'meta_key'      => '_line_tax',
    					'meta_value'    => '0'
    				));

    				$wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', array(
    					'order_item_id' => $lastid,
    					'meta_key'      => '_line_tax_data',
    					'meta_value'    => '0'
    				));

    				update_post_meta(intval($post_order_id), '_order_total', number_format(intval(ceil($gateway->gn_price_format($totalOrder))) / 100, 2, '.', ''));
    				update_post_meta(intval($post_order_id), '_payment_method_title', sanitize_text_field(__('Pix - Gerencianet', WCGerencianetOficial::getTextDomain())));
    				add_post_meta(intval($post_order_id), 'pix_qr', $resultQrCode['imagemQrcode'], true);
                    add_post_meta(intval($post_order_id), 'txid', $resultCheck['txid'], true);
    			}
    			$order->update_status('on-hold', __('Waiting'));
    			$order->reduce_order_stock();
    			WC()->cart->empty_cart();
            }
            else {
    			if ($gateway->gnIntegration->debug == 'yes') {
    				write_log('GERENCIANET :: gerencianet_pay_pix Request QRCode : ERROR : Ajax request fail');
    			}
    		}
		} else {
			if ($gateway->gnIntegration->debug == 'yes') {
				write_log('GERENCIANET :: gerencianet_pay_pix Request : ERROR : Ajax request fail');
			}
		}

		return json_encode($resultCheck);
    }

    /**
	 * Check API webhook response.
	 *
	 * @return void
	 */
	public static function validate_webhook() {
		@ob_clean();

        $postdata = file_get_contents('php://input');
        $data = json_decode($postdata);

        // VALIDADE
        if(isset($data->evento) && isset($data->data_criacao)) {
            header('HTTP/1.0 200 OK');
            exit();
        }
        // HOOK
        else if (isset($data->pix) && Pix::checkWebhookTXID($data->pix)) {
			header('HTTP/1.0 200 OK');
			do_action('pix_webhook', stripslashes_deep($data));
		} else {
            error_log(' :: Gerencianet :: PIX_CALLBACK : ERROR');
			wp_die(__('Request Failure', WCGerencianetOficial::getTextDomain()));
		}
	}

    /**
	 * Update gerencianet webhook
	 *
	 * @return void
	 */
    public static function updateWebhook($gateway) {
        $url = WC()->api_request_url('pix_webhook');

        // Se for localhost não faz update do weebhook
        if(strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
            error_log(' :: GERENCIANET :: Localhost is not a valid webhook');
        }
        else {
            $credential = Pix::get_gn_api_credentials($gateway->gnIntegration->get_gn_api_credentials());
            $pix_key = ($gateway->sandbox == 'yes') ? $gateway->pix_key_dev : $gateway->pix_key_prod;
            $skip_mtls = ($gateway->pix_mtls == 'yes') ? 'false' : 'true'; // Precisa ser string

            $gnApi = GerencianetIntegration::update_webhook($credential, $pix_key, $skip_mtls, $url);
            $result = json_decode($gnApi, true);

            if($gateway->debug == 'yes' && $result['webhookUrl']) {
                write_log(' :: GERENCIANET :: Update Pix Webhook');
            }
            else {
                error_log(' :: GERENCIANET :: Error update webhook');
            }
        }
    }

    /**
	 * Change order status
	 *
	 * @param array $posted gerencianet post data.
	 *
	 * @return void
	 */
    private static function checkWebhookTXID($pixData) {
        $success = false;

        if(is_array($pixData)) {
            foreach($pixData as &$value) {
                $success = isset($value->txid) && sanitize_text_field($value->txid) !== '';
                if(!$success) break;
            }
        }

        return $success;
    }

    /**
	 * Change order status
	 *
	 * @param array $posted gerencianet post data.
	 *
	 * @return void
	 */
	public static function successful_webhook($posted) {

        // Percorre lista de notificações
        foreach($posted as &$order_notify) {
            $args = array(
                'limit' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_key' => 'txid',
                'meta_compare' => 'IN',
                'meta_value' => $order_notify->txid
            );

            // Busca pedidos
            $orders = wc_get_orders($args);

            // Atualiza status
            foreach($orders as &$order) {
                $order->update_status('processing', __('Paid'));
                $order->payment_complete();
            }
        }

        exit();
	}
}
