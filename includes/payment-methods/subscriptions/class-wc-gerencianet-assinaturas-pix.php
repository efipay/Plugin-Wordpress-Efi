<?php

use GN_Includes\Gerencianet_I18n;
use Endroid\QrCode\QrCode;

function init_gerencianet_assinaturas_pix()
{
	if (! class_exists('WC_Payment_Gateway')) {
		return;
	};

	class WC_Gerencianet_Assinaturas_Pix extends WC_Payment_Gateway
	{

		// payment gateway plugin ID
		public $id;
		public $has_fields;
		public $method_title;
		public $method_description;
		public $supports;
		// evitando falhas
		public $gerencianetSDK;
		public $gn_pix_key;
		public $gn_pix_account;
		public $gn_certificate_file;
		public $gn_pix_discount;
		public $gn_pix_discount_shipping;
		public $gn_pix_number_hours;
		public $gn_pix_mtls;
		public $gn_sandbox;
		public $gn_order_status_after_payment;

		public function __construct()
		{
			$this->id                 = GERENCIANET_ASSINATURAS_PIX_ID; // payment gateway plugin ID
			$this->has_fields         = true; // custom form
			$this->method_title       = __('Efí - Assinaturas via Pix', Gerencianet_I18n::getTextDomain());
			$this->method_description = __('Com a Efí você pode receber pagamentos recorrentes via Pix', Gerencianet_I18n::getTextDomain());

			$this->supports = array(
				'products',
			);

			$this->init_form_fields();

			$this->gerencianetSDK = new Gerencianet_Integration();

			// Load the settings.
			$this->init_settings();
			$this->title       = __('Assinatura - Pix', Gerencianet_I18n::getTextDomain());
			$this->description = __('Pagando via Pix, seu pagamento será confirmado em poucos segundos.', Gerencianet_I18n::getTextDomain());
			$this->enabled     = $this->get_option('gn_pix');

			$this->gn_pix_key                    = sanitize_text_field($this->get_option('gn_pix_key'));
			$this->gn_pix_account                = sanitize_text_field($this->get_option('gn_pix_account'));
			$this->gn_certificate_file           = sanitize_text_field($this->get_option('gn_certificate_file'));
			$this->gn_pix_discount          	 = sanitize_text_field($this->get_option('gn_pix_discount'));
			$this->gn_pix_discount_shipping 	 = sanitize_text_field($this->get_option('gn_pix_discount_shipping'));
			$this->gn_pix_number_hours      	 = sanitize_text_field($this->get_option('gn_pix_number_hours'));
			$this->gn_pix_mtls              	 = sanitize_text_field($this->get_option('gn_pix_mtls'));
			$this->gn_sandbox               	 = sanitize_text_field($this->get_option('gn_sandbox'));
			$this->gn_order_status_after_payment = sanitize_text_field($this->get_option('gn_order_status_after_payment'));

			// This action hook saves the settings
			add_action('woocommerce_update_options_payment_gateways_' . GERENCIANET_ASSINATURAS_PIX_ID, array($this, 'process_admin_options'));
			add_action('woocommerce_update_options_payment_gateways_' . GERENCIANET_ASSINATURAS_PIX_ID, array($this, 'savePixCertificate'));
			add_action('woocommerce_update_options_payment_gateways_' . GERENCIANET_ASSINATURAS_PIX_ID, array($this, 'registerWebhook'));

			add_action('woocommerce_api_' . strtolower(GERENCIANET_ASSINATURAS_PIX_ID), array($this, 'webhook'));

			add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_view_payment_methods'));
		}

		public function savePixCertificate()
		{
			$file_name = $_FILES['woocommerce_wc_gerencianet_assinaturas_pix_gn_certificate_file']['name'];

			if ($_FILES['woocommerce_wc_gerencianet_assinaturas_pix_gn_certificate_file']['error'] != 0) {
				return;
			}

			// get the file extension
			$fileExt       = explode('.', $file_name);
			$fileActualExt = strtolower(end($fileExt));
			$file_read = null;

			switch ($fileActualExt) {
				case 'pem':
					if (! $file_read = file_get_contents($_FILES['woocommerce_wc_gerencianet_assinaturas_pix_gn_certificate_file']['tmp_name'])) { // Pega o conteúdo do arquivo
						echo '<div class="error"><p><strong> Falha ao ler arquivo o Certificado Pix! </strong></div>';
						return;
					}
					break;
				case 'p12':
					if (! $cert_file_p12 = file_get_contents($_FILES['woocommerce_wc_gerencianet_assinaturas_pix_gn_certificate_file']['tmp_name'])) { // Pega o conteúdo do arquivo
						echo '<div class="error"><p><strong> Falha ao ler arquivo o Certificado Pix! </strong></div>';
						return;
					}
					if (! openssl_pkcs12_read($cert_file_p12, $cert_info_pem, '')) { // Converte o conteúdo para .pem
						echo '<div class="error"><p><strong> Falha ao converter o arquivo .p12! </strong></div>';
						return;
					}
					$file_read  = "";
					$file_read .= $cert_info_pem['cert'];
					$file_read .= "Key Attributes: <No Attributes>\n";
					$file_read .= $cert_info_pem['pkey'];
					break;
				default:
					echo '<div class="error"><p><strong> Certificado Pix inválido! </strong></div>';
					return;
			}
			if (isset($file_read)) {
				$this->update_option('gn_certificate_file_name', str_replace('p12', 'pem', $file_name));
				$this->update_option('gn_certificate_file', $file_read);
			} else {
				$this->update_option('gn_certificate_file_name', 'Nenhum certificado salvo');
			}
		}

		public function init_form_fields()
		{
			$certificateLabel = $this->get_option('gn_certificate_file') != '' ? 'Certificado salvo:<code>' . $this->get_option('gn_certificate_file_name') . '</code>' : 'Nenhum certificado salvo';

			$this->form_fields = array(
				'gn_api_section'                => array(
					'title'       => __('Credenciais Efí', Gerencianet_I18n::getTextDomain()),
					'type'        => 'title',
					'description' => __("<a href='https://gerencianet.com.br/artigo/como-obter-chaves-client-id-e-client-secret-na-api/#versao-7' target='_blank'>Clique aqui para obter seu Client_Id e Client_Secret! </a>", Gerencianet_I18n::getTextDomain()),
				),
				'gn_client_id_production'       => array(
					'title'       => __('Client_Id Produção', Gerencianet_I18n::getTextDomain()),
					'type'        => 'text',
					'description' => __('Por favor, insira seu Client_Id. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain()),
					'default'     => '',
				),
				'gn_client_secret_production'   => array(
					'title'       => __('Client_Secret Produção', Gerencianet_I18n::getTextDomain()),
					'type'        => 'text',
					'description' => __('Por favor, insira seu Client_Secret. Isso é necessário para receber o pagamento.', Gerencianet_I18n::getTextDomain()),
					'default'     => '',
				),
				'gn_client_id_homologation'     => array(
					'title'       => __('Client_Id Homologação', Gerencianet_I18n::getTextDomain()),
					'type'        => 'text',
					'description' => __('Por favor, insira seu Client_Id de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain()),
					'default'     => '',
				),
				'gn_client_secret_homologation' => array(
					'title'       => __('Client_Secret Homologação', Gerencianet_I18n::getTextDomain()),
					'type'        => 'text',
					'description' => __('Por favor, insira seu Client_Secret de Homologação. Isso é necessário para testar os pagamentos.', Gerencianet_I18n::getTextDomain()),
					'default'     => '',
				),
				'gn_sandbox_section'            => array(
					'title'       => __('Ambiente Sandbox', Gerencianet_I18n::getTextDomain()),
					'type'        => 'title',
					'description' => 'Habilite para usar o ambiente de testes da Efí. Nenhuma cobrança emitida nesse modo poderá ser paga.',
				),
				'gn_sandbox'                    => array(
					'title'   => __('Sandbox', Gerencianet_I18n::getTextDomain()),
					'type'    => 'checkbox',
					'label'   => __('Habilitar o ambiente sandbox', Gerencianet_I18n::getTextDomain()),
					'default' => 'no',
				),
				'gn_one_payment_section'        => array(
					'title' => __('Configurações de recebimento', Gerencianet_I18n::getTextDomain()),
					'type'  => 'title',
				),
				'gn_pix'                        => array(
					'title'   => __('Pix', Gerencianet_I18n::getTextDomain()),
					'type'    => 'checkbox',
					'label'   => __('Habilitar Pix', Gerencianet_I18n::getTextDomain()),
					'default' => 'no',
				),
				'gn_pix_key'                    => array(
					'title'       => __('Chave Pix', Gerencianet_I18n::getTextDomain()),
					'type'        => 'text',
					'description' => __('Insira sua chave Pix da Efí. Atenção, use uma chave Pix diferente da utilizada para o recebimento de cobranças via Pix sem assinatura.', Gerencianet_I18n::getTextDomain()),
					'desc_tip'    => false,
					'placeholder' => '',
					'default'     => '',
				),
				'gn_pix_account'      => array(
					'title'       => __('Número da Conta Efí', Gerencianet_I18n::getTextDomain()),
					'type'        => 'text',
					'description' => __('Insira o número da sua conta com Dígito e sem o hífen.', Gerencianet_I18n::getTextDomain()),
					'desc_tip'    => false,
					'placeholder' => '',
					'default'     => '',
				),
				'gn_certificate_file'                   => array(
					'title'       => __('Certificado Pix', Gerencianet_I18n::getTextDomain()),
					'type'        => 'file',
					'description' => $certificateLabel,
					'desc_tip'    => false,
				),
				'gn_pix_discount'               => array(
					'title'       => __('Desconto no Pix', Gerencianet_I18n::getTextDomain()),
					'type'        => 'number',
					'description' => __('Desconto a ser aplicado para pagamentos com Pix. (Deixe em branco ou como 0 para não aplicar desconto)', Gerencianet_I18n::getTextDomain()),
					'placeholder' => '0',
					'default'     => '0',
				),
				'gn_pix_discount_shipping'      => array(
					'title'       => __('Modo de desconto', Gerencianet_I18n::getTextDomain()),
					'type'        => 'select',
					'description' => __('Escolha como aplicar o desconto definido na opção anterior.', Gerencianet_I18n::getTextDomain()),
					'default'     => 'total',
					'options'     => array(
						'total'    => __('Aplicar desconto no valor total com Frete', Gerencianet_I18n::getTextDomain()),
						'products' => __('Aplicar desconto apenas no preço dos produtos', Gerencianet_I18n::getTextDomain()),
					),
				),
				'gn_pix_number_hours'           => array(
					'title'       => __('Expiração do pix', Gerencianet_I18n::getTextDomain()),
					'type'        => 'number',
					'description' => __('Em quantas horas o Pix expira depois de emitido', Gerencianet_I18n::getTextDomain()),
					'placeholder' => '0',
					'default'     => '24',
				),
				'gn_pix_mtls'                   => array(
					'title'       => __('Validar mTLS', Gerencianet_I18n::getTextDomain()),
					'type'        => 'checkbox',
					'description' => __('Entenda os riscos de não configurar o mTLS <a href="https://dev.gerencianet.com.br/docs/api-pix-endpoints#webhooks" target="_blank">clicando aqui.</a>', Gerencianet_I18n::getTextDomain()),
					'default'     => 'no',
				),
				'gn_order_status_after_payment' => array(
					'title'       => __('Status do pedido após pagamento', 'text-domain'),
					'type'        => 'select',
					'description' => __('Selecione o status do pedido após a confirmação do pagamento.', 'text-domain'),
					'desc_tip'    => true,
					'options'     => wc_get_order_statuses(), // Obtém os status de pedido disponíveis
					'default'     => 'wc-processing', // Define um status padrão, ex: 'wc-processing'
				),
				'webhook_button' => array(
					'title'             => __('Webhook', Gerencianet_I18n::getTextDomain()),
					'type'              => 'button',
					'description'       => __($this->get_option('webhook_status'), Gerencianet_I18n::getTextDomain()),
					'default'           => __('Cadastrar Webhook', Gerencianet_I18n::getTextDomain()),
					'custom_attributes' => array(
						'onclick' => 'location.href="' . admin_url('admin-post.php?action=register_webhook&method=' . GERENCIANET_ASSINATURAS_PIX_ID) . '";',
					),
				),
				'download_button' => array(
					'title'             => __('Baixar Logs', Gerencianet_I18n::getTextDomain()),
					'type'              => 'button',
					'description'       => __('Clique para baixar os logs de emissão de Assinaturas via Pix.', Gerencianet_I18n::getTextDomain()),
					'default'           => __('Baixar Logs', Gerencianet_I18n::getTextDomain()),
					'custom_attributes' => array(
						'onclick' => 'location.href="' . admin_url('admin-post.php?action=gn_download_logs&log=wc_gerencianet_assinaturas_pix') . '";',
					),
				),
			);
		}

		public function process_admin_options()
		{
			parent::process_admin_options();
		}

		public function payment_fields()
		{
			if ($this->description) {
				echo wpautop(wp_kses_post($this->description));
			}

			if (intval($this->get_option('gn_pix_discount')) > 0) {
				$discountMessage = '';
				if ($this->get_option('gn_pix_discount_shipping') == 'total') {
					$discountMessage = ' no valor total da compra (frete incluso)';
				} else {
					$discountMessage = ' no valor total dos produtos (frete não incluso)';
				}

				$discountWarn = '<div class="warning-payment" id="wc-gerencianet-messages-sandbox">
										<div class="woocommerce-info">' . esc_html($this->get_option('gn_pix_discount')) . '% de Desconto' . $discountMessage . '</div>
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
			<div class="form-row form-row-wide" id="gn_field_pix">
				<label>CPF/CNPJ <span class="required">*</span></label>
				<input id="gn_pix_cpf_cnpj" class="input-text" inputmode="numeric" name="gn_pix_cpf_cnpj" type="text" placeholder="___.___.___-__" autocomplete="off" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
			</div>
			<div class="clear"></div>
			<script src="<?php echo plugins_url('../../assets/js/vanilla-masker.min.js', plugin_dir_path(__FILE__)); ?>"></script>
			<script src="<?php echo plugins_url('../../assets/js/scripts-pix.js', plugin_dir_path(__FILE__)); ?>"></script>
			</fieldset>
<?php
		}

		public function validate_fields()
		{

			if (empty(sanitize_text_field($_POST['gn_pix_cpf_cnpj']))) {
				wc_add_notice(__('CPF/CNPJ é obrigatório!', Gerencianet_I18n::getTextDomain()), 'error');
				return false;
			} else {
				$cpf_cnpj = preg_replace('/[^0-9]/', '', sanitize_text_field($_POST['gn_pix_cpf_cnpj']));
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

		// Feito
		public function process_payment($order_id)
		{
			global $woocommerce;
			$order = wc_get_order($order_id);
			$types = array('line_item', 'fee', 'shipping', 'coupon');
			$value = 0;
			$shippingTotal = 0;
			$interval = 0;
			$repeats = 0;
			$recorrenciaTipo = '';
			$namePlan = '';

			$allProductNames = [];
			$currentLength = 0;
			$maxLength = 35;
			foreach ($order->get_items($types) as $item_id => $item) {
				switch ($item->get_type()) {
					case 'fee':
						$value += $item->get_total();
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
						$interval = intval($product->get_meta('_gerencianet_interval'));
						$repeats = intval($product->get_meta('_gerencianet_repeats'));
						// Constrói o nome do plano concatenando nomes dos produtos até 35 caracteres, sem cortar nomes no meio
						$productName = $product->get_name();
						$separator = empty($allProductNames) ? '' : ' | ';
						$nextLength = $currentLength + strlen($separator) + mb_strlen($productName);
						if ($nextLength <= $maxLength) {
							$allProductNames[] = $productName;
							$currentLength = $nextLength;
						} else {
							// Não adiciona mais nomes, mas se não adicionou nenhum, adiciona o primeiro cortado
							if (empty($allProductNames)) {
								$allProductNames[] = mb_substr($productName, 0, $maxLength - 3) . '...';
							} else {
								// Adiciona reticências ao final
								$allProductNames[count($allProductNames) - 1] .= '...';
							}
							// Para de adicionar nomes
							break 2;
						}
						$namePlan = implode(' | ', $allProductNames);
						$value += $item->get_quantity() * $product->get_price();
						// Definir tipo de recorrência baseado no meta do produto
						switch ($interval) {
							case 1:
								$recorrenciaTipo = 'MENSAL';
								break;
							case 2:
								$recorrenciaTipo = 'MENSAL';
								break;
							case 3:
								$recorrenciaTipo = 'TRIMESTRAL';
								break;
							case 4:
								$recorrenciaTipo = 'TRIMESTRAL';
								break;
							case 6:
								$recorrenciaTipo = 'SEMESTRAL';
								break;
							case 12:
								$recorrenciaTipo = 'ANUAL';
								break;
							default:
								echo "Opção inválida.";
								break;
						}
						break;
					default:
						$product = $item->get_product();
						$value += $item->get_quantity() * $product->get_price();
						break;
				}
			}

			if ($order->get_total_tax() > 0) {
				$value += $order->get_total_tax();
			}

			$discount = 0;

			if ($this->get_option('gn_pix_discount') != '' && $this->get_option('gn_pix_discount') != '0') {

				if ($this->get_option('gn_pix_discount_shipping') == 'total') {
					$discount = (($value) * (intval($this->get_option('gn_pix_discount')) / 100));
					$discountMessage = ' no valor total da compra';
				} else {
					$discount = (($value - $shippingTotal) * (intval($this->get_option('gn_pix_discount')) / 100));
					$discountMessage = ' no valor total dos produtos (frete não incluso)';
				}

				$order_item_id = wc_add_order_item(
					$order_id,
					array(
						'order_item_name' => $this->get_option('gn_pix_discount') . __('% de desconto no Pix') . $discountMessage,
						'order_item_type' => 'fee',
					)
				);
				if ($order_item_id) {
					wc_add_order_item_meta($order_item_id, '_fee_amount', -$discount, true);
					wc_add_order_item_meta($order_item_id, '_line_total', -$discount, true);
					$order->set_total($order->get_total() - ($discount));
					$order->save();
				}
			}

			$value -= $discount;

			$cpf_cnpj = str_replace('.', '', sanitize_text_field($_POST['gn_pix_cpf_cnpj']));
			$cpf_cnpj = str_replace('-', '', $cpf_cnpj);
			$cpf_cnpj = str_replace('/', '', $cpf_cnpj);

			if (Gerencianet_Validate::cpf($cpf_cnpj)) {
				$devedor = array(
					'nome' => $order->get_formatted_billing_full_name(),
					'cpf'  => $cpf_cnpj,
				);
			} elseif (Gerencianet_Validate::cnpj($cpf_cnpj)) {
				$devedor = array(
					'nome' => $order->get_billing_company() != '' ? $order->get_billing_company() : $order->get_formatted_billing_full_name(),
					'cnpj' => $cpf_cnpj,
				);
			} else {
				wc_add_notice(__('CPF/CNPJ inválido.', Gerencianet_I18n::getTextDomain()), 'error');
				return false;
			}

			$vinculo = array(
				'devedor' => $devedor,
				'objeto' => $namePlan,
				'contrato' => $order_id . '_' . time(),
			);

			// Calcula dataInicial como hoje + periodicidade
			$hoje = new DateTime();
			switch ($interval) {
				case 1:
					$hoje->add(new DateInterval('P1M'));
					break;
				case 2:
					$hoje->add(new DateInterval('P2M'));
					break;
				case 3:
					$hoje->add(new DateInterval('P3M'));
					break;
				case 4:
					$hoje->add(new DateInterval('P4M'));
					break;
				case 6:
					$hoje->add(new DateInterval('P6M'));
					break;
				case 12:
					$hoje->add(new DateInterval('P1Y'));
					break;
				default:
					// Se não reconhecido, mantém hoje
					break;
			}
			$dataInicial = $hoje->format('Y-m-d');
			$calendario = [
				'dataInicial' => $dataInicial,
				'periodicidade' => $recorrenciaTipo,
			];
			if ($repeats > 1) {
				// Calcula a data final conforme a periodicidade e repetições
				switch ($interval) {
					case 1:
						$intervalSpec = 'P' . ($repeats - 1) . 'M';
						break;
					case 2:
						$intervalSpec = 'P' . (2 * ($repeats - 1)) . 'M';
						break;
					case 3:
						$intervalSpec = 'P' . (3 * ($repeats - 1)) . 'M';
						break;
					case 4:
						$intervalSpec = 'P' . (4 * ($repeats - 1)) . 'M';
						break;
					case 6:
						$intervalSpec = 'P' . (6 * ($repeats - 1)) . 'M';
						break;
					case 12:
						$intervalSpec = 'P' . ($repeats - 1) . 'Y';
						break;
					default:
						$intervalSpec = null;
						break;
				}
				if ($intervalSpec) {
					$date = new DateTime();
					$date->add(new DateInterval($intervalSpec));

					// Adiciona o possível atraso total: (repeats - 1) * 7 dias
					$atrasoTotalDias = 7 * ($repeats - 1);
					$date->add(new DateInterval('P' . $atrasoTotalDias . 'D'));

					$calendario['dataFinal'] = $date->format('Y-m-d');
				}
			}

			$valor = array(
				'valorRec' => sprintf('%0.2f', $value),
			);



			try {
				$bodyCob = array(
					'calendario'     => array('expiracao' => intval($this->get_option('gn_pix_number_hours')) * 3600),
					'valor'          => array('original' => sprintf('%0.2f', $value)),
					'chave'          => $this->get_option('gn_pix_key'),
				);

				$bodyCob['devedor'] = $devedor;

				$bodyCob['infoAdicionais'] = [];

				if (get_bloginfo() != "") {
					$bodyCob['infoAdicionais'] = array(
						array(
							'nome'  => 'Pagamento em',
							'valor' => get_bloginfo(),
						)
					);
				}

				array_push($bodyCob['infoAdicionais'], array('nome'  => 'Numero do Pedido', 'valor' => '#' . $order_id));

				// Refazer tudo aqui
				$chargeCobResponse = $this->gerencianetSDK->pay_pix($bodyCob, GERENCIANET_ASSINATURAS_PIX_ID);
				$chargeCob = json_decode($chargeCobResponse, true);

				$txidCob = $chargeCob['txid'];

				$locationResponse = $this->gerencianetSDK->generate_location_rec();
				$location = json_decode($locationResponse, true);
				$bodyRec = array(
					'vinculo' => $vinculo,
					'calendario' => $calendario,
					'valor' => $valor,
					'loc' => $location['id'],
					'politicaRetentativa' => "PERMITE_3R_7D",
					'ativacao' => array(
						'dadosJornada' => array(
							'txid' => $txidCob,
						)
					)
				);

				$recResponse = $this->gerencianetSDK->pay_pix_subscription($bodyRec, $txidCob);

				$rec = json_decode($recResponse, true);

				$periodo_de_teste = "Não possui";

				$sub_id = $this->criar_assinatura($devedor['nome'], $rec['status'], $rec['idRec'], $namePlan, $periodo_de_teste, $dataInicial, $calendario['dataFinal'] ?? "Não possui", $order_id);

				Gerencianet_Hpos::update_meta($order_id, '_is_subscription', 'yes');
				Gerencianet_Hpos::update_meta($order_id, '_subscription_id', $sub_id);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_name_plan', $namePlan);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_repeats', $repeats);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_interval', $interval);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_data_inicial', $dataInicial);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_data_final', $calendario['dataFinal'] ?? "Não possui");
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_valor', sprintf('%0.2f', $value));

				Gerencianet_Hpos::update_meta($sub_id, '_gn_order_id', $order_id);

				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_txid_0', $txidCob);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_idRec', $rec['idRec']);

				$qrCode = new QrCode($rec['dadosQR']['pixCopiaECola']);
				$qrCode->setSize(300);
				$qrCode->setMargin(10);
				$qrCode->setEncoding('UTF-8');

				// $base64QrCode = base64_encode($qrCode->writeString());

				// $qrCodeUri = 'data:image/png;base64,' . $base64QrCode;

				$qrCodeUri = $qrCode->writeDataUri();

				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_qrcode', $qrCodeUri);
				Gerencianet_Hpos::update_meta($order_id, '_gn_pix_copy', $rec['dadosQR']['pixCopiaECola']);

				$order->update_status('pending-payment');
				wc_reduce_stock_levels($order_id);
				$woocommerce->cart->empty_cart();

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url($order),
				);
			} catch (Exception $e) {
				wc_add_notice($e->getMessage(), 'error');
				return;
			}
		}

		// Feito
		public function webhook()
		{
			if (isset($_GET['hmac']) && isset($_GET['type'])) {
				$received_hmac = $_GET['hmac'];

				// Determina se está em ambiente de homologação ou produção
				$client_secret = $this->gn_sandbox === 'yes'
					? $this->get_option('gn_client_secret_homologation')
					: $this->get_option('gn_client_secret_production');

				// Gera o HMAC esperado (últimos 8 caracteres do segredo + IP do servidor)
				$ip_address   = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
				$last_8_chars = substr($client_secret, -8);
				$expected_hmac = hash('sha256', $last_8_chars . $ip_address);

				// Valida o HMAC
				if (hash_equals($expected_hmac, $received_hmac)) {
					switch ($_GET['type']) {
						case 'cob':
							header('HTTP/1.0 200 OK');
							$this->successful_webhook_cob(file_get_contents('php://input'));
							break;
						case 'rec':
							header('HTTP/1.0 200 OK');
							$this->successful_webhook_rec(file_get_contents('php://input'));
							break;
						case 'cobr':
							header('HTTP/1.0 200 OK');
							$this->successful_webhook_cobr(file_get_contents('php://input'));
							break;
						default:
							gn_log('Tipo de notificação não suportado.', GERENCIANET_ASSINATURAS_PIX_ID);
							header('HTTP/1.1 400 Bad Request');
							break;
					}
				} else {
					header('HTTP/1.1 403 Forbidden');
					gn_log('Não foi possível receber a notificação do Pix. HMAC INVÁLIDO.', GERENCIANET_ASSINATURAS_PIX_ID);
				}
			} else {
				header('HTTP/1.1 400 Bad Request');
				gn_log('Chamada de webhook sem HMAC.', GERENCIANET_ASSINATURAS_PIX_ID);
			}
		}

		public function successful_webhook_cob($posted)
		{
			$pixs = json_decode($posted, true)['pix'];

			foreach ($pixs as $pix) {
				// Percorre lista de notificações
				$args = array(
					'limit'        => -1,
					'orderby'      => 'date',
					'order'        => 'DESC',
					'meta_key'     => '_gn_pix_txid_0',
					'meta_compare' => '=',
					'meta_value'   => sanitize_text_field($pix['txid']),
				);

				// Busca pedidos
				$orders = wc_get_orders($args);

				// Atualiza status
				foreach ($orders as $order) {

					if (isset($pix['txid']) && $pix['txid'] != '' && (Gerencianet_Hpos::get_meta($order->get_id(), '_gn_pix_txid_0', true) == $pix['txid'])) {

						$dataPagamento = new DateTime($pix['horario'], new DateTimeZone('UTC'));
						$dataPagamento->setTimezone(new DateTimeZone('America/Sao_Paulo'));
						$dataPagamentoFormatada = $dataPagamento->format('Y-m-d');
						// Atualiza E2EID
						Gerencianet_Hpos::update_meta(intval($order->get_id()), '_gn_pix_E2EID_0', $pix['endToEndId'], true);
						Gerencianet_Hpos::update_meta(intval($order->get_id()), '_gn_pix_E2EID_0_paid_at', $dataPagamentoFormatada, true);

						if (isset($pix['devolucoes']) && $pix['devolucoes'][0]['status'] == 'DEVOLVIDO') {
							// $order->update_status('refund'); // Implementação futura de reembolso
						} else {
							$order->update_status($this->gn_order_status_after_payment);
							$order->payment_complete();
						}
					}
				}
			}
			exit();
		}

		public function successful_webhook_cobr($posted)
		{
			$cobsr = json_decode($posted, true)['cobsr'];

			foreach ($cobsr as $cobr) {
				// Busca pedidos por idRec
				$args = array(
					'limit'        => -1,
					'orderby'      => 'date',
					'order'        => 'DESC',
					'meta_key'     => '_gn_pix_idRec',
					'meta_compare' => '=',
					'meta_value'   => sanitize_text_field($cobr['idRec']),
				);

				$orders = wc_get_orders($args);

				foreach ($orders as $order) {
					$order_id = intval($order->get_id());

					// Confirma se idRec bate com o pedido
					if (
						isset($cobr['idRec']) && $cobr['idRec'] !== '' &&
						(Gerencianet_Hpos::get_meta($order_id, '_gn_pix_idRec', true) == $cobr['idRec'])
					) {

						if ($cobr['status'] == 'CONCLUIDA') {



							$all_meta = $order->get_meta_data();

							$e2e_meta_keys = [];

							foreach ($all_meta as $meta) {
								$meta_data = $meta->get_data();
								$key = $meta_data['key'];
								if (
									strpos($key, '_gn_pix_E2EID_') === 0 &&
									strpos($key, '_paid_at') === false
								) {
									$e2e_meta_keys[] = $key;
								}
							}

							natsort($e2e_meta_keys);
							$max_index = -1;
							foreach ($e2e_meta_keys as $meta_key) {
								$parts = explode('_', $meta_key);
								$last = end($parts);
								if (is_numeric($last) && intval($last) > $max_index) {
									$max_index = intval($last);
								}
							}

							// Define o PRÓXIMO índice
							$next_index = $max_index + 1;
							$new_meta_key = '_gn_pix_E2EID_' . $next_index;


							Gerencianet_Hpos::update_meta($order_id, $new_meta_key, $cobr['pix'][0]['endToEndId'], true);

							$dataPagamento = new DateTime($cobr['pix'][0]['horario'], new DateTimeZone('UTC'));
							$dataPagamento->setTimezone(new DateTimeZone('America/Sao_Paulo'));
							$dataPagamentoFormatada = $dataPagamento->format('Y-m-d');

							Gerencianet_Hpos::update_meta($order_id, $new_meta_key . '_paid_at', $dataPagamentoFormatada, true);

							// Atualiza status do pedido
							$order->update_status($this->gn_order_status_after_payment);
							$order->payment_complete();

							$repeats = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_repeats', true);

							// Se for a última cobrança, não gera nova cobrança
							if (
								($repeats == 1) ||
								($repeats > 1 && ($next_index + 1) < $repeats)
							) {

								$idRec = $cobr['idRec'];
								$infoAdicional = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_name_plan', true);
								$dataInicial = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_data_inicial', true);
								$interval = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_interval', true);
								$pixValor = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_valor', true);

								$dataDeVencimento = new DateTime($dataInicial);


								if ($interval == 12) {
									$dataDeVencimento->add(new DateInterval('P' . $next_index . 'Y'));
								} else {
									$dataDeVencimento->add(new DateInterval('P' . ($next_index * $interval) . 'M'));
								}

								// Data do proximo pedido
								gn_log('Próxima data de vencimento: ' . $dataDeVencimento->format('Y-m-d'), GERENCIANET_ASSINATURAS_PIX_ID);

								$valor = array(
									'original' => sprintf('%0.2f', $pixValor),
								);

								$recebedor = array(
									'agencia' => '0001',
									'conta' => $this->get_option('gn_pix_account'),
									'tipoConta' => 'PAGAMENTO',
								);

								$bodyCobr = array(
									'idRec' => $idRec,
									'infoAdicional' => $infoAdicional,
									'calendario' => array(
										'dataDeVencimento' => $dataDeVencimento->format('Y-m-d'),
									),
									'valor' => $valor,
									'recebedor' => $recebedor,
									'ajusteDiaUtil' => false
								);

								$repsonse = $this->gerencianetSDK->generate_cobr_to_pix_rec($bodyCobr);
								$cobrResponse = json_decode($repsonse, true);
							}
						} else if ($cobr['status'] !== 'CRIADA' && $cobr['status'] !== 'ATIVA') {
							$order->update_status('failed');
						}



						// Atualiza status do pedido caso tenha devolução
						// Ainda não há webhook para devoluções em cobr
						// if ( isset( $cobr['pix']['devolucoes'] ) && $cobr['pix']['devolucoes'][0]['status'] === 'DEVOLVIDO' ) {
						// 	$order->update_status( 'refund' );
						// } else {
						// }
					}
				}
			}

			exit();
		}

		public function successful_webhook_rec($posted)
		{
			$recs = json_decode($posted, true)['recs'];

			foreach ($recs as $rec) {
				// Busca pedidos por idRec
				$args = array(
					'limit'        => -1,
					'orderby'      => 'date',
					'order'        => 'DESC',
					'meta_key'     => '_gn_pix_idRec',
					'meta_compare' => '=',
					'meta_value'   => sanitize_text_field($rec['idRec']),
				);

				$orders = wc_get_orders($args);

				foreach ($orders as $order) {
					$order_id = intval($order->get_id());

					// Confirma se idRec bate com o pedido
					if (
						isset($rec['idRec']) && $rec['idRec'] !== '' &&
						(Gerencianet_Hpos::get_meta($order_id, '_gn_pix_idRec', true) == $rec['idRec'])
					) {

						if ($rec['status'] == 'APROVADA') {

							$idRec = $rec['idRec'];
							$infoAdicional = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_name_plan', true);
							$dataInicial = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_data_inicial', true);
							$repeats = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_repeats', true);
							$interval = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_interval', true);
							$pixValor = Gerencianet_Hpos::get_meta($order_id, '_gn_pix_valor', true);

							$dataDeVencimento = new DateTime($dataInicial);

							$valor = array(
								'original' => sprintf('%0.2f', $pixValor),
							);

							$recebedor = array(
								'agencia' => '0001',
								'conta' => $this->get_option('gn_pix_account'),
								'tipoConta' => 'PAGAMENTO',
							);

							$bodyCobr = array(
								'idRec' => $idRec,
								'infoAdicional' => $infoAdicional,
								'calendario' => array(
									'dataDeVencimento' => $dataDeVencimento->format('Y-m-d'),
								),
								'valor' => $valor,
								'recebedor' => $recebedor,
								'ajusteDiaUtil' => false
							);

							$response = $this->gerencianetSDK->generate_cobr_to_pix_rec($bodyCobr);
						} else if ($rec['status'] == 'CANCELADA') {
							$order->update_status('cancelled');
						} else if ($rec['status'] != 'CRIADA') {
							$order->update_status('failed');
						}
					}
				}
			}

			exit();
		}

		// Feito
		public function registerWebhook()
		{
			global $woocommerce;

			try {
				$pix_key  = $this->get_option('gn_pix_key');
				$url      = strtolower($woocommerce->api_request_url(GERENCIANET_ASSINATURAS_PIX_ID));
				$response = $this->gerencianetSDK->update_webhooks_recurrency($pix_key, $url);
				$this->update_option("webhook_status", "O webhook foi cadastrado com sucesso!");
				WC_Admin_Settings::add_message("O webhook foi cadastrado com sucesso!");
			} catch (\Throwable $th) {
				$this->update_option("webhook_status", "O webhook ainda não foi cadastrado.");
				WC_Admin_Settings::add_error("Não foi possível cadastrar o webhook.");
				gn_log($th, GERENCIANET_ASSINATURAS_PIX_ID);
			}
		}

		public static function getMethodId()
		{
			return self::$id;
		}

		// Feito
		public function criar_assinatura($nome_cliente, $initial_status, $id_assinatura, $plano, $periodo_de_teste, $data_inicio, $data_fim, $order_id)
		{
			if (!post_type_exists('efi_assinaturas')) {
				return;
			}
			$subscription_data = array(
				'post_type' => 'efi_assinaturas',
				'post_title' => $nome_cliente,
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => 1,
			);
			$subscription_id = wp_insert_post($subscription_data);

			Gerencianet_Hpos::update_meta($subscription_id, '_status', $initial_status);
			Gerencianet_Hpos::update_meta($subscription_id, '_id_da_assinatura', $id_assinatura);
			Gerencianet_Hpos::update_meta($subscription_id, '_plano', $plano);
			Gerencianet_Hpos::update_meta($subscription_id, '_periodo_de_teste', $periodo_de_teste);
			Gerencianet_Hpos::update_meta($subscription_id, '_data_de_inicio', $data_inicio);
			Gerencianet_Hpos::update_meta($subscription_id, '_data_fim', $data_fim);
			Gerencianet_Hpos::update_meta($subscription_id, '_pedido_associado', $order_id);
			Gerencianet_Hpos::update_meta($subscription_id, '_subs_payment_method', GERENCIANET_ASSINATURAS_PIX_ID);

			return $subscription_id;
		}

		public function validate_gn_client_id_production_field($key, $value)
		{
			if (! preg_match('/^Client_Id_[a-zA-Z0-9]{40}$/', $value)) {
				WC_Admin_Settings::add_error('Insira o Client_Id de Produção.');
				$this->update_option('gn_pix', 'no');
				$value = ''; // empty it because it is not correct
			}

			return $value;
		}

		public function validate_gn_client_secret_production_field($key, $value)
		{
			if (! preg_match('/^Client_Secret_[a-zA-Z0-9]{40}$/', $value)) {
				WC_Admin_Settings::add_error('Insira o Client_Secret de Produção.');
				$this->update_option('gn_pix', 'no');
				$value = ''; // empty it because it is not correct
			}

			return $value;
		}

		public function validate_gn_client_id_homologation_field($key, $value)
		{
			if (! preg_match('/^Client_Id_[a-zA-Z0-9]{40}$/', $value)) {
				WC_Admin_Settings::add_error('Insira o Client_Id de Homologação.');
				$this->update_option('gn_pix', 'no');
				$value = ''; // empty it because it is not correct
			}
			return $value;
		}

		public function validate_gn_client_secret_homologation_field($key, $value)
		{
			if (! preg_match('/^Client_Secret_[a-zA-Z0-9]{40}$/', $value)) {
				WC_Admin_Settings::add_error('Insira o Client_Secret de Homologação.');
				$this->update_option('gn_pix', 'no');
				$value = ''; // empty it because it is not correct
			}
			return $value;
		}
	}
}
