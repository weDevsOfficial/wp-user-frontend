<?php

$user_id = get_current_user_id();

$address_fields = [];
$countries      = [];
$cs             = new CountryState();



if ( isset( $_POST['update_billing_address'] ) ) {

    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'dashboard_update_billing_address' ) ) {
        return;
    }



    $add_line_1 = isset( $_POST['add_line_1'] ) ? sanitize_text_field( wp_unslash( $_POST['add_line_1'] ) ) : '';
    $add_line_2 = isset( $_POST['add_line_2'] ) ? sanitize_text_field( wp_unslash( $_POST['add_line_2'] ) ) : '';
    $city       = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
    $state      = isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '';
    $zip_code   = isset( $_POST['zip_code'] ) ? sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ) : '';
    $country    = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';

    $address_fields = [
        'add_line_1'    => $add_line_1,
        'add_line_2'    => $add_line_2,
        'city'          => $city,
        'state'         => strtolower( str_replace( ' ', '', $state ) ),
        'zip_code'      => $zip_code,
        'country'       => $country,
    ];
    update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );
    echo '<div class="wpuf-success">' . esc_html( __( 'Billing address is updated.', 'wp-user-frontend' ) ) . '</div>';
} else {
    if ( metadata_exists( 'user', $user_id, 'wpuf_address_fields' ) ) {
        $address_fields = get_user_meta( $user_id, 'wpuf_address_fields', true );
        $address_fields = $address_fields;
    } else {
        $address_fields = array_fill_keys(
            [ 'add_line_1', 'add_line_2', 'city', 'state', 'zip_code', 'country' ], '' );
    }
}
?>

<form class="wpuf-form form-label-above" action="" method="post">
    <div class="wpuf-fields">
        <?php wp_nonce_field('dashboard_update_billing_address'); ?>
        <ul class="wpuf-form form-label-above">

            <li>
                <div class="wpuf-label"><?php esc_html_e( 'Address Line 1 ', 'wp-user-frontend' ); ?><span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="add_line_1" id="add_line_1" value="<?php echo esc_attr( $address_fields['add_line_1'] ); ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php esc_html_e( 'Address Line 2 ', 'wp-user-frontend' ); ?></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="add_line_2" id="add_line_2" value="<?php echo esc_attr( $address_fields['add_line_2'] ); ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php esc_html_e( 'City', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="city" id="city" value="<?php echo esc_attr( $address_fields['city'] ); ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php esc_html_e( 'State/Province/Region', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="state" id="state" value="<?php echo esc_attr( $cs->getStateName( $address_fields['state'], esc_attr( $address_fields['country'] ) ) ) ; ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-fields">
                    <div class="wpuf-name-field-wrap format-first-last">
                        <div class="wpuf-name-field-first-name">
                            <label class="wpuf-fields wpuf-label"><?php esc_html_e( 'Postal Code/ZIP', 'wp-user-frontend' ); ?></label>
                            <input type="text" class="input" name="zip_code" id="zip_code" value="<?php echo esc_attr( $address_fields['zip_code'] ); ?>" />
                        </div>

                        <div class="wpuf-name-field-last-name">
                            <label class="wpuf-fields wpuf-label"><?php esc_html_e( 'Country', 'wp-user-frontend' ); ?></label>
                            <div class="wpuf-fields">
                                <?php
                                $countries = $cs->countries();
                                ?>
                                <select name="country" id="country">
                                        <option selected value="-1"><?php esc_html_e( 'Select Country', 'wp-user-frontend' ); ?></option>
                                    <?php
                                    foreach ( $countries as $key => $value ) {
                                        if ( $key == $address_fields['country'] ) { ?>
                                            <option selected value="<?php $key; ?>" selected ><?php echo esc_attr( $value ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html($value ); ?></option>
                                        <?php }
                                    } ?>
                                </select>

                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <li class="wpuf-submit">
                <input type="submit" name="update_billing_address" id="wpuf-account-update-billing_address" value="<?php esc_html_e( 'Update Billing Address', 'wp-user-frontend' ); ?>" />
            </li>
        </ul>

    <div class="clear"></div>

    </div>
</form>
