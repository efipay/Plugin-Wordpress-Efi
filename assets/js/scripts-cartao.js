jQuery(document).ready(function ($) {

    if (!document.getElementById('swalCss')) {
        let swalCss = $("<style id='swalCss'></style>").html(".colored-toast .swal2-title {color: white;}.colored-toast .swal2-close {color: white;}.colored-toast .swal2-html-container {color: white;}.colored-toast.swal2-icon-error {background-color: #f27474 !important;}")
        $("#payment").after(swalCss);
    }



    // Gerar Payment Token
    jQuery("#gn_cartao_expiration").on('keyup', function () {
        let cardExpiration = jQuery("#gn_cartao_expiration").val().split("/");

        if (jQuery("#gn_cartao_expiration").val().length >= 7) {

            Swal.fire({
                title: 'Por favor, aguarde...',
                text: '',
                showConfirmButton: false,
            })

            EfiJs.CreditCard
                // .debugger(true)
                .setCardNumber(jQuery("#gn_cartao_number").val())
                .verifyCardBrand()
                .then(brand => {
                    if (brand !== 'undefined') {
                        EfiJs.CreditCard
                            //.debugger(true)
                            .setAccount(options.payeeCode)
                            .setEnvironment(options.enviroment) // 'production' or 'homologation'
                            .setCreditCardData({
                                brand: brand,
                                number: jQuery("#gn_cartao_number").val(),
                                cvv: jQuery("#gn_cartao_cvv").val(),
                                expirationMonth: cardExpiration[0],
                                expirationYear: cardExpiration[1]
                            })
                            .getPaymentToken()
                            .then(data => {
                                // Trata a resposta
                                jQuery('#gn_payment_token').val(data.payment_token);
                                swal.close()
                            }).catch(err => {
                                swalError(err);
                                throw new Error(`Something went wrong in getPaymentToken(.\n ${err}`);
                            });
                    }
                }).catch(err => {
                    swalError(err);
                    throw new Error(`Something went wrong in verifyCardBrand(.\n ${err}`);
                });
        }

    });


    // Buscar Parcelas
    jQuery("#gn_cartao_number").keyup(function () {

        let total = document.querySelector("#gn_payment_total").value;
        total = parseInt(total.replace('.', ''));
        console.log(total);

        if (jQuery("#gn_cartao_number").val().length >= 19) {

            EfiJs.CreditCard
                // .debugger(true)
                .setCardNumber(jQuery("#gn_cartao_number").val())
                .verifyCardBrand()
                .then(brand => {
                    if (brand !== 'undefined') {
                        EfiJs.CreditCard
                            //.debugger(true)
                            .setAccount(options.payeeCode)
                            .setEnvironment(options.enviroment) // 'production' or 'homologation'
                            .setBrand(brand)
                            .setTotal(total)
                            .getInstallments()
                            .then(response => {
                                jQuery('#gn_cartao_no_installments').hide();
                                jQuery('#gn_cartao_installments')
                                    .find('option')
                                    .remove()

                                response.installments.forEach(element => {
                                    jQuery('#gn_cartao_installments').append(jQuery('<option>', {
                                        value: element.installment,
                                        text: element.installment + 'x de R$' + element.currency
                                    }));
                                });

                                jQuery('#gn_cartao_installments').show();
                            }).catch(err => {
                                swalError(err);
                                throw new Error(`Something went wrong in getInstallments().\n ${err}`);
                            });
                    }
                }).catch(err => {
                    swalError(err);
                    throw new Error(`Something went wrong in verifyCardBrand(.\n ${err}`);
                });


        }
    })


    if (document.getElementById('gn_cartao_cpf_cnpj')) {
        jQuery('#gn_cartao_cpf_cnpj').keyup(
            function () {
                jQuery('#gn_cartao_cpf_cnpj').val().length > 14 ? VMasker(document.querySelector("#gn_cartao_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_cartao_cpf_cnpj")).maskPattern("999.999.999-99");
            }
        )
        jQuery('#gn_cartao_cpf_cnpj').blur(
            function () {
                var cpf_cnpj = jQuery('#gn_cartao_cpf_cnpj').val();

                if (!validate_cpf_cnpj(cpf_cnpj)) {
                    jQuery('#gn_cartao_cpf_cnpj').css('border', '1px solid red');
                    toastError("CPF/CNPJ Inválido");
                } else {
                    jQuery('#gn_cartao_cpf_cnpj').css('border', '1px solid green');
                }
            }
        )
    }

    if (document.getElementById('gn_cartao_birth')) {
        VMasker(document.querySelector("#gn_cartao_birth")).maskPattern("99/99/9999");
        jQuery('#gn_cartao_birth').blur(
            function () {
                var date = jQuery('#gn_cartao_birth').val();

                if (!verify_date(date)) {
                    jQuery('#gn_cartao_birth').css('border', '1px solid red');
                    toastError('Data de nascimento inválida!');
                } else {
                    jQuery('#gn_cartao_birth').css('border', '1px solid green');
                }
            }
        )
    }

    if (document.getElementById('gn_cartao_expiration')) {
        VMasker(document.querySelector("#gn_cartao_expiration")).maskPattern("99/9999");
        jQuery('#gn_cartao_expiration').blur(
            function () {
                var exp = jQuery('#gn_cartao_expiration').val();

                if (!validate_cartao_expiration(exp)) {
                    jQuery('#gn_cartao_expiration').css('border', '1px solid red');
                    toastError('Validade do cartão inválida!');
                } else {
                    jQuery('#gn_cartao_expiration').css('border', '1px solid green');
                }
            }
        )
    }

    if (document.getElementById('gn_cartao_number')) {
        VMasker(document.querySelector("#gn_cartao_number")).maskPattern("9999 9999 9999 99999");
    }

    if (typeof jQuery('#billing_cpf').val() !== 'undefined') {

        // cpf_cnpj cartão
        jQuery('#gn_cartao_cpf_cnpj').val(jQuery('#billing_cpf').val())
        jQuery('#gn_field_cpf_cnpj').hide();
        jQuery("#gn_field_birth").removeClass("form-row-last").addClass("form-row-wide");

        jQuery('#billing_cpf').keyup(function () {
            // cpf_cnpj cartão
            jQuery('#gn_cartao_cpf_cnpj').val(jQuery('#billing_cpf').val())
            jQuery('#gn_field_cpf_cnpj').hide();
            jQuery("#gn_field_birth").removeClass("form-row-last").addClass("form-row-wide");
        }
        )
    }

    if (typeof jQuery('#billing_cnpj').val() !== 'undefined') {
        jQuery('#billing_cnpj').keyup(
            function () {
                // cpf_cnpj cartão
                jQuery('#gn_cartao_cpf_cnpj').val(jQuery('#billing_cnpj').val())
                jQuery('#gn_field_cpf_cnpj').hide();
                jQuery("#gn_field_birth").removeClass("form-row-last").addClass("form-row-wide");
            }
        )
    }

    if (typeof jQuery('#billing_birthdate').val() !== 'undefined') {

        jQuery('#gn_cartao_birth').val(jQuery('#billing_birthdate').val())
        jQuery('#gn_field_birth').hide();

        jQuery('#billing_birthdate').keyup(
            function () {
                jQuery('#gn_cartao_birth').val(jQuery('#billing_birthdate').val())
                jQuery('#gn_field_birth').hide();
            }
        )
    }

    // Bairro obrigatório
    if (typeof jQuery('#billing_neighborhood_field > label > span').html() !== 'undefined') {
        jQuery('#billing_neighborhood_field > label > span').html(jQuery('#billing_neighborhood_field > label > span').html().replace('opcional', 'obrigatório'))
    }


    function validate_cpf_cnpj(cpf_cnpj) {

        cpf_cnpj = cpf_cnpj.trim().replace(/\./g, '').replace('-', '').replace('/', '');

        if (cpf_cnpj < 11) {
            return false;
        }

        if (cpf_cnpj.length == 11) {
            var remnant, sum = 0;

            for (i = 1; i <= 9; i++) {
                sum = sum + parseInt(cpf_cnpj.substring(i - 1, i)) * (11 - i);
            }
            remnant = (sum * 10) % 11;

            if ((remnant == 10) || (remnant == 11)) {
                remnant = 0;
            }
            if (remnant != parseInt(cpf_cnpj.substring(9, 10))) {
                return false;
            }

            sum = 0;

            for (i = 1; i <= 10; i++) {
                sum = sum + parseInt(cpf_cnpj.substring(i - 1, i)) * (12 - i);
            }
            remnant = (sum * 10) % 11;

            if ((remnant == 10) || (remnant == 11)) {
                remnant = 0;
            }
            if (remnant != parseInt(cpf_cnpj.substring(10, 11))) {
                return false;
            }
            return true;

        } else if (cpf_cnpj.length == 14) {
            var tam = cpf_cnpj.length - 2
            var numbers = cpf_cnpj.substring(0, tam);
            var digits = cpf_cnpj.substring(tam);
            var sum = 0;
            var pos = tam - 7;

            for (i = tam; i >= 1; i--) {
                sum += numbers.charAt(tam - i) * pos--;
                if (pos < 2) {
                    pos = 9;
                }
            }

            var result = sum % 11 < 2 ? 0 : 11 - sum % 11;

            if (result != digits.charAt(0)) {
                return false;
            }

            tam = tam + 1;
            numbers = cpf_cnpj.substring(0, tam);
            sum = 0;
            pos = tam - 7;

            for (i = tam; i >= 1; i--) {
                sum += numbers.charAt(tam - i) * pos--;
                if (pos < 2) {
                    pos = 9;
                }
            }

            result = sum % 11 < 2 ? 0 : 11 - sum % 11;

            if (result != digits.charAt(1)) {
                return false;
            }

            return true;
        }

    }

    function verify_date(date) {
        var patternValidateDate = /^(((0[1-9]|[12][0-9]|3[01])([-.\/])(0[13578]|10|12)([-.\/])(\d{4}))|(([0][1-9]|[12][0-9]|30)([-.\/])(0[469]|11)([-.\/])(\d{4}))|((0[1-9]|1[0-9]|2[0-8])([-.\/])(02)([-.\/])(\d{4}))|((29)(\.|-|\/)(02)([-.\/])([02468][048]00))|((29)([-.\/])(02)([-.\/])([13579][26]00))|((29)([-.\/])(02)([-.\/])([0-9][0-9][0][48]))|((29)([-.\/])(02)([-.\/])([0-9][0-9][2468][048]))|((29)([-.\/])(02)([-.\/])([0-9][0-9][13579][26])))$/;
        return patternValidateDate.test(date);
    }

    function validate_cartao_expiration(exp) {
        exp = exp.split('/');
        const newDate = new Date();
        const year = newDate.getFullYear();

        if ((parseInt(exp[0]) > 12 || (parseInt(exp[0] < 0)) || (parseInt(exp[1] < year)))) {
            return false;
        }
        return true;

    }

    function swalError(msg) {
        if (msg == 'Error: Escolha uma bandeira entre "visa", "mastercard", "amex", "elo", "hipercard"')
            msg = 'As bandeiras aceitas são: VISA, MASTERCARD, AMERICAN EXPRESS, ELO E HIPERCARD.'

        if (msg == 'Error: O total fornecido é superior ao limite máximo por transação.')
            msg = 'O valor total da compra é superior ao limite para emissão.'

        if (msg.toString().includes('Identificador de conta'))
            msg = 'Identificador de Conta inválido. Entre em contato com o administrador da loja.'

        if (msg != null && msg != '') {
            console.log(msg);
            Swal.fire({
                icon: 'error',
                title: 'Houve um Erro!',
                text: msg.toString().replace('Error:', ''),
            })
        }


    }

    function toastError(msg) {
        Swal.mixin(
            {
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            }
        ).fire({ icon: 'error', title: msg })
    }
})