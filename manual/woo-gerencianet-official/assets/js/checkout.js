
var errorMessage;
var id_charge = 0;
var classBackground;
var containsPrice;
var containsCollapse;

var getPaymentToken;
$gn.ready(function (checkout) {
    getPaymentToken = checkout.getPaymentToken;
});

jQuery(document).ready(function ($) {

    if ($().mask) {
        function initOptions() {
            classBackground    = $('.gn-accordion-option-background');
            containsPrice      = $('[id*="price"]');
            containsCollapse   = $('[id*="collapse-payment"]');

            if(classBackground.length === 1) {
                onCardClick(classBackground[0].id);
            }
        }

        initOptions();

        $("#cpf-cnpj").keyup((event) => documentMask(event));
        $("#pix-cpf-cnpj").keyup((event) => documentMask(event));
        $("#input-payment-card-cpf-cnpj").keyup((event) => documentMask(event));

        $(".phone-mask").keyup(function () {
            $(".phone-mask").unmask();
            var phone = $(".phone-mask").val().replace(/[^\d]+/g, '');
            if (phone.length > 10) {
                $(".phone-mask").mask("(00) 00000-0009");
            } else {
                $(".phone-mask").mask("(00) 0000-00009");
            }
            var elem = this;
            setTimeout(function () {
                // muda a posição do seletor
                elem.selectionStart = elem.selectionEnd = 10000;
            }, 0);
        });

        $('.birth-mask').mask("00/00/0000", {
            completed: function () {
                if (!verifyBirthDate($(".birth-mask").val())) {
                    showError('Data de nascimento inválida. Digite novamente.');
                } else {
                    hideError();
                }
            }, placeholder: "__/__/____"
        });

        $('#input-payment-card-number').mask('0000 0000 0000 0000999', { placeholder: "" });
        $('#input-payment-card-cvv').mask('00099', { placeholder: "" });
    }

    function documentMask(event) {
        const data = $(event.currentTarget).val();

        $(event.currentTarget).unmask();
        if (data.length <= 14) {
            $(event.currentTarget).mask("000.000.000-009");
        } else {
            $(event.currentTarget).mask("00.000.000/0000-00");
        }
        event.currentTarget.setSelectionRange(data.length, data.length);
    }

    $('.phone-mask').change(function () {
        var pattern = new RegExp(/^[ ]*(?:[^\\s]+[ ]+)+[^\\s]+[ ]*$/);
        if (!verifyPhone($(".phone-mask").val())) {
            showError('Telefone inválido. Digite novamente.');
        } else {
            hideError();
        }
    });

    jQuery('.corporate-name-corporate_validade').change(function () {
        var pattern = new RegExp(/^[ ]*(?:[^\\s]+[ ]+)+[^\\s]+[ ]*$/);
        if (!pattern.test(jQuery(this).val())) {
            showError('Nome ou Razão Social inválida(o). Digite novamente.');
        } else {
            hideError();
        }
    });

    jQuery('#background-card').click((event) => onCardClick(event.currentTarget.id));
    jQuery('#background-billet').click((event) => onCardClick(event.currentTarget.id));
    jQuery('#background-pix').click((event) => {onCardClick(event.currentTarget.id)});

    function onCardClick(id) {

        const idPayment = id.split('-')[1];//["background", "id-Payment"]
        const idPrice = `price-${idPayment}`;
        const idCollapse = `collapse-payment-${idPayment}`;

        $.each(classBackground, (index, div) => {
            const isChecket = $(div).find('input[type=radio]').prop('checked');
            if(id === div.id && !isChecket) {
                $(div).css('background-color', '#f5f5f5');
                $(div).find('input[type=radio]').prop('checked', true);
            } else {
                $(div).css('background-color', '#ffffff');
                $(div).find('input[type=radio]').prop('checked', false);
            }
        });

        $.each(containsPrice, (index, div) => {
            const isVisible = $(div).is(':visible');
            if(div.id === idPrice && !isVisible) {
                $(div).show();
            } else {
                $(div).hide();
            }
        });

        $.each(containsCollapse, (index, div) => {
            const isVisible = $(div).is(':visible');
            if(div.id === idCollapse && !isVisible) {
                $(div).slideDown();
            } else {
                $(div).slideUp();
            }
        });

        if($(`#${idPrice}`).is(':visible')) {
            $(containsPrice[containsPrice.length-1]).hide();
        } else {
            $(containsPrice[containsPrice.length-1]).show();
        }
    }

    $('input[type=radio][name=input-payment-card-brand]').change(function () {
        getInstallments(this.value);
    });

    $('#gn-pay-billet-button').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        $('#gn-pay-billet-button').prop("disabled", true);

        if (document.getElementById('paymentMethodBilletRadio').checked) {
            if (validateBilletFields()) {
                if (id_charge != 0) {
                    payBilletCharge();
                } else {
                    createCharge('billet');
                }
            }
        } else {
            showError("Selecione um método de pagamento.");
            $('#gn-pay-billet-button').prop("disabled", false);
        }

    });

    $('#gn-pay-card-button').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        $('#gn-pay-card-button').prop("disabled", true);
        if (document.getElementById('paymentMethodCardRadio').checked) {
            if (validateCardFields()) {
                if (id_charge != 0) {
                    payCardCharge();
                } else {
                    createCharge('card');
                }
            }
        } else {
            showError("Selecione um método de pagamento.");
            $('#gn-pay-card-button').prop("disabled", false);
        }

    });

    $('#gn-pay-pix-button').click((e) => {
        e.preventDefault();
        e.stopPropagation();

        $('#gn-pay-pix-button').prop('disabled', true);
        if (document.getElementById('paymentMethodPixRadio').checked) {
            if (validatePixFields()) {
                if (id_charge != 0) {
                    payPixCharge();
                } else {
                    createCharge('pix');
                }
            }
        } else {
            showError('Selecione um método de pagamento.');
            $('#gn-pay-pix-button').prop('disabled', false);
        }

    });

    function createCharge(paymentType) {

        if (paymentType == 'pix') {
            payPixCharge();
        }
        else {
            $('.gn-loading-request').fadeIn();

            var order_id = jQuery('input[name="wc_order_id"]').val(),
            data = {
                action: "woocommerce_gerencianet_create_charge",
                security: woocommerce_gerencianet_api.security,
                order_id: order_id
            };

            jQuery.ajax({
                type: "POST",
                url: woocommerce_gerencianet_api.ajax_url,
                data: data,
                success: (response) => {
                    const obj = $.parseJSON(response);
                    if (obj.code == 200) {
                        id_charge = obj.data.charge_id;
                        if (paymentType == 'billet') {
                            payBilletCharge();
                        } else if (paymentType == 'card') {
                            payCardCharge();
                        }
                    } else {
                        $('#gn-pay-billet-button').prop("disabled", false);
                        $('#gn-pay-card-button').prop("disabled", false);
                        $('#gn-pay-pix-button').prop("disabled", false);
                        $('.gn-loading-request').fadeOut();
                        if (!$('.warning-payment').is(":visible")) {
                            $('.warning-payment').slideDown();
                            scrollToTop();
                        }
                        $('.warning-payment').html('<i class="fa fa-exclamation-circle"></i> Ocorreu um erro ao tentar gerar a cobrança: <b>' + obj.message + '</b><button type="button" class="close" data-dismiss="alert">&times;</button>');
                    }

                },
                error: function () {
                    alert("error ocurred");
                }
            });
        }
    }

    function payPixCharge() {
        $('.gn-loading-request').fadeIn();

        var data = {
            action: 'woocommerce_gerencianet_pay_pix',
            security: woocommerce_gerencianet_api.security,
            charge_id: id_charge,
            order_id: jQuery('input[name="wc_order_id"]').val(),
            cpf_cnpj: jQuery('#pix-cpf-cnpj').val().replace(/[^\d]+/g, '')
        };

        jQuery.ajax({
            type: "POST",
            url: woocommerce_gerencianet_api.ajax_url,
            data: data,
            success: function (response) {
                var obj = $.parseJSON(response);

                if(!!obj.txid) {
                    var redirect = $('<form action="' + home_url + '&method=pix&charge_id=' + obj.charge_id + '" method="post">' +
                        '<input type="text" name="charge" value="' + obj.charge_id + '" />' +
                        '</form>');
                    $('body').append(redirect);
                    redirect.submit();
                } else {
                    $('#gn-pay-pix-button').prop('disabled', false);
                    $('.gn-pix-field').show();
                    $('.gn-loading-request').fadeOut();
                    showError(obj.message);
                }
            },
            error: function () {
                alert("error ocurred");
            }
        });
    }

    function validatePixFields() {
        errorMessage = '';
        if ($("#pix-cpf-cnpj").val().replace(/[^\d]+/g, '').length <= 11) {
            if (!(verifyCPF($('#pix-cpf-cnpj').val()))) {
                errorMessage = 'O CPF digitado é inválido.';
            }
        }
        else {
            if (!(verifyCNPJ($('#pix-cpf-cnpj').val()))) {
                errorMessage = 'O CNPJ digitado é inválido.';
            }
        }

        if (errorMessage != '') {
            showError(errorMessage);
            $('#gn-pay-pix-button').prop("disabled", false);
            return false;
        } else {
            return true;
        }
    }

    function payBilletCharge() {
        $('.gn-loading-request').fadeIn();

        var data = {
            action: "woocommerce_gerencianet_pay_billet",
            security: woocommerce_gerencianet_api.security,
            charge_id: id_charge,
            order_id: jQuery('input[name="wc_order_id"]').val(),
            name_corporate: jQuery('#name_corporate').val(),
            cpf_cnpj: jQuery('#cpf-cnpj').val().replace(/[^\d]+/g, ''),
            phone_number: jQuery('#phone_number').val().replace(/[^\d]+/g, ''),
            email: jQuery('#input-payment-billet-email').val()
        };

        jQuery.ajax({
            type: "POST",
            url: woocommerce_gerencianet_api.ajax_url,
            data: data,
            success: function (response) {
                var obj = $.parseJSON(response);
                if (obj.code == 200) {
                    var url = encodeURIComponent(obj.data.link);
                    var redirect = $('<form action="' + home_url + '&method=billet&charge_id=' + obj.data.charge_id + '" method="post">' +
                        '<input type="text" name="billet" value="' + url + '" />' +
                        '<input type="text" name="charge" value="' + obj.data.charge_id + '" />' +
                        '</form>');
                    $('body').append(redirect);
                    redirect.submit();
                } else {
                    $('#gn-pay-billet-button').prop("disabled", false);
                    $('.gn-billet-field').show();
                    $('.gn-loading-request').fadeOut();
                    showError(obj.message);
                }
            },
            error: function () {
                alert("error ocurred");
            }
        });

    }

    function validateBilletFields() {
        errorMessage = '';
        if ($("#cpf-cnpj").val().replace(/[^\d]+/g, '').length <= 11) {
            if (!(verifyCPF($('#cpf-cnpj').val()))) {
                errorMessage = 'O CPF digitado é inválido.';
            }
        }
        else {
            if (!(verifyCNPJ($('#cpf-cnpj').val()))) {
                errorMessage = 'O CNPJ digitado é inválido.';
            }
        }
        if ($('#name_corporate').val() == "") {
            errorMessage = 'Digite o Nome ou a Razão Social.';
        } else if (!(verifyPhone($('#phone_number').val()))) {
            errorMessage = 'O Telefone digitado é inválido.';
        } else if (!(verifyEmail($('#input-payment-billet-email').val()))) {
            errorMessage = 'O e-mail digitado é inválido.';
        }

        if (errorMessage != '') {
            showError(errorMessage);
            $('#gn-pay-billet-button').prop("disabled", false);
            return false;
        } else {
            return true;
        }
    }

    function validateCardFields() {
        errorMessage = '';
        if ($("#input-payment-card-cpf-cnpj").val().replace(/[^\d]+/g, '').length <= 11) {
            if (!(verifyCPF($('#input-payment-card-cpf-cnpj').val()))) {
                errorMessage = 'O CPF digitado é inválido.';
            }
        }
        else {
            if (!(verifyCNPJ($('#input-payment-card-cpf-cnpj').val()))) {
                errorMessage = 'O CNPJ digitado é inválido.';
            }
        }
        if ($('#input-payment-card-name-corporate').val() == "") {
            errorMessage = 'Digite o Nome ou a Razão Social.';
        } else if (!(verifyPhone($('#input-payment-card-phone').val()))) {
            errorMessage = 'O Telefone digitado é inválido.';
        } else if (!(verifyEmail($('#input-payment-card-email').val()))) {
            errorMessage = 'O e-mail digitado é inválido.';
        } else if (!(verifyBirthDate($('#input-payment-card-birth').val()))) {
            errorMessage = 'A data de nascimento digitada é inválida.';
        } else if ($('#input-payment-card-street').val() == "") {
            errorMessage = 'Digite o endereço de cobrança.';
        } else if ($('#input-payment-card-address-number').val() == "") {
            errorMessage = 'Digite número do endereço de cobrança.';
        } else if ($('#input-payment-card-neighborhood').val() == "") {
            errorMessage = 'Digite bairro do endereço de cobrança.';
        } else if ($('#input-payment-card-zipcode').val() == "") {
            errorMessage = 'Digite CEP do endereço de cobrança.';
        } else if ($('#input-payment-card-city').val() == "") {
            errorMessage = 'Digite a cidade do endereço de cobrança.';
        } else if ($('#input-payment-card-state').val() == "") {
            errorMessage = 'Selecione o estado do endereço de cobrança.';
        } else if ($('input[name=input-payment-card-brand]:checked', '#payment-card-form').val() == "") {
            errorMessage = 'Selecione a bandeira do cartão de crédito.';
        } else if ($('#input-payment-card-installments').val() == "") {
            errorMessage = 'Selecione a quantidade de parcelas que deseja.';
        } else if ($('#input-payment-card-number').val() == "") {
            errorMessage = 'Digite o número do cartão de crédito.';
        } else if ($('#input-payment-card-cvv').val() == "") {
            errorMessage = 'Digite o código de segurança do cartão de crédito.';
        } else if ($('#input-payment-card-expiration-month').val() == "" || $('#input-payment-card-expiration-year').val() == "") {
            errorMessage = 'Digite os dados de validade do cartão de crédito.';
        }

        if (errorMessage != '') {
            showError(errorMessage);
            $('#gn-pay-card-button').prop("disabled", false);
            return false;
        } else {
            return true;
        }
    }

    function showError(message) {
        if (!$('.warning-payment').is(":visible")) {
            $('.warning-payment').slideDown();
        }
        scrollToTop();
        jQuery("#wc-gerencianet-messages").html('<div class="woocommerce-error">' + message + ' </div>')
    }

    function hideError() {
        $('.warning-payment').slideUp();
    }

    function payCardCharge() {

        $('.gn-loading-request').fadeIn();

        card_brand = $('input[name=input-payment-card-brand]:checked', '#payment-card-form').val()
        card_number = $("#input-payment-card-number").val();
        card_cvv = $("#input-payment-card-cvv").val();
        expiration_month = $("#input-payment-card-expiration-month").val();
        expiration_year = $("#input-payment-card-expiration-year").val();

        var callback = function (error, response) {
            if (error) {

                showError("Os dados do cartão digitados são inválidos. Tente novamente.");
                $('#gn-pay-card-button').prop("disabled", false);

                $('.gn-loading-request').fadeOut();

            } else {
                var dateBirth = $('#input-payment-card-birth').val().split("/");

                var data = {
                    action: "woocommerce_gerencianet_pay_card",
                    security: woocommerce_gerencianet_api.security,
                    charge_id: id_charge,
                    order_id: jQuery('input[name="wc_order_id"]').val(),
                    name_corporate: jQuery('#input-payment-card-name-corporate').val(),
                    cpf_cnpj: jQuery('#input-payment-card-cpf-cnpj').val().replace(/[^\d]+/g, ''),
                    phone_number: jQuery('#input-payment-card-phone').val().replace(/[^\d]+/g, ''),
                    payment_token: response.data.payment_token,
                    birth: dateBirth[2] + "-" + dateBirth[1] + "-" + dateBirth[0],
                    email: $('#input-payment-card-email').val(),
                    street: $('#input-payment-card-street').val(),
                    number: $('#input-payment-card-address-number').val(),
                    neighborhood: $('#input-payment-card-neighborhood').val(),
                    complement: $('#input-payment-card-complement').val(),
                    zipcode: $('#input-payment-card-zipcode').val().replace(/[^\d]+/g, ''),
                    city: $('#input-payment-card-city').val(),
                    state: $('#input-payment-card-state').val(),
                    installments: $('#input-payment-card-installments').val()
                };

                jQuery.ajax({
                    type: "POST",
                    url: woocommerce_gerencianet_api.ajax_url,
                    data: data,
                    success: function (response) {
                        var obj = $.parseJSON(response);
                        if (obj.code == 200) {
                            var url = encodeURIComponent(obj.data.link);
                            var redirect = $('<form action="' + home_url + '&method=card&charge_id=' + obj.data.charge_id + '" method="post">' +
                                '<input type="text" name="charge" value="' + obj.data.charge_id + '" />' +
                                '</form>');
                            $('body').append(redirect);
                            redirect.submit();
                        } else {
                            $('#gn-pay-card-button').prop("disabled", false);
                            showError(obj.message);
                            $('.gn-card-field').show();
                            $('.gn-loading-request').fadeOut();
                        }

                    },
                    error: function () {
                        alert("error ocurred");
                    }
                });
            }
        };

        getPaymentToken({
            brand: card_brand,
            number: card_number,
            cvv: card_cvv,
            expiration_month: expiration_month,
            expiration_year: expiration_year
        }, callback);
    }

    function getInstallments(card_brand) {
        $('#input-payment-card-installments').html('<option value="">Aguarde, carregando...</option>').show();
        var order_id = jQuery('input[name="wc_order_id"]').val(),
            data = {
                action: "woocommerce_gerencianet_get_installments",
                security: woocommerce_gerencianet_api.security,
                order_id: order_id,
                brand: card_brand
            };

        jQuery.ajax({
            type: "POST",
            url: woocommerce_gerencianet_api.ajax_url,
            data: data,
            success: function (response) {
                var obj = $.parseJSON(response);
                if (obj.code == 200) {

                    var options = '';
                    for (var i = 0; i < obj.data.installments.length; i++) {
                        options += '<option value="' + obj.data.installments[i].installment + '">' + obj.data.installments[i].installment + 'x de R$' + obj.data.installments[i].currency + '</option>';
                    }
                    $('#input-payment-card-installments').html(options).show();
                }
            },
            error: function () {
                alert("error ocurred");
            }
        });
    }


    function verifyCPF(cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');

        if (cpf == '' || cpf.length != 11) return false;

        var resto;
        var soma = 0;

        if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" || cpf == "12345678909") return false;

        for (i = 1; i <= 9; i++) soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
        resto = (soma * 10) % 11;

        if ((resto == 10) || (resto == 11)) resto = 0;
        if (resto != parseInt(cpf.substring(9, 10))) return false;

        soma = 0;
        for (i = 1; i <= 10; i++) soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
        resto = (soma * 10) % 11;

        if ((resto == 10) || (resto == 11)) resto = 0;
        if (resto != parseInt(cpf.substring(10, 11))) return false;
        return true;
    }

    function verifyCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');

        if (cnpj == '' || cnpj.length != 14) return false;

        if (cnpj == "00000000000000" || cnpj == "11111111111111" || cnpj == "22222222222222" || cnpj == "33333333333333" || cnpj == "44444444444444" || cnpj == "55555555555555" || cnpj == "66666666666666" || cnpj == "77777777777777" || cnpj == "88888888888888" || cnpj == "99999999999999") return false;

        var tamanho = cnpj.length - 2
        var numeros = cnpj.substring(0, tamanho);
        var digitos = cnpj.substring(tamanho);
        var soma = 0;
        var pos = tamanho - 7;

        for (i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2)
                pos = 9;
        }

        var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

        if (resultado != digitos.charAt(0)) return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;

        for (i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }

        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

        if (resultado != digitos.charAt(1)) return false;

        return true;
    }

    function verifyPhone(phone_number) {
        if (phone_number.length < 14) {
            showError("O telefone informado é inválido.");
            return false;
        } else {
            var pattern = new RegExp(/^[1-9]{2}9?[0-9]{8}$/);
            if (pattern.test(phone_number.replace(/[^\d]+/g, ''))) {
                hideError();
                return true;
            } else {
                return false;
            }
        }
    }

    function verifyEmail(email) {
        var pattern = new RegExp(/^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/);
        return pattern.test(email);
    }

    function verifyBirthDate(birth) {
        var pattern = new RegExp(/^[12][0-9]{3}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12][0-9]|3[01])$/);
        var date = birth.split("/");
        return pattern.test(date[2] + "-" + date[1] + "-" + date[0]);
    }

    function scrollToTop() {
        $("html, body").animate({ scrollTop: $("#wc-gerencianet-messages").offset().top - 80 }, "slow");
    }

});
