jQuery( document ).ready(function ($) {

    function detectCardType(number) {
        let cards = {
            visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
            mastercard: /^5[1-5][0-9]{14}$/,
            amex: /^3[47][0-9]{13}$/,
            elo: /^((((636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})/,
            hipercard: /^(606282\d{10}(\d{3})?)|(3841\d{15})/
        }
    
        for (let key in cards) {
            if (cards[key].test(number)) {
                return key
            }
        }
        Swal.fire(
            'Bandeira do cartão não aceita',
            'Por favor, tente novamente com outro cartão. <br> Bandeiras aceitas: Visa, Mastercard, Amex, Elo, Hipercard',
            'info'
        )
        return 0;
    }
    
    $gn.ready(function (checkout) {
    
        // Gerar Payment Token
        jQuery("#gn_cartao_expiration").on('keyup', function () {
            let cardNumber = jQuery("#gn_cartao_number").val().replace(/\s/g, '');
            let cardCvv = jQuery("#gn_cartao_cvv").val();
            let cardExpiration = jQuery("#gn_cartao_expiration").val().split("/");
    
            if (jQuery("#gn_cartao_expiration").val().length >= 7) {
    
                Swal.fire({
                    title: 'Por favor, aguarde...',
                    text: '',
                    showConfirmButton: false,
                })
    
                let brand = detectCardType(cardNumber);
    
                if (brand != 0) {
                    let params = {
                        brand: brand, // bandeira do cartão
                        number: cardNumber, // número do cartão
                        cvv: cardCvv, // código de segurança
                        expiration_month: cardExpiration[0], // mês de vencimento
                        expiration_year: cardExpiration[1] // ano de vencimento
                    }
                    checkout.getPaymentToken(params,
                        function (error, response) {
                            if (error) {
                                // Trata o erro ocorrido
                                console.error(error);
                                Swal.fire(
                                    'Ocorreu um erro',
                                    'Houve uma falha ao gerar o Token do seu cartão. Por favor, entre em contato com o administrador da loja.',
                                    'error'
                                )
                            } else {
                                // Trata a resposta
                                jQuery('#gn_payment_token').val(response.data.payment_token);
                                swal.close()
                            }
                        }
                    );
                }
            }
    
    
        });
    
    
        jQuery("#gn_cartao_number").keyup(function () {
    
            let total = document.querySelector("#order_review > table > tfoot > tr.order-total > td > strong > span > bdi").innerHTML.replace('<span class="woocommerce-Price-currencySymbol">R$</span>', '').replaceAll(',', '').replaceAll('.', '').replaceAll('&nbsp;', '');
            console.log(total);
            let cardNumber = jQuery("#gn_cartao_number").val().replace(/\s/g, '');
            if (cardNumber.length >= 16) {
    
                let brand = detectCardType(cardNumber);
    
                if (brand != 0) {
                    checkout.getInstallments(
                        parseInt(total), // valor total da cobrança
                        brand, // bandeira do cartão
                        function (error, response) {
                            if (error) {
                                // Trata o erro ocorrido
                                console.log(error);
                                Swal.fire(
                                    'Ocorreu um erro',
                                    'Houve um erro ao recuperar o número de parcelas. Por favor, entre em contato com o administrador da loja.',
                                    'error'
                                )
                            } else {
                                // Trata a respostae
                                jQuery('#gn_cartao_no_installments').hide();
    
                                jQuery('#gn_cartao_installments')
                                    .find('option')
                                    .remove()
    
                                response.data.installments.forEach(element => {
                                    jQuery('#gn_cartao_installments').append(jQuery('<option>', {
                                        value: element.installment,
                                        text: element.installment + 'x de R$' + element.currency
                                    }));
                                });
    
                                jQuery('#gn_cartao_installments').show();
                            }
                        }
                    );
                }
            }
        })
    
    });
    
    if (document.getElementById('gn_cartao_cpf_cnpj' )) {
        jQuery( '#gn_cartao_cpf_cnpj' ).keyup(
            function () {
                 jQuery( '#gn_cartao_cpf_cnpj' ).val().length > 14 ? VMasker(document.querySelector("#gn_cartao_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_cartao_cpf_cnpj")).maskPattern("999.999.999-99");
            }
        )
        jQuery( '#gn_cartao_cpf_cnpj' ).blur(
            function () {
                var cpf_cnpj = jQuery( '#gn_cartao_cpf_cnpj' ).val();
    
                if ( ! validate_cpf_cnpj( cpf_cnpj )) {
                    jQuery( '#gn_cartao_cpf_cnpj' ).css( 'border', '1px solid red' );
                    customError("CPF/CNPJ Inválido");
                } else {
                    jQuery( '#gn_cartao_cpf_cnpj' ).css( 'border', '1px solid green' );
                }
            }
        )
    }
    
    if (document.getElementById('gn_cartao_birth' )) {
        VMasker(document.querySelector("#gn_cartao_birth")).maskPattern("99/99/9999");
        jQuery( '#gn_cartao_birth' ).blur(
            function () {
                var date = jQuery( '#gn_cartao_birth' ).val();
    
                if ( ! verify_date( date )) {
                    jQuery( '#gn_cartao_birth' ).css( 'border', '1px solid red' );
                    customError( 'Data de nascimento inválida!' );
                } else {
                    jQuery( '#gn_cartao_birth' ).css( 'border', '1px solid green' );
                }
            }
        )
    }
    
    if (document.getElementById( 'gn_cartao_expiration' )) {
        VMasker(document.querySelector("#gn_cartao_expiration")).maskPattern("99/9999");
        jQuery( '#gn_cartao_expiration' ).blur(
            function () {
                var exp = jQuery( '#gn_cartao_expiration' ).val();
    
                if ( ! validate_cartao_expiration( exp )) {
                    jQuery( '#gn_cartao_expiration' ).css( 'border', '1px solid red' );
                    customError( 'Validade do cartão inválida!' );
                } else {
                    jQuery( '#gn_cartao_expiration' ).css( 'border', '1px solid green' );
                }
            }
        )
    }
    
    if (document.getElementById('gn_cartao_number' )) {
        VMasker(document.querySelector("#gn_cartao_number")).maskPattern("9999 9999 9999 99999");
    }
    
    if (typeof jQuery( '#billing_cpf' ).val() !== 'undefined') {
    
        // cpf_cnpj cartão
        jQuery( '#gn_cartao_cpf_cnpj' ).val( jQuery( '#billing_cpf' ).val() )
        jQuery( '#gn_field_cpf_cnpj' ).hide();
        jQuery( "#gn_field_birth" ).removeClass( "form-row-last" ).addClass( "form-row-wide" );
    
        jQuery( '#billing_cpf' ).keyup(function () {
                // cpf_cnpj cartão
                jQuery( '#gn_cartao_cpf_cnpj' ).val( jQuery( '#billing_cpf' ).val() )
                jQuery( '#gn_field_cpf_cnpj' ).hide();
                jQuery( "#gn_field_birth" ).removeClass( "form-row-last" ).addClass( "form-row-wide" );
            }
        )
    }
    
    if (typeof jQuery( '#billing_cnpj' ).val() !== 'undefined') {
        jQuery( '#billing_cnpj' ).keyup(
            function () {
                // cpf_cnpj cartão
                jQuery( '#gn_cartao_cpf_cnpj' ).val( jQuery( '#billing_cnpj' ).val() )
                jQuery( '#gn_field_cpf_cnpj' ).hide();
                jQuery( "#gn_field_birth" ).removeClass( "form-row-last" ).addClass( "form-row-wide" );
            }
        )
    }
    
    if (typeof jQuery( '#billing_birthdate' ).val() !== 'undefined') {
    
        jQuery( '#gn_cartao_birth' ).val( jQuery( '#billing_birthdate' ).val() )
        jQuery( '#gn_field_birth' ).hide();
    
        jQuery( '#billing_birthdate' ).keyup(
            function () {
                jQuery( '#gn_cartao_birth' ).val( jQuery( '#billing_birthdate' ).val() )
                jQuery( '#gn_field_birth' ).hide();
            }
        )
    }
    
    // Bairro obrigatório
    if (typeof jQuery( '#billing_neighborhood_field > label > span' ).html() !== 'undefined') {
        jQuery( '#billing_neighborhood_field > label > span' ).html( jQuery( '#billing_neighborhood_field > label > span' ).html().replace( 'opcional', 'obrigatório' ) )
    }
    
    
    function validate_cpf_cnpj(cpf_cnpj) {
    
    cpf_cnpj = cpf_cnpj.trim().replace( /\./g, '' ).replace( '-', '' ).replace( '/', '' );
    
    if (cpf_cnpj < 11) {
        return false;
    }
    
    if (cpf_cnpj.length == 11) {
        var remnant, sum = 0;
    
        for (i = 1; i <= 9; i++) {
            sum = sum + parseInt( cpf_cnpj.substring( i - 1, i ) ) * (11 - i);
        }
        remnant = (sum * 10) % 11;
    
        if ((remnant == 10) || (remnant == 11)) {
            remnant = 0;
        }
        if (remnant != parseInt( cpf_cnpj.substring( 9, 10 ) )) {
            return false;
        }
    
        sum = 0;
    
        for (i = 1; i <= 10; i++) {
            sum = sum + parseInt( cpf_cnpj.substring( i - 1, i ) ) * (12 - i);
        }
        remnant = (sum * 10) % 11;
    
        if ((remnant == 10) || (remnant == 11)) {
            remnant = 0;
        }
        if (remnant != parseInt( cpf_cnpj.substring( 10, 11 ) )) {
            return false;
        }
        return true;
    
    } else if (cpf_cnpj.length == 14) {
        var tam     = cpf_cnpj.length - 2
        var numbers = cpf_cnpj.substring( 0, tam );
        var digits  = cpf_cnpj.substring( tam );
        var sum     = 0;
        var pos     = tam - 7;
    
        for (i = tam; i >= 1; i--) {
            sum += numbers.charAt( tam - i ) * pos--;
            if (pos < 2) {
                pos = 9;
            }
        }
    
        var result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    
        if (result != digits.charAt( 0 )) {
            return false;
        }
    
        tam     = tam + 1;
        numbers = cpf_cnpj.substring( 0, tam );
        sum     = 0;
        pos     = tam - 7;
    
        for (i = tam; i >= 1; i--) {
            sum += numbers.charAt( tam - i ) * pos--;
            if (pos < 2) {
                pos = 9;
            }
        }
    
        result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    
        if (result != digits.charAt( 1 )) {
            return false;
        }
    
        return true;
    }
    
    }
    
    function verify_date(date) {
    var patternValidateDate = /^(((0[1-9]|[12][0-9]|3[01])([-.\/])(0[13578]|10|12)([-.\/])(\d{4}))|(([0][1-9]|[12][0-9]|30)([-.\/])(0[469]|11)([-.\/])(\d{4}))|((0[1-9]|1[0-9]|2[0-8])([-.\/])(02)([-.\/])(\d{4}))|((29)(\.|-|\/)(02)([-.\/])([02468][048]00))|((29)([-.\/])(02)([-.\/])([13579][26]00))|((29)([-.\/])(02)([-.\/])([0-9][0-9][0][48]))|((29)([-.\/])(02)([-.\/])([0-9][0-9][2468][048]))|((29)([-.\/])(02)([-.\/])([0-9][0-9][13579][26])))$/;
    return patternValidateDate.test( date );
    }
    
    function validate_cartao_expiration(exp) {
    exp           = exp.split( '/' );
    const newDate = new Date();
    const year    = newDate.getFullYear();
    
    if ((parseInt( exp[0] ) > 12 || (parseInt( exp[0] < 0 )) || (parseInt( exp[1] < year )))) {
        return false;
    }
    return true;
    
    }
    
    function customError(msg) {
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
    ).fire( { icon: 'error', title: msg } )
    }
    })