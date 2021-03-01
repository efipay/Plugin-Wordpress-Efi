<?php
/**
 * Order Received template.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div>
    <div class="gn-success-payment">
        <div class="gn-row gn-box-emission">
            <div class="pull-left gn-left-space-2">
                <img src="<?php echo esc_url( plugins_url( 'assets/images/', plugin_dir_path( __FILE__ ) ) ); ?>gerencianet-configurations.png"
                     alt="Gerencianet" title="Gerencianet"/>
            </div>
            <div class="pull-left gn-title-emission">
				<?php echo $showText[$generated_payment_type]['title']; ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="gn-success-payment-inside-box">
            <div class="gn-row">
                <div class="gn-col-1">
                    <div class="gn-icon-emission-success">
                        <span class="gn-icon-check-circle-o"></span>
                    </div>
                </div>

                <div class="gn-col-10 gn-success-payment-billet-comments">
					<?php echo $showText[$generated_payment_type]['content']; ?>

                    <?php if ($charge_id != '') { ?>
                        <p>
    						<?php echo $gn_success_payment_charge_number; ?> <b><?php echo $charge_id; ?></b>
                        </p>
                    <?php } ?>
                </div>

            </div>

            <?php if ($qrcode !== "") { ?>
            <div class="gn-qrcode" style="text-align:center;">
                <img src="<?php echo $qrcode; ?>" />
                <div style="font-size: 15px!important;text-align: center !important;">

                    <span style="font-weight: bold !important;">
                        <?php echo __('Or copy the Code below and paste it into the app where you are going to make the payment:', WCGerencianetOficial::getTextDomain()) ; ?>
                    </span>
                    <span style=" max-width: 400px!important; display: inline-block !important">
                        <?php echo $pixCopiaCola;  ?>
                    </span>

                </div>
            </div>
            <?php } ?>

			<?php if ( $generated_payment_type == "billet" && $billet_url != "" ) { ?>
                <div class="gn-align-center gn-success-payment-button">
                    <button class="button" id="showBillet" name="showBillet"
                            onclick="window.open('<?php echo $billet_url; ?>', '_blank');" style="height: auto;">
                        <div class="gn-success-payment-button-icon pull-left"><span class="gn-icon-download"></span>
                        </div>
                        <div class="pull-left"><?php echo $gn_success_payment_open_billet; ?></div>
                        <div class="clear"></div>
                    </button>
                </div>
			<?php } ?>
        </div>
    </div>
</div>
