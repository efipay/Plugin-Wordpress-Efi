<?php

use GN_Includes\Gerencianet_I18n;

function init_gerencianet_open_finance() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	};

	class WC_Gerencianet_Open_Finance extends WC_Payment_Gateway {

		// payment gateway plugin ID
		public $id;
		public $has_fields;
		public $method_title;
		public $method_description;
		public $supports;
		private $gerencianetSDK;
		private $gn_open_finance_document;
		private $gn_open_finance_name;
		private $gn_open_finance_account;
		private $gn_certificate_file;
		private $gn_open_finance_mtls;
		private $gn_sandbox;

		public function __construct() {
			$this->id                 = GERENCIANET_OPEN_FINANCE_ID; // payment gateway plugin ID
			$this->has_fields         = true; // custom form
			$this->method_title       = __( 'Efí - Open Finance', Gerencianet_I18n::getTextDomain() );
			$this->method_description = __( 'Com a Efí você pode receber pagamentos via Open Finance', Gerencianet_I18n::getTextDomain() );

			$this->supports = array(
				'products', 'refunds'
			);
			

			$this->init_form_fields();

			$this->gerencianetSDK = new Gerencianet_Integration();

			// Load the settings.
			$this->init_settings();
			$this->title       = __( 'Open Finance', Gerencianet_I18n::getTextDomain() );
			$this->description = __( 'Pagando via Open Finance, seu pagamento será confirmado em poucos segundos.', Gerencianet_I18n::getTextDomain() );
			$this->enabled     = $this->get_option( 'gn_open_finance' );

			$this->gn_open_finance_document			= sanitize_text_field( $this->get_option( 'gn_open_finance_document'));
			$this->gn_open_finance_name	    		= sanitize_text_field( $this->get_option( 'gn_open_finance_name' ) );
			$this->gn_open_finance_account     		= sanitize_text_field( $this->get_option( 'gn_open_finance_account' ) );
			$this->gn_certificate_file              = sanitize_text_field( $this->get_option( 'gn_certificate_file' ) );
			$this->gn_open_finance_mtls             = sanitize_text_field( $this->get_option( 'gn_open_finance_mtls  ' ) );
			$this->gn_sandbox              			= sanitize_text_field( $this->get_option( 'gn_sandbox' ) );

			// // This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . GERENCIANET_OPEN_FINANCE_ID, array( $this, 'process_admin_options' ));
			add_action( 'woocommerce_update_options_payment_gateways_' . GERENCIANET_OPEN_FINANCE_ID, array( $this, 'saveOpenFinanceCertificate' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . GERENCIANET_OPEN_FINANCE_ID, array( $this, 'registerWebhookOpenFinance' ) );

			add_action( 'woocommerce_api_' . strtolower( GERENCIANET_OPEN_FINANCE_ID.'-order-received' ), array( $this, 'redirectPage' ) );
			add_action( 'woocommerce_api_' . strtolower( GERENCIANET_OPEN_FINANCE_ID ), array( $this, 'webhook' ) );

			// This hook add the "view payment methods" button
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_view_payment_methods' ) );
			
	
	        
	        add_filter( 'the_title', array($this, 'title_order_received'), 10, 2 );
	        add_filter('woocommerce_thankyou_order_received_text', array($this, 'change_order_received_text'), 10, 2 );

		}
		
        public function change_order_received_text( $str, $order ) {
            if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && isset($_GET['erro'])) {
        		$str ="<b>Por favor verifique seus dados e tente novamente.</b>";
        	}
        	return $str;
        }
		
 
       public function title_order_received( $title, $id ) {
        	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && get_the_ID() === $id && isset($_GET['erro'])) {
        		$title = "<b>Falha ao concluir pagamento: </b>".$_GET['erro'];
        	}
        	return $title;
        }

		public function redirectPage() {
		    $args = array(
				'limit'        => -1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'meta_key'     => '_gn_of_identificador_pagamento',
				'meta_compare' => '=',
				'meta_value'   => sanitize_text_field($_GET['identificadorPagamento']),
			);
			
			$params = '';
			foreach($_GET as $key => $param){
                $params = $params.'&'. $key . '=' . urlencode($param);
            }

			// Busca pedidos
			$orders = wc_get_orders( $args );
		   
		   if(isset($_GET['erro'])){
		        wp_redirect($this->get_return_url( $orders[0] ).$params);     
		   }else{
		       gn_log('sem erro');
		        wp_redirect($this->get_return_url( $orders[0] ));   
		   }
		   
		}
		
		public function failedPage(){
		    include dirname( __file__ ) . '/../templates/failed-page.php';
		}
		

		public function saveOpenFinanceCertificate() {
			$file_name = $_FILES['woocommerce_WC_Gerencianet_Open_Finance_gn_certificate_file']['name'];
			
			if (empty( $_FILES['woocommerce_WC_Gerencianet_Open_Finance_gn_certificate_file']['name'] ) ) {
                WC_Admin_Settings::add_error("É necessário realizar o upload do certificado para salvar as configurações.");
                $this->update_option( 'gn_open_finance', 'no' );
                return;
            }
            
            if ( $_FILES['woocommerce_WC_Gerencianet_Open_Finance_gn_certificate_file']['error'] != 0 ) {
				return;
			}
			
			// get the file extension
			$fileExt       = explode( '.', $file_name );
			$fileActualExt = strtolower( end( $fileExt ) );
			$file_read = null;
			
			switch ($fileActualExt) {
				case 'pem':
					if ( ! $file_read = file_get_contents( $_FILES['woocommerce_WC_Gerencianet_Open_Finance_gn_certificate_file']['tmp_name'] ) ) { // Pega o conteúdo do arquivo
						WC_Admin_Settings::add_error('Falha ao ler arquivo o Certificado Open Finance!');
						$this->update_option( 'gn_open_finance', 'no' );
						return;
					}
					break;
				case 'p12':
					if ( ! $cert_file_p12 = file_get_contents( $_FILES['woocommerce_WC_Gerencianet_Open_Finance_gn_certificate_file']['tmp_name'] ) ) { // Pega o conteúdo do arquivo
						WC_Admin_Settings::add_error('Falha ao ler arquivo o Certificado Open Finance!');
						$this->update_option( 'gn_open_finance', 'no' );
						return;
					}
					if ( ! openssl_pkcs12_read( $cert_file_p12, $cert_info_pem, '' ) ) { // Converte o conteúdo para .pem
						WC_Admin_Settings::add_error('Falha ao converter o arquivo .p12!');
						$this->update_option( 'gn_open_finance', 'no' );
						return;
					}
					$file_read  = "";
					$file_read .= $cert_info_pem['cert'];
					$file_read .= "Key Attributes: <No Attributes>\n";
					$file_read .= $cert_info_pem['pkey'];
					break;
				default:
					WC_Admin_Settings::add_error('Certificado inválido!');
					$this->update_option( 'gn_open_finance', 'no' );
					return;
			}
			if ( isset( $file_read ) ) {
				$this->update_option( 'gn_certificate_file_name', str_replace('p12', 'pem', $file_name) );
				$this->update_option( 'gn_certificate_file', $file_read );
			}

		}

		public function init_form_fields() {
			$certificateLabel = $this->get_option( 'gn_certificate_file' ) != '' ? 'Certificado salvo:<code>'.$this->get_option( 'gn_certificate_file_name' ).'</code>' : 'Nenhum certificado salvo';

			$this->form_fields = array(
				'gn_api_section'                => array(
					'title'       => __( 'Credenciais Efí', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'title',
					'description' => __( "<a href='https://gerencianet.com.br/artigo/como-obter-chaves-client-id-e-client-secret-na-api/#versao-7' target='_blank'>Clique aqui para obter seu Client_id e Client_secret! </a>", Gerencianet_I18n::getTextDomain() ),
				),
				'gn_client_id_production'       => array(
					'title'       => __( 'Client_Id Produção', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Por favor, insira seu Client_id. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain() ),
					'default'     => '',
				),
				'gn_client_secret_production'   => array(
					'title'       => __( 'Client_secret Produção', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Por favor, insira seu Client_secret. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain() ),
					'default'     => '',
				),
				'gn_client_id_homologation'     => array(
					'title'       => __( 'Client_id Homologação', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Por favor, insira seu Client_id de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain() ),
					'default'     => '',
				),
				'gn_client_secret_homologation' => array(
					'title'       => __( 'Client_secret Homologação', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Por favor, insira seu Client_secret de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain() ),
					'default'     => '',
				),
				'gn_certificate_file'    => array(
					'title'       => __( 'Certificado Open Finance', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'file',
					'description' => $certificateLabel,
					'desc_tip'    => false,
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
				'gn_open_finance'                        => array(
					'title'   => __( 'Open Finance', Gerencianet_I18n::getTextDomain() ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar Open Finance', Gerencianet_I18n::getTextDomain() ),
					'default' => 'no',
				),
				'gn_open_finance_document'      => array(
					'title'       => __( 'CPF/CNPJ', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Insira seu CPF/CNPJ (Apenas números).', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'placeholder' => '',
					'default'     => '',
				),
				'gn_open_finance_name'      => array(
					'title'       => __( 'Nome', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Insira seu nome completo', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'placeholder' => '',
					'default'     => '',
				),
				'gn_open_finance_account'      => array(
					'title'       => __( 'Número da Conta Efí', Gerencianet_I18n::getTextDomain() ),
					'type'        => 'text',
					'description' => __( 'Insira o número da sua conta com Dígito e sem o hífen.', Gerencianet_I18n::getTextDomain() ),
					'desc_tip'    => false,
					'placeholder' => '',
					'default'     => '',
				)
			);
		}

		public function payment_fields() {

			$participants = $this->gerencianetSDK->get_participants();
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
			<script>
				if('undefined'== typeof gnApiResponse) {
					var gnApiResponse = <?php echo $participants; ?>
				}
			</script>
			
			<link rel="stylesheet" type="text/css" href= <?php echo plugins_url( '../assets/css/open-finance.css', plugin_dir_path( __FILE__ ) ); ?> media="screen" />

			<div class="form-row form-row-wide" id="gn_field_open_finance_cpfcnpj">
				<label>CPF/CNPJ <span class="required">*</span></label>
				<input id="gn_open_finance_cpf_cnpj" class="input-text" inputmode="numeric" name="gn_open_finance_cpf_cnpj" type="text" placeholder="___.___.___-__" autocomplete="off" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
			</div>
			<div class="form-row form-row-wide gn-default-participant" id="gn_field_open_finance_participant">
			    <div class='gn-participant-item ' id='gn-default-participant' onClick="showParticipants(gnApiResponse)">
			        <img class='gn-participant-logo' id='gn-default-logo' src='<?php echo plugins_url( '../assets/img/question.png', plugin_dir_path( __FILE__ ) ); ?>' />
			        <img class='gn-participant-logo' id='gn-participant-logo' style="display:none;" src='<?php echo plugins_url( '../assets/img/question.png', plugin_dir_path( __FILE__ ) ); ?>' />
			        <label id='gn-default-name'>Escolha sua instituição</label>
			        <img class='gn-of-edit' src='<?php echo plugins_url( '../assets/img/edit.png', plugin_dir_path( __FILE__ ) ); ?>' />
			    </div>
			    <input id="gn_of_participant" name="gn_of_participant" type="hidden">
			</div>
			<div class="clear"></div>
			<script src="<?php echo plugins_url( '../assets/js/vanilla-masker.min.js', plugin_dir_path( __FILE__ ) ); ?>"></script>
			<script src="<?php echo plugins_url( '../assets/js/scripts-open-finance.js', plugin_dir_path( __FILE__ ) ); ?>"></script>
			</fieldset>
			<?php
		}

		public function validate_fields() {
			if ( empty( sanitize_text_field( $_POST['gn_open_finance_cpf_cnpj'] ) ) ) {
				wc_add_notice( __( 'O CPF é obrigatório', Gerencianet_I18n::getTextDomain() ), 'error' );
				return false;
			} else {
				$cpf_cnpj = sanitize_text_field( $_POST['gn_open_finance_cpf_cnpj'] );
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

			$types         = array( 'line_item', 'fee', 'shipping', 'coupon' );
			$value         = 0;
			$shippingTotal = 0;
			// get the Items
			foreach ( $order->get_items( $types ) as $item_id => $item ) {
				switch ( $item->get_type() ) {
					case 'fee':
						$value += $item->get_subtotal();
						break;
					case 'shipping':
						$value += $item->get_total();
						$shippingTotal += $item->get_total();
						break;
					case 'coupon':
						$value -= $item->get_discount();
						break;
					case 'line_item':
						$product = $item->get_product();
						$value += $item->get_quantity() * $product->get_price();
						break;
					default:
						$product = $item->get_product();
						$value  += ( $item->get_quantity() * $product->get_price() );
						break;
				}
			}

			$value += $order->get_total_tax();


			$cpf_cnpj = str_replace( '.', '', sanitize_text_field( $_POST['gn_open_finance_cpf_cnpj'] ) );
			$cpf_cnpj = str_replace( '-', '', $cpf_cnpj );
			$cpf_cnpj = str_replace( '/', '', $cpf_cnpj );
			if ( Gerencianet_Validate::cpf( $cpf_cnpj ) ) {
				$pagador = array(
					'idParticipante' => sanitize_text_field( $_POST['gn_of_participant'] ),
					'cpf'  => $cpf_cnpj,
				);
			} elseif ( Gerencianet_Validate::cnpj( $cpf_cnpj ) ) {
				$pagador = array(
					'idParticipante' => sanitize_text_field( $_POST['gn_of_participant'] ),
					'cnpj' => $cpf_cnpj,
				);
			} else {
				wc_add_notice( __( 'CPF/CNPJ inválido.', Gerencianet_I18n::getTextDomain() ), 'error' );
				return false;
			}

			$body = array(
				'pagador' => $pagador,
				'favorecido' => array(
					'contaBanco' => array(
						'codigoBanco' => '364',
						'agencia'	  => '0001',
						'documento'   => $this->get_option('gn_open_finance_document'),
						'nome'		  => $this->get_option('gn_open_finance_name'),
						'conta' 	  => $this->get_option('gn_open_finance_account'),
						'tipoConta'   => 'CACC'
					),
				),
				'valor' => sprintf( '%0.2f', $value ),
				'infoPagador' => 'Pagamento em '.get_bloginfo().' Numero do Pedido #'. $order_id,
			);

			try {
				$chargeResponse = $this->gerencianetSDK->pay_open_finance( $body );
				$charge         = json_decode( $chargeResponse, true );

				$order->update_status( 'pending-payment' );
				wc_reduce_stock_levels( $order_id );
				$woocommerce->cart->empty_cart();

    			gn_log('processando');
                gn_log($charge['identificadorPagamento']);
				update_post_meta( $order_id, '_gn_of_identificador_pagamento', $charge['identificadorPagamento'] );

				return array(
					'result'   => 'success',
					'redirect' => $charge['redirectURI']
				);
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
				return;
			}
		}

		public function webhook() {
			if(isset($_GET['hmac'])) {
				$hmac = $_GET['hmac'];
				$credential = md5($this->gn_sandbox  == 'yes' ? $this->get_option( 'gn_client_id_homologation') : $this->get_option( 'gn_client_id_production')); 
				if($hmac == $credential) {
					header( 'HTTP/1.0 200 OK' );
					$this->successful_webhook( file_get_contents( 'php://input' ) );
				} else {
					header('HTTP/1.1 403 Forbidden');
					gn_log("Não foi possível receber a notificação do Open Finance");
				}
			}
		}

		public function registerWebhookOpenFinance() {
			global $woocommerce;

			try {
				$redirectUrl = strtolower( $woocommerce->api_request_url( GERENCIANET_OPEN_FINANCE_ID.'-order-received') );
				$url      = strtolower( $woocommerce->api_request_url( GERENCIANET_OPEN_FINANCE_ID ));
				// hmac criado dentro da SDK
				$response = $this->gerencianetSDK->update_webhook_open_finance($url, $redirectUrl);
			} catch ( \Throwable $th ) {
				gn_log( $th );
			}
		}

		public static function getMethodId() {
			return self::$id;
		}

		public function successful_webhook( $posted ) {
			$payment = json_decode( $posted, true );
			// Percorre lista de notificações
			$args = array(
				'limit'        => -1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'meta_key'     => '_gn_of_identificador_pagamento',
				'meta_compare' => '=',
				'meta_value'   => sanitize_text_field( $payment['identificadorPagamento'] ),
			);

			// Busca pedidos
			$orders = wc_get_orders( $args );

			// Atualiza status
			foreach ( $orders as $order ) {

				if ( isset( $payment['identificadorPagamento'] ) && $payment['identificadorPagamento'] != '' && ( get_post_meta( $order->get_id(), '_gn_of_identificador_pagamento', true ) == $payment['identificadorPagamento'] ) ) {
					add_post_meta( intval( $order->get_id() ), '_gn_open_finance_E2EID', $payment['endToEndId'], true );

					gn_log( $payment);

					if ( isset( $payment['identificadorDevolucao'] ) && $payment['tipo'] == 'devolucao' && $payment['status'] == 'aceito') {
						add_post_meta( intval( $order->get_id() ), '_gn_open_finance_identificadorDevolucao', $payment['identificadorDevolucao'], true );
						$order->update_status( 'refunded' );
					} else if ($payment['status'] == 'expirado' && $payment['tipo'] == 'pagamento') {
						$order->update_status( 'failed' );
					}
					else if ($payment['status'] == 'rejeitado' && $payment['tipo'] == 'pagamento') {
						$order->update_status( 'failed' );
					}
					else if ($payment['status'] == 'aceito' && $payment['tipo'] == 'pagamento') {
						$order->update_status( 'processing' );
						$order->payment_complete();
					}
				}
			}

			exit();
		}
		
		public function process_refund( $order_id, $amount = null, $reason = '' ) {

			try {
				$res   = $this->gerencianetSDK->open_finance_refund( $order_id, $amount );
				$order = wc_get_order( $order_id );
				return $res;
			} catch ( Error $e ) {
				throw $e;
			}

		}

		public function add_view_payment_methods( $order ) {

			if ( $order->get_payment_method() != $this->id ) {
				return;
			}

			if ( get_post_meta( $order->get_id(), '_gn_pix_copy', true ) || get_post_meta( $order->get_id(), '_gn_pix_link', true ) ) {
				?>
					<style>

						.gngrid-container {
							display: grid;
							grid-template-columns: 50% 50%;
							overflow: hidden;
						}
						.gngrid-item {
							padding: 20px;
							font-size: 30px;
						}

						.gn-item-area {
							border-radius: 1.2rem;
							transition: transform 300ms ease;
						}

						.gn-item-area:hover {
							transform: scale(1.02);
						}

						.gn-btn{
							width: 100%;
							background: #EB6608 !important; 
							color:#ffff !important; 
							border:none;
							border-color: #EB6608 !important; 
							font-size: 1rem !important; 
							padding: 3px 5px 3px 5px !important;
						}
					</style>

			<script>

				function gncopy($field) {
					document.getElementById('gnpix').innerHTML = 'Copiado!';
					navigator.clipboard.writeText('<?php echo esc_html( get_post_meta( $order->get_id(), '_gn_pix_copy', true ) ); ?>');
					setTimeout(()=> {
							document.getElementById('gnpix').innerHTML = 'Copiar Pix Copia e Cola';
						},1000)
				}

				function viewGnInfos(params) {
					Swal.fire({
						title: 'Meios de Pagamento Disponíveis',
						icon: 'info',
						html: '<div class="gngrid-container"><div class="gngrid-item"><?php if ( get_post_meta( $order->get_id(), '_gn_pix_copy', true ) !== NULL ) { ?><div class="gn-item-area"><img style="width:150px;" src=" <?php echo esc_url(plugins_url( 'woo-gerencianet-official/assets/img/pix-copia.png' )); ?> " /><br> <a onclick="gncopy(2)" id="gnpix" class="button gn-btn">Copiar Pix Copia e Cola</a> </div><?php }?></div><div class="gn-item-area"><?php if ( get_post_meta( $order->get_id(), '_gn_pix_link', true ) !== NULL ) { ?> <div class="gngrid-item"><img style="width:150px;" src=" <?php echo esc_url(plugins_url( 'woo-gerencianet-official/assets/img/boleto-online.png' )); ?> " /><br><a href=" <?php echo esc_url(get_post_meta( $order->get_id(), '_gn_pix_link', true )); ?>  " target="_blank" class="button gn-btn">Acessar Pix Online</a></div> <?php }?> </div></div>',
						showCloseButton: true,
						showCancelButton: false,
						showConfirmButton: false
					})
				}
			</script>
			<div class="gn_payment_methods">
				<a onclick="viewGnInfos()" style="background: #EB6608; color:#ffff; border:none;" class="button">
					<span class="dashicons dashicons-visibility" style="margin-top:4px;"></span>
					<?php echo __( 'Visualizar Métodos de Pagamento', Gerencianet_I18n::getTextDomain() ); ?>
				</a>
			</div>

				<?php
			}
		}
	
	    public function validate_gn_client_id_production_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Id_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Você não inseriu o Client_Id de Produção corretamente.' );
        		$this->update_option( 'gn_open_finance', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        
        	return $value;
        }
        
        public function validate_gn_client_secret_production_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Secret_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Você não inseriu o Client_Secret de Produção corretamente.' );
        		$this->update_option( 'gn_open_finance', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        
        	return $value;
        }
        
        public function validate_gn_client_id_homologation_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Id_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Você não inseriu o Client_Id de Homologação corretamente.' );
        		$this->update_option( 'gn_open_finance', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        	return $value;
        }
        
        public function validate_gn_client_secret_homologation_field( $key, $value ) {
        	if ( ! preg_match( '/^Client_Secret_[a-zA-Z0-9]{40}$/', $value ) ) {
        		WC_Admin_Settings::add_error( 'Você não inseriu o Client_Secret de Homologação corretamente.' );
        		$this->update_option( 'gn_open_finance', 'no' );
        		$value = ''; // empty it because it is not correct
        	}
        	return $value;
        }
        
        public function validate_gn_open_finance_document_field( $key, $value ){
            $response = true;
            if ( empty( $value ) ){
				WC_Admin_Settings::add_error( 'O CPJ/CNPJ é obrigatório.' );
				$this->update_option( 'gn_open_finance', 'no' );
				return false;
			} else {
				$cpf_cnpj = $value;
				if ( strlen( $cpf_cnpj ) == 11 ) {
					$response = Gerencianet_Validate::cpf( $cpf_cnpj );
				} else if ( strlen( $cpf_cnpj ) == 14 ) {
					// cnpj
					$response = Gerencianet_Validate::cnpj( $cpf_cnpj );
				} else {
				    $response = false;
				}
			}
			if(!$response) {
			    $value = '';
			    WC_Admin_Settings::add_error( 'O CPF/CNPJ informado é inválido.' );
			    $this->update_option( 'gn_open_finance', 'no' );
			}
			return $value;
        }
        
        public function validate_gn_open_finance_account_field( $key, $value ){
            $response = true;
            if ( empty( $value ) ){
				WC_Admin_Settings::add_error( 'O número da conta é obrigatório' );
				$this->update_option( 'gn_open_finance', 'no' );
				return false;
			} else if(!is_numeric($value)) {
			    WC_Admin_Settings::add_error( 'O número da conta deve conter apenas números.' );
			    $this->update_option( 'gn_open_finance', 'no' );
			    $value = '';
			    return false;
			}
			return $value;
        }
	}
}
