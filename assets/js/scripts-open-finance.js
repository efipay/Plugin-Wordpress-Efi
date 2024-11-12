if ('undefined' == typeof participantsVector) {
    let participantsVector
}


function showParticipants(apiData) {
    participantsVector = apiData

    jQuery('#gn-default-logo').show()
    jQuery('#gn-participant-logo').hide()
    jQuery('#gn-default-name').text('Escolha sua instituição')

    let finalReturn = jQuery('<div></div>')

    let searchInput = jQuery('<input type="text" placeholder="Digite o nome da sua Instituição.." id="gn-of-search" class="gn-of-search" onkeyup="filterParticipants()">')

    finalReturn.append(searchInput)

    let participantsList = jQuery("<div id='gn-participants-list' class='gn-participants-list'></div>")

    apiData.forEach((participant, index) => {
        let outerDiv = jQuery('<div class="gn-participant" onclick="selectParticipant(' + index + ')"></div>')
        let participantItem = jQuery("<div class='gn-participant-item' ></div>")
        let participantLogo = jQuery("<img class='gn-participant-logo' src='" + participant.logo + "' />")
        let participantName = jQuery("<label class='gn-participant-name'>" + participant.nome + "</label>")

        participantItem.append(participantLogo)
        participantItem.append(participantName)
        outerDiv.append(participantItem)
        outerDiv.append('<hr class="gn-hr">')
        participantsList.append(outerDiv)

    });

    finalReturn.append(participantsList)

    Swal.fire({
        title: 'Escolha sua Instituição',
        html: finalReturn,
        showCancelButton: false,
        showConfirmButton: false
    })


}


function filterParticipants() {
    var input, filter, ul, li, a, i;
    input = document.getElementById("gn-of-search");
    filter = input.value.toUpperCase();
    div = document.getElementById("gn-participants-list");
    elements = div.getElementsByTagName("div");

    for (i = 0; i < elements.length; i++) {
        txtValue = elements[i].textContent || elements[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            elements[i].style.display = "";
        } else {
            elements[i].style.display = "none";
        }
    }
}

function selectParticipant(participantIndex) {
    jQuery('#gn-default-logo').hide()
    jQuery('#gn_of_participant').val(participantsVector[participantIndex].identificador)
    jQuery('#gn-participant-logo').attr('src', participantsVector[participantIndex].logo)
    jQuery('#gn-participant-logo').show()
    jQuery('#gn-default-name').text(participantsVector[participantIndex].nome)

    Swal.close()
    jQuery("#place_order").prop("disabled", false)
}

if (document.getElementById('gn_open_finance_cpf_cnpj')) {
    jQuery('#gn_open_finance_cpf_cnpj').keyup(
        function () {
            jQuery('#gn_open_finance_cpf_cnpj').val().length > 14 ? VMasker(document.querySelector("#gn_open_finance_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_open_finance_cpf_cnpj")).maskPattern("999.999.999-99");
        }
    )
    jQuery('#gn_open_finance_cpf_cnpj').blur(
        function () {
            var cpf_cnpj = jQuery('#gn_open_finance_cpf_cnpj').val();

            if (cpf_cnpj != "") {

                if (!validate_cpf_cnpj(cpf_cnpj)) {
                    jQuery('#gn_open_finance_cpf_cnpj').css('border', '1px solid red');
                    toastError("CPF/CNPJ Inválido");
                } else {
                    jQuery('#gn_open_finance_cpf_cnpj').css('border', '1px solid green');
                }
            }
        }
    )

    if (typeof jQuery('#billing_cpf').val() !== 'undefined') {


        // cpf_cnpj Open Finance
        if (typeof jQuery('#gn_open_finance_cpf_cnpj').val() !== 'undefined') {
            jQuery('#gn_open_finance_cpf_cnpj').val(jQuery('#billing_cpf').val())
            jQuery('#gn_field_open_finance_cpfcnpj').hide();
        }

        jQuery('#billing_cpf').keyup(function () {

            if (typeof jQuery('#gn_open_finance_cpf_cnpj').val() !== 'undefined') {
                // cpf_cnpj Open Finance
                jQuery('#gn_open_finance_cpf_cnpj').val(jQuery('#billing_cpf').val())
                jQuery('#gn_field_open_finance_cpfcnpj').hide();
            }
        }
        )
    }

    if (typeof jQuery('#billing_cnpj').val() !== 'undefined') {
        jQuery('#billing_cnpj').keyup(
            function () {
                // cpf_cnpj Open Finance
                jQuery('#gn_open_finance_cpf_cnpj').val($('#billing_cnpj').val())
                jQuery('#gn_field_open_finance_cpfcnpj').hide();
            }
        )
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

}



jQuery('form[name="checkout"]').submit(function () {
    if (jQuery('#payment_method_wc_gerencianet_open_finance').is(':checked')) {
        let participant = jQuery('<div id="conteudo" class="gn-default-participant"></div>')
        let participantLogo = jQuery("<img class='gn-participant-logo' src='" + jQuery("#gn-participant-logo").attr('src') + "' />")
        let participantName = jQuery("<label class='gn-participant-name'>" + jQuery("#gn-default-name").html() + "</label>")

        participant.append(participantLogo)
        participant.append(participantName)

        Swal.fire({
            title: 'Você está sendo redirecionado para',
            html: participant,
            timer: 500000,
            didOpen: () => {
                Swal.showLoading()
            },
            willClose: () => {
                return true
            }
        })
        setTimeout(500000)
    }

});

jQuery('input[name="payment_method"]').click(function () {
    if (jQuery('#payment_method_wc_gerencianet_open_finance').is(':checked') && jQuery("#gn_of_participant").val() == '') {
        jQuery("#place_order").prop("disabled", true)

    } else {
        jQuery("#place_order").prop("disabled", false)
    }
})
