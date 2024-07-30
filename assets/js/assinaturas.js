// Captura o checkbox e o select
jQuery(document).ready(function () {
    // Captura o checkbox e o select
    const checkbox = jQuery('#habilitar_recorrencia');
    const recorrencia = jQuery('#gerencianet_interval');
    const repeats = jQuery('#gerencianet_repeats');

    if (!checkbox.is(':checked')) {
        recorrencia.attr('disabled', true);
        repeats.attr('disabled', true);
    }
    // Adiciona um event listener para verificar quando o checkbox é alterado
    checkbox.change(function () {
        // Se o checkbox estiver marcado, desabilita o select, caso contrário, habilita-o
        recorrencia.prop('disabled', !this.checked);
        repeats.prop('disabled', !this.checked);
    });
});