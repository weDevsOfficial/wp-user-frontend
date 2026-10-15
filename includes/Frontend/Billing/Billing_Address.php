<?php
/**
 * Billing address fields of the checkout
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Billing;

use WeDevs\Wpuf\Data\Country_State;

/**
 * The billing address fields (the "Billing Address" Customizer options):
 * printed in the checkout's payment form, read by billing-address.js, which
 * saves them and recalculates the tax over `wpuf/v1/account/billing-address`
 * and copies them into the payment POST (`billing_address[...]`).
 *
 * @since WPUF_SINCE
 */
class Billing_Address {

    /**
     * The data billing-address.js reads (`window.wpufBillingAddress`).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function script_data() {
        return [
            'rest'        => wpuf_rest_script_data(),
            'fill_notice' => __( 'Some Required Fields are not filled!', 'wp-user-frontend' ),
        ];
    }

    /**
     * Localize a script with the billing data (and the old `ajax_object` name
     * add-ons may still read).
     *
     * @since WPUF_SINCE
     *
     * @param string $handle Script handle
     *
     * @return void
     */
    public function localize( $handle ) {
        $data = $this->script_data();

        wp_localize_script( $handle, 'wpufBillingAddress', $data );
        wp_localize_script( $handle, 'ajax_object', array_merge( $data, [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] ) );
    }

    /**
     * Print the address fields.
     *
     * @since WPUF_SINCE
     *
     * @param bool $in_checkout Inside the checkout's payment form: the fields only, in a div
     *
     * @return void
     */
    public function form( $in_checkout = false ) {
        $address_fields = wpuf_get_user_address();
        $show_address   = wpuf_get_option( 'show_address', 'wpuf_address_options', false );
        $show_country   = wpuf_get_option( 'country', 'wpuf_address_options', false );
        $show_state     = wpuf_get_option( 'state', 'wpuf_address_options', false );
        $show_add1      = wpuf_get_option( 'address_1', 'wpuf_address_options', false );
        $show_add2      = wpuf_get_option( 'address_2', 'wpuf_address_options', false );
        $show_city      = wpuf_get_option( 'city', 'wpuf_address_options', false );
        $show_zip       = wpuf_get_option( 'zip', 'wpuf_address_options', false );

        $required_class = 'bill_required';

        $country_req  = '';
        $country_hide = '';
        $state_req    = '';
        $state_hide   = '';
        $add1_req     = '';
        $add1_hide    = '';
        $add2_req     = '';
        $add2_hide    = '';
        $city_req     = '';
        $city_hide    = '';
        $zip_req      = '';
        $zip_hide     = '';
        $required     = '';

        if ( $show_country == 'hidden' ) {
            $show_state = 'hidden';
        }

        switch ( $show_country ) {
            case 'required':
                $country_required = true;
                break;

            case 'hidden':
                $country_hide = 'display: none;';
				// no break
            default:
                break;
        }

        switch ( $show_state ) {
            case 'required':
                $state_required = true;
                break;

            case 'hidden':
                $state_hide = 'display: none;';
				// no break
            default:
                break;
        }

        switch ( $show_add1 ) {
            case 'required':
                $address1_required = true;
                break;

            case 'hidden':
                $add1_hide = 'display: none;';
				// no break
            default:
                break;
        }

        switch ( $show_add2 ) {
            case 'required':
                $address2_required = true;
                break;

            case 'hidden':
                $add2_hide = 'display: none;';
				// no break
            default:
                break;
        }
        switch ( $show_city ) {
            case 'required':
                $city_required = true;
                break;

            case 'hidden':
                $city_hide = 'display: none;';
				// no break
            default:
                break;
        }
        switch ( $show_zip ) {
            case 'required':
                $zip_required = true;
                break;

            case 'hidden':
                $zip_hide = 'display: none;';
				// no break
            default:
                break;
        }

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
        } elseif ( isset( $_GET['user_id'] ) ) {
            $user_id = absint( wp_unslash( $_GET['user_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only echoed back into the form's hidden field.
        } else {
            return;
        }

        if ( $show_address ) {
            ?>

            <?php
            // Inside the checkout the fields are part of the payment form: a nested
            // <form> is dropped by the browser (and its nonce / user_id collided).
            if ( $in_checkout ) {
                ?>
            <div class="wpuf-form form-label-above" id="wpuf-ajax-address-form">
                <?php
            } else {
                ?>
            <form class="wpuf-form form-label-above" id="wpuf-ajax-address-form" action="" method="post">
                <?php wp_nonce_field( 'wpuf_ajax_address' ); ?>
                <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>">
                <?php
            }
            ?>

                <table id="wpuf-address-country-state" class="wp-list-table widefat">
                    <tr>
                        <td class="<?php echo isset( $country_required ) ? esc_attr( $required_class ) : null; ?>"
                            style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo esc_attr( $country_hide ); ?>">
                            <label><?php esc_html_e( 'Country', 'wp-user-frontend' ); ?><?php echo isset( $country_required ) ? wp_kses( '<span class="required">*</span>', array( 'span' => array() ) ) : null; ?></label>
                            <br>
                            <?php
                            if ( function_exists( 'wpuf_get_tax_rates' ) ) {
                                $rates = wpuf_get_tax_rates();
                            }

                            $cs        = new Country_State();
                            $states    = [];
                            $selected  = [];
                            $base_addr = wp_parse_args(
                                (array) get_option( 'wpuf_base_country_state', [] ), [
									'country' => '',
									'state' => '',
								]
                            );
                            $address_fields = wpuf_map_address_fields( $address_fields );

                            $selected['country'] = ! ( empty( $address_fields['country'] ) ) ? $address_fields['country'] : $base_addr['country'];

                            echo wp_kses(
                                wpuf_select(
                                    [
										'options'          => $cs->countries(),
										'name'             => 'wpuf_biiling_country',
										'selected'         => $selected['country'],
										'show_option_all'  => false,
										'show_option_none' => false,
										'id'               => 'wpuf_biiling_country',
										'class'            => 'wpuf_biiling_country',
										'chosen'           => false,
										'placeholder'      => __( 'Choose a country', 'wp-user-frontend' ),
									]
                                ), [
									'select' => [
										'class'            => [],
										'name'             => [],
										'id'               => [],
										'data-placeholder' => [],
									],
									'option' => [
										'value'    => [],
										'class'    => [],
										'id'       => [],
										'selected' => [],
									],
								]
                            );
                            ?>
                        </td>
                        <td class="<?php echo isset( $state_required ) ? esc_attr( $required_class ) : null; ?>"
                            style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo esc_attr( $state_hide ); ?>">
                            <label><?php esc_html_e( 'State/Province/Region', 'wp-user-frontend' ); ?><?php echo isset( $state_required ) ? wp_kses( '<span class="required">*</span>', array( 'span' => array() ) ) : null; ?></label>
                            <br>
                            <?php
                            $states            = $cs->getStates( $selected['country'] );
                            $selected['state'] = ! ( empty( $address_fields['state'] ) ) ? $address_fields['state'] : $base_addr['state'];
                            echo wp_kses(
                                wpuf_select(
                                    [
										'options'          => $states,
										'name'             => 'wpuf_biiling_state',
										'selected'         => $selected['state'],
										'show_option_all'  => false,
										'show_option_none' => false,
										'id'               => 'wpuf_biiling_state',
										'class'            => 'wpuf_biiling_state',
										'chosen'           => false,
										'placeholder'      => __( 'Choose a state', 'wp-user-frontend' ),
									]
                                ), [
									'select' => [
										'class'            => [],
										'name'             => [],
										'id'               => [],
										'data-placeholder' => [],
									],
									'option' => [
										'value'    => [],
										'class'    => [],
										'id'       => [],
										'selected' => [],
									],
								]
                            );
                            ?>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo esc_attr( $add1_hide ); ?>">
                            <div
                                class="wpuf-label"><?php esc_html_e( 'Address Line 1 ', 'wp-user-frontend' ); ?><?php echo isset( $address1_required ) ? wp_kses( '<span class="required">*</span>', array( 'span' => array() ) ) : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text"
                                        class="input <?php echo isset( $address1_required ) ? esc_attr( $required_class ) : null; ?>"
                                        name="wpuf_biiling_add_line_1"
                                        id="wpuf_biiling_add_line_1"
                                        value="<?php echo esc_attr( $address_fields['add_line_1'] ); ?>"/>
                            </div>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo esc_attr( $add2_hide ); ?>">
                            <div
                                class="wpuf-label"><?php esc_html_e( 'Address Line 2 ', 'wp-user-frontend' ); ?><?php echo isset( $address2_required ) ? wp_kses( '<span class="required">*</span>', array( 'span' => array() ) ) : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text"
                                        class="input <?php echo isset( $address2_required ) ? esc_attr( $required_class ) : null; ?>"
                                        name="wpuf_biiling_add_line_2"
                                        id="wpuf_biiling_add_line_2"
                                        value="<?php echo esc_attr( $address_fields['add_line_2'] ); ?>"/>
                            </div>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo esc_attr( $city_hide ); ?>">
                            <div
                                class="wpuf-label"><?php esc_html_e( 'City', 'wp-user-frontend' ); ?><?php echo isset( $city_required ) ? wp_kses( '<span class="required">*</span>', array( 'span' => array() ) ) : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text"
                                        class="input <?php echo isset( $city_required ) ? esc_attr( $required_class ) : null; ?>"
                                        name="wpuf_biiling_city" id="wpuf_biiling_city"
                                        value="<?php echo esc_attr( $address_fields['city'] ); ?>"/>
                            </div>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo esc_attr( $zip_hide ); ?>">
                            <div
                                class="wpuf-label"><?php esc_html_e( 'Postal Code/ZIP', 'wp-user-frontend' ); ?><?php echo isset( $zip_required ) ? wp_kses( '<span class="required">*</span>', array( 'span' => array() ) ) : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text"
                                        class="input <?php echo isset( $zip_required ) ? esc_attr( $required_class ) : null; ?>"
                                        name="wpuf_biiling_zip_code" id="wpuf_biiling_zip_code"
                                        value="<?php echo esc_attr( $address_fields['zip_code'] ); ?>"/>
                            </div>
                        </td>
                        <?php if ( ! $in_checkout ) { ?>
                        <td class="<?php echo esc_attr( $required ); ?>" class="wpuf-submit" style="display:none;">
                            <input type="submit" class="wpuf-btn" name="submit" id="wpuf-account-update-billing_address"
                                    value="<?php esc_html_e( 'Update Billing Address', 'wp-user-frontend' ); ?>"/>
                        </td>
                        <?php } ?>
                    </tr>

                </table>
                <div class="clear"></div>
            <?php echo $in_checkout ? '</div>' : '</form>'; ?>

            <?php
        }
    }
}
