jQuery(document).ready(function ($) {
    $('#gn-btn-retry').on('click', async function () {
        showCardForm()
    });
});

function showCardForm() {
    Swal.fire({
        title: "Insira os dados do Cartão",
        confirmButtonText: "Continuar",
        html: `
                <label class="swal2-input-label gn-swal2-label">Número do Cartão</label>
                <input id="gn_cartao_number" class="swal2-input gn-swal2-input" inputmode="numeric" name="gn_cartao_number" type="text" autocomplete="off">
                <label class="swal2-input-label gn-swal2-label">CVV</label>
                <input id="gn_cartao_cvv" class="swal2-input gn-swal2-input" inputmode="numeric" name="gn_cartao_cvv" type="text" autocomplete="off">
                <label class="swal2-input-label gn-swal2-label">Expiração (MM/AA)</label>
                <input id="gn_cartao_expiration" class="swal2-input gn-swal2-input" inputmode="numeric" placeholder="__/__" name="gn_cartao_expiration"type="text" autocomplete="off">

                <style>
                    .gn-swal2-input{
                        margin: 5px !important;
                        text-align: center;
                    }   
                    .gn-swal2-label{
                        margin-bottom: 0 !important;
                    }
                    #swal2-content {
                        overflow: hidden;
                    }
                </style>

            `,

        focusConfirm: false,
        didOpen: function (el) {
            VMasker(document.querySelector("#gn_cartao_number")).maskPattern("9999 9999 9999 9999");
            VMasker(document.querySelector("#gn_cartao_cvv")).maskPattern("9999");
            VMasker(document.querySelector("#gn_cartao_expiration")).maskPattern("99/99");

            // Referência ao botão de confirmação
            const confirmButton = Swal.getConfirmButton();
            confirmButton.disabled = true; // Desabilita o botão inicialmente

            const inputs = [
                document.querySelector("#gn_cartao_number"),
                document.querySelector("#gn_cartao_cvv"),
                document.querySelector("#gn_cartao_expiration")
            ];

            // Função para verificar se todos os campos estão preenchidos
            function validateInputs() {
                const allFilled = inputs.every(input => input.value.trim() !== "");
                confirmButton.disabled = !allFilled;
            }

            // Adiciona evento de input para cada campo
            inputs.forEach(input => {
                input.addEventListener('input', validateInputs);
            });
        },
        preConfirm: () => {
            return [
                document.querySelector("#gn_cartao_number").value,
                document.querySelector("#gn_cartao_cvv").value,
                document.querySelector("#gn_cartao_expiration").value
            ];
        }
    }).then((result) => {
        if (result.isConfirmed) {
            generatePaymentToken(result.value);
        }
    });
}


function generatePaymentToken(cardData) {
    let cardExpiration = cardData[2].split("/");

    Swal.fire({
        title: 'Por favor, aguarde...',
        text: '',
        showConfirmButton: false,
        didOpen: async () => {
            Swal.showLoading();
            await EfiPay.CreditCard
                // .debugger(true)
                .setCardNumber(cardData[0])
                .verifyCardBrand()
                .then(async (brand) => {
                    if (brand !== 'undefined') {
                        await EfiPay.CreditCard
                            //.debugger(true)
                            .setAccount(options.payeeCode)
                            .setEnvironment(options.enviroment) // 'production' or 'sandbox'
                            .setCreditCardData({
                                brand: brand,
                                number: cardData[0],
                                cvv: cardData[1],
                                expirationMonth: cardExpiration[0],
                                expirationYear: getFullYear(cardExpiration[1])
                            })
                            .getPaymentToken()
                            .then(async (payment_data) => {
                                swal.close()
                                await getParcels(payment_data)
                            }).catch(err => {
                                console.log(err);
                                throw new Error(`Something went wrong in getPaymentToken(.\n ${err}`);
                            });
                    }
                }).catch(err => {
                    console.log(err);
                    throw new Error(`Something went wrong in verifyCardBrand(.\n ${err}`);
                });
        },
    })


}

async function getParcels(payment_data) {

    Swal.fire({
        title: "Deseja tentar pagar sua assinatura novamente?",
        showCancelButton: true,

    }).then((result) => {
        if (result.isConfirmed) {
            Swal.showLoading();
            tryAgain(payment_data.payment_token, result.value);
        }
    });
}

function tryAgain(paymentToken) {
    let data = {
        action: 'woocommerce_gerencianet_assinatura_retry',
        security: retry_assinatura.security,
        order_id: jQuery('#order_id').val(),
        charge_id: jQuery('#charge_id').val(),
        payment_token: paymentToken,
    }
    Swal.showLoading();

    jQuery.ajax({
        url: retry_assinatura.ajax_url, // URL do AJAX fornecida pelo wp_localize_script

        type: 'POST',
        data: data,
        success: function (response) {
            console.log('Sucesso: ' + response);
            // Atualiza a página
            window.location.reload();

        },
        error: function (error) {
            console.log(error);
        }
    });
}

function getFullYear(lastTwoDigits) {
    // Obtendo o ano atual
    const currentYear = new Date().getFullYear();

    const currentYearLastTwoDigits = currentYear % 100;

    if (lastTwoDigits < currentYearLastTwoDigits) {
        // Aciona o erro de ano inválido da lib
        return 0;
    }

    // Se os dois últimos dígitos forem menores que os do ano atual, adicione 100 anos
    const year = lastTwoDigits >= currentYearLastTwoDigits
        ? currentYear - currentYearLastTwoDigits + parseInt(lastTwoDigits)
        : 0; // Setado como zero para acionar o erro de ano inválido

    return year.toString();
}



