<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       www.gerencianet.com.br
 *
 * @package    Gerencianet_Oficial
 * @subpackage Gerencianet_Oficial/front-office/partials
 */
$order          = new WC_Order( $order_id );
$payment_method = $order->get_payment_method();

$wcSettings = maybe_unserialize( get_option( 'woocommerce_' . GERENCIANET_CARTAO_ID . '_settings' ) );
$isSandbox  = $wcSettings['gn_sandbox'] == 'yes' ? true : false;
$payeeCode  = $wcSettings['gn_payee_code'];
$body = Gerencianet_Hpos::get_meta( $order_id, '_gn_retry_body');


switch ( $payment_method ) {
	case GERENCIANET_CARTAO_ID:
		$status = Gerencianet_Hpos::get_meta( $order_id, '_gn_status_card', true );
		if($status == "approved"){
			echo '<div id="approval-screen" class="result-screen">
       				<h3>Compra Aprovada!</h3>
        			<p>Obrigado pela sua compra. Seu pedido está sendo processado.</p>
    			</div>';
		} elseif ($status == "unpaid") {
			?><div id="denial-screen" class="result-screen">
        		<h3>Compra Recusada</h3>
        		<p>Infelizmente, sua compra não foi aprovada. Por favor, tente novamente ou entre em contato com o prorietário da loja.</p>
				<link rel="stylesheet" href="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/css/thankyou-page.css">
				<?php
					if(Gerencianet_Hpos::get_meta( $order_id, '_gn_can_retry') == "yes"){
						$installments = $body['payment']['credit_card']['installments'];
						echo '<button type="button" class="woocommerce-button button" id="gn-btn-retry">Tentar novamente</button>';
						echo '<input type="hidden" id="order_id" value="'.$order_id.'">';
						echo '<input id="gn_installments" name="gn_installments" type="hidden" value="'.$installments.'">';
					}
				?>
				<input id="gn_payment_total" name="gn_payment_total" type="hidden" value="<?php echo $order->get_total(); ?>">
    		</div> 
			<script>
				var options = {
					payeeCode: '<?php echo $payeeCode; ?>',
					enviroment:'<?php echo $isSandbox ? 'sandbox' : 'production'; ?>'
				}
			</script>
			<?php
		} 
		break;
	case GERENCIANET_BOLETO_ID:
		echo '<iframe  src=' . esc_url( Gerencianet_Hpos::get_meta( $order_id, '_gn_link_responsive', true ) ) . " width='900' height='400'></iframe>";
		break;
	case GERENCIANET_PIX_ID:
		$pixCopy = Gerencianet_Hpos::get_meta( $order_id, '_gn_pix_copy', true );
		$qrcode  = Gerencianet_Hpos::get_meta( $order_id, '_gn_pix_qrcode', true );

		// Importante: envolva o conteúdo num container com ID
		echo "<div id='pix-container'>
				<h2>Escaneie o QrCode abaixo para pagar</h2>
				<div style='float:left'>
					<img src='" . esc_html( $qrcode ) . "' />
				</div>
				<div style='padding: 10px;'>
					<p style='font-weight: bold;'>Ou copie o Pix Copia e Cola clicando no botão abaixo!</p>
					<a onclick='gncopy()' id='gnbtncopy' class='button gnbtn'>Copiar Pix Copia e Cola</a>
				</div>
			</div>";

		// Supondo que deseja checar a cada 5 segundos por no máximo 5 minutos
		$interval_ms = 5000; // 5 segundos
		$max_minutes = 5;

		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$pix_gateway        = isset( $available_gateways['wc_gerencianet_pix'] ) ? $available_gateways['wc_gerencianet_pix'] : null;

		$desired_status = $pix_gateway->gn_order_status_after_payment;

		?>
		<script type="text/javascript">

			var desiredStatus = "<?php echo str_replace('wc-', '', $desired_status); ?>";

			(function($){
				var intervalMs   = <?php echo $interval_ms; ?>;
				var maxMinutes   = <?php echo $max_minutes; ?>;
				var maxAttempts  = (maxMinutes * 60 * 1000) / intervalMs;
				var currentTry   = 0;

				var checkInterval = setInterval(function(){
					currentTry++;
					if (currentTry >= maxAttempts) {
						clearInterval(checkInterval);
						// console.log("Tempo máximo de tentativa atingido");
						return;
					}

					$.ajax({
						url: "<?php echo admin_url('admin-ajax.php'); ?>",
						method: "POST",
						dataType: "json",
						data: {
							action: "gn_check_order_status",
							order_id: "<?php echo $order_id; ?>"
						},
						success: function(response) {
							if (response.success) {
								var status = response.data.current_status;
								// console.log("Status atual do pedido: " + status);
								// console.log("Status esperado é: " + desiredStatus)

								if (status === desiredStatus) {
									clearInterval(checkInterval);
									exibirResultadoPixPago();
								}
							}
						},
						error: function(xhr) {
							console.log("Erro ao checar status: ", xhr);
						}
					});
				}, intervalMs);

				// Função que atualiza o DOM quando detectamos pagamento
				function exibirResultadoPixPago() {
					// Localiza o container que exibe o QR Code
					var container = document.getElementById("pix-container");
					
					if(container){
						// Substitui o conteúdo pelo que você deseja
						container.innerHTML = "<h2 style='color:green;'>Pagamento confirmado!</h2>"
											+ "<p>Obrigado! Seu pedido foi pago com sucesso.</p>";
					} else {
						// Fallback caso a div não exista
						alert("Pagamento confirmado!");
					}
				}


			})(jQuery);

			function gncopy() {
				document.getElementById('gnbtncopy').innerHTML = 'Copiado!';
				navigator.clipboard.writeText('<?php if(isset($pixCopy)) echo esc_html($pixCopy); ?>');
				setTimeout(() => {
					document.getElementById('gnbtncopy').innerHTML = 'Copiar Pix Copia e Cola';
				}, 1000);
			}
		</script>
		<?php
		break;
	case GERENCIANET_ASSINATURAS_PIX_ID:
		$pixCopy = Gerencianet_Hpos::get_meta( $order_id, '_gn_pix_copy', true );
		$qrcode  = Gerencianet_Hpos::get_meta( $order_id, '_gn_pix_qrcode', true );

		// Importante: envolva o conteúdo num container com ID
		echo "<div id='pix-container'>
				<h2>Escaneie o QrCode abaixo para pagar</h2>
				<div style='float:left'>
					<img src='" . esc_html( $qrcode ) . "' />
				</div>
				<div style='padding: 10px;'>
					<p style='font-weight: bold;'>Ou copie o Pix Copia e Cola clicando no botão abaixo!</p>
					<a onclick='gncopy()' id='gnbtncopy' class='button gnbtn'>Copiar Pix Copia e Cola</a>
				</div>
			</div>";

		// Supondo que deseja checar a cada 5 segundos por no máximo 5 minutos
		$interval_ms = 5000; // 5 segundos
		$max_minutes = 5;

		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		$pix_gateway        = isset( $available_gateways['wc_gerencianet_assinaturas_pix'] ) ? $available_gateways['wc_gerencianet_assinaturas_pix'] : null;

		$desired_status = $pix_gateway->gn_order_status_after_payment;

		?>
		<script type="text/javascript">

			var desiredStatus = "<?php echo str_replace('wc-', '', $desired_status); ?>";

			(function($){
				var intervalMs   = <?php echo $interval_ms; ?>;
				var maxMinutes   = <?php echo $max_minutes; ?>;
				var maxAttempts  = (maxMinutes * 60 * 1000) / intervalMs;
				var currentTry   = 0;

				var checkInterval = setInterval(function(){
					currentTry++;
					if (currentTry >= maxAttempts) {
						clearInterval(checkInterval);
						// console.log("Tempo máximo de tentativa atingido");
						return;
					}

					$.ajax({
						url: "<?php echo admin_url('admin-ajax.php'); ?>",
						method: "POST",
						dataType: "json",
						data: {
							action: "gn_check_order_status",
							order_id: "<?php echo $order_id; ?>"
						},
						success: function(response) {
							if (response.success) {
								var status = response.data.current_status;
								// console.log("Status atual do pedido: " + status);
								// console.log("Status esperado é: " + desiredStatus)
								
								if (status === desiredStatus) {
									// console.log("Pagamento confirmado!");
									clearInterval(checkInterval);
									exibirResultadoPixPago();
								}
							}
						},
						error: function(xhr) {
							console.log("Erro ao checar status: ", xhr);
						}
					});
				}, intervalMs);

				// Função que atualiza o DOM quando detectamos pagamento
				function exibirResultadoPixPago() {
					// Localiza o container que exibe o QR Code
					var container = document.getElementById("pix-container");
					
					if(container){
						// Substitui o conteúdo pelo que você deseja
						container.innerHTML = "<h2 style='color:green;'>Pagamento confirmado!</h2>"
											+ "<p>Obrigado! Seu pedido foi pago com sucesso.</p>";
					} else {
						// Fallback caso a div não exista
						alert("Pagamento confirmado!");
					}
				}


			})(jQuery);

			function gncopy() {
				document.getElementById('gnbtncopy').innerHTML = 'Copiado!';
				navigator.clipboard.writeText('<?php if(isset($pixCopy)) echo esc_html($pixCopy); ?>');
				setTimeout(() => {
					document.getElementById('gnbtncopy').innerHTML = 'Copiar Pix Copia e Cola';
				}, 1000);
			}
		</script>
		<?php
		break;
	case GERENCIANET_ASSINATURAS_BOLETO_ID:
		echo '<iframe  src=' . esc_url( Gerencianet_Hpos::get_meta( $order_id, '_gn_link_responsive', true ) ) . " width='900' height='400'></iframe>";
		break;
	case GERENCIANET_ASSINATURAS_CARTAO_ID:
		echo '<div id="confirmation-screen" class="result-screen">
			<h3>Sua assinatura está sendo processada.</h3>
			<p>Aguarde maiores informações no seu email.</p>
		</div>';
		break;
	default:
		// code...
		break;
}


?>

<style>
	.gnbtn{
		background: #EB6608; 
		color:#ffff;
		border:none;
		font-size: 1rem; 
		padding: 5px 7px 5px 7px;
		border-radius: 5px;
	}

	.gnbtn:hover{
		background: #d35700;
		color: #ffff;
	}
	.result-screen {
		text-align: center;
		padding: 20px;
		border-radius: 5px;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		background-color: #fff;
		width: 100%;
	}

	.hidden {
		display: none;
	}

	#approval-screen {
		border: 2px solid #4CAF50;
	}

	#denial-screen {
		border: 2px solid #F44336;
	}

</style>

<script>
	function gncopy() {
		document.getElementById('gnbtncopy').innerHTML = 'Copiado!';
		navigator.clipboard.writeText('<?php if(isset($pixCopy)){ echo esc_html( $pixCopy );} ?>');
		setTimeout(()=> {
			document.getElementById('gnbtncopy').innerHTML = 'Copiar Pix Copia e Cola';
			},1000)
	}
</script>


