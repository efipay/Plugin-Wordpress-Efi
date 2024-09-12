<?php

    if (is_wc_endpoint_url('order-received')) {
        return;
    }

            $subscription_id = get_the_ID();

            if (isset($_GET['view-order'])) {
                $order_id = $_GET['view-order'];
            }else{
                $order_id = Gerencianet_Hpos::get_meta( $subscription_id, '_gn_order_id', true );
            }
            
            $order = wc_get_order($order_id);
            $paymentMethod = Gerencianet_Hpos::get_meta($subscription_id, '_subs_payment_method', true); 

            $newStatus['processing'] = '<mark class="order-status status-processing tips"><span>Assinatura Ativa</span></mark>';
			$newStatus['pending'] = '<mark class="order-status status-on-hold tips"><span>Aguardando Pagamento</span></mark>';
			$newStatus['cancelled'] = '<mark class="order-status status-failed tips"><span>Assinatura Cancelada</span></mark>';
			$newStatus['completed'] = '<mark class="order-status status-completed tips"><span>Assinatura Finalizada</span></mark>';

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
            
            <input type="hidden" id="sub_id" value="<?php echo $subscription_id;?>">
            <input type="hidden" id="order_id" value="<?php echo $order_id;?>">
            
            <div class="custom-panel">
            <h2>Detalhes da Assinatura</h2>
            <div class="custom-panel-container">
                <div class="custom-panel-column">
                    <h3>Geral</h3>
                    <label class="subscription-label"><span>ID da Assinatura: </span><?php echo sanitize_text_field(Gerencianet_Hpos::get_meta( get_the_ID(), '_id_da_assinatura', true )); ?></label>
                    <label class="subscription-label"><span>Cliente: </span><?php echo sanitize_text_field($order->get_formatted_billing_full_name()); ?></label>
                    <label class="subscription-label"><span>Plano: </span><?php echo sanitize_text_field($order_item_data['name']); ?></label>
                    <label class="subscription-label"><span>Recorrência: </span><?php echo sanitize_text_field(get_recorrency($interval)); ?></label>
                    <label class="subscription-label"><span>Duração: </span><?php echo sanitize_text_field($duracao); ?></label>
                    
                </div>
                <div class="custom-panel-column">
                    <h3>Status</h3>
                    <?php echo $newStatus[$order->get_status()]; ?>
                </div>
                <div class="custom-panel-column">
                    <h3>Ações</h3>
                    <?php
                        if ($order->get_status() != 'cancelled' && $order->get_status() != 'completed') {
                    ?>
                            <button type="button" class="btn btn-efi" onclick="areYouSure()"><span class="dashicons dashicons-no" style="margin-right:4px;"></span>Cancelar Assinatura</button>
                    <?php
                        }else{
                            echo "Sem ações disponíveis";
                        }
                    ?>
                </div>
            </div>
            <div class="custom-panel-column">
                <h3>Cobranças da Assinatura</h3>
                
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
                <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/dataTables.bootstrap4.min.css">
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
                <script src="https://cdn.datatables.net/1.11.0/js/dataTables.bootstrap4.min.js"></script>
                <link rel="stylesheet" href="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/css/assinaturas.css">
                <script src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/js/cancelSubscription.js"></script>
                <table id="tbcharges" class="table table-striped table-bordered" style="width: 100%;">
	                    <thead>
	                        <tr>
                                <th scope="col"><span>ID da Cobrança</span></th>
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
                        <td><?php echo sanitize_text_field($charge['charge_id']); ?></td>
                        <td><?php echo sanitize_text_field(ucfirst($charge['mes'])); ?></td>
                        <td><?php echo sanitize_text_field(traduz_status($charge['status'])); ?></td>
                        <td><?php echo sanitize_text_field($charge['valor']); ?></td>
                        <td><?php echo sanitize_text_field($charge['pagamento']); ?></td>
                        <td>
                            <?php

                                if ($charge['status'] == 'waiting' && $paymentMethod == GERENCIANET_ASSINATURAS_BOLETO_ID) {
                                    ?>
                                    <a target="_blank" style="text-decoration:none;"  title="Clique para baixar o boleto" href="<?php echo $link; ?>"><img style="height: 20px;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/download-pdf.png'; ?>" alt="Baixar PDF"> </a>
                                    <a onClick="gncopy('<?php echo $barcode; ?>')" title="Clique para copiar o código de barras" ><img style="height: 20px; cursor: pointer;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/barcode-copy.png'; ?>" alt="Copiar Código de Barras"> </a>
                                    <a onClick="gncopy('<?php echo $pixCopia; ?>')" title="Clique para copiar o Pix Copia e Cola"><img style="height: 20px; cursor: pointer;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/pix-icon.png'; ?>" alt="Copiar Pix"> </a>
                                    <?php
                                } else {
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
                jQuery(document).ready( function () {
                    jQuery("#tbcharges").DataTable({
                        "language" : {
                            "url": "<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/js/locale.json"
                        }
                    });
                } );

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
			$newStatus['canceled'] = 'Cancelado';
			$newStatus['settled'] = 'Marcado como Pago';

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