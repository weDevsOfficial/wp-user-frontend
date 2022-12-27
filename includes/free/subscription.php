<?php

class WPUF_Subscription_Element extends WPUF_Pro_Prompt {

    public static function add_subscription_element( $sub_meta, $hidden_recurring_class, $hidden_trial_class, $obj ) {
        $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';
        $crown      = '';

        if ( file_exists( $crown_icon ) ) {
            $crown = sprintf( '<span class="pro-icon-title"> %s</span>', file_get_contents( $crown_icon ) );
        }
        ?>
        <tr class="wpuf-subscription-recurring pro-preview">
            <th><label><?php esc_html_e( 'Recurring ', 'wp-user-frontend' ); echo $crown; ?></label></th>
            <td>
                <label for="wpuf-recuring-pay">
                    <input type="checkbox" disabled size="20" style="" id="wpuf-recuring-pay" value="no" />
                    <?php esc_html_e( 'Enable Recurring Payment', 'wp-user-frontend' ); ?>
                </label>
                <?php echo wpuf_get_pro_preview_html(); ?>
            </td>
        </tr>
    <?php
        echo wpuf_get_pro_preview_tooltip();
    }
}
