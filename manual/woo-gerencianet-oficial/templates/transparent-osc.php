<?php
/**
 * Gerencianet Payment template.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

?>
<script type="text/javascript">

<?php echo html_entity_decode($script_load);?>

jQuery(document).ready(function($){

    var billetActive = "<?php echo $billet_option; ?>";
    var cardActive = "<?php echo $card_option; ?>";

    if (billetActive=="yes") {
        $('#collapse-payment-card').hide();
        $('#collapse-payment-billet').show();
        $('#paymentMethodBilletRadio').prop('checked', true);
        $('#paymentMethodCardRadio').prop('checked', false);
        $("#gn-billet-payment-option").removeClass('gn-osc-payment-option-unselected');
        $("#gn-billet-payment-option").addClass('gn-osc-payment-option-selected');
        $("#gn-card-payment-option").removeClass('gn-osc-payment-option-selected');
        $("#gn-card-payment-option").addClass('gn-osc-payment-option-unselected');
    } else if (billetActive=="no" && cardActive=="yes") {
        $('#collapse-payment-card').show();
        $('#collapse-payment-billet').hide();
        $('#paymentMethodBilletRadio').prop('checked', false);
        $('#paymentMethodCardRadio').prop('checked', true);
        $("#gn-billet-payment-option").removeClass('gn-osc-payment-option-selected');
        $("#gn-billet-payment-option").addClass('gn-osc-payment-option-unselected');
        $("#gn-card-payment-option").removeClass('gn-osc-payment-option-unselected');
        $("#gn-card-payment-option").addClass('gn-osc-payment-option-selected');
    }

    if ($('#gn_billet_full_name').val()=="")  {
        if (typeof $('#billing_first_name').val() != "undefined")
            $('#gn_billet_full_name').val($('#billing_first_name').val());
        if (typeof $('#billing_last_name').val() != "undefined")
            $('#gn_billet_full_name').val($('#gn_billet_full_name').val() + " " + $('#billing_last_name').val());
    }
    if ($('#gn_billet_email').val()=="")
        $('#gn_billet_email').val($('#billing_email').val());
    if ($('#gn_billet_phone_number').val()=="")    
        $('#gn_billet_phone_number').val($('#billing_phone').val());
    if ($('#gn_billet_cpf').val()=="")
        $('#gn_billet_cpf').val($('#billing_cpf').val());
    if ($('#gn_billet_corporate_name').val()=="")
        $('#gn_billet_corporate_name').val($('#billing_company').val());
    if ($('#gn_billet_cnpj').val()=="")
        $('#gn_billet_cnpj').val($('#billing_cnpj').val());

    if ($('#gn_card_full_name').val()=="") {
        if (typeof $('#billing_first_name').val() != "undefined")
            $('#gn_card_full_name').val($('#billing_first_name').val());
        if (typeof $('#billing_last_name').val() != "undefined")
            $('#gn_card_full_name').val($('#gn_card_full_name').val() + " " + $('#billing_last_name').val());
    }

    if ($('#gn_card_email').val()=="")
        $('#gn_card_email').val($('#billing_email').val());
    if ($('#gn_card_phone_number').val()=="")    
        $('#gn_card_phone_number').val($('#billing_phone').val());
    if ($('#gn_card_cpf').val()=="")
        $('#gn_card_cpf').val($('#billing_cpf').val());
    if ($('#gn_card_corporate_name').val()=="")
        $('#gn_card_corporate_name').val($('#billing_company').val());
    if ($('#gn_card_cnpj').val()=="")
        $('#gn_card_cnpj').val($('#billing_cnpj').val());
    if ($('#gn_card_birth').val()=="")
        $('#gn_card_birth').val($('#billing_birthdate').val());
    if ($('#gn_card_street').val()=="")
        $('#gn_card_street').val($('#billing_address_1').val());
    if ($('#gn_card_street_number').val()=="")
        $('#gn_card_street_number').val($('#billing_number').val());
    if ($('#gn_card_neighborhood').val()=="")
        $('#gn_card_neighborhood').val($('#billing_neighborhood').val());
    if ($('#gn_card_complement').val()=="")
        $('#gn_card_complement').val($('#billing_address_2').val());
    if ($('#gn_card_zipcode').val()=="")
        $('#gn_card_zipcode').val($('#billing_postcode').val());
    if ($('#gn_card_city').val()=="")
        $('#gn_card_city').val($('#billing_city').val());
    if ($('#gn_card_state').val()=="")
        $('#gn_card_state').val($('#billing_state').val());

    validateBilletCustomerData();
    validateCardCustomerData();

    $('#billing_first_name').change(function() {
        if (typeof $('#billing_first_name').val() != "undefined") {
            $('#gn_billet_full_name').val($('#billing_first_name').val());
            $('#gn_card_full_name').val($('#billing_first_name').val());
        }
        if (typeof $('#billing_last_name').val() != "undefined") {
            $('#gn_billet_full_name').val($('#gn_billet_full_name').val() + " " + $('#billing_last_name').val());
            $('#gn_card_full_name').val($('#gn_card_full_name').val() + " " + $('#billing_last_name').val());
        }
        validateBilletCustomerData();
        validateCardCustomerData();
    });

    $('#billing_last_name').change(function() {
        if (typeof $('#billing_first_name').val() != "undefined") {
            $('#gn_billet_full_name').val($('#billing_first_name').val());
            $('#gn_card_full_name').val($('#billing_first_name').val());
        }
        if (typeof $('#billing_last_name').val() != "undefined") {
            $('#gn_billet_full_name').val($('#gn_billet_full_name').val() + " " + $('#billing_last_name').val());
            $('#gn_card_full_name').val($('#gn_card_full_name').val() + " " + $('#billing_last_name').val());
        }
        validateBilletCustomerData();
        validateCardCustomerData();
    });

    $('#billing_email').change(function() {
        $('#gn_billet_email').val($('#billing_email').val());
        $('#gn_card_email').val($('#billing_email').val());
        validateBilletCustomerData();
        validateCardCustomerData();
    });

    $('#billing_phone').change(function() {
        $('#gn_billet_phone_number').val($('#billing_phone').val());
        $('#gn_card_phone_number').val($('#billing_phone').val());
        validateBilletCustomerData();
        validateCardCustomerData();
    });

    $('#billing_cpf').change(function() {
        $('#gn_billet_cpf').val($('#billing_cpf').val());
        $('#gn_card_cpf').val($('#billing_cpf').val());
        validateBilletCustomerData();
        validateCardCustomerData();
    });

    $('#billing_company').change(function() {
        $('#gn_billet_corporate_name').val($('#billing_company').val());
        $('#gn_card_corporate_name').val($('#billing_company').val());
    });

    $('#billing_cnpj').change(function() {
        $('#gn_billet_cnpj').val($('#billing_cnpj').val());
        $('#gn_card_cnpj').val($('#billing_cnpj').val());
    });

    $('#billing_birthdate').change(function() {
        $('#gn_card_birth').val($('#billing_birthdate').val());
        validateCardCustomerData();
    });

    $('#billing_address_1').change(function() {
        $('#gn_card_street').val($('#billing_address_1').val());
        validateCardCustomerData();
    });

    $('#billing_number').change(function() {
        $('#gn_card_street_number').val($('#billing_number').val());
        validateCardCustomerData();
    });

    $('#billing_neighborhood').change(function() {
        $('#gn_card_neighborhood').val($('#billing_neighborhood').val());
        validateCardCustomerData();
    });

    $('#billing_address_2').change(function() {
        $('#gn_card_complement').val($('#billing_address_2').val());
        validateCardCustomerData();
    });

    $('#billing_postcode').change(function() {
        $('#gn_card_zipcode').val($('#billing_postcode').val());
        validateCardCustomerData();
    });

    $('#billing_city').change(function() {
        $('#gn_card_city').val($('#billing_city').val());
        validateCardCustomerData();
    });

    $('#billing_state').change(function() {
        $('#gn_card_state').val($('#billing_state').val());
        validateCardCustomerData();
    });

    $('#gn_card_number_card,#gn_card_cvv,#gn_card_expiration_month,#gn_card_expiration_year,input[name=gn_card_brand]').change(function() {
        generatePaymentToken();
    });

    $('form[name="checkout"] input[type="submit"]').click(function(event){

        if ($('input[type=radio][name=paymentMethodRadio]:checked').val()=="billet") {
            if (!billetValidateFields()) {
                if (typeof $('#payment_method_gerencianet_oficial').val() != "undefined") {
                    if ($("#payment_method_gerencianet_oficial:checked").val() == "gerencianet_oficial") {
                        event.preventDefault();
                    }
                }
            }
        } else {
            if (!cardValidateFields()) {
                if (typeof $('#payment_method_gerencianet_oficial').val() != "undefined") {
                    if ($("#payment_method_gerencianet_oficial:checked").val() == "gerencianet_oficial") {
                        event.preventDefault();
                    }
                }
            } else if ($('#gn_card_number_card').val()=="") {
                if (!generatePaymentToken()) {
                    if (typeof $('#payment_method_gerencianet_oficial').val() != "undefined") {
                        if ($("#payment_method_gerencianet_oficial:checked").val() == "gerencianet_oficial") {
                            event.preventDefault();
                        }
                    }
                    showError("Dados do cartão inválidos. Digite Novamente.");
                }
            }
        }
        
    });

    function generatePaymentToken() {
        card_brand = $('input[name=gn_card_brand]:checked').val()
        card_number = $("#gn_card_number_card").val();
        card_cvv = $("#gn_card_cvv").val();
        expiration_month = $("#gn_card_expiration_month").val();
        expiration_year = $("#gn_card_expiration_year").val();

        if (card_brand!="" && card_number!=""  && card_cvv!=""  && expiration_month!=""  && expiration_year!="" ) {
            var callback = function(error, response) {
                if(error) {
                    return false;
                } else {
                    hideError();
                    $("#gn_card_payment_token").val(response.data.payment_token);
                    return true;
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
    }

    $('#gn_billet_cnpj').change(function() {
        if (verifyCNPJ($('#gn_billet_cnpj').val())) {
            $('#gn_billet_cnpj').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_billet_cnpj').addClass("gn-inputs-error");
            showError("CNPJ inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_billet_corporate_name').change(function() {
        if (validateName($('#gn_billet_corporate_name').val())) {
            $('#gn_billet_corporate_name').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_billet_corporate_name').addClass("gn-inputs-error");
            showError("Razão Social inválida. Por favor, digite novamente.");
        }
    });

    $('#gn_billet_full_name').change(function() {
        if (validateName($('#gn_billet_full_name').val())) {
            $('#gn_billet_full_name').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_billet_full_name').addClass("gn-inputs-error");
            showError("Nome inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_billet_email').change(function() {
        if (validateEmail($('#gn_billet_email').val())) {
            $('#gn_billet_email').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_billet_email').addClass("gn-inputs-error");
            showError("Email inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_billet_cpf').change(function() {
        if (verifyCPF($('#gn_billet_cpf').val())) {
            $('#gn_billet_cpf').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_billet_cpf').addClass("gn-inputs-error");
            showError("CPF inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_billet_phone_number').change(function() {
        if (validatePhone($('#gn_billet_phone_number').val())) {
            $('#gn_billet_phone_number').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_billet_phone_number').addClass("gn-inputs-error");
            showError("Telefone inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_cnpj').change(function() {
        if (verifyCNPJ($('#gn_card_cnpj').val())) {
            $('#gn_card_cnpj').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_cnpj').addClass("gn-inputs-error");
            showError("CNPJ inválido. Por favor, digite novamente.");
        }
    });

     $('#gn_card_corporate_name').change(function() {
        if (validateName($('#gn_card_corporate_name').val())) {
            $('#gn_card_corporate_name').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_corporate_name').addClass("gn-inputs-error");
            showError("Razão Social inválida. Por favor, digite novamente.");
        }
    });

    $('#gn_card_full_name').change(function() {
        if (validateName($('#gn_card_full_name').val())) {
            $('#gn_card_full_name').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_full_name').addClass("gn-inputs-error");
            showError("Nome inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_email').change(function() {
        if (validateEmail($('#gn_card_email').val())) {
            $('#gn_card_email').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_email').addClass("gn-inputs-error");
            showError("Email inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_cpf').change(function() {
        if (verifyCPF($('#gn_card_cpf').val())) {
            $('#gn_card_cpf').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_cpf').addClass("gn-inputs-error");
            showError("CPF inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_phone_number').change(function() {
        if (validatePhone($('#gn_card_phone_number').val())) {
            $('#gn_card_phone_number').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_phone_number').addClass("gn-inputs-error");
            showError("Telefone inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_birth').change(function() {
        if (validateBirth($('#gn_card_birth').val())) {
            $('#gn_card_birth').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_birth').addClass("gn-inputs-error");
            showError("Data de nascimento inválida. Por favor, digite novamente.");
        }
    });

    $('#gn_card_street').change(function() {
        if (validateStreet($('#gn_card_street').val())) {
            $('#gn_card_street').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_street').addClass("gn-inputs-error");
            showError("Endereço inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_street_number').change(function() {
        if (validateStreetNumber($('#gn_card_street_number').val())) {
            $('#gn_card_street_number').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_street_number').addClass("gn-inputs-error");
            showError("Número do endereço inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_neighborhood').change(function() {
        if (validateNeighborhood($('#gn_card_neighborhood').val())) {
            $('#gn_card_neighborhood').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_neighborhood').addClass("gn-inputs-error");
            showError("Bairro inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_complement').change(function() {
        if (validateComplement($('#gn_card_complement').val())) {
            $('#gn_card_complement').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_complement').addClass("gn-inputs-error");
            showError("Complemento do endereço inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_city').change(function() {
        if (validateCity($('#gn_card_city').val())) {
            $('#gn_card_city').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_city').addClass("gn-inputs-error");
            showError("Cidade inválida. Por favor, digite novamente.");
        }
    });

    $('#gn_card_zipcode').change(function() {
        if (validateZipcode($('#gn_card_zipcode').val())) {
            $('#gn_card_zipcode').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_zipcode').addClass("gn-inputs-error");
            showError("CEP inválido. Por favor, digite novamente.");
        }
    });

    $('#gn_card_state').change(function() {
        if (validateState($('#gn_card_state').val())) {
            $('#gn_card_state').removeClass("gn-inputs-error");
            hideError();
        } else {
            $('#gn_card_state').addClass("gn-inputs-error");
            showError("Estado inválido. Por favor, selecione novamente.");
        }
    });


    function billetValidateFields() {
        errorMessage = "";

        if ($("#pay_billet_with_cnpj").is(':checked')) {
            if (verifyCNPJ($('#gn_billet_cnpj').val())) {
                $('#gn_billet_cnpj').removeClass("gn-inputs-error");
            } else {
                $('#gn_billet_cnpj').addClass("gn-inputs-error");
                errorMessage = "CNPJ inválido. Por favor, digite novamente.";
            }

            if (validateName($('#gn_billet_corporate_name').val())) {
                $('#gn_billet_corporate_name').removeClass("gn-inputs-error");
            } else {
                $('#gn_billet_corporate_name').addClass("gn-inputs-error");
                errorMessage = "Razão Social inválida. Por favor, digite novamente.";
            }
        }

        if (validateName($('#gn_billet_full_name').val())) {
            $('#gn_billet_full_name').removeClass("gn-inputs-error");
        } else {
            $('#gn_billet_full_name').addClass("gn-inputs-error");
            errorMessage = "Nome inválido. Por favor, digite novamente.";
        }

        if (validateEmail($('#gn_billet_email').val())) {
            $('#gn_billet_email').removeClass("gn-inputs-error");
        } else {
            $('#gn_billet_email').addClass("gn-inputs-error");
            errorMessage = "Email inválido. Por favor, digite novamente.";
        }

        if (verifyCPF($('#gn_billet_cpf').val())) {
            $('#gn_billet_cpf').removeClass("gn-inputs-error");
        } else {
            $('#gn_billet_cpf').addClass("gn-inputs-error");
            errorMessage = "CPF inválido. Por favor, digite novamente.";
        }

        if (validatePhone($('#gn_billet_phone_number').val())) {
            $('#gn_billet_phone_number').removeClass("gn-inputs-error");
        } else {
            $('#gn_billet_phone_number').addClass("gn-inputs-error");
            errorMessage = "Telefone inválido. Por favor, digite novamente.";
        }

        if (errorMessage!="") {
            showError(errorMessage);
            return false;
        } else {
            hideError();
            return true;
        }
    }

    function cardValidateFields() {
        errorMessage = "";

        if ($("#pay_card_with_cnpj").is(':checked')) {
            if (verifyCNPJ($('#gn_card_cnpj').val())) {
                $('#gn_card_cnpj').removeClass("gn-inputs-error");
            } else {
                $('#gn_card_cnpj').addClass("gn-inputs-error");
                errorMessage = "CNPJ inválido. Por favor, digite novamente.";
            }

            if (validateName($('#gn_card_corporate_name').val())) {
                $('#gn_card_corporate_name').removeClass("gn-inputs-error");
            } else {
                $('#gn_card_corporate_name').addClass("gn-inputs-error");
                errorMessage = "Razão Social inválida. Por favor, digite novamente.";
            }
        }

        if (validateName($('#gn_card_full_name').val())) {
            $('#gn_card_full_name').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_full_name').addClass("gn-inputs-error");
            errorMessage = "Nome inválido. Por favor, digite novamente.";
        }

        if (validateEmail($('#gn_card_email').val())) {
            $('#gn_card_email').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_email').addClass("gn-inputs-error");
            errorMessage = "Email inválido. Por favor, digite novamente.";
        }

        if (verifyCPF($('#gn_card_cpf').val())) {
            $('#gn_card_cpf').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_cpf').addClass("gn-inputs-error");
            errorMessage = "CPF inválido. Por favor, digite novamente.";
        }

        if (validatePhone($('#gn_card_phone_number').val())) {
            $('#gn_card_phone_number').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_phone_number').addClass("gn-inputs-error");
            errorMessage = "Telefone inválido. Por favor, digite novamente.";
        }

        if (validateBirth($('#gn_card_birth').val())) {
            $('#gn_card_birth').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_birth').addClass("gn-inputs-error");
            errorMessage = "Data de nascimento inválida. Por favor, digite novamente.";
        }

        if (validateStreet($('#gn_card_street').val())) {
            $('#gn_card_street').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_street').addClass("gn-inputs-error");
            errorMessage = "Endereço inválido. Por favor, digite novamente.";
        }

        if (validateStreetNumber($('#gn_card_street_number').val())) {
            $('#gn_card_street_number').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_street_number').addClass("gn-inputs-error");
            errorMessage = "Número do endereço inválido. Por favor, digite novamente.";
        }

        if (validateNeighborhood($('#gn_card_neighborhood').val())) {
            $('#gn_card_neighborhood').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_neighborhood').addClass("gn-inputs-error");
            errorMessage = "Bairro inválido. Por favor, digite novamente.";
        }

        if (validateCity($('#gn_card_city').val())) {
            $('#gn_card_city').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_city').addClass("gn-inputs-error");
            errorMessage = "Cidade inválida. Por favor, digite novamente.";
        }

        if (validateZipcode($('#gn_card_zipcode').val())) {
            $('#gn_card_zipcode').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_zipcode').addClass("gn-inputs-error");
            errorMessage = "CEP inválido. Por favor, digite novamente.";
        }

        if (validateState($('#gn_card_state').val())) {
            $('#gn_card_state').removeClass("gn-inputs-error");
        } else {
            $('#gn_card_state').addClass("gn-inputs-error");
            errorMessage = "Estado inválido. Por favor, selecione novamente.";
        }

        if (errorMessage!="") {
            showError(errorMessage);
            return false;
        } else {
            hideError();
            return true;
        }

    }

    function validateBilletCustomerData() {
        if (validateName($('#gn_billet_full_name').val())) {
            $('#gn_name_row').hide();
        } else {
            $('#gn_name_row').show();
        }

        if (validateEmail($('#gn_billet_email').val())) {
            $('#gn_email_row').hide();
        } else {
            $('#gn_email_row').show();
        }

        if (verifyCPF($('#gn_billet_cpf').val()) && validatePhone($('#gn_billet_phone_number').val())) {
            $('#gn_cpf_phone_row').hide();
        } else {
            $('#gn_cpf_phone_row').show();
        }
    }

    function validateCardCustomerData() {
        if (validateName($('#gn_card_full_name').val())) {
            $('#gn_card_name_row').hide();
        } else {
            $('#gn_card_name_row').show();
        }

        if (validateEmail($('#gn_card_email').val())) {
            $('#gn_card_email_row').hide();
        } else {
            $('#gn_card_email_row').show();
        }

        if (verifyCPF($('#gn_card_cpf').val()) && validatePhone($('#gn_card_phone_number').val())) {
            $('#gn_card_cpf_phone_row').hide();
        } else {
            $('#gn_card_cpf_phone_row').show();
        }

        if (validateBirth($('#gn_card_birth').val())) {
            $('#gn_card_birth_row').hide();
        } else {
            $('#gn_card_birth_row').show();
        }

        if (validateStreet($('#gn_card_street').val()) && validateStreetNumber($('#gn_card_street_number').val())) {
            $('#gn_card_street_number_row').hide();
        } else {
            $('#gn_card_street_number_row').show();
        }

        if (validateNeighborhood($('#gn_card_neighborhood').val()) ) {
            $('#gn_card_neighborhood_row').hide();
        } else {
            $('#gn_card_neighborhood_row').show();
        }

        if (validateCity($('#gn_card_city').val()) && validateZipcode($('#gn_card_zipcode').val())) {
            $('#gn_card_city_zipcode_row').hide();
        } else {
            $('#gn_card_city_zipcode_row').show();
        }

        if (validateState($('#gn_card_state').val())) {
            $('#gn_card_state_row').hide();
        } else {
            $('#gn_card_state_row').show();
        }

        if (validateStreet($('#gn_card_street').val()) && validateStreetNumber($('#gn_card_street_number').val()) && validateNeighborhood($('#gn_card_neighborhood').val()) && validateCity($('#gn_card_city').val()) && validateZipcode($('#gn_card_zipcode').val()) && validateState($('#gn_card_state').val())) {
            $('#billing-adress').hide();
        } else {
            $('#billing-adress').show();
        }

    }

    if ($('#billing_persontype').val()=="2") {
        $('#pay_cnpj').show();
        $('#pay_cnpj_card').show();
        $('#pay_billet_with_cnpj').prop( "checked", true );
        $('#pay_card_with_cnpj').prop( "checked", true );
    } else {
        $('#pay_cnpj').hide();
        $('#pay_cnpj_card').hide();
        $('#pay_billet_with_cnpj').prop( "checked", false );
        $('#pay_card_with_cnpj').prop( "checked", false );
    }

    $('#billing_persontype').on('change', function() {
        if (this.value==2) {
            $('#pay_cnpj').show();
            $('#pay_cnpj_card').show();
            $('#pay_billet_with_cnpj').prop( "checked", true );
            $('#pay_card_with_cnpj').prop( "checked", true );
        } else {
            $('#pay_cnpj').hide();
            $('#pay_cnpj_card').hide();
            $('#pay_billet_with_cnpj').prop( "checked", false );
            $('#pay_card_with_cnpj').prop( "checked", false );
        }
    });

    $('#pay_billet_with_cnpj').click(function() {
        if ($(this).is(':checked')) {
            $('#pay_cnpj').slideDown();
        } else {
            $('#pay_cnpj').slideUp();
        }
    });

    $('#pay_card_with_cnpj').click(function() {
        if ($(this).is(':checked')) {
            $('#pay_cnpj_card').slideDown();
        } else {
            $('#pay_cnpj_card').slideUp();
        }
    });

    $('input[type=radio][name=gn_card_brand]').change(function() {
        getInstallments(this.value);
    });

    jQuery('#gn-billet-payment-option').click(function(e){
        $('#collapse-payment-card').hide();
        $('#collapse-payment-billet').show();
        $('#paymentMethodBilletRadio').prop('checked', true);
        $('#paymentMethodCardRadio').prop('checked', false);
        $("#gn-billet-payment-option").removeClass('gn-osc-payment-option-unselected');
        $("#gn-billet-payment-option").addClass('gn-osc-payment-option-selected');
        $("#gn-card-payment-option").removeClass('gn-osc-payment-option-selected');
        $("#gn-card-payment-option").addClass('gn-osc-payment-option-unselected');
        fixScreenSize();
    });

    jQuery('#gn-card-payment-option').click(function(e){
        $('#collapse-payment-card').show();
        $('#collapse-payment-billet').hide();
        $('#paymentMethodBilletRadio').prop('checked', false);
        $('#paymentMethodCardRadio').prop('checked', true);
        $("#gn-billet-payment-option").removeClass('gn-osc-payment-option-selected');
        $("#gn-billet-payment-option").addClass('gn-osc-payment-option-unselected');
        $("#gn-card-payment-option").removeClass('gn-osc-payment-option-unselected');
        $("#gn-card-payment-option").addClass('gn-osc-payment-option-selected');
        fixScreenSize();
    });

    if(jQuery.mask) {
        $(".cpf-mask").mask("999.999.999-99",{
            completed:function(){ 
                if (!verifyCPF(this.val())) {
                    showError('CPF inválido. Digite novamente.');
                } else {
                    hideError();
                }
            },placeholder:"___.___.___-__"});

        $(".cnpj-mask").mask("99.999.999/9999-99",{
            completed:function(){ 
                if (!verifyCNPJ(this.val())) {
                    showError('CNPJ inválido. Digite novamente.');
                } else {
                    hideError();
                }
            },placeholder:"__.___.___/____-__"});

        $(".phone-mask").focusout(function(){
            $(".phone-mask").unmask();
                var phone = $(".phone-mask").val().replace(/[^\d]+/g,'');
                if(phone.length > 10) {
                    $(".phone-mask").mask("(99) 99999-999?9");
                } else {
                    $(".phone-mask").mask("(99) 9999-9999?9");
                }
            }).trigger("focusout");

        $('.birth-mask').mask("99/99/9999",{
            completed:function(){ 
                if (!validateBirth(this.val())) {
                    showError('Data de nascimento inválida. Digite novamente.');
                } else {
                    hideError();
                }
            },placeholder:"__/__/____"});

        $('#input-payment-card-number').mask('9999999999999999?999',{placeholder:""});
        $('#input-payment-card-cvv').mask('999?99',{placeholder:""});

    }

    function validateName(data) {
        if (data) {
            if (data.length > 7) {
                return true;
            } else {
                return false;
            }
        }
    }

    function validateEmail(email) {
        if (email) {
            var pattern = new RegExp(/^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/);
            return pattern.test(email);
        } else {
            return false;
        }
    }

    function validatePhone(phone_number) {
        if (phone_number) {
            if (phone_number.length < 14) {
                return false; 
            } else {
                var pattern = new RegExp(/^[1-9]{2}9?[0-9]{8}$/);
                if (pattern.test(phone_number.replace(/[^\d]+/g,''))) {
                    return true; 
                } else {
                    return false; 
                }
            }
        }
    }

    function validateStreet(data) {
        if (data) {
            if (data.length<1 || data.length>200) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function validateStreetNumber(data) {
        if (data) {
            if (data.length<1 || data.length>55) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function validateNeighborhood(data) {
        if (data) {
            if (data.length<1 || data.length>255) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function validateComplement(data) {
        if (data) {
            if (data.length>55) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function validateState(data) {
        if (data) {
            var pattern = new RegExp(/^(?:A[CLPM]|BA|CE|DF|ES|GO|M[ATSG]|P[RBAEI]|R[JNSOR]|S[CEP]|TO)$/);
            if (!pattern.test(data)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function validateZipcode(data) {
        if (data) {
            if (data.replace(/[^\d]+/g,'').length!=8) {
                return false;
            } else {
                return true;
            }
        }
    }

    function validateCity(data) {
        if (data) {
            if (data.length<1 || data.length>255) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function verifyCPF(cpf) {
        if (cpf) {
            cpf = cpf.replace(/[^\d]+/g,'');
            
            if(cpf == '' || cpf.length != 11) return false;
            
            var resto;
            var soma = 0;
            
            if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" || cpf == "12345678909") return false;
            
            for (i=1; i<=9; i++) soma = soma + parseInt(cpf.substring(i-1, i)) * (11 - i);
            resto = (soma * 10) % 11;
            
            if ((resto == 10) || (resto == 11))  resto = 0;
            if (resto != parseInt(cpf.substring(9, 10)) ) return false;
            
            soma = 0;
            for (i = 1; i <= 10; i++) soma = soma + parseInt(cpf.substring(i-1, i)) * (12 - i);
            resto = (soma * 10) % 11;
            
            if ((resto == 10) || (resto == 11))  resto = 0;
            if (resto != parseInt(cpf.substring(10, 11) ) ) return false;
            return true;
        } else {
            return false;
        }
    }

    function verifyCNPJ(cnpj) {
        if (cnpj) {
            cnpj = cnpj.replace(/[^\d]+/g,'');

            if(cnpj == '' || cnpj.length != 14) return false;

            if (cnpj == "00000000000000" || cnpj == "11111111111111" || cnpj == "22222222222222" || cnpj == "33333333333333" || cnpj == "44444444444444" || cnpj == "55555555555555" || cnpj == "66666666666666" || cnpj == "77777777777777" || cnpj == "88888888888888" || cnpj == "99999999999999") return false;

            var tamanho = cnpj.length - 2
            var numeros = cnpj.substring(0,tamanho);
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
            numeros = cnpj.substring(0,tamanho);
            soma = 0;
            pos = tamanho - 7;
            
            for (i = tamanho; i >= 1; i--) {
              soma += numeros.charAt(tamanho - i) * pos--;
              if (pos < 2) pos = 9;
            }
            
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            
            if (resultado != digitos.charAt(1)) return false;

            return true; 
        } else {
            return false;
        }
    }

    function verifyPhone(phone_number) {
        if (phone_number) {
            if (phone_number.length < 14) {
                showError("O telefone informado é inválido.");
                return false; 
            } else {
                var pattern = new RegExp(/^[1-9]{2}9?[0-9]{8}$/);
                if (pattern.test(phone_number.replace(/[^\d]+/g,''))) {
                    hideError();
                    return true; 
                } else {
                    return false; 
                }
            }
        } else {
            return false;
        }
    }

    function validateBirth(birth) {
        if (birth) {
            var pattern = new RegExp(/^[12][0-9]{3}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12][0-9]|3[01])$/);
            var date = birth.split("/");
            return pattern.test(date[2] + "-" + date[1] + "-" + date[0]);
        } else {
            return false;
        }
    }

    function scrollToTop() {
        $("html, body").animate({ scrollTop: $("#wc-gerencianet-messages").offset().top-80 }, "slow");
    }

    function showError(message) {
        if (!$('.gn-osc-warning-payment').is(":visible")) {
            $('.gn-osc-warning-payment').slideDown();
        }
        scrollToTop();
        jQuery("#wc-gerencianet-messages").html( message )
    }

    function hideError() {
        $('.gn-osc-warning-payment').slideUp();
    }

    function getInstallments(card_brand) {
        $('#gn_card_installments').html('<option value="">Aguarde, carregando...</option>').show();
        var data = {
            action: "woocommerce_gerencianet_get_installments",
            security: "woocommerce_gerencianet",
            order_id: "",
            brand: card_brand
        };
        
        jQuery.ajax({
            type: "POST",
            url: "<?php echo $ajax_url; ?>",
            data: data,
            success: function(response) {
                var obj = $.parseJSON(response);
                if (obj.code==200) {

                    var options = ''; 
                    for (var i = 0; i < obj.data.installments.length; i++) {
                        options += '<option value="' + obj.data.installments[i].installment + '">' + obj.data.installments[i].installment + 'x de R$' + obj.data.installments[i].currency + '</option>';
                    }   
                    $('#gn_card_installments').html(options).show();
                }
            },
            error: function(){
                alert("error ocurred");
            }
        });
    }

    $( window ).resize(function() {
        fixScreenSize();
    });

    function fixScreenSize() {
        if($( "#gerencianet-container" ).width()<600) {
            $( "#gerencianet-container" ).addClass("gerencianet-container-fix-size");
        } else {
            $( "#gerencianet-container" ).removeClass("gerencianet-container-fix-size");
        }
    }
    fixScreenSize();

    $('#payment_method_gerencianet_oficial').on('change', function() {
        fixScreenSize();
    });
    

});

</script>


<div id="gerencianet-container">
    <?php if ($sandbox == "yes") { ?>
    <div class="warning-payment" id="wc-gerencianet-messages-sandbox">
        <div class="woocommerce-error"><?php echo $gn_warning_sandbox_message; ?></div>
    </div>
    <?php } ?>

    <div class="gn-osc-warning-payment" id="wc-gerencianet-messages">
        <?php if (($card_option && $order_total_card<500) && ($billet_option && $order_total_billet<500)) { ?>
            <div class="woocommerce-error"><?php echo $gn_mininum_gn_charge_price; ?></div>
        <?php } ?>
    </div>

    <div style="margin: 0px;">
        <?php if ($billet_option=="yes") { ?>
        <div id="gn-billet-payment-option" class="gn-osc-payment-option gn-osc-payment-option-selected">
            <div>
                <div id="billet-radio-button" class="gn-osc-left">
                    <input type="radio" name="paymentMethodRadio" id="paymentMethodBilletRadio" class="gn-osc-radio" value="billet" checked="true" />
                </div>
                <div class="gn-osc-left gn-osc-icon-gerencianet">
                    <span class="gn-icon-icones-personalizados_boleto"></span>
                </div>
                <div class="gn-osc-left gn-osc-payment-option-gerencianet">
                    <strong><?php echo "Boleto Bancário"; ?></strong>
                    <?php if ($discount>0) { ?>
                        <span style="font-size: 14px; line-height: 15px;"><br>+<?php echo $discount_formatted; ?>% de desconto</span>
                    <?php } ?>
                </div>
                <div class="gn-osc-left gn-osc-payment-option-sizer"></div>
                <div class="clear"></div>
            </div>
        </div>
        <?php } ?>
        <?php if ($card_option=="yes") { ?>
        <div id="gn-card-payment-option" class="gn-osc-payment-option gn-osc-payment-option-unselected">
            <div>
                <div id="card-radio-button" class="gn-osc-left">
                    <input type="radio" name="paymentMethodRadio" id="paymentMethodCardRadio" class="gn-osc-radio" value="card" />
                </div>
                <div class="gn-osc-left gn-osc-icon-gerencianet">
                    <span class="gn-icon-credit-card2"></span>
                </div>
                <div class="gn-osc-left gn-osc-payment-option-gerencianet">
                    <strong><?php echo "Cartão de Crédito"; ?></strong>
                    <span style="font-size: 14px; line-height: 15px;"><br>em até <?php echo $max_installments; ?></span>
                </div>
                <div class="gn-osc-left gn-osc-payment-option-sizer"></div>
                <div class="clear"></div>
            </div>
        </div>
        <?php } ?>
        <div class="clear"></div>
    </div>
    <?php if ($billet_option=="yes") { ?>
    <div id="collapse-payment-billet" class="gn-osc-background" >
      <div class="panel-body">
          <div class="gn-osc-row gn-osc-pay-comments">
              <p class="gn-left-space-2"><strong><?php echo $gn_billet_payment_method_comments; ?></strong></p>
          </div>
          <div class="gn-form">
            <div id="billet-data">
                <div style="background-color: #F3F3F3; border: 1px solid #F3F3F3; margin-top: 10px; margin-bottom: 10px;">
              <div class="gn-osc-row">
                <div class="gn-col-12 gn-cnpj-row">
                <input type="checkbox" name="pay_billet_with_cnpj" id="pay_billet_with_cnpj" value="1" />  <?php echo $gn_cnpj_option; ?>
                </div>
              </div>

              <div id="pay_cnpj" class="required gn-osc-row">
                <div class="gn-col-2 gn-label">
                  <label for="gn_billet_cnpj" class="gn-right-padding-1"><?php echo $gn_cnpj; ?></label>
                </div>
                <div class="gn-col-10">
                  
                  <div>
                    <div class="gn-col-3 required">
                      <input type="text" name="gn_billet_cnpj" id="gn_billet_cnpj" class="form-control cnpj-mask" value="" />
                    </div>
                    <div class="gn-col-8">
                      <div class="required">
                        <div class="gn-col-4 gn-label">
                          <label class=" gn-col-12 gn-right-padding-1" for="gn_billet_corporate_name"><?php echo $gn_corporate_name; ?></label>
                        </div>
                        <div class="gn-col-8">
                          <input type="text" name="gn_billet_corporate_name" id="gn_billet_corporate_name" class="form-control" value="" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              </div>

              <div id="gn_name_row" class="required gn-osc-row gn-billet-field" >
                <div class="gn-col-2 gn-label">
                  <label for="gn_billet_full_name" class="gn-right-padding-1"><?php echo $gn_name; ?></label>
                </div>
                <div class="gn-col-10">
                  <input type="text" name="gn_billet_full_name" id="gn_billet_full_name" value="" class="form-control" />
                </div>
              </div>


              <div id="gn_email_row" class=" required gn-osc-row gn-billet-field" >
                <div class="gn-col-2 gn-label">
                  <label class="gn-col-12 gn-right-padding-1" for="gn_billet_email"><?php echo $gn_email; ?></label>
                </div>
                <div class="gn-col-10">
                  <input type="text" name="gn_billet_email" value="" id="gn_billet_email" class="form-control" />
                </div>
              </div>

              <div id="gn_cpf_phone_row" class="required gn-osc-row gn-billet-field" >
                <div class="gn-col-2 gn-label">
                  <label for="gn_billet_cpf" class="gn-right-padding-1"><?php echo $gn_cpf; ?></label>
                </div>
                <div class="gn-col-10">
                  
                  <div>
                    <div class="gn-col-3 required">
                      <input type="text" name="gn_billet_cpf" id="gn_billet_cpf" value="" class="form-control cpf-mask" />
                    </div>
                    <div class="gn-col-8">
                      <div class=" required">
                        <div class="gn-col-4 gn-label">
                        <label class="gn-col-12 gn-right-padding-1" for="gn_billet_phone_number" ><?php echo $gn_phone; ?></label>
                        </div>
                        <div class="gn-col-4">
                          <input type="text" name="gn_billet_phone_number" id="gn_billet_phone_number" value="" class="form-control phone-mask" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            </div>

        </div>

        <div class="gn-osc-row" style="padding: 20px;">
            <?php if ($discount>0) { ?>
            <div class="gn-osc-row" style="border: 1px solid #DEDEDE; border-bottom: 0px; margin: 0px; padding:5px;">
                <div style="float: left;">
                    <strong>DESCONTO DE <?php echo $discount_formatted; ?>% NO BOLETO:</strong>
                </div>
                <div style="float: right;">
                    <strong>-<?php echo $order_billet_discount; ?></strong>
                </div>
            </div>
            <?php } ?>
            <div class="gn-osc-row" style="border: 1px solid #DEDEDE; margin: 0px; padding:5px;">
                <div style="float: left;">
                    <strong>TOTAL:</strong>
                </div>
                <div style="float: right;">
                    <strong><?php echo $order_with_billet_discount; ?></strong>
                </div>
            </div>
        </div>
      </div>

    <?php } ?>  
    <?php if ($card_option=="yes") { ?>
      <div id="collapse-payment-card"  class="panel-collapse gn-hide gn-osc-background" >
        <div class="panel-body">
                <div class="gn-osc-row gn-osc-pay-comments">
                   <p class="gn-left-space-2"><strong><?php echo $gn_card_payment_comments; ?></strong></p>
                </div>

                <div class="gn-form">
                <div id="card-data" >
                    <div style="background-color: #F3F3F3; border: 1px solid #F3F3F3; margin-top: 10px; margin-bottom: 10px;">
                    <div class="gn-osc-row">
                      <div class="gn-col-12 gn-cnpj-row">
                        <input type="checkbox" name="pay_card_with_cnpj" id="pay_card_with_cnpj" value="1" />  <?php echo $gn_cnpj_option; ?>
                      </div>
                    </div>

                    <div id="pay_cnpj_card" class=" required gn-osc-row" >
                      <div class="gn-col-2 gn-label">
                      <label class="gn-right-padding-1" for="gn_card_cnpj"><?php echo $gn_cnpj; ?></label>
                      </div>
                      <div class="gn-col-10">
                        
                        <div>
                          <div class="gn-col-3 required">
                            <input type="text" name="gn_card_cnpj" id="gn_card_cnpj" class="form-control cnpj-mask" value="" />
                          </div>
                          <div class="gn-col-8">
                            <div class=" required gn-left-space-2">
                              <div class="gn-col-4 gn-label">
                                <label class="gn-col-12 gn-right-padding-1" for="gn_card_corporate_name"><?php echo $gn_corporate_name; ?></label>
                              </div>
                              <div class="gn-col-8">
                                <input type="text" name="gn_card_corporate_name" id="gn_card_corporate_name" class="form-control" value="" />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    </div>

                    <div id="gn_card_name_row" class="required gn-osc-row gn-card-field" >
                      <div class="gn-col-2 gn-label">
                        <label class="gn-col-12 gn-right-padding-1" for="gn_card_full_name"><?php echo $gn_name; ?></label>
                      </div>
                      <div class="gn-col-10">
                        <input type="text" name="gn_card_full_name" id="gn_card_full_name" value="" class="form-control" />
                      </div>
                    </div>

                    <div id="gn_card_cpf_phone_row" class="required gn-osc-row gn-card-field" >
                    
                        <div class="gn-col-2 gn-label">
                            <label for="gn_card_cpf" class="gn-right-padding-1" ><?php echo $gn_cpf; ?></label>
                        </div>
                        <div class="gn-col-4">
                            <input type="text" name="gn_card_cpf" id="gn_card_cpf" value="" class="form-control cpf-mask gn-minimum-size-field" />
                        </div>
                        <div class="gn-col-6">
                          <div class="gn-col-4 gn-label">
                              <label class="gn-left-space-2 gn-right-padding-1" for="gn_card_phone_number"><?php echo $gn_phone; ?></label>
                          </div>
                          <div class="gn-col-8">
                              <input type="text" name="gn_card_phone_number" value="" id="gn_card_phone_number" class="form-control phone-mask gn-minimum-size-field" />
                          </div>
                          
                        </div>
                    </div>

                    <div id="gn_card_birth_row" class=" required gn-osc-row gn-card-field" >
                      <div class="gn-col-3 gn-label-birth">
                          <label class="gn-right-padding-1" for="gn_card_birth"><?php echo $gn_birth; ?></label>
                      </div>
                      <div class="gn-col-3">
                          <input type="text" name="gn_card_birth" id="gn_card_birth" value="" class="form-control birth-mask" />
                      </div>
                    </div>

                    <div id="gn_card_email_row" class=" required gn-card-field" >
                      <div class="gn-col-2">
                        <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_email"><?php echo $gn_email; ?></label>
                      </div>
                      <div class="gn-col-10">
                        <input type="text" name="gn_card_email" value="" id="gn_card_email" class="form-control" />
                      </div>
                    </div>
                    <div class="clear"></div>

                    <div id="billing-adress" class="gn-section">
                        <div class="gn-osc-row gn-card-field">
                            <p>
                            <strong><?php echo $gn_billing_address_title; ?></strong>
                            </p>
                        </div>

                        <div id="gn_card_street_number_row" class="required gn-osc-row gn-card-field" >
                            <div class="gn-col-2">
                                <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_street"><?php echo $gn_street; ?></label>
                            </div>
                            
                            <div class="gn-col-10">
                                <div class="gn-col-6 required">
                                    <input type="text" name="gn_card_street" id="gn_card_street" value="" class="form-control" />
                                </div>
                                <div class="gn-col-6">
                                    <div class=" required gn-left-space-2">
                                        <div class="gn-col-5">
                                            <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_street_number"><?php echo $gn_street_number; ?></label>
                                        </div>
                                        <div class="gn-col-7">
                                            <input type="text" name="gn_card_street_number" id="gn_card_street_number" value="" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="gn_card_neighborhood_row" class="gn-osc-row gn-card-field">
                            <div class="gn-col-2 required">
                                <label class="gn-col-12 gn-label required gn-right-padding-1" for="gn_card_neighborhood"><?php echo $gn_neighborhood; ?></label>
                            </div>
                    
                            <div class="gn-col-3">
                                
                                <input type="text" name="gn_card_neighborhood" id="gn_card_neighborhood" value="" class="form-control" />
                            </div>
                            <div class="gn-col-7">
                                <div class=" gn-left-space-2">
                                  <div class="gn-col-5">
                                  <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_complement"><?php echo $gn_address_complement; ?></label>
                                  </div>
                                  <div class="gn-col-7">
                                    <input type="text" name="gn_card_complement" id="gn_card_complement" value="" class="form-control" maxlength="54" />
                                  </div>
                                </div>
                            </div>
                        </div>

                        <div id="gn_card_city_zipcode_row" class="required billing-address-data gn-card-field gn-osc-row" >
                            <div class="gn-col-2">
                                <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_zipcode"><?php echo $gn_cep; ?></label>
                            </div>
                            <div class="gn-col-10">
                                <div class="gn-col-4 required">
                                    <input type="text" name="gn_card_zipcode" id="gn_card_zipcode" value="" class="form-control" />
                                </div>
                                <div class="gn-col-8">
                                    <div class=" required gn-left-space-2">
                                      <div class="gn-col-4">
                                          <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_city"><?php echo $gn_city; ?></label>
                                      </div>
                                      <div class="gn-col-6">
                                        <input type="text" name="gn_card_city" id="gn_card_city" value="" class="form-control" />
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="gn_card_state_row" class="required billing-address-data gn-card-field gn-osc-row" >
                          <div class="gn-col-2">
                            <label class="gn-col-12 gn-label gn-right-padding-1" for="gn_card_state"><?php echo $gn_state; ?></label>
                          </div>
                          <div class="gn-col-10">
                            <select name="gn_card_state" id="gn_card_state" class="form-control gn-form-select">
                              <option value=""></option> 
                              <option value="AC">Acre</option> 
                              <option value="AL">Alagoas</option> 
                              <option value="AP">Amapá</option> 
                              <option value="AM">Amazonas</option> 
                              <option value="BA">Bahia</option> 
                              <option value="CE">Ceará</option> 
                              <option value="DF">Distrito Federal</option> 
                              <option value="ES">Espírito Santo</option> 
                              <option value="GO">Goiás</option> 
                              <option value="MA">Maranhão</option> 
                              <option value="MT">Mato Grosso</option> 
                              <option value="MS">Mato Grosso do Sul</option> 
                              <option value="MG">Minas Gerais</option> 
                              <option value="PA">Pará</option> 
                              <option value="PB">Paraíba</option> 
                              <option value="PR">Paraná</option> 
                              <option value="PE">Pernambuco</option> 
                              <option value="PI">Piauí</option> 
                              <option value="RJ">Rio de Janeiro</option> 
                              <option value="RN">Rio Grande do Norte</option> 
                              <option value="RS">Rio Grande do Sul</option> 
                              <option value="RO">Rondônia</option> 
                              <option value="RR">Roraima</option> 
                              <option value="SC">Santa Catarina</option> 
                              <option value="SP">São Paulo</option> 
                              <option value="SE">Sergipe</option> 
                              <option value="TO">Tocantins</option> 
                            </select>
                          </div>
                        </div>
                    </div>
                    <div class="clear"></div>

                    <div class="gn-section" style="background-color: #F0F0F0; padding: 5px 10px;">
                        <div class="required gn-osc-row">
                            <div>
                            <label class="" for="gn_card_brand"><?php echo $gn_card_brand; ?></label>
                            </div>
                            <div>
                                <div class="gn-card-brand-selector">
                                    <input id="none" type="radio" name="gn_card_brand" id="gn_card_brand" value="" checked class="gn-hide" />
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="visa" type="radio" name="gn_card_brand" id="gn_card_brand" value="visa" class="gn-hide" />
                                        <label class="gn-card-brand gn-visa" for="visa"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="mastercard" type="radio" name="gn_card_brand" id="gn_card_brand" value="mastercard" class="gn-hide" />
                                        <label class="gn-card-brand gn-mastercard" for="mastercard"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="amex" type="radio" name="gn_card_brand" id="gn_card_brand" value="amex" class="gn-hide" />
                                        <label class="gn-card-brand gn-amex" for="amex"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="diners" type="radio" name="gn_card_brand" id="gn_card_brand" value="diners" class="gn-hide" />
                                        <label class="gn-card-brand gn-diners" for="diners"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="discover" type="radio" name="gn_card_brand" id="gn_card_brand" value="discover" class="gn-hide" />
                                        <label class="gn-card-brand gn-discover" for="discover"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="jcb" type="radio" name="gn_card_brand" id="gn_card_brand" value="jcb" class="gn-hide" />
                                        <label class="gn-card-brand gn-jcb" for="jcb"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="elo" type="radio" name="gn_card_brand" id="gn_card_brand" value="elo" class="gn-hide" />
                                        <label class="gn-card-brand gn-elo" for="elo"></label>
                                    </div>
                                    <div class="pull-left gn-card-brand-content">
                                        <input id="aura" type="radio" name="gn_card_brand" id="gn_card_brand" value="aura" class="gn-hide" />
                                        <label class="gn-card-brand gn-aura" for="aura"></label>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>

                        <div class="gn-osc-row required">
                                <div class="gn-col-6">
                                    <div>
                                        <?php echo $gn_card_number; ?>
                                    </div>
                                    <div>
                                        <div class="gn-card-number-input-row" style="margin-right: 20px;">
                                            <input type="text" name="gn_card_number_card" id="gn_card_number_card" value="" class="form-control gn-input-card-number" />
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                
                                <div class="gn-col-6">
                                    <div>
                                        <?php echo $gn_card_cvv; ?>
                                    </div>
                                    <div>
                                        <div class="pull-left gn-cvv-row">
                                            <input type="text" name="gn_card_cvv" id="gn_card_cvv" value="" class="form-control gn-cvv-input" />
                                        </div>
                                        <div class="pull-left">
                                            <div class="gn-cvv-info">
                                                <div class="pull-left gn-icon-card-input">
                                                </div>
                                                <div class="pull-left">
                                                    <?php echo $gn_card_cvv_tip; ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <input type="hidden" name="gn_card_payment_token" id="gn_card_payment_token" value="" />
                        </div>

                        <div class="gn-osc-row">
                            <div class="gn-col-12">
                                    <div>   
                                        <?php echo $gn_card_expiration; ?>
                                    </div>
                                    <div class="gn-card-expiration-row">
                                        <select class="form-control gn-card-expiration-select" name="gn_card_expiration_month" id="gn_card_expiration_month" >
                                            <option value=""> MM </option>
                                            <option value="01"> 01 </option>
                                            <option value="02"> 02 </option>
                                            <option value="03"> 03 </option>
                                            <option value="04"> 04 </option>
                                            <option value="05"> 05 </option>
                                            <option value="06"> 06 </option>
                                            <option value="07"> 07 </option>
                                            <option value="08"> 08 </option>
                                            <option value="09"> 09 </option>
                                            <option value="10"> 10 </option>
                                            <option value="11"> 11 </option>
                                            <option value="12"> 12 </option>
                                        </select>
                                        <div class="gn-card-expiration-divisor">
                                            /
                                        </div>
                                        <select class="form-control gn-card-expiration-select" name="gn_card_expiration_year" id="gn_card_expiration_year" >
                                            <option value=""> YYYY </option>
                                            <?php 
                                            $actual_year = intval(date("Y")); 
                                            $last_year = $actual_year + 15;
                                            for ($i = $actual_year; $i <= $last_year; $i++) {
                                                echo '<option value="'.$i.'"> '.$i.' </option>';
                                            }
                                            ?>
                                        </select>
                                        <div class="clear"></div>
                                    </div>
                                </div>

                        </div>

                        <div class="gn-osc-row required">
                            <div class="gn-col-12">
                                <label class="" for="gn_card_installments"><?php echo $gn_card_installments_options; ?></label>
                            </div>
                            <div class="gn-col-12">
                                <select name="gn_card_installments" id="gn_card_installments" class="form-control gn-form-select">
                                    <option value=""><?php echo $gn_card_brand_select; ?></option> 
                                </select>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
              </div>

            </div>
        </div>
        <div class="gn-osc-row" style="padding: 20px;">
            <div class="gn-osc-row" style="border: 1px solid #DEDEDE; margin: 0px; padding:5px;">
                <div style="float: left;">
                    <strong>TOTAL:</strong>
                </div>
                <div style="float: right;">
                    <strong><?php echo $order_total; ?></strong>
                </div>
            </div>
        </div>

      </div>
      <?php } ?>

</div>