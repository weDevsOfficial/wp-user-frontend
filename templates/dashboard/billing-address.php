<?php

$user_id = get_current_user_id();

$address_fields = [];
$countries      = [];
$cs             = new WeDevs\Wpuf\Data\Country_State();


if ( isset( $_POST['update_billing_address'] ) ) {
    if ( ! isset( $_POST['wpuf_save_address_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wpuf_save_address_nonce'] ), 'wpuf_address_ajax_action' ) ) {
        return;
    }

    $add_line_1 = isset( $_POST['wpuf_biiling_add_line_1'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_biiling_add_line_1'] ) ) : '';
    $add_line_2 = isset( $_POST['wpuf_biiling_add_line_2'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_biiling_add_line_2'] ) ) : '';
    $city       = isset( $_POST['wpuf_biiling_city'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_biiling_city'] ) ) : '';
    $state      = isset( $_POST['wpuf_biiling_state'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_biiling_state'] ) ) : '';
    $zip_code   = isset( $_POST['wpuf_biiling_zip_code'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_biiling_zip_code'] ) ) : '';
    $country    = isset( $_POST['wpuf_biiling_country'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_biiling_country'] ) ) : '';

    $address_fields = [
        'add_line_1' => $add_line_1,
        'add_line_2' => $add_line_2,
        'city'       => $city,
        'state'      => strtolower( str_replace( ' ', '', $state ) ),
        'zip_code'   => $zip_code,
        'country'    => $country,
    ];
    update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );
    echo '<div class="wpuf-bg-green-50 wpuf-border wpuf-border-green-200 wpuf-text-green-800 wpuf-rounded-lg wpuf-p-4 wpuf-mb-6">' . esc_html( __( 'Billing address is updated.', 'wp-user-frontend' ) ) . '</div>';
} else {
    if ( metadata_exists( 'user', $user_id, 'wpuf_address_fields' ) ) {
        $address_fields = wpuf_get_user_address();
    } else {
        $address_fields = array_fill_keys(
            [ 'add_line_1', 'add_line_2', 'city', 'state', 'zip_code', 'country' ], '' );
    }
}
?>

<form class="wpuf-space-y-6" action="" method="post" id="wpuf-payment-gateway">
    <?php
    wp_nonce_field( 'wpuf_ajax_address' );
    wp_nonce_field( 'wpuf_address_ajax_action', 'wpuf_save_address_nonce' );

    $address_fields = wpuf_map_address_fields( $address_fields );
    $selected['country'] = ! ( empty( $address_fields['country'] ) ) ? $address_fields['country'] : 'US';
    $states = $cs->getStates( $selected['country'] );
    $selected['state'] = ! ( empty( $address_fields['state'] ) ) ? $address_fields['state'] : '';
    $add_line_1 = isset( $address_fields['add_line_1'] ) ? esc_attr( $address_fields['add_line_1'] ) : '';
    $add_line_2 = isset( $address_fields['add_line_2'] ) ? esc_attr( $address_fields['add_line_2'] ) : '';
    $city = isset( $address_fields['city'] ) ? esc_attr( $address_fields['city'] ) : '';
    $zip_code = isset( $address_fields['zip_code'] ) ? esc_attr( $address_fields['zip_code'] ) : '';
    ?>

    <div class="wpuf-grid wpuf-grid-cols-1 md:wpuf-grid-cols-2 wpuf-gap-6">
        <!-- Country -->
        <div>
            <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-2">
                <?php esc_html_e( 'Country', 'wp-user-frontend' ); ?>
                <span class="wpuf-text-red-500">*</span>
            </label>
            <?php
            echo wp_kses( wpuf_select( [
                    'options'          => $cs->countries(),
                    'name'             => 'wpuf_biiling_country',
                    'selected'         => $selected['country'],
                    'show_option_all'  => false,
                    'show_option_none' => false,
                    'id'               => 'wpuf_biiling_country',
                    'class'            => 'wpuf_biiling_country wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 focus:wpuf-border-primary focus:wpuf-ring-primary',
                    'chosen'           => false,
                    'placeholder'      => __( 'Choose a country', 'wp-user-frontend' ),
                    'data'             => [ 'required' => 'yes', 'type' => 'select' ],
                ]
            ), [
                'select' => [
                    'class'            => [],
                    'name'             => [],
                    'id'               => [],
                    'data-placeholder' => [],
                    'data-required'    => [],
                    'data-type'        => [],
                ],
                'option' => [
                    'value'    => [],
                    'class'    => [],
                    'id'       => [],
                    'selected' => []
                ],
            ] ); ?>
        </div>

        <!-- State -->
        <div>
            <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-2">
                <?php esc_html_e( 'State/Province/Region', 'wp-user-frontend' ); ?>
                <span class="wpuf-text-red-500">*</span>
            </label>
            <?php
            echo wp_kses( wpuf_select( [
                    'options'          => $states,
                    'name'             => 'wpuf_biiling_state',
                    'selected'         => $selected['state'],
                    'show_option_all'  => false,
                    'show_option_none' => false,
                    'id'               => 'wpuf_biiling_state',
                    'class'            => 'wpuf_biiling_state wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 focus:wpuf-border-primary focus:wpuf-ring-primary',
                    'chosen'           => false,
                    'placeholder'      => __( 'Choose a state', 'wp-user-frontend' ),
                    'data'             => [ 'required' => 'yes', 'type' => 'select' ],
                ]
            ), [
                'select' => [
                    'class'            => [],
                    'name'             => [],
                    'id'               => [],
                    'data-placeholder' => [],
                    'data-required'    => [],
                    'data-type'        => [],
                ],
                'option' => [
                    'value'    => [],
                    'class'    => [],
                    'id'       => [],
                    'selected' => []
                ],
            ] ); ?>
        </div>
    </div>

    <!-- Address Line 1 -->
    <div>
        <label for="wpuf_biiling_add_line_1" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-2">
            <?php esc_html_e( 'Address Line 1', 'wp-user-frontend' ); ?>
            <span class="wpuf-text-red-500">*</span>
        </label>
        <input
            data-required="yes"
            data-type="text"
            type="text"
            name="wpuf_biiling_add_line_1"
            id="wpuf_biiling_add_line_1"
            value="<?php echo esc_attr( $add_line_1 ); ?>"
            class="wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 focus:wpuf-border-primary focus:wpuf-ring-primary"
        />
    </div>

    <!-- Address Line 2 -->
    <div>
        <label for="wpuf_biiling_add_line_2" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-2">
            <?php esc_html_e( 'Address Line 2', 'wp-user-frontend' ); ?>
        </label>
        <input
            data-required="no"
            type="text"
            name="wpuf_biiling_add_line_2"
            id="wpuf_biiling_add_line_2"
            data-type="text"
            value="<?php echo esc_attr( $add_line_2 ); ?>"
            class="wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 focus:wpuf-border-primary focus:wpuf-ring-primary"
        />
    </div>

    <div class="wpuf-grid wpuf-grid-cols-1 md:wpuf-grid-cols-2 wpuf-gap-6">
        <!-- City -->
        <div>
            <label for="wpuf_biiling_city" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-2">
                <?php esc_html_e( 'City', 'wp-user-frontend' ); ?>
                <span class="wpuf-text-red-500">*</span>
            </label>
            <input
                data-required="yes"
                type="text"
                name="wpuf_biiling_city"
                id="wpuf_biiling_city"
                data-type="text"
                value="<?php echo esc_attr( $city ); ?>"
                class="wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 focus:wpuf-border-primary focus:wpuf-ring-primary"
            />
        </div>

        <!-- ZIP Code -->
        <div>
            <label for="wpuf_biiling_zip_code" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-2">
                <?php esc_html_e( 'Postal/ZIP Code', 'wp-user-frontend' ); ?>
                <span class="wpuf-text-red-500">*</span>
            </label>
            <input
                data-required="yes"
                type="text"
                name="wpuf_biiling_zip_code"
                id="wpuf_biiling_zip_code"
                data-type="text"
                value="<?php echo esc_attr( $zip_code ); ?>"
                class="wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 focus:wpuf-border-primary focus:wpuf-ring-primary"
            />
        </div>
    </div>

    <!-- Submit Button -->
    <div class="wpuf-flex wpuf-justify-end wpuf-border-t wpuf-border-gray-200 wpuf-pt-6">
        <button
            type="submit"
            name="update_billing_address"
            id="wpuf-account-update-billing_address"
            class="wpuf-edit-profile-btn"
        >
            <?php esc_html_e( 'Update', 'wp-user-frontend' ); ?>
        </button>
    </div>
</form>
