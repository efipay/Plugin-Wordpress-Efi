	jQuery(document).ready(
		function ($) {

			if(!document.getElementById('swalCss')){
				let swalCss = $( "<style id='swalCss'></style>" ).html( ".colored-toast .swal2-title {color: white;}.colored-toast .swal2-close {color: white;}.colored-toast .swal2-html-container {color: white;}.colored-toast.swal2-icon-error {background-color: #f27474 !important;}" )
				$( "#payment" ).after( swalCss );
			}

			if (document.getElementById("gn_boleto_cpf_cnpj")) {
				$('#gn_boleto_cpf_cnpj').keyup(
					function () {
						$('#gn_boleto_cpf_cnpj').val().length > 14 ? VMasker(document.querySelector("#gn_boleto_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_boleto_cpf_cnpj")).maskPattern("999.999.999-99");
					}
				)
				$('#gn_boleto_cpf_cnpj').blur(
					function () {
						var cpf_cnpj = $('#gn_boleto_cpf_cnpj').val();
						
						if(cpf_cnpj != "") {
    						if (!validate_cpf_cnpj(cpf_cnpj)) {
    							$('#gn_boleto_cpf_cnpj').css('border', '1px solid red');
    							customError("CPF/CNPJ Inv谩lido");
    						} else {
    							$('#gn_boleto_cpf_cnpj').css('border', '1px solid green');
    						}
						}
					}
				)
			}

			// Esconder campos caso haja o brazillian market

			if (typeof $('#billing_cpf').val() !== 'undefined') {

				// cpf_cnpj boleto
				if (typeof $('#gn_boleto_cpf_cnpj').val() !== 'undefined') {
					$('#gn_boleto_cpf_cnpj').val($('#billing_cpf').val())
					$('#gn_field_boleto').hide();
				}

				$('#billing_cpf').keyup(function () {

					if (typeof $('#gn_boleto_cpf_cnpj').val() !== 'undefined') {
						// cpf_cnpj boleto
						$('#gn_boleto_cpf_cnpj').val($('#billing_cpf').val())
						$('#gn_field_boleto').hide();
					}
				}
				)
			}

			if (typeof $('#billing_cnpj').val() !== 'undefined') {
				$('#billing_cnpj').keyup(
					function () {
						// cpf_cnpj boleto
						$('#gn_boleto_cpf_cnpj').val($('#billing_cnpj').val())
						$('#gn_field_boleto').hide();
					}
				)
			}

			// Bairro obrigat贸rio
			if (typeof $('#billing_neighborhood_field > label > span').html() !== 'undefined') {
				$('#billing_neighborhood_field > label > span').html($('#billing_neighborhood_field > label > span').html().replace('opcional', 'obrigat贸rio'))
			}

		}
	);

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
		).fire({ icon: 'error', title: msg })
	}

