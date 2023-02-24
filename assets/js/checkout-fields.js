jQuery( document ).ready(function ($) {


	if(!document.getElementById( 'billing_number' )){
		$( "#billing_address_2_field" ).removeClass( "form-row-wide" ).addClass( "form-row-first" );
		$( "#billing_address_2_field > label" ).removeClass( "screen-reader-text" ).html( "Complemento" );

		let newNode = $( "<p></p>" )
		newNode.addClass( "form-row form-row-last validate-required" );
		newNode.html( '<label for="gn_billing_number" class="">Número <abbr class="required" title="obrigatório">*</abbr></label><span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="gn_billing_number" id="gn_billing_number" placeholder=""></span>' );

		$( "#billing_address_2_field" ).after( newNode )
	}else{
		$( "#billing_address_2_field" ).removeClass( "form-row-wide" ).addClass( "form-row-last" );
		$( "#billing_address_2_field > label" ).removeClass( "screen-reader-text" ).html( "Complemento" );
	}

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
	}
		



		
})
