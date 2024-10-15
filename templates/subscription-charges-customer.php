<?php

    if (is_wc_endpoint_url('order-received')) {
        return;
    }

            global $wp;
            $order_id = isset( $wp->query_vars['view-order'] ) ? $wp->query_vars['view-order'] : null;


            $subscription_id = Gerencianet_Hpos::get_meta($order_id, '_subscription_id', true);


            
            $order = wc_get_order($order_id);

            $paymentMethod = $order->get_payment_method(); 

            foreach ($order->get_items() as $item_id => $item ) {
                $order_item_data      = $item->get_data(); // Get WooCommerce order item meta data in an unprotected array
            }

            $interval = Gerencianet_Hpos::get_meta($order_item_data['product_id'], '_gerencianet_interval', true);
            $repeats = Gerencianet_Hpos::get_meta($order_item_data['product_id'], '_gerencianet_repeats', true);

            $duracao = "Indeterminada (Até o cancelamento)";
            if($repeats > 1){
                $duracao = $repeats." Meses";
            }
            
            ?>
            <div class="custom-panel">
            <h2>Detalhes da Assinatura</h2>
            <div class="custom-panel-container">
                <div class="custom-panel-column">
                    <h3>Geral</h3>
                    <label class="subscription-label"><span>ID da Assinatura: </span><?php echo sanitize_text_field(Gerencianet_Hpos::get_meta( $subscription_id, '_id_da_assinatura', true )); ?></label>
                    <label class="subscription-label"><span>Recorrência: </span><?php echo sanitize_text_field(get_recorrency($interval)); ?></label>
                    <label class="subscription-label"><span>Duração: </span><?php echo sanitize_text_field($duracao); ?></label>
                    
                </div>
                <div class="custom-panel-column">
                    <h3>Status</h3>
                    <?php echo verifica_status($order->get_status()); ?>
                    <input type="hidden" id="sub_id" value="<?php echo $subscription_id;?>">
                    <input type="hidden" id="order_id" value="<?php echo $order_id;?>">
                </div>
                <div class="custom-panel-column">
                    <h3>Ações</h3>
                    <?php
                        if ($order->get_status() != 'cancelled' && $order->get_status() != 'completed') {
                    ?>
                            <button type="button" class='btn-efi' onclick="areYouSure()">Cancelar Assinatura</button>
                    <?php
                        }else{
                            echo "Sem ações disponíveis";
                        }
                    ?>
                </div>
            </div>
            <div class="custom-panel-column">
                <h3>Cobranças da Assinatura</h3>
                
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                <link rel="stylesheet" href="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/css/assinaturas-customer.css">
                <script src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/js/cancelSubscription.js"></script>
                <table id="tbcharges" class="table table-striped table-bordered" style="width: 100%;">
	                    <thead>
	                        <tr>
                                <th scope="col"><span>Mês</span></th>
                                <th scope="col"><span>Status</span></th>
                                <th scope="col"><span>Valor</span></th>
                                <th scope="col"><span>Data de Pagamento</span></th>
                                <th scope="col"><span>Ações</span></th>
	                        </tr>
                        </thead>
                        <tbody>
                <?php

                $notification = json_decode(Gerencianet_Hpos::get_meta( $order_id, '_notification_subscription', true));

                $charges = [];

                $formatter = new IntlDateFormatter(
                        'pt_BR',
                        IntlDateFormatter::NONE,
                        IntlDateFormatter::NONE,
                        'America/Sao_Paulo',
                        IntlDateFormatter::GREGORIAN,
                        'MMMM/YYYY'
                    );
                
                $formatter_date = new IntlDateFormatter(
                        'pt_BR',
                        IntlDateFormatter::FULL,
                        IntlDateFormatter::NONE,
                        'America/Sao_Paulo',
                        IntlDateFormatter::GREGORIAN,
                        'dd/MM/yyyy'
                    );


                if(isset($notification->data)){
                    foreach ($notification->data as $notification_data) {
                        if($notification_data->type == 'subscription_charge'){
                            $charges[$notification_data->identifiers->charge_id]['charge_id'] = $notification_data->identifiers->charge_id;
                            $date = new DateTime($notification_data->created_at);
                            $mesPorExtenso = $formatter->format($date);
                            $charges[$notification_data->identifiers->charge_id]['mes'] = $mesPorExtenso;
                            $charges[$notification_data->identifiers->charge_id]['status'] = $notification_data->status->current;
                            $charges[$notification_data->identifiers->charge_id]['valor'] = isset($notification_data->value) ? 'R$'.number_format((intval($notification_data->value))/100, 2, ',', '.') : "---";
                            if ($notification_data->status->current == 'paid') {
                                $charges[$notification_data->identifiers->charge_id]['pagamento'] = isset($notification_data->received_by_bank_at) ? $notification_data->received_by_bank_at : $notification_data->created_at;
                                $timezone = new DateTimeZone('America/Sao_Paulo');
                                $charges[$notification_data->identifiers->charge_id]['pagamento'] = $formatter_date->format(new DateTime($charges[$notification_data->identifiers->charge_id]['pagamento'], $timezone));
                            } else {
                                $charges[$notification_data->identifiers->charge_id]['pagamento'] = "---";
                            }
                        }
                    }
                }
            $gerencianetSDK = new Gerencianet_Integration();
            
            foreach ($charges as $charge) {
                if ($charge['status'] == 'waiting' && $paymentMethod == GERENCIANET_ASSINATURAS_BOLETO_ID) {
                    $response = $gerencianetSDK->get_charge(GERENCIANET_ASSINATURAS_BOLETO_ID, $charge['charge_id']);
                    $cobranca = json_decode($response);
                    
                    $link = $cobranca->data->payment->banking_billet->pdf->charge;
                    $barcode = $cobranca->data->payment->banking_billet->barcode;
                    $pixCopia = $cobranca->data->payment->banking_billet->pix->qrcode;
                }
                

                ?>
                    <tr>
                        <td style="text-align: center;"><?php echo sanitize_text_field(ucfirst($charge['mes'])); ?></td>
                        <td style="text-align: center;"><?php echo sanitize_text_field(traduz_status($charge['status'])); ?></td>
                        <td style="text-align: center;"><?php echo sanitize_text_field($charge['valor']); ?></td>
                        <td style="text-align: center;"><?php echo sanitize_text_field($charge['pagamento']); ?></td>
                        <td style="text-align: center;">
                            <?php

                                if ($charge['status'] == 'waiting' && $paymentMethod == GERENCIANET_ASSINATURAS_BOLETO_ID) {
                                    ?>
                                    <a target="_blank" style="text-decoration:none;" title="Clique para baixar o boleto" href="<?php echo $link; ?>"><img style="height: 20px;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/download-pdf.png'; ?>" alt="Baixar PDF"> </a>
                                    <a onClick="gncopy('<?php echo $barcode; ?>')" style="text-decoration:none;" title="Clique para copiar o código de barras"><img style="height: 20px; cursor: pointer;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/barcode-copy.png'; ?>" alt="Copiar Código de Barras"> </a>
                                    <a onClick="gncopy('<?php echo $pixCopia; ?>')" style="text-decoration:none;" title="Clique para copiar o Pix Copia e Cola"><img style="height: 20px; cursor: pointer;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/pix-icon.png'; ?>" alt="Copiar Pix"> </a>
                                    <?php
                                } elseif ($charge['status'] == 'unpaid' && $paymentMethod == GERENCIANET_ASSINATURAS_CARTAO_ID) {
                                    ?>
                                        <link rel="stylesheet" href="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/css/thankyou-page.css">
                                        <button type="button" class="woocommerce-button button" id="gn-btn-retry">Tentar pagar novamente</button>
                                        <input type="hidden" id="charge_id" value="<?php echo $charge['charge_id']?>">
                                    <?php
                                } else{
                                    echo "Sem ações disponíveis";
                                }
                            ?>
                        </td>
                    </tr>
                <?php    
            }
            ?>
            
            </tbody>
        </table>
    </div>
</div>
            <script>

                function gncopy($text) {
                    navigator.clipboard.writeText($text);
                    Swal.fire({
                        icon: 'success',
                        title: 'Copiado!',
                    })
                    }

            </script>
<?php

        function verifica_status($status)
		{
            $newStatus['processing'] = '<mark class="order-status status-processing tips"><span>Assinatura Ativa</span></mark>';
			$newStatus['pending'] = '<mark class="order-status status-on-hold tips"><span>Aguardando Pagamento</span></mark>';
			$newStatus['cancelled'] = '<mark class="order-status status-failed tips"><span>Assinatura Cancelada</span></mark>';
            $newStatus['canceled'] = '<mark class="order-status status-failed tips"><span>Assinatura Cancelada</span></mark>';
			$newStatus['completed'] = '<mark class="order-status status-completed tips"><span>Assinatura Finalizada</span></mark>';

			return $newStatus[$status];
		}

        function traduz_status($status)
		{
			$newStatus['waiting'] = 'Aguardando Pagamento';
			$newStatus['identified'] = 'Processando Pagamento';
			$newStatus['paid'] = 'Pago';
			$newStatus['unpaid'] = 'Inadimplente';
			$newStatus['cancelled'] = 'Cancelado';
            $newStatus['canceled'] = 'Cancelado';
			$newStatus['settled'] = 'Marcado como Pago';
            $newStatus['refunded'] = 'Reembolsado';

			return $newStatus[$status];
		}

        function get_recorrency($recorrency)
        {
            $textafter = '';
            switch ($recorrency) {
                case '1':
                    $textafter = 'Mensal';
                    break;
                case '2':
                    $textafter = 'Bimestral';
                    break;
                case '3':
                    $textafter = 'Trimestral';
                    break;
                case '4':
                    $textafter = 'Quadrimestral';
                    break;
                case '6':
                    $textafter = 'Semestral';
                    break;
                case '12':
                    $textafter = 'Anual';
                    break;
            }
            return $textafter;
        }