document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');

    let buttons = [
        {
            text: 'Cadastrar Webhook',
            id: '_webhook_button'
        },
        {
            text: 'Baixar Logs',
            id: '_download_button'
        }
    ]

    buttons.forEach(bt => {
        var elemento = document.querySelector('#woocommerce_' + section + bt.id); // Selecione o elemento desejado
        if (elemento) {
            elemento.classList.add('woocommerce-save-button');
            elemento.classList.add('components-button');
            elemento.classList.add('is-primary');
            elemento.value = bt.text
        }
    });

    jQuery(document).ready(function ($) {
        // Lista de IDs dos elementos a serem monitorados
        const monitoredFields = [
            '#woocommerce_' + section + '_gn_client_id_production',
            '#woocommerce_' + section + '_gn_client_secret_production',
            '#woocommerce_' + section + '_gn_client_id_homologation',
            '#woocommerce_' + section + '_gn_client_secret_homologation',
            '#woocommerce_' + section + '_gn_sandbox',
            '#woocommerce_' + section + '_gn_pix',
            '#woocommerce_' + section + '_gn_pix_key',
            '#woocommerce_' + section + '_gn_pix_account',
            '#woocommerce_' + section + '_gn_certificate_file',
            '#woocommerce_' + section + '_gn_pix_discount',
            '#woocommerce_' + section + '_gn_pix_discount_shipping',
            '#woocommerce_' + section + '_gn_pix_number_hours',
            '#woocommerce_' + section + '_gn_pix_mtls',
            '#woocommerce_' + section + '_gn_order_status_after_payment',
            '#woocommerce_' + section + '_gn_open_finance',
            '#woocommerce_' + section + '_gn_open_finance_document',
            '#woocommerce_' + section + '_gn_open_finance_name',
            '#woocommerce_' + section + '_gn_open_finance_account',
        ];

        // Função para desabilitar o botão
        function disableWebhookButton() {
            $('#woocommerce_wc_gerencianet_pix_webhook_button').prop('disabled', true);
            $('#woocommerce_wc_gerencianet_assinaturas_pix_webhook_button').prop('disabled', true);
        }

        // Adiciona listeners para alteração nos campos
        monitoredFields.forEach(function (field) {
            if ($(field).length) {
                $(document).on('change input', field, disableWebhookButton);
            }

        });
    });


});
