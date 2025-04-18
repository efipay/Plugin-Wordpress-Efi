<?php

use GN_Includes\Gerencianet_I18n;

function init_gerencianet_assinaturas_cartao() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	};

	class WC_Gerencianet_Assinaturas_Cartao extends WC_Payment_Gateway {

		// payment gateway plugin ID
		public $id;
		public $has_fields;
		public $method_title;
		public $method_description;
		public $supports;
		// evitando falhas
		public $gerencianetSDK;
		public $gn_payee_code;
		public $gn_client_id_production;
		public $gn_billet_number_days;
		public $gn_client_secret_production;
		public $gn_client_id_homologation;
		public $gn_client_secret_homologation;
		public $gn_sandbox;
		public $gn_trial_days;
		public $gn_order_status_after_payment;

		public function __construct() {

			$this->id                 = GERENCIANET_ASSINATURAS_CARTAO_ID; // payment gateway plugin ID
			$this->has_fields         = true; // custom form
			$this->method_title       = __( 'Efí - Assinaturas via Cartão de Crédito', Gerencianet_I18n::getTextDomain() );
			$this->method_description = __( 'Com a Efí você pode receber pagamentos recorrentes via Cartão de Crédito', Gerencianet_I18n::getTextDomain() );

			$this->supports = array(
				'products',
			);

			$this->init_form_fields();

			$this->gerencianetSDK = new Gerencianet_Integration();

			// Load the settings.
			$this->init_settings();
			$this->title                         = __( 'Assinatura - Cartão de Crédito', Gerencianet_I18n::getTextDomain() );
			$this->description                   = __( 'Pagando com Cartão de Crédito, seu pagamento será confirmado em até um dia útil.', Gerencianet_I18n::getTextDomain() );
			$this->enabled                       = sanitize_text_field( $this->get_option( 'gn_credit_card' ) );
			$this->gn_payee_code                 = sanitize_text_field( $this->get_option( 'gn_payee_code' ) );
			$this->gn_client_id_production       = sanitize_text_field( $this->get_option( 'gn_client_id_production' ) );
			$this->gn_client_secret_production   = sanitize_text_field( $this->get_option( 'gn_client_secret_production' ) );
			$this->gn_client_id_homologation     = sanitize_text_field( $this->get_option( 'gn_client_id_homologation' ) );
			$this->gn_client_secret_homologation = sanitize_text_field( $this->get_option( 'gn_client_secret_homologation' ) );
			$this->gn_sandbox                    = sanitize_text_field( $this->get_option( 'gn_sandbox' ) );
			$this->gn_trial_days                 = sanitize_text_field( $this->get_option( 'gn_trial_days' ) );
			$this->gn_order_status_after_payment = sanitize_text_field($this->get_option('gn_order_status_after_payment'));

			// // This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . GERENCIANET_ASSINATURAS_CARTAO_ID, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_' . strtolower( GERENCIANET_ASSINATURAS_CARTAO_ID ), array( $this, 'webhook' ) );
			// This Hook add the payment token script on checkout
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		}

		public function gn_price_format($value){
			$value = number_format($value, 2, "", "");
			return $value;
		}

		public function init_form_fields() {

			$this->form_fields = array(
				'gn_api_section'                => array(
					'title'       => __( 'Credenciais Efí', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'title',
					'description' => __( "<a href='https://gerencianet.com.br/artigo/como-obter-chaves-client-id-e-client-secret-na-api/#versao-7' target='_blank'>Clique aqui para obter seu Client_id e Client_secret! </a>", Gerencianet_I18n::getTextDomain() ),
				),
				'gn_client_id_production'       => array(
					'title'       => __( 'Client_id Produção', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Por favor, insira seu Client_id. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'default'     => '',
				),
				'gn_client_secret_production'   => array(
					'title'       => __( 'Client_secret Produção', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'password',
					'description' => __( 'Por favor, insira seu Client_secret. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'default'     => '',
				),
				'gn_client_id_homologation'     => array(
					'title'       => __( 'Client_id Homologação', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Por favor, insira seu Client_id de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'default'     => '',
				),
				'gn_client_secret_homologation' => array(
					'title'       => __( 'Client_secret Homologação', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'password',
					'description' => __( 'Por favor, insira seu Client_secret de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'default'     => '',
				),
				'gn_sandbox_section'            => array(
					'title'       => __( 'Ambiente Sandbox', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'title',
					'description' => 'Habilite para usar o ambiente de testes da Efí. Nenhuma cobrança emitida nesse modo poderá ser paga.',
				),
				'gn_sandbox'                    => array(
					'title'   => __( 'Sandbox', Gerencianet_I18n::getTextDomain() ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar o ambiente sandbox', Gerencianet_I18n::getTextDomain() ),
					'default' => 'no',
				),
				'gn_one_payment_section'        => array(
					'title' => __( 'Configurações de recebimento', Gerencianet_I18n::getTextDomain() ),
					'type'  => 'title',
				),
				'gn_credit_card'                => array(
					'title'   => __( 'Cartão de Crédito', Gerencianet_I18n::getTextDomain() ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar Cartão de Crédito', Gerencianet_I18n::getTextDomain() ),
					'default' => 'no',
				),
				'gn_payee_code'                 => array(
					'title'       => __( 'Identificador de Conta', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( "Por favor, insira seu <a href='https://sejaefi.link/SJgFadiyw3' target='_blank'>Identificador de conta</a>. Isso é necessário para receber os pagamentos.", Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'default'     => '',
				),
				'gn_trial_days'                 => array(
					'title'       => __( 'Período de Teste (em dias)', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'number',
					'description' => __( "Permite definir uma quantidade de dias para teste gratuito da assinatura. O período para a avaliação gratuita começa a contar a partir do dia seguinte ao dia da realização da assinatura.", Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'default'     => '0',
					'custom_attributes' => array(
                        'step' 	=> '1',
                            'min'	=> '1',
                            'max'   => '365'
                        )
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
					'title'             => __( 'Baixar Logs', Gerencianet_I18n::getTextDomain() ),
					'type'              => 'button',
					'description'       => __( 'Clique para baixar os logs de emissão de Assinaturas via Cartão.', Gerencianet_I18n::getTextDomain() ),
					'default'           => __( 'Baixar Logs', Gerencianet_I18n::getTextDomain() ),
					'custom_attributes' => array(
						'onclick' => 'location.href="' . admin_url('admin-post.php?action=gn_download_logs&log=wc_gerencianet_assinaturas_cartao') . '";',
					),
				),
			);
		}

		public function process_admin_options() {
			// Chama o método pai para processar as opções padrão
			parent::process_admin_options();
		
			if ($this->get_option('gn_client_secret_production')) {
				$this->update_option('gn_client_secret_production', Efi_Cypher::encrypt_data($this->get_option('gn_client_secret_production')));
			}
		
			if ($this->get_option('gn_client_secret_homologation')) {
				$this->update_option('gn_client_secret_homologation', Efi_Cypher::encrypt_data($this->get_option('gn_client_secret_homologation')));
			}
		}

		public function payment_fields() {

			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description ) );
			}

			$is_sandbox = $this->get_option( 'gn_sandbox' ) == 'yes' ? true : false;
			if ( $is_sandbox ) {
				$sandboxWarn = '<div class="warning-payment" id="wc-gerencianet-messages-sandbox">
                                    <div class="woocommerce-error">' . __( 'O modo Sandbox está ativo. As cobranças emitidas não serão válidas.', Gerencianet_I18n::getTextDomain() ) . '</div>
                                </div>';
				echo wpautop( wp_kses_post( $sandboxWarn ) );
			}

			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent; border: none;">';

			?>
				<div id="gn_row_cpf_birth">
					<div class="form-row form-row-first" id="gn_field_cpf_cnpj">
						<label>CPF/CNPJ <span class="required">*</span></label>
						<input id="gn_cartao_cpf_cnpj" class="input-text" inputmode="numeric" name="gn_cartao_cpf_cnpj" type="text" placeholder="___.___.___-__" autocomplete="off" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
					</div>
					<div class="form-row form-row-last" id="gn_field_birth">
						<label><?php echo __( 'Data de Nasc.', Gerencianet_I18n::getTextDomain() ); ?><span class="required">*</span></label>
						<input id="gn_cartao_birth" class="input-text" inputmode="numeric" name="gn_cartao_birth" placeholder="__/__/____" type="text" autocomplete="off">
					</div>
				</div>
				<div class="form-row form-row-wide"><label><?php echo __( 'Número do Cartão', Gerencianet_I18n::getTextDomain() ); ?><span class="required">*</span></label>
					<input id="gn_cartao_number" class="input-text" inputmode="numeric" name="gn_cartao_number" type="text" autocomplete="off" style="width: 100%;">
				</div>
				<div class="form-row form-row-first"><label>CVV<span class="required">*</span></label>
					<input id="gn_cartao_cvv" class="input-text" inputmode="numeric" name="gn_cartao_cvv" type="text" autocomplete="off">
				</div>
				<div class="form-row form-row-last"><label><?php echo __( 'Expiração (MM/AA)', Gerencianet_I18n::getTextDomain() ); ?><span class="required">*</span></label>
					<input id="gn_cartao_expiration" class="input-text" inputmode="numeric" placeholder="__/__" name="gn_cartao_expiration" type="text" autocomplete="off">
				</div>
				<input id="gn_payment_token" name="gn_payment_token" type="hidden">
				<input id="gn_payment_total" name="gn_payment_total" type="hidden" value="<?php echo WC()->cart->total; ?>">
				<script src="<?php echo plugins_url( '../../assets/js/vanilla-masker.min.js', plugin_dir_path( __FILE__ ) ); ?>"></script>
				<script>
					var options = {
						payeeCode: '<?php echo esc_html($this->get_option( 'gn_payee_code' )); ?>',
						enviroment:'<?php echo esc_html($this->get_option( 'gn_sandbox' )) == 'yes'? 'sandbox' : 'production'; ?>'
					}
				</script>
				<script type="module" src="<?php echo plugins_url( '../../assets/js/payment-token-efi.min.js', plugin_dir_path( __FILE__ ) ); ?>"></script>
				<script src="<?php echo plugins_url( '../../assets/js/scripts-cartao.js', plugin_dir_path( __FILE__ ) ); ?>"></script>
			</fieldset>
			<?php

		}

		public function payment_scripts() {

			if ( $this->enabled != 'yes' ) {
				return;
			}

			// if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
			// 	return;
			// }

			if ( is_account_page() ) {
				// Enfileira o script JavaScript
				wp_enqueue_script( 'gn_retry_assinatura', plugins_url( '../../assets/js/retry-assinatura.js', plugin_dir_path( __FILE__ ) ), array('jquery'), null, true );
				wp_enqueue_script( 'gn_payment_token', plugins_url( '../../assets/js/payment-token-efi.min.js', plugin_dir_path( __FILE__ ) ), array('jquery'), null, true );
				wp_enqueue_script( 'gn_vmasker', plugins_url( '../../assets/js/vanilla-masker.min.js', plugin_dir_path( __FILE__ ) ), array('jquery'), null, true );

				?>
					<script>
						var options = {
							payeeCode: '<?php echo esc_html($this->get_option( 'gn_payee_code' )); ?>',
							enviroment:'<?php echo esc_html($this->get_option( 'gn_sandbox' )) == 'yes'? 'sandbox' : 'production'; ?>',
						}
					</script>
				<?php
				
				// Passa a URL do AJAX para o script
				wp_localize_script( 'gn_retry_assinatura', 'retry_assinatura', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce('woocommerce_gerencianet_assinatura_retry'),
				));
			}

			wp_enqueue_script( 'gn-sweetalert', plugins_url( '../../assets/js/sweetalert.js', plugin_dir_path( __FILE__ ) ), '11.0.0', true );

		}

		public function validate_fields() {

			if ( empty( sanitize_text_field( $_POST['gn_cartao_cpf_cnpj'] ) ) ) {
				wc_add_notice( __( 'O CPF é obrigatório', Gerencianet_I18n::getTextDomain() ), 'error' );
				return false;
			} else {
				$cpf_cnpj = preg_replace( '/[^0-9]/', '', sanitize_text_field( $_POST['gn_cartao_cpf_cnpj'] ) );
				if ( strlen( $cpf_cnpj ) == 11 ) {
					// cpf
					return Gerencianet_Validate::cpf( $cpf_cnpj );
				} else {
					// cnpj
					return Gerencianet_Validate::cnpj( $cpf_cnpj );
				}
			}
			return true;
		}

		public function process_payment( $order_id ) {

			global $woocommerce;

			$order = wc_get_order( $order_id );
			$customer_id = $order->get_customer_id();

			// Salva os campos customizados
			update_user_meta( $customer_id, 'gn_billing_neighborhood', $_POST['gn_billing_neighborhood']);
			update_user_meta( $customer_id, 'gn_billing_number', $_POST['gn_billing_number']);

			$types    = array( 'line_item', 'fee', 'shipping', 'coupon' );
			$items    = array();
			$shipping = array();
			$discount = false;
			// get the Items
			foreach ( $order->get_items( $types ) as $item_id => $item ) {

				switch ( $item->get_type() ) {
					case 'fee':
						$newFee      = array(
							'name'   => __( 'Taxas', Gerencianet_I18n::getTextDomain() ),
							'amount' => 1,
							'value'  => (int)$this->gn_price_format($item->get_total()),
						);
						$items[]     = $newFee;
						break;
					case 'shipping':
						if ( $item->get_total() > 0 ) {
							$shipping[] = array(
								'name'  => __( 'Frete', Gerencianet_I18n::getTextDomain()),
								'value' => (int)$this->gn_price_format($item->get_total()),
							);
						}
						break;
					case 'coupon':
						$newDiscount = array(
							'type'  => 'currency',
							'value' => (int)$this->gn_price_format($item->get_discount()),
						);
						$discount    = $newDiscount;
						break;
					case 'line_item':
						// Definições importantes para criação do plano
						$product     = $item->get_product();
						$interval    = $product->get_meta('_gerencianet_interval');
						$repeats	 = $product->get_meta('_gerencianet_repeats');
						$namePlan    = $product->get_name();
						
						$product     = $item->get_product();
						$newItem     = array(
							'name'   => $namePlan,
							'amount' => $item->get_quantity(),
							'value'  => (int)$this->gn_price_format($product->get_price()),
						);

						$orderTotal += (int)$this->gn_price_format($product->get_price() * $item->get_quantity());
						$items[]     = $newItem;
						break;
					default:
						$product     = $item->get_product();
						$newItem     = array(
							'name'   => $item->get_name(),
							'amount' => $item->get_quantity(),
							'value'  => (int)$this->gn_price_format($product->get_price()),
						);
						$items[]     = $newItem;
						break;
				}
			}

			if($order->get_total_tax()>0){
				$newItem     = array(
					'name'   => 'Taxas',
					'amount' => 1,
					'value'  => (int)$this->gn_price_format($order->get_total_tax()),
				);
				array_push($items, $newItem);
			}

			$cpf_cnpj = preg_replace( '/[^0-9]/', '', sanitize_text_field( $_POST['gn_cartao_cpf_cnpj'] ) );
			if ( Gerencianet_Validate::cpf( $cpf_cnpj ) ) {
				$customer = array(
					'name'         => $order->get_formatted_billing_full_name(),
					'cpf'          => $cpf_cnpj,
					'phone_number' => preg_replace( '/[^0-9]/', '', $order->get_billing_phone() ),
				);
			} else {
				$customer = array(
					'juridical_person' => array(
						'corporate_name' => $order->get_billing_company() != '' ? $order->get_billing_company() : $order->get_formatted_billing_full_name(),
						'cnpj'           => $cpf_cnpj,
					),
					'phone_number'     => preg_replace( '/[^0-9]/', '', $order->get_billing_phone() ),
				);
			}
			$customer['email'] = $order->get_billing_email();
			$birth             = explode( '/', sanitize_text_field( $_POST['gn_cartao_birth'] ) );
			$customer['birth'] = $birth[2] . '-' . $birth[1] . '-' . $birth[0];

			$paymentToken = sanitize_text_field( $_POST['gn_payment_token'] );

			$number = sanitize_text_field( $_POST['billing_number'] ) != null ? sanitize_text_field( $_POST['billing_number'] ) : sanitize_text_field( $_POST['gn_billing_number'] );

			$billingAddress = array(
				'street'       => $order->get_billing_address_1(),
				'number'       => $number,
				'neighborhood' => sanitize_text_field( $_POST['billing_neighborhood'] ) != null ? sanitize_text_field( $_POST['billing_neighborhood'] ) : sanitize_text_field( $_POST['gn_billing_neighborhood'] ),
				'zipcode'      => str_replace( '-', '', $order->get_billing_postcode() ),
				'city'         => $order->get_billing_city(),
				'state'        => $order->get_billing_state(),
			);

			$trial_days = 0;

			if ( $this->get_option( 'gn_trial_days' ) != '' && $this->get_option( 'gn_trial_days' ) != '0' ) {
				$trial_days = intval($this->get_option( 'gn_trial_days' ));
			}

			try {
                $response = $this->gerencianetSDK->create_plan(GERENCIANET_ASSINATURAS_CARTAO_ID, $namePlan, intval($interval), intval($repeats) );
				$plan 	  = json_decode( $response, true );
				$plan_id  = $plan['data']['plan_id'];

				$response = $this->gerencianetSDK->create_subscription_card( $plan_id, $order_id, $items, $shipping, strtolower( $woocommerce->api_request_url( GERENCIANET_ASSINATURAS_CARTAO_ID ) ), $customer, $paymentToken, $billingAddress, $trial_days, $discount );
				$charge   = json_decode( $response, true );

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

				$order->update_status( 'pending-payment' );
				wc_reduce_stock_levels( $order_id );
				$woocommerce->cart->empty_cart();

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);

			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
				return;
			}
		}

		public function webhook() {
			header( 'HTTP/1.0 200 OK' );
			$post_notification = sanitize_text_field( $_POST['notification'] );
			if ( isset( $post_notification ) && ! empty( $post_notification ) ) {

				$notification_response = $this->gerencianetSDK->getNotification( GERENCIANET_ASSINATURAS_CARTAO_ID, $post_notification );
				$notification = json_decode($notification_response);

				Gerencianet_Hpos::update_meta( sanitize_text_field($notification->data[0]->custom_id), '_notification_subscription', $notification_response);

				if ( $notification->code == 200 ) {

					foreach ( $notification->data as $notification_data ) {
						$orderIdFromNotification     = sanitize_text_field( $notification_data->custom_id );
						$orderStatusFromNotification = sanitize_text_field( $notification_data->status->current );
					}

					$order = wc_get_order( $orderIdFromNotification );

					switch ( $orderStatusFromNotification ) {
						case 'paid':
							$order->update_status($this->gn_order_status_after_payment);
							$order->payment_complete();
							break;
						case 'unpaid':
							$order->update_status( 'failed' );
							break;
						case 'refunded':
							$order->update_status( 'refund' );
							break;
						case 'contested':
							$order->update_status( 'failed' );
							break;
						case 'canceled':
							$order->update_status( 'cancelled' );
							break;
						default:
							// no action
							break;
					}
				} else {
					gn_log( 'Notification Request : FAIL ', GERENCIANET_ASSINATURAS_CARTAO_ID);
					gn_log( $notification, GERENCIANET_ASSINATURAS_CARTAO_ID);
					
				}

				exit();

			} else {
				wp_die( __( 'Request Failure', Gerencianet_I18n::getTextDomain() ) );
			}
		}

		public static function getMethodId() {
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
			Gerencianet_Hpos::update_meta($subscription_id, '_status', $this->verifica_status($initial_status));
			Gerencianet_Hpos::update_meta($subscription_id, '_id_da_assinatura', $id_assinatura); // Usando o próprio ID como exemplo
			Gerencianet_Hpos::update_meta($subscription_id, '_plano', $plano);
			Gerencianet_Hpos::update_meta($subscription_id, '_periodo_de_teste', $periodo_de_teste);
			Gerencianet_Hpos::update_meta($subscription_id, '_data_de_inicio', $data_inicio); // Data atual
			Gerencianet_Hpos::update_meta($subscription_id, '_data_fim', $data_fim); // Fim da assinatura em um ano
			Gerencianet_Hpos::update_meta($subscription_id, '_pedido_associado', $order_id); // ID do pedido associado, exemplo fixo
			Gerencianet_Hpos::update_meta($subscription_id, '_subs_payment_method', GERENCIANET_ASSINATURAS_CARTAO_ID);

			// Retornar o ID da nova assinatura para referência
			return $subscription_id;
		}

		public function verifica_status($status)
		{
			$newStatus['new'] = '<mark class="order-status status-pending tips"><span>Aguardando Pagamento</span></mark>';
			$newStatus['active'] = '<mark class="order-status status-processing tips"><span>Ativa</span></mark>';
			$newStatus['canceled'] = '<mark class="order-status status-failed tips"><span>Cancelada</span></mark>';
			$newStatus['expired'] = '<mark class="order-status status-completed tips"><span>Expirada</span></mark>';

			return $newStatus[$status];
		}

		public static function assinatura_retry(){
			$order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';
			$payment_token = isset($_POST['payment_token']) ? sanitize_text_field($_POST['payment_token']) : '';

			try {
				$gerencianetSDK = new Gerencianet_Integration();
				$response = $gerencianetSDK->card_retry($order_id, $payment_token, GERENCIANET_ASSINATURAS_CARTAO_ID);
				$charge = json_decode( $response, true );

				if ( isset( $charge['data']['status'] ) ) {
					Gerencianet_Hpos::update_meta( $order_id, '_gn_status_card', $charge['data']['status'] );
					Gerencianet_Hpos::update_meta( $order_id, '_gn_can_retry', "no");
					Gerencianet_Hpos::update_meta( $order_id, '_gn_retry_body', "");
				}
			} catch (Exception $e) {
				gn_log($e, GERENCIANET_ASSINATURAS_CARTAO_ID);
			}
		}

		public function validate_gn_client_id_production_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Id_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Insira o Client_Id de Produção.' );
        		$this->update_option( 'gn_credit_card', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        
        	return $value;
        }
        
        public function validate_gn_client_secret_production_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Secret_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Insira o Client_Secret de Produção.' );
        		$this->update_option( 'gn_credit_card', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        
        	return $value;
        }
        
        public function validate_gn_client_id_homologation_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Id_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Insira o Client_Id de Homologação.' );
        		$this->update_option( 'gn_credit_card', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        	return $value;
        }
        
        public function validate_gn_client_secret_homologation_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Secret_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Insira o Client_Secret de Homologação.' );
        		$this->update_option( 'gn_credit_card', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        	return $value;
        }

	}
}
