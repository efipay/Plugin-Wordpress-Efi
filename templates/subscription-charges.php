<?php

if (is_wc_endpoint_url('order-received')) {
    return;
}

$subscription_id = get_the_ID();

if (isset($_GET['view-order'])) {
    $order_id = $_GET['view-order'];
} else {
    $order_id = Gerencianet_Hpos::get_meta($subscription_id, '_gn_order_id', true);
}

$order = wc_get_order($order_id);
$paymentMethod = Gerencianet_Hpos::get_meta($subscription_id, '_subs_payment_method', true);

$newStatus['processing'] = '<mark class="order-status status-processing tips"><span>Assinatura Ativa</span></mark>';
$newStatus['pending'] = '<mark class="order-status status-on-hold tips"><span>Aguardando Pagamento</span></mark>';
$newStatus['cancelled'] = '<mark class="order-status status-failed tips"><span>Assinatura Cancelada</span></mark>';
$newStatus['completed'] = '<mark class="order-status status-completed tips"><span>Assinatura Finalizada</span></mark>';

foreach ($order->get_items() as $item_id => $item) {
    $order_item_data      = $item->get_data(); // Get WooCommerce order item meta data in an unprotected array
}

$interval = Gerencianet_Hpos::get_meta($order_item_data['product_id'], '_gerencianet_interval', true);
$repeats = Gerencianet_Hpos::get_meta($order_item_data['product_id'], '_gerencianet_repeats', true);

list($recorrencia_label_singular, $recorrencia_label_plural, $meses_por_ciclo) = get_recorrency_label($interval);

$duracao = "Indeterminada (Até o cancelamento)";
if ($repeats > 1 && $meses_por_ciclo > 0) {
    $renovacoes = $repeats - 1;
    $total_meses = $renovacoes * $meses_por_ciclo;

    $renovacao_texto = $renovacoes === 1 
        ? "1 renovação $recorrencia_label_singular" 
        : "$renovacoes renovações $recorrencia_label_plural";

    $duracao = "$renovacao_texto (~{$total_meses} meses) | Total: ${repeats} pagamentos";
}
?>

<input type="hidden" id="sub_id" value="<?php echo $subscription_id; ?>">
<input type="hidden" id="order_id" value="<?php echo $order_id; ?>">

<div class="custom-panel">
    <h2>Detalhes da Assinatura</h2>
    <div class="custom-panel-container">
        <div class="custom-panel-column">
            <h3>Geral</h3>
            <label class="subscription-label"><span>ID da Assinatura: </span><?php echo sanitize_text_field(Gerencianet_Hpos::get_meta(get_the_ID(), '_id_da_assinatura', true)); ?></label>
            <label class="subscription-label"><span>Cliente: </span><?php echo sanitize_text_field($order->get_formatted_billing_full_name()); ?></label>
            <label class="subscription-label"><span>Plano: </span><?php echo sanitize_text_field($order_item_data['name']); ?></label>
            <label class="subscription-label"><span>Recorrência: </span><?php echo sanitize_text_field($recorrencia_label_singular); ?></label>
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
                <button type="button" class="btn btn-efi" onclick="areYouSure()"><span class="dashicons dashicons-no" style="margin-right:2px;"></span>Cancelar Assinatura</button>
            <?php
            } else {
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
                // Verifica se é assinatura de Pix ou Charges
                if ($paymentMethod == GERENCIANET_ASSINATURAS_PIX_ID) {
                    $order = wc_get_order($order_id);
                    $all_meta_objects = $order->get_meta_data();

                    $e2e_keys = [];

                    // Pega todas as chaves _gn_pix_E2EID_ que não terminam com _paid_at
                    foreach ($all_meta_objects as $meta) {
                        $meta_data = $meta->get_data();
                        $key = $meta_data['key'];

                        if (
                            strpos($key, '_gn_pix_E2EID_') === 0 &&
                            strpos($key, '_paid_at') === false
                        ) {
                            $e2e_keys[] = $key;
                        }
                    }




                    natsort($e2e_keys);

                    $valor_total = $order->get_total();

                    foreach ($e2e_keys as $e2e_key) {
                        $e2eid = Gerencianet_Hpos::get_meta($order_id, $e2e_key);
                        $index = (int) str_replace('_gn_pix_E2EID_', '', $e2e_key);
                        $paid_at_key = '_gn_pix_E2EID_' . $index . '_paid_at';

                        $paid_at = Gerencianet_Hpos::get_meta($order_id, $paid_at_key);

                        if (!empty($paid_at)) {
                            // Como a data já está salva em Y-m-d, só convertemos para o mês formatado (ex: Julho/2025)
                            list($ano, $mes, $dia) = explode('-', $paid_at);
                            $data_obj = new DateTime("$ano-$mes-$dia");
                            $mes_formatado = ucfirst($formatter->format($data_obj));

                ?>
                            <tr>
                                <td><?php echo sanitize_text_field($e2eid); ?></td>
                                <td><?php echo sanitize_text_field($mes_formatado); ?></td>
                                <td><?php echo sanitize_text_field('Pago'); ?></td>
                                <td><?php echo sanitize_text_field('R$ ' . number_format($valor_total, 2, ',', '.')); ?></td>
                                <td><?php echo sanitize_text_field(date('d/m/Y', strtotime($paid_at))); ?></td>
                                <td>Sem ações disponíveis</td>
                            </tr>
                        <?php
                        }
                    }
                } else {

                    $notification = json_decode(Gerencianet_Hpos::get_meta($order_id, '_notification_subscription', true));
                    $charges = [];

                    if (isset($notification->data)) {
                        foreach ($notification->data as $notification_data) {
                            if ($notification_data->type == 'subscription_charge') {
                                $charges[$notification_data->identifiers->charge_id]['charge_id'] = $notification_data->identifiers->charge_id;
                                $date = new DateTime($notification_data->created_at);
                                $mesPorExtenso = $formatter->format($date);
                                $charges[$notification_data->identifiers->charge_id]['mes'] = $mesPorExtenso;
                                $charges[$notification_data->identifiers->charge_id]['status'] = $notification_data->status->current;
                                $charges[$notification_data->identifiers->charge_id]['valor'] = isset($notification_data->value) ? 'R$' . number_format((intval($notification_data->value)) / 100, 2, ',', '.') : "---";
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
                                    <a target="_blank" style="text-decoration:none;" title="Clique para baixar o boleto" href="<?php echo $link; ?>"><img style="height: 20px;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/download-pdf.png'; ?>" alt="Baixar PDF"> </a>
                                    <a onClick="gncopy('<?php echo $barcode; ?>')" title="Clique para copiar o código de barras"><img style="height: 20px; cursor: pointer;" src="<?php echo GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/barcode-copy.png'; ?>" alt="Copiar Código de Barras"> </a>
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
                }
                ?>
            </tbody>

        </table>
    </div>
</div>
<script>
    jQuery(document).ready(function() {
        jQuery("#tbcharges").DataTable({
            "language": {
                "url": "<?php echo GERENCIANET_OFICIAL_PLUGIN_URL ?>assets/js/locale.json"
            }
        });
    });

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
    $newStatus['refunded'] = 'Reembolsado';

    return $newStatus[$status];
}

function get_recorrency_label($recorrency) {
    switch ($recorrency) {
        case '1':
            return ['Mensal', 'mensais', 1];
        case '2':
            return ['Bimestral', 'bimestrais', 2];
        case '3':
            return ['Trimestral', 'trimestrais', 3];
        case '4':
            return ['Quadrimestral', 'quadrimestrais', 4];
        case '6':
            return ['Semestral', 'semestrais', 6];
        case '12':
            return ['Anual', 'anuais', 12];
        default:
            return ['Desconhecida', 'desconhecidas', 0];
    }
}
