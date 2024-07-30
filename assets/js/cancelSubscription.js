function areYouSure() {
    Swal.fire({
        title: 'Tem certeza que deseja cancelar essa assinatura?',
        text: "Essa ação não poderá ser desfeita, mas uma nova assinatura pode ser realizada.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, quero cancelar a assinatura',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            cancelSubscription();
        }
    }).catch((response) => {
        console.log("catch: " + response)
    })
}

function cancelSubscription() {
    var subs_id = jQuery('#sub_id').val();
    var order_id = jQuery('#order_id').val();

    var data = {
        action: "woocommerce_gerencianet_cancel_subscription",
        security: woocommerce_gerencianet_api.security,
        subs_id: subs_id,
        order_id: order_id
    };

    Swal.fire({
        title: 'Por favor, aguarde...',
        html:
            '<center><img src="' + woocommerce_gerencianet_api.loading_img + '" style="width:150px; margin:0;" ><br><p style="font-size: 20px;">Estamos processando sua solicitação.</p></center>',
        text: '',
        showConfirmButton: false,
    })

    jQuery.ajax({
        type: "POST",
        url: woocommerce_gerencianet_api.ajax_url,
        data: data,
        success: () => {
            Swal.fire({
                title: 'Assinatura Cancelada!',
                text: 'Essa assinatura foi cancelada com sucesso!',
                icon: 'success'
            }).then(() => {
                document.location.reload(true);
            })
        },
        error: function () {
            Swal.fire(
                'Ops!',
                'Houve um erro ao cancelar essa assinatura. Tente novamente mais tarde.',
                'error'
            )
        }
    });
}