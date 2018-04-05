<?php

$user_id = get_current_user_id();

$address_fields = array();
$countries = array();
$cs = new CountryState();

if ( isset( $_POST['update_billing_address'] )
    && isset( $_POST['add_line_1'] )
    && isset( $_POST['city'] )
    && isset( $_POST['state'] )
    && isset( $_POST['zip_code'] )
    && isset( $_POST['country'] ) ) {
    $address_fields = array(
        'add_line_1'    => $_POST['add_line_1'],
        'add_line_2'    => $_POST['add_line_2'],
        'city'          => $_POST['city'],
        'state'         => strtolower( str_replace( ' ', '', $_POST['state'] ) ),
        'zip_code'      => $_POST['zip_code'],
        'country'       => $_POST['country']
    );
    update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );
    echo '<div class="wpuf-success">' . __( 'Billing address is updated.', 'wpuf' ) . '</div>';
} else {
    if ( metadata_exists( 'user', $user_id, 'wpuf_address_fields') ) {
        $address_fields = get_user_meta( $user_id, 'wpuf_address_fields', true );
        $address_fields = $address_fields;
    } else {
        $address_fields = array_fill_keys(
            array( 'add_line_1', 'add_line_2', 'city', 'state', 'zip_code', 'country' ), '' );
    }
}
?>

<form class="wpuf-form form-label-above" action="" method="post">
    <div class="wpuf-fields">

        <ul class="wpuf-form form-label-above">

            <li>
                <div class="wpuf-label"><?php _e( 'Address Line 1 ', 'wpuf' ); ?><span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="add_line_1" id="add_line_1" value="<?php echo $address_fields['add_line_1']; ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e( 'Address Line 2 ', 'wpuf' ); ?></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="add_line_2" id="add_line_2" value="<?php echo $address_fields['add_line_2']; ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e( 'City', 'wpuf' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="city" id="city" value="<?php echo $address_fields['city']; ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e('State/Province/Region', 'wpuf' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" class="input" name="state" id="state" value="<?php echo $cs->getStateName( $address_fields['state'], $address_fields['country'] ); ?>" />
                </div>
            </li>

            <li>
                <div class="wpuf-fields">
                    <div class="wpuf-name-field-wrap format-first-last">
                        <div class="wpuf-name-field-first-name">
                            <label class="wpuf-fields wpuf-label"><?php _e( 'Postal Code/ZIP', 'wpuf' ); ?></label>
                            <input type="text" class="input" name="zip_code" id="zip_code" value="<?php echo $address_fields['zip_code']; ?>" />
                        </div>

                        <div class="wpuf-name-field-last-name">
                            <label class="wpuf-fields wpuf-label"><?php _e('Country', 'wpuf' ); ?></label>
                            <div class="wpuf-fields">
                                <?php
                                $countries = $cs->countries();
                                ?>
                                <select name="country" id="country">
                                    <?php
                                    foreach ( $countries as $key => $value ) {
                                        if ( $key == $address_fields['country'] ) { ?>
                                            <option selected value="<?php $key; ?>" selected ><?php echo $value; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php }
                                    } ?>
                                </select>

                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <li class="wpuf-submit">
                <input type="submit" name="update_billing_address" id="wpuf-account-update-billing_address" value="<?php _e( 'Update Billing Address', 'wpuf' ); ?>" />
            </li>
        </ul>

    <div class="clear"></div>

    </div>
</form>
