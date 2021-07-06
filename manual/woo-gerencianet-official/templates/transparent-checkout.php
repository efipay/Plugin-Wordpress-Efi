<?php
/**
 * Gerencianet Payment template.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<script type="text/javascript">
var home_url = "<?php echo esc_url($order_received_url); ?>";
</script>

<p><?php echo apply_filters('woocommerce_gerencianet_transparent_checkout_message', __('This payment will be processed by Gerencianet Payments.', WCGerencianetOficial::getTextDomain())); ?>
</p>
<?php if ($sandbox == "yes") { ?>
<div class="warning-payment" id="wc-gerencianet-messages-sandbox">
    <div class="woocommerce-error"><?php echo $gn_warning_sandbox_message; ?></div>
</div>
<?php } ?>

<div class="warning-payment" id="wc-gerencianet-messages">
    <?php if (($card_option && $order_total_card < 500) || ($billet_option && $order_total_billet < 500)) { ?>
    <div class="woocommerce-error"><?php echo __("To pay Bank Slip or Credit Card the order must have more than R$5,00. But you can pay with PIX", WCGerencianetOficial::getTextDomain()); ?></div>
    <?php } ?>
</div>

<div class="panel-group" id="accordion">

    <?php if ($billet_option == 'yes' && $order_total_billet >= 500) { ?>
    <div class="panel panel-default" id="billet-option" style="border: 1px solid #CCC; margin-bottom: 20px;">
        <div id="background-billet" name="background-billet" class="gn-accordion-option-background">
            <div class="gn-row-left panel-heading panel-gerencianet ">
                <div id="billet-radio-button" class="gn-left">
                    <input type="radio" name="paymentMethodBilletRadio" id="paymentMethodBilletRadio" value="0" />
                </div>
                <div class="gn-left gn-icon-gerencianet">
                    <span class="gn-icon-icones-personalizados_boleto"></span>
                </div>
                <div class="gn-left payment-option-gerencianet">
                    <?php echo $gn_pay_billet_option; ?>
                </div>
                <div class="gn-left gn-payment-option-sizer"></div>
                <div class="clear"></div>
            </div>
            <div class="gn-row-right">
                <div>
                    <div class="gn-left gn-price-payment-info">
                        <?php if (floatval($discount) > 0) { ?>
                        <div style="text-align: center;">
                            <span
                                class="payment-old-price-gerencianet"><?php echo strip_tags($order->get_formatted_order_total()); ?></span><br><span
                                class="payment-discount-gerencianet"><b><?php echo $gn_discount_billet; ?><?= str_replace(".", ",", $discount); ?>
                                    %</b>
                            </span>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="gn-right gn-price-payment-selected total-gerencianet">
                        <?php echo $order_with_billet_discount; ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div id="collapse-payment-billet" class="panel-collapse gn-hide" style="border-top: 1px solid #CCC;">
            <div class="panel-body">

                <form class="form-horizontal">
                    <input name="wc_order_id" type="hidden" value="<?php echo $order->get_id(); ?>" />
                    <div class="gn-row ">
                        <p class="gn-left-space-2">
                            <strong><?php echo $gn_billet_payment_method_comments; ?></strong>
                        </p>
                    </div>

                    <div class="gn-form">
                        <div id="billet-data">

                            <div
                                class="required gn-row gn-billet-field <?php if ($gn_billing_name_corporate_validate) { ?> gn-hide <?php } ?>">
                                <div class="gn-col-2 gn-label">
                                    <label for="input-payment-billet-name-corporate"
                                        class="gn-right-padding-1"><?php echo $gn_name_corporate; ?></label>
                                </div>
                                <div class="gn-col-10">
                                    <input type="text" name="name_corporate" id="name_corporate"
                                        value="<?php echo $gn_order_name_corporate; ?>" class="form-control" />
                                </div>
                            </div>


                            <div
                                class=" required gn-row gn-billet-field <?php if ($gn_billing_email_validate) { ?> gn-hide <?php } ?>">
                                <div class="gn-col-2 gn-label">
                                    <label class="gn-col-12 gn-right-padding-1"
                                        for="input-payment-billet-email"><?php echo $gn_email; ?></label>
                                </div>
                                <div class="gn-col-10">
                                    <input type="text" name="input-payment-billet-email"
                                        value="<?php echo $order->get_billing_email(); ?>" id="input-payment-billet-email"
                                        class="form-control" />
                                </div>
                            </div>

                            <div
                                class="required gn-row gn-billet-field <?php if ($gn_billing_cpf_cnpj_validate && $gn_billing_phone_number_validate) { ?> gn-hide <?php } ?>">
                                <div class="gn-col-2 gn-label">
                                    <label for="input-payment-billet-cpf-cnpj"
                                        class="gn-right-padding-1"><?php echo $gn_cpf_cnpj; ?></label>
                                </div>
                                <div class="gn-col-10">

                                    <div>
                                        <div class="gn-col-3 required">
                                            <input type="text" name="cpf-cnpj" id="cpf-cnpj"
                                                value="<?php echo $gn_order_cpf_cnpj; ?>"
                                                class="form-control cpf-mask" />
                                        </div>
                                        <div class="gn-col-8">
                                            <div class=" required">
                                                <div class="gn-col-4 gn-label">
                                                    <label class="gn-col-12 gn-right-padding-1"
                                                        for="input-payment-billet-phone"><?php echo $gn_phone; ?></label>
                                                </div>
                                                <div class="gn-col-4">
                                                    <input type="text" name="phone_number" id="phone_number"
                                                        value="<?php echo $order->get_billing_phone(); ?>"
                                                        class="form-control phone-mask" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
    <?php } ?>



    <?php if ($card_option == 'yes' && $order_total_card >= 500) { ?>
    <div id="card-option" style="border: 1px solid #CCC; margin-top: 0px; margin-bottom: 30px;">
        <div id="background-card" name="background-card" class="gn-accordion-option-background">
            <div class="gn-row-left panel-heading panel-gerencianet ">
                <div id="card-radio-button" class="gn-left">
                    <input type="radio" name="paymentMethodCardRadio" id="paymentMethodCardRadio" value="0" />
                </div>
                <div class="gn-left gn-icon-gerencianet">
                    <span class="gn-icon-credit-card2"></span>
                </div>
                <div class="gn-left payment-option-gerencianet">
                    <?php echo $gn_pay_card_option; ?>
                </div>
                <div class="gn-left gn-payment-option-sizer"></div>
                <div class="clear"></div>
            </div>
            <div class="gn-row-right">
                <div>
                    <div class="gn-left gn-price-payment-info">
                        <center><span
                                class="payment-installments-gerencianet"><?php echo $gn_installments_pay; ?></span><br><span
                                class="payment-discount-gerencianet"><b><?php echo $max_installments; ?></b></span>
                        </center>
                    </div>
                    <div class="gn-right gn-price-payment-selected total-gerencianet">
                        <?php echo $order_total; ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div id="collapse-payment-card" class="panel-collapse gn-hide"
            style="border-top: 1px solid #CCC; background-color: #FFF;">
            <div class="panel-body">

                <form class="form-horizontal" id="payment-card-form">
                    <input name="wc_order_id" type="hidden" value="<?php echo $order->get_id(); ?>" />
                    <div class="gn-row">
                        <p class="gn-left-space-2"><strong><?php echo $gn_card_payment_comments; ?></strong></p>
                    </div>

                    <div class="gn-form">
                        <div id="card-data">
                            <div class="gn-initial-section">

                                <div
                                    class=" required gn-row gn-card-field <?php if ($gn_billing_name_corporate_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-2 gn-label">
                                        <label class="gn-col-12 gn-right-padding-1"
                                            for="input-payment-card-name-corporate"><?php echo $gn_name_corporate; ?></label>
                                    </div>
                                    <div class="gn-col-10">
                                        <input type="text" name="input-payment-card-name-corporate"
                                            id="input-payment-card-name-corporate"
                                            value="<?php echo $gn_order_name_corporate; ?>" class="form-control" />
                                    </div>
                                </div>

                                <div
                                    class=" required gn-row gn-card-field <?php if ($gn_billing_cpf_cnpj_validate && $gn_billing_phone_number_validate) { ?> gn-hide <?php } ?>">

                                    <div class="gn-col-2 gn-label">
                                        <label for="input-payment-card-cpf-cnpj"
                                            class="gn-right-padding-1"><?php echo $gn_cpf_cnpj; ?></label>
                                    </div>
                                    <div class="gn-col-4">
                                        <input type="text" name="input-payment-card-cpf-cnpj"
                                            id="input-payment-card-cpf-cnpj" value="<?php echo $gn_order_cpf_cnpj; ?>"
                                            class="form-control cpf-mask gn-minimum-size-field" />
                                    </div>
                                    <div class="gn-col-6">
                                        <div class="gn-col-4 gn-label">
                                            <label class="gn-left-space-2 gn-right-padding-1"
                                                for="input-payment-card-phone"><?php echo $gn_phone; ?></label>
                                        </div>
                                        <div class="gn-col-8">
                                            <input type="text" name="input-payment-card-phone"
                                                value="<?php echo $order->get_billing_phone(); ?>"
                                                id="input-payment-card-phone"
                                                class="form-control phone-mask gn-minimum-size-field" />
                                        </div>

                                    </div>
                                </div>


                                <div
                                    class=" required gn-row gn-card-field <?php if ($gn_billing_birthdate_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-3 gn-label-birth">
                                        <label class="gn-right-padding-1"
                                            for="input-payment-card-birth"><?php echo $gn_birth; ?></label>
                                    </div>
                                    <div class="gn-col-3">
                                        <input type="text" name="input-payment-card-birth" id="input-payment-card-birth"
                                            value="<?php echo $order->billing_birthdate ?>"
                                            class="form-control birth-mask" />
                                    </div>
                                </div>

                                <div
                                    class=" required gn-card-field <?php if ($gn_billing_email_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-2">
                                        <label class="gn-col-12 gn-label gn-right-padding-1"
                                            for="input-payment-card-email"><?php echo $gn_email; ?></label>
                                    </div>
                                    <div class="gn-col-10">
                                        <input type="text" name="input-payment-card-email"
                                            value="<?php echo $order->get_billing_email(); ?>" id="input-payment-card-email"
                                            class="form-control" />
                                    </div>
                                </div>
                            </div>

                            <div id="billing-adress" class="gn-section">
                                <div
                                    class="gn-row gn-card-field <?php if ($gn_billing_street_validate && $gn_billing_number_validate && $gn_billing_neighborhood_validate && $gn_billing_city_validate && $gn_billing_zipcode_validate && $gn_billing_state_validate) { ?> gn-hide <?php } ?>">
                                    <p>
                                        <strong><?php echo $gn_billing_address_title; ?></strong>
                                    </p>
                                </div>

                                <div
                                    class="required gn-row gn-card-field <?php if ($gn_billing_street_validate && $gn_billing_number_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-2">
                                        <label class="gn-col-12 gn-label gn-right-padding-1"
                                            for="input-payment-card-street"><?php echo $gn_street; ?></label>
                                    </div>

                                    <div class="gn-col-10">
                                        <div class="gn-col-6 required">
                                            <input type="text" name="input-payment-card-address-street"
                                                id="input-payment-card-street"
                                                value="<?php echo $order->get_billing_address_1(); ?>" class="form-control" />
                                        </div>
                                        <div class="gn-col-6">
                                            <div class=" required gn-left-space-2">
                                                <div class="gn-col-5">
                                                    <label class="gn-col-12 gn-label gn-right-padding-1"
                                                        for="input-payment-card-address-number"><?php echo $gn_street_number; ?></label>
                                                </div>
                                                <div class="gn-col-7">
                                                    <input type="text" name="input-payment-card-address-number"
                                                        id="input-payment-card-address-number"
                                                        value="<?php echo $order->billing_number; ?>"
                                                        class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="gn-row gn-card-field <?php if ($gn_billing_neighborhood_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-2 required">
                                        <label class="gn-col-12 gn-label required gn-right-padding-1"
                                            for="input-payment-card-neighborhood"><?php echo $gn_neighborhood; ?></label>
                                    </div>

                                    <div class="gn-col-3">

                                        <input type="text" name="input-payment-card-neighborhood"
                                            id="input-payment-card-neighborhood"
                                            value="<?php echo $order->billing_neighborhood; ?>" class="form-control" />
                                    </div>
                                    <div class="gn-col-7">
                                        <div class=" gn-left-space-2">
                                            <div class="gn-col-5">
                                                <label class="gn-col-12 gn-label gn-right-padding-1"
                                                    for="input-payment-card-complement"><?php echo $gn_address_complement; ?></label>
                                            </div>
                                            <div class="gn-col-7">
                                                <input type="text" name="input-payment-card-complement"
                                                    id="input-payment-card-complement"
                                                    value="<?php echo $order->get_billing_address_2(); ?>"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="required billing-address-data gn-card-field gn-row <?php if ($gn_billing_city_validate && $gn_billing_zipcode_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-2">
                                        <label class="gn-col-12 gn-label gn-right-padding-1"
                                            for="input-payment-card-zipcode"><?php echo $gn_cep; ?></label>
                                    </div>
                                    <div class="gn-col-10">
                                        <div class="gn-col-4 required">

                                            <input type="text" name="input-payment-card-zipcode"
                                                id="input-payment-card-zipcode"
                                                value="<?php echo $order->get_billing_postcode() ?>" class="form-control" />
                                        </div>
                                        <div class="gn-col-8">
                                            <div class=" required gn-left-space-2">
                                                <div class="gn-col-4">
                                                    <label class="gn-col-12 gn-label gn-right-padding-1"
                                                        for="input-payment-card-city"><?php echo $gn_city; ?></label>
                                                </div>
                                                <div class="gn-col-6">
                                                    <input type="text" name="input-payment-card-city"
                                                        id="input-payment-card-city"
                                                        value="<?php echo $order->get_billing_city(); ?>"
                                                        class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class=" required billing-address-data gn-card-field gn-row <?php if ($gn_billing_state_validate) { ?> gn-hide <?php } ?>">
                                    <div class="gn-col-2">
                                        <label class="gn-col-12 gn-label gn-right-padding-1"
                                            for="input-payment-card-state"><?php echo $gn_state; ?></label>
                                    </div>
                                    <div class="gn-col-10">
                                        <select name="input-payment-card-state" id="input-payment-card-state"
                                            class="form-control gn-form-select">
                                            <option value=""><?php echo $gn_billing_state_select; ?></option>
                                            <option value="AC"
                                                <?php if ($order->get_billing_state() == "AC" || $order->get_billing_state() == "Acre") { ?>
                                                selected <?php } ?>>
                                                Acre
                                            </option>
                                            <option value="AL"
                                                <?php if ($order->get_billing_state() == "AL" || $order->get_billing_state() == "Alagoas") { ?>
                                                selected <?php } ?>>
                                                Alagoas
                                            </option>
                                            <option value="AP"
                                                <?php if ($order->get_billing_state() == "AP" || $order->get_billing_state() == "Amapá") { ?>
                                                selected <?php } ?>>
                                                Amapá
                                            </option>
                                            <option value="AM"
                                                <?php if ($order->get_billing_state() == "AM" || $order->get_billing_state() == "Amazonas") { ?>
                                                selected <?php } ?>>
                                                Amazonas
                                            </option>
                                            <option value="BA"
                                                <?php if ($order->get_billing_state() == "BA" || $order->get_billing_state() == "Bahia") { ?>
                                                selected <?php } ?>>
                                                Bahia
                                            </option>
                                            <option value="CE"
                                                <?php if ($order->get_billing_state() == "CE" || $order->get_billing_state() == "Ceará") { ?>
                                                selected <?php } ?>>
                                                Ceará
                                            </option>
                                            <option value="DF"
                                                <?php if ($order->get_billing_state() == "DF" || $order->get_billing_state() == "Distrito Federal") { ?>
                                                selected <?php } ?>>
                                                Distrito Federal
                                            </option>
                                            <option value="ES"
                                                <?php if ($order->get_billing_state() == "ES" || $order->get_billing_state() == "Espírito Santo") { ?>
                                                selected <?php } ?>>
                                                Espírito Santo
                                            </option>
                                            <option value="GO"
                                                <?php if ($order->get_billing_state() == "GO" || $order->get_billing_state() == "Goiás") { ?>
                                                selected <?php } ?>>
                                                Goiás
                                            </option>
                                            <option value="MA"
                                                <?php if ($order->get_billing_state() == "MA" || $order->get_billing_state() == "Maranhão") { ?>
                                                selected <?php } ?>>
                                                Maranhão
                                            </option>
                                            <option value="MT"
                                                <?php if ($order->get_billing_state() == "MT" || $order->get_billing_state() == "Mato Grosso") { ?>
                                                selected <?php } ?>>
                                                Mato Grosso
                                            </option>
                                            <option value="MS"
                                                <?php if ($order->get_billing_state() == "MS" || $order->get_billing_state() == "Mato Grosso do Sul") { ?>
                                                selected <?php } ?>>
                                                Mato Grosso do Sul
                                            </option>
                                            <option value="MG"
                                                <?php if ($order->get_billing_state() == "MG" || $order->get_billing_state() == "Minas Gerais") { ?>
                                                selected <?php } ?>>
                                                Minas Gerais
                                            </option>
                                            <option value="PA"
                                                <?php if ($order->get_billing_state() == "PA" || $order->get_billing_state() == "Pará") { ?>
                                                selected <?php } ?>>
                                                Pará
                                            </option>
                                            <option value="PB"
                                                <?php if ($order->get_billing_state() == "PB" || $order->get_billing_state() == "Paraíba") { ?>
                                                selected <?php } ?>>
                                                Paraíba
                                            </option>
                                            <option value="PR"
                                                <?php if ($order->get_billing_state() == "PR" || $order->get_billing_state() == "Paraná") { ?>
                                                selected <?php } ?>>
                                                Paraná
                                            </option>
                                            <option value="PE"
                                                <?php if ($order->get_billing_state() == "PE" || $order->get_billing_state() == "Pernambuco") { ?>
                                                selected <?php } ?>>
                                                Pernambuco
                                            </option>
                                            <option value="PI"
                                                <?php if ($order->get_billing_state() == "PI" || $order->get_billing_state() == "Piauí") { ?>
                                                selected <?php } ?>>
                                                Piauí
                                            </option>
                                            <option value="RJ"
                                                <?php if ($order->get_billing_state() == "RJ" || $order->get_billing_state() == "Rio de Janeiro") { ?>
                                                selected <?php } ?>>
                                                Rio de Janeiro
                                            </option>
                                            <option value="RN"
                                                <?php if ($order->get_billing_state() == "RN" || $order->get_billing_state() == "Rio Grande do Norte") { ?>
                                                selected <?php } ?>>
                                                Rio Grande do Norte
                                            </option>
                                            <option value="RS"
                                                <?php if ($order->get_billing_state() == "RS" || $order->get_billing_state() == "Rio Grande do Sul") { ?>
                                                selected <?php } ?>>
                                                Rio Grande do Sul
                                            </option>
                                            <option value="RO"
                                                <?php if ($order->get_billing_state() == "RO" || $order->get_billing_state() == "Rondônia") { ?>
                                                selected <?php } ?>>
                                                Rondônia
                                            </option>
                                            <option value="RR"
                                                <?php if ($order->get_billing_state() == "RR" || $order->get_billing_state() == "Roraima") { ?>
                                                selected <?php } ?>>
                                                Roraima
                                            </option>
                                            <option value="SC"
                                                <?php if ($order->get_billing_state() == "SC" || $order->get_billing_state() == "Santa Catarina") { ?>
                                                selected <?php } ?>>
                                                Santa Catarina
                                            </option>
                                            <option value="SP"
                                                <?php if ($order->get_billing_state() == "SP" || $order->get_billing_state() == "São Paulo") { ?>
                                                selected <?php } ?>>
                                                São Paulo
                                            </option>
                                            <option value="SE"
                                                <?php if ($order->get_billing_state() == "SE" || $order->get_billing_state() == "Sergipe") { ?>
                                                selected <?php } ?>>
                                                Sergipe
                                            </option>
                                            <option value="TO"
                                                <?php if ($order->get_billing_state() == "TO" || $order->get_billing_state() == "Tocantins") { ?>
                                                selected <?php } ?>>
                                                Tocantins
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="clear"></div>

                            <div class="gn-section">
                                <p><strong><?php echo $gn_card_title; ?></strong></p>

                                <div class="required gn-row">

                                    <div style="display: none;">
                                        <div class="gn-card-brand-selector">
                                            <input id="none" type="radio" name="input-payment-card-brand"
                                                id="input-payment-card-brand" value="" checked class="gn-hide" />
                                            <div class="pull-left gn-card-brand-content">
                                                <input id="visa" type="radio" name="input-payment-card-brand"
                                                    id="input-payment-card-brand" value="visa" class="gn-hide" />
                                                <label class="gn-card-brand gn-visa" for="visa"></label>
                                            </div>
                                            <div class="pull-left gn-card-brand-content">
                                                <input id="mastercard" type="radio" name="input-payment-card-brand"
                                                    id="input-payment-card-brand" value="mastercard" class="gn-hide" />
                                                <label class="gn-card-brand gn-mastercard" for="mastercard"></label>
                                            </div>
                                            <div class="pull-left gn-card-brand-content">
                                                <input id="amex" type="radio" name="input-payment-card-brand"
                                                    id="input-payment-card-brand" value="amex" class="gn-hide" />
                                                <label class="gn-card-brand gn-amex" for="amex"></label>
                                            </div>
                                            <div class="pull-left gn-card-brand-content">
                                                <input id="diners" type="radio" name="input-payment-card-brand"
                                                    id="input-payment-card-brand" value="diners" class="gn-hide" />
                                                <label class="gn-card-brand gn-diners" for="diners"></label>
                                            </div>
                                            <div class="pull-left gn-card-brand-content">
                                                <input id="elo" type="radio" name="input-payment-card-brand"
                                                    id="input-payment-card-brand" value="elo" class="gn-hide" />
                                                <label class="gn-card-brand gn-elo" for="elo"></label>
                                            </div>
                                            <div class="pull-left gn-card-brand-content">
                                                <input id="hipercard" type="radio" name="input-payment-card-brand"
                                                    id="input-payment-card-brand" value="hipercard" class="gn-hide" />
                                                <label class="gn-card-brand gn-hipercard" for="hipercard"></label>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="gn-row required gn-card-data">
                                    <style>
                                    .ccicon {
                                        height: 31.59px !important;
                                        right: 6px;
                                        /* top: calc(50% - 17px); */
                                        width: 60px;
                                    }
                                    </style>
                                    <div class="gn-card-number">
                                        <div>
                                            <?php echo $gn_card_number; ?>
                                        </div>

                                        <div class="gn-col-2" id="icon-div">
                                            <svg id="ccicon" class="ccicon" viewBox="0 60 500 400" version="1.1"
                                                xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <g>
                                                    <path style="fill:#6ABDA0;"
                                                        d="M476.69,441.379H35.31c-19.5,0-35.31-15.81-35.31-35.31V105.931c0-19.5,15.81-35.31,35.31-35.31   H476.69c19.5,0,35.31,15.81,35.31,35.31v300.138C512,425.569,496.19,441.379,476.69,441.379">
                                                    </path>
                                                    <polygon style="fill:#488578;"
                                                        points="0,194.207 512,194.207 512,123.586 0,123.586  ">
                                                    </polygon>
                                                    <polygon style="fill:#F0C419;"
                                                        points="300.138,388.414 459.034,388.414 459.034,300.138 300.138,300.138  ">
                                                    </polygon>
                                                    <g>
                                                        <path style="fill:#488578;"
                                                            d="M123.483,264.828H44.141c-4.882,0-8.828-3.946-8.828-8.828s3.946-8.828,8.828-8.828h79.342    c4.882,0,8.828,3.946,8.828,8.828S128.365,264.828,123.483,264.828">
                                                        </path>
                                                        <path style="fill:#488578;"
                                                            d="M238.345,264.828h-79.342c-4.882,0-8.828-3.946-8.828-8.828s3.946-8.828,8.828-8.828h79.342    c4.882,0,8.828,3.946,8.828,8.828S243.226,264.828,238.345,264.828">
                                                        </path>
                                                        <path style="fill:#488578;"
                                                            d="M176.552,300.138H44.138c-4.882,0-8.828-3.946-8.828-8.828s3.946-8.828,8.828-8.828h132.414    c4.882,0,8.828,3.946,8.828,8.828S181.433,300.138,176.552,300.138">
                                                        </path>
                                                        <path style="fill:#488578;"
                                                            d="M238.345,300.138h-26.483c-4.882,0-8.828-3.946-8.828-8.828s3.946-8.828,8.828-8.828h26.483    c4.882,0,8.828,3.946,8.828,8.828S243.226,300.138,238.345,300.138">
                                                        </path>
                                                    </g>
                                                </g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                                <g></g>
                                            </svg>
                                        </div>
                                        <div class="gn-col-9">
                                            <div class="gn-card-number-input-row">
                                                <input type="text" name="input-payment-card-number"
                                                    id="input-payment-card-number" value=""
                                                    class="form-control gn-input-card-number" />
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                        <script>
                                        const source = document.getElementById('input-payment-card-number');
                                        const inputHandler = function() {
                                            getCardBrand(source.value);
                                        }
                                        source.addEventListener('input', inputHandler);
                                        </script>
                                    </div>



                                    <div class="gn-card-expiration" sytle="overflow: auto;">
                                        <div>
                                            <?php echo $gn_card_expiration; ?>
                                        </div>
                                        <div class="gn-card-expiration-row">
                                            <select class="form-control gn-card-expiration-select"
                                                name="input-payment-card-expiration-month"
                                                id="input-payment-card-expiration-month">
                                                <option value=""> MM</option>
                                                <option value="01"> 01</option>
                                                <option value="02"> 02</option>
                                                <option value="03"> 03</option>
                                                <option value="04"> 04</option>
                                                <option value="05"> 05</option>
                                                <option value="06"> 06</option>
                                                <option value="07"> 07</option>
                                                <option value="08"> 08</option>
                                                <option value="09"> 09</option>
                                                <option value="10"> 10</option>
                                                <option value="11"> 11</option>
                                                <option value="12"> 12</option>
                                            </select>
                                            <div class="gn-card-expiration-divisor">
                                                /
                                            </div>
                                            <select class="form-control gn-card-expiration-select"
                                                name="input-payment-card-expiration-year"
                                                id="input-payment-card-expiration-year">
                                                <option value=""> YYYY</option>
                                                <?php
                                                    $actual_year = intval(date("Y"));
                                                    $last_year   = $actual_year + 15;
                                                    for ($i = $actual_year; $i <= $last_year; $i++) {
                                                        echo '<option value="' . $i . '"> ' . $i . ' </option>';
                                                    }
                                                    ?>
                                            </select>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                    <div class="gn-cvv">
                                        <div>
                                            <?php echo $gn_card_cvv; ?>
                                        </div>
                                        <div>
                                            <div class="pull-left gn-cvv-row">
                                                <input type="text" name="input-payment-card-cvv"
                                                    id="input-payment-card-cvv" value=""
                                                    class="form-control gn-cvv-input" />
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
                                </div>

                                <div class="gn-row required">
                                    <div class="gn-col-12">
                                        <label class=""
                                            for="input-payment-card-installments"><?php echo $gn_card_installments_options; ?></label>
                                    </div>
                                    <div class="gn-col-12">
                                        <select name="input-payment-card-installments"
                                            id="input-payment-card-installments" class="form-control gn-form-select">
                                            <option value=""><?php echo __("Enter your credit card details", WCGerencianetOficial::getTextDomain()); ?></option>
                                        </select>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
    <?php } ?>

    <?php if ($pix_option == 'yes') {?>
    <div id="pix-option" style="border: 1px solid #CCC; margin-top: 0px; margin-bottom: 30px;">
        <div id="background-pix" name="background-pix" class="gn-accordion-option-background">
            <div class="gn-row-left panel-heading panel-gerencianet ">
                <div id="pix-radio-button" class="gn-left">
                    <input type="radio" name="paymentMethodPixRadio" id="paymentMethodPixRadio" value="0" />
                </div>
                <div class="gn-left gn-icon-gerencianet">
                    <img style="width:26px;" src="<?php  echo plugin_dir_url( __DIR__ )."./assets/images/pix.png"; ?>"
                        alt="Pix logo">
                </div>
                <div class="gn-left payment-option-gerencianet">
                    <?php echo $gn_pay_pix_option ?>
                </div>
                <div class="gn-left gn-payment-option-sizer"></div>
                <div class="clear"></div>
            </div>
            <div class="gn-row-right">
                <div>
                    <div class="gn-left gn-price-payment-info">
                        <?php if (floatval($discountPix) > 0) { ?>
                        <div style="text-align: center;">
                            <span
                                class="payment-old-price-gerencianet"><?php echo strip_tags($order->get_formatted_order_total()); ?></span><br><span
                                class="payment-discount-gerencianet"><b><?php echo $gn_discount_pix; ?><?= str_replace(".", ",", $discountPix); ?>
                                    %</b>
                            </span>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="gn-right gn-price-payment-selected total-gerencianet">
                        <?php echo $totalValuePix; ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div id="collapse-payment-pix" class="panel-collapse gn-hide" style="border-top: 1px solid #CCC;">
            <div class="panel-body">
                <div class="gn-osc-row gn-osc-pay-comments">
                    <p class="gn-left-space-2"><strong><?php echo __("Opting to pay by Pix, the payment is processed and the confirmation will take place within a couple seconds.", WCGerencianetOficial::getTextDomain()); ?></strong></p>
                </div>
                <form class="form-horizontal">
                    <input name="wc_order_id" type="hidden" value="<?php echo $order->get_id(); ?>" />

                    <div class="gn-form">
                        <div id="pix-data">

                            <div
                                class="required gn-row gn-pix-field <?php if ($gn_pix_cpf_cnpj_validate) { ?> gn-hide <?php } ?>">
                                <div class="gn-col-2 gn-label">
                                    <label for="input-payment-pix-cpf-cnpj"
                                        class="gn-right-padding-1"><?php echo $gn_cpf_cnpj; ?></label>
                                </div>
                                <div class="gn-col-10">
                                    <input type="text" name="pix-cpf-cnpj" id="pix-cpf-cnpj"
                                        value="<?php echo $gn_order_cpf_cnpj; ?>" class="form-control cpf-mask" />
                                </div>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="checkout-footer">
    <div class="pull-left">
        <p>
            <a class="button cancel"
                href="<?php echo esc_url($order->get_cancel_order_url()) ?>"><?php _e('Cancel order &amp; restore cart', WCGerencianetOficial::getTextDomain()) ?></a>
        </p>
    </div>
    <div class="pull-right">
        <div id="price-billet" name="price-billet" class="gn-hide">
            <p>
                <button class="button alt"
                    id="gn-pay-billet-button"><?php _e('Generate Bank Slip', WCGerencianetOficial::getTextDomain());
                                                                        echo ' &nbsp; | &nbsp; ' . $order_with_billet_discount; ?></button>
            </p>
        </div>
        <div id="price-card" name="price-card" class="gn-hide">
            <p>
                <button class="button alt"
                    id="gn-pay-card-button"><?php _e('Pay with Credit Card', WCGerencianetOficial::getTextDomain());
                                                                    echo ' &nbsp; | &nbsp; ' . $order_total; ?></button>
            </p>
        </div>
        <div id="price-pix" name="price-pix" class="gn-hide">
            <p>
                <button class="button alt"
                    id="gn-pay-pix-button"><?php _e('Generate QR-Code', WCGerencianetOficial::getTextDomain());
                                                                    echo ' &nbsp; | &nbsp; ' . $totalValuePix; ?></button>
            </p>
        </div>
        <div id="price-no-payment-selected" name="price-no-payment-selected">
            <p>
                <button class="button" id="gn-pay-no-selected"
                    disabled=""><?php _e('Select a payment option', WCGerencianetOficial::getTextDomain()) ?></button>
            </p>
        </div>
    </div>

    <div class="pull-right gn-loading-request">
        <div class="gn-loading-request-row">
            <div class="pull-left gn-loading-request-text">
                <?php echo $gn_loading_payment_request; ?>
            </div>
            <div class="spin pull-left gn-loading-request-spin-box">
                <div class="gn-icon-spinner6 gn-loading-request-spin-icon"></div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>

<div class="modal">
    <!-- Place at bottom of page -->
</div>