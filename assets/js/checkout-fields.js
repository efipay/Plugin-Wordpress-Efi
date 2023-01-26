jQuery( document ).ready(
	function ($) {
		$( "#billing_address_2_field" ).removeClass( "form-row-wide" ).addClass( "form-row-first" );
		$( "#billing_address_2_field > label" ).removeClass( "screen-reader-text" ).html( "Complemento" );

		let newNode = $( "<p></p>" )
		newNode.addClass( "form-row form-row-last validate-required" );
		newNode.html( '<label for="gn_billing_number" class="">Número <abbr class="required" title="obrigatório">*</abbr></label><span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="gn_billing_number" id="gn_billing_number" placeholder=""></span>' );

		$( "#billing_address_2_field" ).after( newNode )
	}
)
