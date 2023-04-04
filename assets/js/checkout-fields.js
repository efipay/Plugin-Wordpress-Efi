jQuery( document ).ready(function ($) {


	if (document.getElementById( 'billing_neighborhood' )) {
		$( "#billing_neighborhood_field" ).addClass( "validate-required" );
		$( "#billing_neighborhood_field > label" ).removeClass( "screen-reader-text" ).html( "Bairro: <abbr class='required' title='required'>*</abbr>" );
		
		
		$( '#billing_neighborhood' ).blur(function () {
				let valor = $( '#billing_neighborhood' ).val();

				if ( valor == "" || valor.length < 3) {
					$( '#billing_neighborhood' ).css( 'border', '1px solid red' );
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
					).fire( { icon: 'error', title: 'O campo BAIRRO é obrigatório!' } )
				} else {
					$( '#billing_neighborhood' ).css( 'border', '1px solid green' );
				}
			}
		)
	}else{
		let newNode = $( "<p id='gn_billing_neighborhood_field'></p>" )
		newNode.addClass( "form-row form-row-first validate-required" );
		newNode.html( '<label for="gn_billing_neighborhood" class="">Bairro <abbr class="required" title="obrigatório">*</abbr></label><span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="gn_billing_neighborhood" id="gn_billing_neighborhood" placeholder="Bairro"></span>' );

		$( "#billing_address_1_field" ).after( newNode )

		$( '#gn_billing_neighborhood' ).blur(function () {
			let valor = $( '#gn_billing_neighborhood' ).val();

			if ( valor == "" || valor.length < 3) {
				$( '#gn_billing_neighborhood' ).css( 'border', '1px solid red' );
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
				).fire( { icon: 'error', title: 'O campo BAIRRO é obrigatório!' } )
			} else {
				$( '#gn_billing_neighborhood' ).css( 'border', '1px solid green' );
			}
		}
	)




	}


	if(!document.getElementById( 'billing_number' )){

		let newNode = $( "<p id='gn_billing_number_field'></p>" )
		newNode.addClass( "form-row form-row-last validate-required" );
		newNode.html( '<label for="gn_billing_number" class="">Número <abbr class="required" title="obrigatório">*</abbr></label><span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="gn_billing_number" id="gn_billing_number" placeholder="Número"></span>' );

		$( "#gn_billing_neighborhood_field" ).after( newNode )

		$( '#gn_billing_number' ).blur(function () {
			let valor = $( '#gn_billing_number' ).val();

			if ( valor == "" || valor.length < 1) {
				$( '#gn_billing_number' ).css( 'border', '1px solid red' );
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
				).fire( { icon: 'error', title: 'O campo NÚMERO é obrigatório!' } )
			} else {
				$( '#gn_billing_number' ).css( 'border', '1px solid green' );
			}
		})
	}
		
})
