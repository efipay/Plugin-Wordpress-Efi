<?php

use GN_Includes\Gerencianet_I18n;

function init_gerencianet_assinaturas_boleto()
{
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}
	;

	class WC_Gerencianet_Assinaturas_Boleto extends WC_Payment_Gateway
	{

		// payment gateway plugin ID
		public $id;
		public $has_fields;
		public $method_title;
		public $method_description;
		public $supports;

		public $discountMessage;

		public function __construct()
		{

			$this->id = GERENCIANET_ASSINATURAS_BOLETO_ID; // payment gateway plugin ID
			$this->has_fields = true; // custom form
			$this->method_title = __('Efí - Assinaturas via Boleto', Gerencianet_I18n::getTextDomain());
			$this->method_description = __('Com a Efí você pode receber pagamentos recorrentes via Boleto', Gerencianet_I18n::getTextDomain());

			$this->supports = array(
				'products',
			);

			$this->init_form_fields();

			$this->gerencianetSDK = new Gerencianet_Integration();

			$this->discountMessage = '';

			$discountText = '';

			if ($this->get_option('gn_billet_discount') != '' && $this->get_option('gn_billet_discount') != '0' && $this->get_option('gn_billet_discount') != null && intval($this->get_option('gn_billet_discount')) > 0) {
				$discountText = ' - ' . esc_html($this->get_option('gn_billet_discount')) . '% de Desconto';
			}

			// Load the settings.
			$this->init_settings();
			$this->title = __('Assinatura - Boleto', Gerencianet_I18n::getTextDomain()) . $discountText;
			$this->description = __('Pagando por Boleto, seu pagamento será confirmado em até dois dias úteis.', Gerencianet_I18n::getTextDomain());
			$this->enabled = sanitize_text_field($this->get_option('gn_billet_banking'));
			$this->gn_billet_discount = sanitize_text_field($this->get_option('gn_billet_discount'));
			$this->gn_billet_discount_shipping = sanitize_text_field($this->get_option('gn_billet_discount_shipping'));
			$this->gn_billet_number_days = sanitize_text_field($this->get_option('gn_billet_number_days'));
			$this->gn_client_id_production = sanitize_text_field($this->get_option('gn_client_id_production'));
			$this->gn_client_secret_production = sanitize_text_field($this->get_option('gn_client_secret_production'));
			$this->gn_client_id_homologation = sanitize_text_field($this->get_option('gn_client_id_homologation'));
			$this->gn_client_secret_homologation = sanitize_text_field($this->get_option('gn_client_secret_homologation'));
			$this->gn_sandbox = sanitize_text_field($this->get_option('gn_sandbox'));
			$this->gn_order_status_after_payment = sanitize_text_field($this->get_option('gn_order_status_after_payment'));

			// // This action hook saves the settings
			add_action('woocommerce_update_options_payment_gateways_' . GERENCIANET_ASSINATURAS_BOLETO_ID, array($this, 'process_admin_options'));

			wp_enqueue_script('gn_sweetalert', GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/js/sweetalert.js', array('jquery'), GERENCIANET_OFICIAL_VERSION, false);
			add_action('woocommerce_api_' . strtolower(GERENCIANET_ASSINATURAS_BOLETO_ID), array($this, 'webhook'));
		}

		public function init_form_fields()
		{

			$this->form_fields = array(
				'gn_api_section' => array(
					'title' => __('Credenciais Efí', Gerencianet_I18n::getTextDomain()),
					'type' => 'title',
					'description' => __("<a href='https://gerencianet.com.br/artigo/como-obter-chaves-client-id-e-client-secret-na-api/#versao-7' target='_blank'>Clique aqui para obter seu Client_id e Client_secret! </a>", Gerencianet_I18n::getTextDomain()),
				),
				'gn_client_id_production' => array(
					'title' => __('Client_id Produção', Gerencianet_I18n::getTextDomain()),
					'type' => 'text',
					'description' => __('Por favor, insira seu Client_id. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain()),
					'desc_tip' => false,
					'default' => '',
				),
				'gn_client_secret_production' => array(
					'title' => __('Client_secret Produção', Gerencianet_I18n::getTextDomain()),
					'type' => 'text',
					'description' => __('Por favor, insira seu Client_secret. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain()),
					'desc_tip' => false,
					'default' => '',
				),
				'gn_client_id_homologation' => array(
					'title' => __('Client_id Homologação', Gerencianet_I18n::getTextDomain()),
					'type' => 'text',
					'description' => __('Por favor, insira seu Client_id de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain()),
					'desc_tip' => false,
					'default' => '',
				),
				'gn_client_secret_homologation' => array(
					'title' => __('Client_secret Homologação', Gerencianet_I18n::getTextDomain()),
					'type' => 'text',
					'description' => __('Por favor, insira seu Client_secret de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain()),
					'desc_tip' => false,
					'default' => '',
				),
				'gn_sandbox_section' => array(
					'title' => __('Ambiente Sandbox', Gerencianet_I18n::getTextDomain()),
					'type' => 'title',
					'description' => 'Habilite para usar o ambiente de testes da Efí. Nenhuma cobrança emitida nesse modo poderá ser paga.',
				),
				'gn_sandbox' => array(
					'title' => __('Sandbox', Gerencianet_I18n::getTextDomain()),
					'type' => 'checkbox',
					'label' => __('Habilitar o ambiente sandbox', Gerencianet_I18n::getTextDomain()),
					'default' => 'no',
				),
				'gn_billet_section' => array(
					'title' => __('Configurações de recebimento', Gerencianet_I18n::getTextDomain()),
					'type' => 'title',
				),
				'gn_billet_banking' => array(
					'title' => __('Boleto', Gerencianet_I18n::getTextDomain()),
					'type' => 'checkbox',
					'label' => __('Habilitar Boleto', Gerencianet_I18n::getTextDomain()),
					'default' => 'no',
				),
				'gn_billet_discount' => array(
					'title' => __('Desconto no Boleto', Gerencianet_I18n::getTextDomain()),
					'type' => 'number',
					'description' => __('Porcentagem de desconto para pagamento com Boleto. (Opcional)', Gerencianet_I18n::getTextDomain()),
					'desc_tip' => false,
					'placeholder' => '10',
					'default' => '0',
				),
				'gn_billet_discount_shipping' => array(
					'title' => __('Aplicar desconto do Boleto', Gerencianet_I18n::getTextDomain()),
					'type' => 'select',
					'description' => __('Escolha a modalidade de desconto.', Gerencianet_I18n::getTextDomain()),
					'default' => 'total',
					'options' => array(
						'total' => __('Aplicar desconto no valor total com Frete', Gerencianet_I18n::getTextDomain()),
						'products' => __('Aplicar desconto apenas no preço dos produtos', Gerencianet_I18n::getTextDomain()),
					),
				),
				'gn_billet_number_days' => array(
					'title' => __('Vencimento do Boleto', Gerencianet_I18n::getTextDomain()),
					'type' => 'number',
					'description' => __('Dias para expirar o Boleto depois de emitido.', Gerencianet_I18n::getTextDomain()),
					'desc_tip' => false,
					'placeholder' => '0',
					'default' => '5',
				),
				'gn_order_status_after_payment' => array(
					'title'       => __( 'Status do pedido após pagamento', 'text-domain' ),
					'type'        => 'select',
					'description' => __( 'Selecione o status do pedido após a confirmação do pagamento.', 'text-domain' ),
					'desc_tip'    => true,
					'options'     => wc_get_order_statuses(), // Obtém os status de pedido disponíveis
					'default'     => 'wc-processing', // Define um status padrão, ex: 'wc-processing'
				),
				'download_button' => array(
					'default'           => 'Baixar Logs',
					'title'             => __( 'Baixar Logs', Gerencianet_I18n::getTextDomain() ),
					'type'              => 'button',
					'description'       => __( 'Clique para baixar os logs de emissão de Assinaturas via Boleto.', Gerencianet_I18n::getTextDomain() ),
					'custom_attributes' => array(
						'onclick' => 'location.href="' . admin_url('admin-post.php?action=gn_download_logs&log=wc_gerencianet_assinaturas_boleto') . '";',
					),
				),
			);
		}

		public function gn_price_format($value)
		{
			$value = number_format($value, 2, "", "");
			return $value;
		}

		public function payment_fields()
		{


			if ($this->description) {
				echo wpautop(wp_kses_post($this->description));
			}

			if (intval($this->get_option('gn_billet_discount')) > 0) {
				if ($this->get_option('gn_billet_discount_shipping') == 'total') {
					$this->discountMessage = ' no valor total da compra (frete incluso)';
				} else {
					$this->discountMessage = ' no valor total dos produtos (frete não incluso)';
				}

				$discountWarn = '<div class="warning-payment" id="wc-gerencianet-messages-sandbox">
										<div class="woocommerce-info">' . esc_html($this->get_option('gn_billet_discount')) . '% de Desconto' . $this->discountMessage . '</div>
									</div>';
				echo wpautop(wp_kses_post($discountWarn));
			}

			$is_sandbox = $this->get_option('gn_sandbox') == 'yes' ? true : false;
			if ($is_sandbox) {
				$sandboxWarn = '<div class="warning-payment" id="wc-gerencianet-messages-sandbox">
                                    <div class="woocommerce-error">' . __('O modo Sandbox está ativo. As cobranças emitidas não serão válidas.', Gerencianet_I18n::getTextDomain()) . '</div>
                                </div>';
				echo wpautop(wp_kses_post($sandboxWarn));
			}

			echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent; border: none;">';

			?>
			<div class="form-row form-row-wide" id="gn_field_boleto">
				<label>CPF/CNPJ <span class="required">*</span></label>
				<input id="gn_boleto_cpf_cnpj" class="input-text" inputmode="numeric" name="gn_boleto_cpf_cnpj" type="text"
					placeholder="___.___.___-__" autocomplete="off"
					onkeypress="return event.charCode >= 48 && event.charCode <= 57">
			</div>
			<div class="clear"></div>
			</fieldset>
			<script src="<?php echo plugins_url('../../assets/js/vanilla-masker.min.js', plugin_dir_path(__FILE__)); ?>"></script>
			<script src="<?php echo plugins_url('../../assets/js/scripts-boleto.js', plugin_dir_path(__FILE__)); ?>"></script>
			<?php

		}

		public function validate_fields()
		{

			if (empty(sanitize_text_field($_POST['gn_boleto_cpf_cnpj']))) {
				wc_add_notice(__('CPF é obrigatório!', Gerencianet_I18n::getTextDomain()), 'error');
				return false;
			} else {
				$cpf_cnpj = preg_replace('/[^0-9]/', '', sanitize_text_field($_POST['gn_boleto_cpf_cnpj']));
				if (strlen($cpf_cnpj) == 11) {
					// cpf
					return Gerencianet_Validate::cpf($cpf_cnpj);
				} else {
					// cnpj
					return Gerencianet_Validate::cnpj($cpf_cnpj);
				}
			}
			return true;
		}

		public function process_payment($order_id)
		{

			global $woocommerce;

			$order = wc_get_order($order_id);

			$types = array('line_item', 'fee', 'shipping', 'coupon');
			$items = array();
			$shipping = array();
			$discount = false;
			$orderTotal = 0;
			$expirationDate = date('Y-m-d');
			$recorrencia = 0;
			$repeats = 0;
			$namePlan = '';
			

			// get the Items
			foreach ($order->get_items($types) as $item_id => $item) {

				switch ($item->get_type()) {
					case 'fee':
						$newFee = array(
							'name' => __('Taxas', Gerencianet_I18n::getTextDomain()),
							'amount' => 1,
							'value' => (int) $this->gn_price_format($item->get_total()),
						);
						$orderTotal += (int) $this->gn_price_format($item->get_total());
						$items[] = $newFee;
						break;
					case 'shipping':
						if ($item->get_total() > 0) {
							$shipping[] = array(
								'name' => __('Frete', Gerencianet_I18n::getTextDomain()),
								'value' => (int) $this->gn_price_format($item->get_total()),
							);
						}
						break;
					case 'coupon':
						$newDiscount = array(
							'type' => 'currency',
							'value' => (int) $this->gn_price_format($item->get_discount()),
						);
						$discount = $newDiscount;
						break;
					case 'line_item':
						// Definições importantes para criação do plano
						$product = $item->get_product();
						$interval = $product->get_meta('_gerencianet_interval');
						$repeats = $product->get_meta('_gerencianet_repeats');
						$namePlan = $product->get_name();

						$product = $item->get_product();
						$newItem = array(
							'name' => $namePlan,
							'amount' => $item->get_quantity(),
							'value' => (int) $this->gn_price_format($product->get_price()),
						);

						$orderTotal += (int) $this->gn_price_format($product->get_price() * $item->get_quantity());
						$items[] = $newItem;
						break;
					default:
						$product = $item->get_product();
						$newItem = array(
							'name' => $item->get_name(),
							'amount' => $item->get_quantity(),
							'value' => (int) $this->gn_price_format($product->get_price()),
						);
						$orderTotal += (int) $this->gn_price_format($product->get_price() * $item->get_quantity());
						$items[] = $newItem;
						break;
				}
			}

			if ($order->get_total_tax() > 0) {
				$newItem = array(
					'name' => 'Taxas',
					'amount' => 1,
					'value' => (int) $this->gn_price_format($order->get_total_tax())
				);
				array_push($items, $newItem);
			}

			$cpf_cnpj = preg_replace('/[^0-9]/', '', sanitize_text_field($_POST['gn_boleto_cpf_cnpj']));
			if (Gerencianet_Validate::cpf($cpf_cnpj)) {
				$customer = array(
					'name' => $order->get_formatted_billing_full_name(),
					'cpf' => $cpf_cnpj,
					'email' => $order->get_billing_email(),
				);
			} elseif (Gerencianet_Validate::cnpj($cpf_cnpj)) {
				$customer = array(
					'juridical_person' => array(
						'corporate_name' => $order->get_billing_company() != '' ? $order->get_billing_company() : $order->get_formatted_billing_full_name(),
						'cnpj' => $cpf_cnpj,
					),
					'email' => $order->get_billing_email(),
				);
			} else {
				wc_add_notice(__('Verifique seu CPF/CNPJ e tente emitir novamente!', Gerencianet_I18n::getTextDomain()), 'error');
				return;
			}

			if ($this->get_option('gn_billet_discount') != '' && $this->get_option('gn_billet_discount') != '0') {

				if (!isset($discount['value'])) {
					$discount = array(
						'type' => 'currency',
						'value' => 0,
					);
				}

				$discount_gn = 0;

				if ($this->get_option('gn_billet_discount_shipping') == 'total') {
					if (isset($shipping[0]['value'])) {
						$discount_gn = round(($orderTotal + $shipping[0]['value']) * (intval($this->get_option('gn_billet_discount')) / 100), 0, PHP_ROUND_HALF_UP);
						$discount['value'] += $discount_gn;
					} else {
						$discount_gn = round(($orderTotal) * (intval($this->get_option('gn_billet_discount')) / 100), 0, PHP_ROUND_HALF_UP);
						$discount['value'] += $discount_gn;
					}
				} else {
					$discount_gn = round($orderTotal * (intval($this->get_option('gn_billet_discount')) / 100), 0, PHP_ROUND_HALF_UP);
					$discount['value'] += $discount_gn;
				}
				$order_item_id = wc_add_order_item(
					$order_id,
					array(
						'order_item_name' => $this->get_option('gn_billet_discount') . __('% de desconto no boleto') . $this->discountMessage,
						'order_item_type' => 'fee',
					)
				);
				if ($order_item_id) {
					wc_add_order_item_meta($order_item_id, '_fee_amount', -$discount_gn / 100, true);
					wc_add_order_item_meta($order_item_id, '_line_total', -$discount_gn / 100, true);
					$order->set_total($order->get_total() - ($discount_gn / 100));
					$order->save();

				}
			}

			if ($this->get_option('gn_billet_number_days') != '' && $this->get_option('gn_billet_number_days') != '0') {
				$today = date('Y-m-d');
				$numberDays = $this->get_option('gn_billet_number_days');
				$expirationDate = date('Y-m-d', strtotime('+' . $numberDays . ' days', strtotime($today)));
			}


			try {

				$response = $this->gerencianetSDK->create_plan(GERENCIANET_ASSINATURAS_BOLETO_ID, $namePlan, intval($interval), intval($repeats));
				$plan = json_decode($response, true);
				$plan_id = $plan['data']['plan_id'];

				$response = $this->gerencianetSDK->create_subscription_billet($plan_id, $order_id, $items, $shipping, strtolower($woocommerce->api_request_url(GERENCIANET_ASSINATURAS_BOLETO_ID)), $customer, $expirationDate, $discount);
				$charge = json_decode($response, true);

				Gerencianet_Hpos::update_meta($order_id, '_is_subscription', 'yes');

				$nome_cliente = isset($customer['name']) ? $customer['name'] : $customer['juridical_person']['corporate_name'];
				$initial_status = $charge['data']['status'];
				$id_assinatura = $charge['data']['subscription_id'];
				$plano = $namePlan;
				$periodo_de_teste = isset($charge['data']['trial_days']) ? $charge['data']['trial_days']." dias" : "Não possui";
				$data_inicio = $charge['data']['first_execution'];
				$data_fim = 'a fazer';

				$sub_id = $this->criar_assinatura($nome_cliente, $initial_status, $id_assinatura, $plano, $periodo_de_teste, $data_inicio, $data_fim, $order_id);

				Gerencianet_Hpos::update_meta($order_id, '_subscription_id', $sub_id);
				Gerencianet_Hpos::update_meta($sub_id, '_gn_order_id', $order_id);

				if (isset($charge['data']['barcode'])) {
					Gerencianet_Hpos::update_meta($order_id, '_gn_barcode', $charge['data']['barcode']);
				}
				if (isset($charge['data']['pix'])) {
					Gerencianet_Hpos::update_meta($order_id, '_gn_pix_qrcode', $charge['data']['pix']['qrcode_image']);
					Gerencianet_Hpos::update_meta($order_id, '_gn_pix_copy', $charge['data']['pix']['qrcode']);
				}
				if (isset($charge['data']['link'])) {
					Gerencianet_Hpos::update_meta($order_id, '_gn_link_responsive', $charge['data']['link']);
					Gerencianet_Hpos::update_meta($order_id, '_gn_link_pdf', $charge['data']['pdf']['charge']);
				}

				$order->update_status('pending-payment');
				wc_reduce_stock_levels($order_id);
				$woocommerce->cart->empty_cart();

				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order),
				);

			} catch (Exception $e) {
				wc_add_notice($e->getMessage(), 'error');
				return;
			}
		}

		public function webhook()
		{
			$post_notification = sanitize_text_field($_POST['notification']);
			if (isset($post_notification) && !empty($post_notification)) {
				header('HTTP/1.0 200 OK');

				$notification_response = $this->gerencianetSDK->getNotification(GERENCIANET_ASSINATURAS_BOLETO_ID, $post_notification);
				$notification = json_decode($notification_response);

				Gerencianet_Hpos::update_meta( sanitize_text_field($notification->data[0]->custom_id), '_notification_subscription', $notification_response);
				
				if ($notification->code == 200) {

					foreach ($notification->data as $notification_data) {
						$orderIdFromNotification = sanitize_text_field($notification_data->custom_id);
						$orderStatusFromNotification = sanitize_text_field($notification_data->status->current);
						$gerencianetChargeId = sanitize_text_field($notification_data->identifiers->charge_id);
					}

					$order = wc_get_order($orderIdFromNotification);

					switch ($orderStatusFromNotification) {
						case 'paid':
							$order->update_status($this->gn_order_status_after_payment);
							$order->payment_complete();
							break;
						case 'unpaid':
							$order->update_status('failed');
							break;
						case 'refunded':
							$order->update_status('refund');
							break;
						case 'contested':
							$order->update_status('failed');
							break;
						case 'canceled':
							$order->update_status('cancelled');
							break;
						default:
							// no action
							break;
					}
				} else {
					error_log('gerencianet-oficial', 'GERENCIANET :: notification Request : FAIL ');
				}

				exit();

			} else {
				wp_die(__('Request Failure', Gerencianet_I18n::getTextDomain()));
			}
		}

		public static function getMethodId()
		{
			return self::$id;
		}


		public function criar_assinatura($nome_cliente, $initial_status, $id_assinatura, $plano, $periodo_de_teste, $data_inicio, $data_fim, $order_id)
		{
			// Verificar se o tipo de post 'efi_assinaturas' existe
			if (!post_type_exists('efi_assinaturas')) {
				return;
			}

			// Dados do post da assinatura
			$subscription_data = array(
				'post_type' => 'efi_assinaturas',
				'post_title' => $nome_cliente,
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => 1,
			);

			// Inserir o post da assinatura
			$subscription_id = wp_insert_post($subscription_data);

			// Adicionar metadados específicos da assinatura
			Gerencianet_Hpos::update_meta($subscription_id, '_status', $initial_status);
			Gerencianet_Hpos::update_meta($subscription_id, '_id_da_assinatura', $id_assinatura); // Usando o próprio ID como exemplo
			Gerencianet_Hpos::update_meta($subscription_id, '_plano', $plano);
			Gerencianet_Hpos::update_meta($subscription_id, '_periodo_de_teste', $periodo_de_teste);
			Gerencianet_Hpos::update_meta($subscription_id, '_data_de_inicio', $data_inicio); // Data atual
			Gerencianet_Hpos::update_meta($subscription_id, '_data_fim', $data_fim); // Fim da assinatura em um ano
			Gerencianet_Hpos::update_meta($subscription_id, '_pedido_associado', $order_id); // ID do pedido associado, exemplo fixo
			Gerencianet_Hpos::update_meta($subscription_id, '_subs_payment_method', GERENCIANET_ASSINATURAS_BOLETO_ID);

			// Retornar o ID da nova assinatura para referência
			return $subscription_id;
		}


	}
}
