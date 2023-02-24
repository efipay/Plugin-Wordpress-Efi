window.onload = function () {
	jQuery( document ).ready(
		function ($) {

			let swalCss = $( "<style></style>" ).html( ".colored-toast .swal2-title {color: white;}.colored-toast .swal2-close {color: white;}.colored-toast .swal2-html-container {color: white;}.colored-toast.swal2-icon-error {background-color: #f27474 !important;}" )
			$( "#gn-checkout-js" ).after( swalCss );
		
			if (document.getElementById("gn_boleto_cpf_cnpj")) {
				$( '#gn_boleto_cpf_cnpj' ).keyup(
					function () {
						$( '#gn_boleto_cpf_cnpj' ).val().length > 14 ? VMasker(document.querySelector("#gn_boleto_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_boleto_cpf_cnpj")).maskPattern("999.999.999-99");
					}
				)
				$( '#gn_boleto_cpf_cnpj' ).blur(
					function () {
						var cpf_cnpj = $( '#gn_boleto_cpf_cnpj' ).val();

						if ( ! validate_cpf_cnpj( cpf_cnpj )) {
							$( '#gn_boleto_cpf_cnpj' ).css( 'border', '1px solid red' );
							customError("CPF/CNPJ Inválido");
						} else {
							$( '#gn_boleto_cpf_cnpj' ).css( 'border', '1px solid green' );
						}
					}
				)
			}

			if (document.getElementById('gn_pix_cpf_cnpj' )) {
				$( '#gn_pix_cpf_cnpj' ).keyup(
					function () {
						$( '#gn_pix_cpf_cnpj' ).val().length > 14 ? VMasker(document.querySelector("#gn_pix_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_pix_cpf_cnpj")).maskPattern("999.999.999-99");
					}
				)
				$( '#gn_pix_cpf_cnpj' ).blur(
					function () {
						var cpf_cnpj = $( '#gn_pix_cpf_cnpj' ).val();

						if ( ! validate_cpf_cnpj( cpf_cnpj )) {
							$( '#gn_pix_cpf_cnpj' ).css( 'border', '1px solid red' );
							customError("CPF/CNPJ Inválido");
						} else {
							$( '#gn_pix_cpf_cnpj' ).css( 'border', '1px solid green' );
						}
					}
				)
			}

			if (document.getElementById('gn_cartao_cpf_cnpj' )) {
				$( '#gn_cartao_cpf_cnpj' ).keyup(
					function () {
						 $( '#gn_cartao_cpf_cnpj' ).val().length > 14 ? VMasker(document.querySelector("#gn_cartao_cpf_cnpj")).maskPattern("99.999.999/9999-99") : VMasker(document.querySelector("#gn_cartao_cpf_cnpj")).maskPattern("999.999.999-99");
					}
				)
				$( '#gn_cartao_cpf_cnpj' ).blur(
					function () {
						var cpf_cnpj = $( '#gn_cartao_cpf_cnpj' ).val();

						if ( ! validate_cpf_cnpj( cpf_cnpj )) {
							$( '#gn_cartao_cpf_cnpj' ).css( 'border', '1px solid red' );
							customError("CPF/CNPJ Inválido");
						} else {
							$( '#gn_cartao_cpf_cnpj' ).css( 'border', '1px solid green' );
						}
					}
				)
			}

			if (document.getElementById('gn_cartao_birth' )) {
				VMasker(document.querySelector("#gn_cartao_birth")).maskPattern("99/99/9999");
				$( '#gn_cartao_birth' ).blur(
					function () {
						var date = $( '#gn_cartao_birth' ).val();

						if ( ! verify_date( date )) {
							$( '#gn_cartao_birth' ).css( 'border', '1px solid red' );
							customError( 'Data de nascimento inválida!' );
						} else {
							$( '#gn_cartao_birth' ).css( 'border', '1px solid green' );
						}
					}
				)
			}

			if (document.getElementById( 'gn_cartao_expiration' )) {
				VMasker(document.querySelector("#gn_cartao_expiration")).maskPattern("99/9999");
				$( '#gn_cartao_expiration' ).blur(
					function () {
						var exp = $( '#gn_cartao_expiration' ).val();

						if ( ! validate_cartao_expiration( exp )) {
							$( '#gn_cartao_expiration' ).css( 'border', '1px solid red' );
							customError( 'Validade do cartão inválida!' );
						} else {
							$( '#gn_cartao_expiration' ).css( 'border', '1px solid green' );
						}
					}
				)
			}

			if (document.getElementById('gn_cartao_number' )) {
				VMasker(document.querySelector("#gn_cartao_number")).maskPattern("**** **** **** *****");
			}

			// Esconder campos caso haja o brazillian market

			if (typeof $( '#billing_cpf' ).val() !== 'undefined') {

				// cpf_cnpj boleto
				$( '#gn_boleto_cpf_cnpj' ).val( $( '#billing_cpf' ).val() )
				$( '#gn_field_boleto' ).hide();

				// cpf_cnpj cartão
				$( '#gn_cartao_cpf_cnpj' ).val( $( '#billing_cpf' ).val() )
				$( '#gn_field_cpf_cnpj' ).hide();
				$( "#gn_field_birth" ).removeClass( "form-row-last" ).addClass( "form-row-wide" );

				// cpf_cnpj Pix
				$( '#gn_pix_cpf_cnpj' ).val( $( '#billing_cpf' ).val() )
				$( '#gn_field_pix' ).hide();

				$( '#billing_cpf' ).keyup(function () {

						// cpf_cnpj boleto
						$( '#gn_boleto_cpf_cnpj' ).val( $( '#billing_cpf' ).val() )
						$( '#gn_field_boleto' ).hide();

						// cpf_cnpj cartão
						$( '#gn_cartao_cpf_cnpj' ).val( $( '#billing_cpf' ).val() )
						$( '#gn_field_cpf_cnpj' ).hide();
						$( "#gn_field_birth" ).removeClass( "form-row-last" ).addClass( "form-row-wide" );

						// cpf_cnpj Pix
						$( '#gn_pix_cpf_cnpj' ).val( $( '#billing_cpf' ).val() )
						$( '#gn_field_pix' ).hide();
					}
				)
			}

			if (typeof $( '#billing_cnpj' ).val() !== 'undefined') {
				$( '#billing_cnpj' ).keyup(
					function () {
						// cpf_cnpj boleto
						$( '#gn_boleto_cpf_cnpj' ).val( $( '#billing_cnpj' ).val() )
						$( '#gn_field_boleto' ).hide();

						// cpf_cnpj cartão
						$( '#gn_cartao_cpf_cnpj' ).val( $( '#billing_cnpj' ).val() )
						$( '#gn_field_cpf_cnpj' ).hide();
						$( "#gn_field_birth" ).removeClass( "form-row-last" ).addClass( "form-row-wide" );

						// cpf_cnpj Pix
						$( '#gn_pix_cpf_cnpj' ).val( $( '#billing_cnpj' ).val() )
						$( '#gn_field_pix' ).hide();
					}
				)
			}

			if (typeof $( '#billing_birthdate' ).val() !== 'undefined') {

				$( '#gn_cartao_birth' ).val( $( '#billing_birthdate' ).val() )
				$( '#gn_field_birth' ).hide();

				$( '#billing_birthdate' ).keyup(
					function () {
						$( '#gn_cartao_birth' ).val( $( '#billing_birthdate' ).val() )
						$( '#gn_field_birth' ).hide();
					}
				)
			}

			// Bairro obrigatório
			if (typeof $( '#billing_neighborhood_field > label > span' ).html() !== 'undefined') {
				$( '#billing_neighborhood_field > label > span' ).html( $( '#billing_neighborhood_field > label > span' ).html().replace( 'opcional', 'obrigatório' ) )
			}

		}
	);

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

};
