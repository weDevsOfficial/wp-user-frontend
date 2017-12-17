<?php
global $post;

$form_settings = wpuf_get_form_settings( $post->ID );

$payment_options       = isset( $form_settings['payment_options'] ) ? $form_settings['payment_options'] : 'false';
$enable_pay_per_post   = isset( $form_settings['enable_pay_per_post'] ) ? $form_settings['enable_pay_per_post'] : 'false';
$force_pack_purchase   = isset( $form_settings['force_pack_purchase'] ) ? $form_settings['force_pack_purchase'] : 'false';

$pay_per_post_cost     = isset( $form_settings['pay_per_post_cost'] ) ? $form_settings['pay_per_post_cost'] : 2;
$fallback_ppp_enable   = isset( $form_settings['fallback_ppp_enable'] ) ? $form_settings['fallback_ppp_enable'] : 'false';
$fallback_ppp_cost     = isset( $form_settings['fallback_ppp_cost'] ) ? $form_settings['fallback_ppp_cost'] : 1;

?>
    <table class="form-table">

        <!-- Added Payment Settings -->

        <tr>
            <th><?php _e( 'Payment Options', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[payment_options]" value="false">
                    <input type="checkbox" name="wpuf_settings[payment_options]" value="true"<?php checked( $payment_options, 'true' ); ?> />
                    <?php _e( 'Enable Payments', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Check to enable Payments for this form.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-payment">
            <th>&mdash; <?php _e( 'Force Pack', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[force_pack_purchase]" value="false">
                    <input type="checkbox" name="wpuf_settings[force_pack_purchase]" value="true"<?php checked( $force_pack_purchase, 'true' ); ?> />
                    <?php _e( 'Force subscription pack', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Force users to purchase and use subscription pack.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-payment show-if-force-pack">
            <th>&mdash; &mdash; <?php _e( 'Fallback to pay per post', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[fallback_ppp_enable]" value="false">
                    <input type="checkbox" name="wpuf_settings[fallback_ppp_enable]" value="true"<?php checked( $fallback_ppp_enable, 'true' ); ?> />
                    <?php _e( 'Fallback pay per post charging', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Fallback to pay per post charging if pack limit exceeds', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-payment show-if-force-pack">
            <th>&mdash; &mdash; <?php _e( 'Fallback cost', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="number" name="wpuf_settings[fallback_ppp_cost]" value="<?php echo esc_attr( $fallback_ppp_cost ); ?>" />
                </label>
                <p class="description"><?php _e( 'Cost of pay per post after a subscription pack limit is reached.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-payment">
            <th>&mdash; <?php _e( 'Pay Per Post', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[enable_pay_per_post]" value="false">
                    <input type="checkbox" name="wpuf_settings[enable_pay_per_post]" value="true"<?php checked( $enable_pay_per_post, 'true' ); ?> />
                    <?php _e( 'Enable Pay Per Post', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Charge users for posting,', 'wpuf' ); ?><a target="_blank" href="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/how-to-charge-for-each-post-submission/"><?php _e( ' Learn More about Pay Per Post.', 'wpuf' ); ?></a></p>
            </td>
        </tr>

        <tr class="show-if-payment show-if-pay-per-post">
            <th>&mdash; &mdash; <?php _e( 'Cost Settings', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="number" name="wpuf_settings[pay_per_post_cost]" value="<?php echo esc_attr( $pay_per_post_cost ); ?>" />
                </label>
                <p class="description"><?php _e( 'Amount to be charged per post', 'wpuf' ); ?></p>
            </td>
        </tr>
        <?php do_action( 'wpuf_form_setting_payment', $form_settings, $post ); ?>
    </table>
