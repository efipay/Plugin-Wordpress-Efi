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
                    <img src="<?php echo home_url( '/'); ?>wp-content/plugins/woo-gerencianet-oficial/assets/images/gerencianet-configurations.png" alt="Gerencianet" title="Gerencianet" />
                </div>
                <div class="pull-left gn-title-emission">
                    <?php if ($generated_payment_type=="billet") { 
                        echo $gn_success_payment_box_title_billet;
                    } else { 
                        echo $gn_success_payment_box_title_card;
                    }  ?>
                </div>
                <div class="clear"></div>
            </div>

            <div class="gn-success-payment-inside-box">
                <div class="gn-row">
                    <div class="gn-col-1">
                      <div class="gn-icon-emission-success">
                          <span class="icon-check-circle-o"></span>
                      </div>
                    </div>

                    <div class="gn-col-10 gn-success-payment-billet-comments">
                        <?php if ($generated_payment_type=="billet") { 
                            echo $gn_success_payment_box_comments_billet;
                        } else {
                            echo $gn_success_payment_box_comments_card_part1 . " " . $email . " " . $gn_success_payment_box_comments_card_part2;
                        } ?>
                        
                        <p>
                            <?php echo $gn_success_payment_charge_number; ?> <b><?php echo $charge_id; ?></b>
                        </p>
                    </div>

                </div>

                <?php if ($generated_payment_type=="billet") { ?>
                <div class="gn-align-center">
                    <button class="button" id="showBillet" name="showBillet" onclick="window.open('<?php echo urldecode($_POST["billet"]); ?>', '_blank');">
                        <div class="gn-success-payment-button-icon pull-left"><span class="icon-download"></span></div> 
                        <div class="gn-button-divisor pull-left" ></div>
                        <div class="pull-left"><?php echo $gn_success_payment_open_billet; ?></div>
                        <div class="clear"></div>
                    </button>
                </div>
                <?php } ?>
            </div>
          </div>
      </div>
      </p>