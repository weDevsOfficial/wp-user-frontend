<?php

namespace WeDevs\Wpuf\Traits;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WC_Countries;
use WC_Customer;
use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Data\Country_State;

trait TaxableTrait {
    /**
     * Checks if taxes are enabled
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return bool
     */
    function wpuf_tax_enabled() {
        $tax_enabled = wpuf_get_option( 'enable_tax', 'wpuf_payment_tax', 'on' );
        $value = false;
        if ( $tax_enabled === 'on' ) {
            $value = true;
        }

        return apply_filters( 'wpuf_tax_enabled', $value );
    }

    /**
     * Check if the individual product prices include tax
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return bool $include_tax
     */
    function wpuf_prices_include_tax() {
        $enable_tax    = $this->wpuf_tax_enabled();
        $price_inc_tax = wpuf_get_option( 'prices_include_tax', 'wpuf_payment_tax', 'yes' );
        $value = false;

        if ( $price_inc_tax === 'yes' && $enable_tax ) {
            $value = true;
        }

        return apply_filters( 'wpuf_prices_includes_tax', $value );
    }

    /**
     * Retrieve tax rates
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return array Defined tax rates
     */
    function wpuf_get_tax_rates() {
        $rates = get_option( 'wpuf_tax_rates', [] );
        return apply_filters( 'wpuf_get_tax_rates', $rates );
    }

    /**
     * Base country and states callback
     * @since 4.0.0 moved to TaxableTrait
     *
     * @param array $args Arguments passed by the setting
     * @return void
     */
    function wpuf_base_country_state( $args ) {
        $rates = $this->wpuf_get_tax_rates();

        $cs = new Country_State();

        $states = [];

        ob_start(); ?>
        <p><?php echo $args['desc']; ?></p>
        <table style="width:80%; table-layout:auto;" id="wpuf-base-country-state" class="wp-list-table">
            <thead>
            <tr>
                <th scope="col" class="wpuf_tax_country" style="padding: 10px;"><?php esc_html_e( 'Country', 'wp-user-frontend' ); ?></th>
                <th scope="col" class="wpuf_tax_state" style="padding: 10px;"><?php esc_html_e( 'State / Province', 'wp-user-frontend' ); ?></th>
            </tr>
            </thead>
            <tr>
                <?php
                $selected = get_option( 'wpuf_base_country_state', false );

                if ( ! is_array( $selected ) ) {
                    $selected = [ 'country' => 'US' ];
                } else {
                    $selected['country'] = ! empty( $selected ) && ! empty( $selected['country'] ) ? $selected['country'] : 'US';
                }

                ?>
                <td style="width:40%" class="wpuf_base_country">
                    <?php

                    echo wpuf_select(
                        [
                            'options'          => $cs->countries(),
                            'name'             => 'wpuf_base[country]',
                            'selected'         => $selected['country'],
                            'show_option_all'  => false,
                            'show_option_none' => false,
                            'id'               => 'wpuf-base-country',
                            'class'            => 'wpuf-base-country',
                            'chosen'           => false,
                            'placeholder'      => __( 'Choose a country', 'wp-user-frontend' ),
                        ]
                    );
                    ?>
                </td>
                <td style="width:25%" class="wpuf_base_state">
                    <?php
                    $states = $cs->getStates( $selected['country'] );
                    echo wpuf_select(
                        [
                            'options'          => $states,
                            'name'             => 'wpuf_base[state]',
                            'selected'         => isset( $selected['state'] ) ? $selected['state'] : '',
                            'show_option_all'  => false,
                            'show_option_none' => false,
                            'class'            => 'wpuf-base-state',
                            'chosen'           => false,
                            'placeholder'      => __( 'Choose a state', 'wp-user-frontend' ),
                        ]
                    );
                    ?>
                </td>
            </tr>
        </table>
        <?php
        echo ob_get_clean();
    }

    /**
     * Retrieve base states drop down
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return void
     */
    function wpuf_ajax_get_base_states() {
        $cc = ! empty( $_REQUEST['country'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['country'] ) ) : 'US';
        $cs = new Country_State();
        $countries = $cs->countries();
        $states    = $cs->getStates( $countries[ $cc ] );

        $response = 'nostates';

        if ( ! empty( $states ) ) {
            $selected = get_option( 'wpuf_base_country_state', [] );
            $args = [
                'options'          => $states,
                'name'             => 'wpuf_base[state]',
                'selected'         => $selected['state'],
                'show_option_all'  => false,
                'show_option_none' => false,
                'class'            => 'wpuf-base-state',
                'chosen'           => false,
                'placeholder'      => __( 'Choose a state', 'wp-user-frontend' ),
            ];

            $response = wpuf_select( $args );
        }

        wp_send_json_success( $response );
    }

    /**
     * Tax Rates Callback
     * Renders tax rates table
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @param array $args Arguments passed by the setting
     * @return void
     */
    public function wpuf_tax_rates( $args ) {
        $rates  = $this->wpuf_get_tax_rates();
        $cs     = new Country_State();
        $states = [];

        ob_start();
        ?>
        <p><?php echo $args['desc']; ?></p>
        <table style="width:100%; table-layout:auto;" id="wpuf_tax_rates" class="wp-list-table widefat">
            <thead>
            <tr>
                <th scope="col" class="wpuf_tax_country" style="padding: 10px;"><?php esc_html_e( 'Country', 'wp-user-frontend' ); ?></th>
                <th scope="col" class="wpuf_tax_state" style="padding: 10px;"><?php esc_html_e( 'State / Province', 'wp-user-frontend' ); ?></th>
                <th scope="col" class="wpuf_tax_rate" style="padding: 10px;"><?php esc_html_e( 'Rate', 'wp-user-frontend' ); ?><span alt="f223" class="wpuf-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Regional tax rates: When a customer enters an address on payment page that matches the specified region for this tax rate, the payment tax will adjust automatically. Enter a percentage, such as 5 for 5%.', 'wp-user-frontend' ); ?>"></span></th>
                <th scope="col" style="padding: 10px;"><?php esc_attr_e( 'Remove', 'wp-user-frontend' ); ?></th>
            </tr>
            </thead>
            <?php if ( ! empty( $rates ) ) : ?>
                <?php foreach ( $rates as $key => $rate ) : ?>
                    <tr>
                        <td style="width:35%" class="wpuf_tax_country">
                            <?php
                            $selected = isset( $rate['country'] ) ? $rate['country'] : '';

                            echo wpuf_select(
                                [
                                    'options'          => $cs->countries(),
                                    'name'             => 'wpuf_tax_rates[' . $key . '][country]',
                                    'selected'         => $selected,
                                    'show_option_all'  => false,
                                    'show_option_none' => false,
                                    'class'            => 'wpuf-tax-country',
                                    'chosen'           => false,
                                    'placeholder'      => __( 'Choose a country', 'wp-user-frontend' ),
                                ]
                            );
                            ?>
                        </td>
                        <td style="width:35%" class="wpuf_tax_state">
                            <?php
                            if ( isset( $rate['country'] ) ) {
                                $states    = [ 'country_wide' => 'Country Wide' ];
                                $states    += $cs->getStates( $rate['country'] );
                            }
                            $rate['state'] = isset( $rate['state'] ) ? $rate['state'] : '';
                            if ( ! empty( $states ) ) {
                                echo wpuf_select(
                                    [
                                        'options'          => $states,
                                        'name'             => 'wpuf_tax_rates[' . $key . '][state]',
                                        'selected'         => $rate['state'],
                                        'show_option_all'  => false,
                                        'show_option_none' => false,
                                        'class'            => 'wpuf-tax-state',
                                        'chosen'           => false,
                                        'placeholder'      => __( 'Choose a state', 'wp-user-frontend' ),
                                    ]
                                );
                            } else {
                                echo wpuf_text(
                                    [
                                        'name'  => 'wpuf_tax_rates[0][state]',
                                        $rate['state'],
                                        'value' => ! empty( $rate['state'] ) ? $rate['state'] : '',
                                    ]
                                );
                            }
                            ?>
                        </td>
                        <td style="width:20%" class="wpuf_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="wpuf_tax_rates[<?php echo $key; ?>][rate]" value="<?php echo esc_html( ! empty( $rate['rate'] ) ? $rate['rate'] : 0 ); ?>"/></td>
                        <td style="width:10%"><span class="wpuf_remove_tax_rate button-secondary"><?php esc_html_e( 'Remove Rate', 'wp-user-frontend' ); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td class="wpuf_tax_country">
                        <?php
                        $selected = ! empty( $rate['country'] ) ? $cs->getCountry( $rate['country'] ) : '';
                        echo wpuf_select(
                            [
                                'options'          => $cs->countries(),
                                'name'             => 'wpuf_tax_rates[0][country]',
                                'selected'         => $selected,
                                'show_option_all'  => false,
                                'show_option_none' => false,
                                'class'            => 'wpuf-tax-country',
                                'chosen'           => false,
                                'placeholder'      => __( 'Choose a country', 'wp-user-frontend' ),
                            ]
                        );
                        ?>
                    </td>
                    <td class="wpuf_tax_state">
                        <?php
                        echo wpuf_text(
                            [
                                'name' => 'wpuf_tax_rates[0][state]',
                            ]
                        );
                        ?>
                    </td>
                    <td class="wpuf_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" name="wpuf_tax_rates[0][rate]" value=""/></td>
                    <td><span class="wpuf_remove_tax_rate button-secondary"><?php esc_html_e( 'Remove Rate', 'wp-user-frontend' ); ?></span></td>
                </tr>
            <?php endif; ?>
        </table>
        <p>
            <span class="button-secondary" id="wpuf_add_tax_rate"><?php esc_attr_e( 'Add Tax Rate', 'wp-user-frontend' ); ?></span>
        </p>
        <?php
        echo ob_get_clean();
    }

    /**
     * Get Tax rate of current user
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @param $post_id
     * @return string
     */
    function wpuf_current_tax_rate() {
        $tax_amount = 0;

        if ( ! $this->wpuf_tax_enabled() ) {
            return $tax_amount;
        }

        $user_id     = get_current_user_id();
        $tax_amount  = wpuf_get_option( 'fallback_tax_rate', 'wpuf_payment_tax', 0 );

        if ( metadata_exists( 'user', $user_id, 'wpuf_address_fields' ) ) {
            $address_fields = get_user_meta( $user_id, 'wpuf_address_fields', true );

            if ( array_key_exists( 'billing_country', $address_fields ) ) {
                foreach ( $address_fields as $key => $val ) {
                    unset( $address_fields[ $key ] );
                    $address_fields[ str_replace( 'billing_', '', $key ) ] = $val;
                }
            }
            $base_addr       = get_option( 'wpuf_base_country_state' );
            $billing_country = ! empty( $address_fields['country'] ) ? $address_fields['country'] : '';
            $billing_state   = ! empty( $address_fields['state'] ) ? $address_fields['state'] : '';
            $billing_country = empty( $billing_country ) && $base_addr ? $base_addr['country'] : '';
            $billing_state   = empty( $billing_state ) && $base_addr ? $base_addr['state'] : '';
            $tax_amount      = $this->wpuf_tax_rate_country_state( $billing_country, $billing_state );
        } else {
            if ( class_exists( 'WooCommerce' ) ) {
                $customer_id = get_current_user_id();
                $woo_address = [];
                $rates       = $this->wpuf_get_tax_rates();
                $customer    = new WC_Customer( $customer_id );

                $woo_address = $customer->get_billing();
                unset( $woo_address['email'], $woo_address['tel'], $woo_address['phone'], $woo_address['company'] );

                $countries_obj   = new WC_Countries();
                $countries_array = $countries_obj->get_countries();
                $country_states_array = $countries_obj->get_states();
                $woo_address['state'] = isset( $country_states_array[ $woo_address['country'] ][ $woo_address['state'] ] ) ? $country_states_array[ $woo_address['country'] ][ $woo_address['state'] ] : '';
                $woo_address['state'] = strtolower( str_replace( ' ', '', $woo_address['state'] ) );

                if ( ! empty( $woo_address ) && ! empty( $rates ) ) {
                    foreach ( $rates as $rate ) {
                        $rate_str    = ! empty( $rate['rate'] ) ? $rate['rate'] : '';
                        $state       = ! empty( $rate['state'] ) ? $rate['state'] : '';
                        $woo_state   = ! empty( $woo_address['state'] ) ? $woo_address['state'] : '';
                        $country     = ! empty( $rate['country'] ) ? $rate['country'] : '';
                        $woo_country = ! empty( $woo_address['country'] ) ? $woo_address['country'] : '';

                        if ( '' === $rate_str ) {
                            return $tax_amount;
                        }
                        if ( $state === $woo_state && $country === $woo_country ) {
                            $tax_amount = $rate['rate'];
                            return $tax_amount;
                        }
                    }
                }
            }
        }

        return $tax_amount;
    }

    /**
     * Get Tax rate by country and state
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @param $post_id
     * @return string
     */
    function wpuf_tax_rate_country_state( $country, $state ) {
        $tax_amount = 0;

        $rates = $this->wpuf_get_tax_rates();
        $cs = new Country_State();

        if ( empty( $rates ) ) {
            return $tax_amount;
        }

        foreach ( $rates as $rate ) {
            if ( ! array_key_exists( 'state', $rate ) ) {
                continue;
            }
            if ( $rate['state'] === $state && $rate['country'] === $country ) {
                $tax_amount = ! empty( $rate['rate'] ) ? $rate['rate'] : 0;
            }

            if ( intval( $tax_amount ) === 0 && $rate['country'] === $country && 'country_wide' === $rate['state'] ) {
                $tax_amount = ! empty( $rate['rate'] ) ? $rate['rate'] : 0;
            }
        }

        $tax_enabled = $this->wpuf_tax_enabled();

        if ( $tax_enabled && intval( $tax_amount ) === 0 ) {
            $tax_amount = wpuf_get_option( 'fallback_tax_rate', 'wpuf_payment_tax', 0 );
        }

        return $tax_amount;
    }

    /**
     * Get billing amount with tax
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @param $post_id
     * @return string
     */
    function wpuf_amount_with_tax( $billing_amount ) {
        global $current_user;

        $tax_enabled = $this->wpuf_tax_enabled();

        if ( $tax_enabled ) {
            $tax_amount = $this->wpuf_current_tax_rate();
            $billing_amount = $billing_amount + ( ( $billing_amount * $tax_amount ) / 100 );
        }

        return (float) $billing_amount;
    }

    /**
     * Recalculate taxes
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return void|bool
     */
    function wpuf_calculate_taxes( $post_data ) {
        if ( ! $this->wpuf_tax_enabled() ) {
            return false;
        }

        global $current_user;
        $tax_amount = 0;
        $billing_amount = 0;
        $user_id = '';

        if ( isset( $post_data['type'] ) && isset( $post_data['id'] ) ) {
            if ( 'pack' === $post_data['type'] ) {
                $pack           = ( new Subscription() )->get_subscription( $post_data['id'] );
                $billing_amount = $pack->meta_value['billing_amount'];
                $user_id        = $current_user->ID;
            } elseif ( 'post' === $post_data['type'] ) {
                $form              = new Form( get_post_meta( $post_data['id'], '_wpuf_form_id', true ) );
                $fallback_cost     = $form->get_subs_fallback_cost();
                $fallback_enabled  = $form->is_enabled_fallback_cost();
                $pay_per_post_cost = (float) $form->get_pay_per_post_cost();
                $current_user_id      = wpuf_get_user();

                if ( $current_user_id->subscription()->current_pack_id() && $fallback_enabled ) {
                    $billing_amount = $fallback_cost;
                } else {
                    $billing_amount = $pay_per_post_cost;
                }

                $postdata = get_post( $post_data['id'] );
                $user_id  = $postdata->post_author;
            }
        }

        $tax_amount = $this->wpuf_tax_rate_country_state( $post_data['billing_country'], $post_data['billing_state'] );
        $tax_rate = $tax_amount . '%';
        $billing_amount = (float) $billing_amount + ( ( $billing_amount * $tax_amount ) / 100 );
        $billing_amount = wpuf_format_price( $billing_amount );

        $response = [
            'tax'    => html_entity_decode( $tax_rate, ENT_COMPAT, 'UTF-8' ),
            'cost'   => html_entity_decode( $billing_amount, ENT_COMPAT, 'UTF-8' ),
        ];

        echo json_encode( $response );
        die();
    }

    /**
     * Save taxes options
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return void
     */
    function wpuf_save_tax_options() {
        if ( isset( $_REQUEST['option_page'] ) && $_REQUEST['option_page'] === 'wpuf_payment_tax' ) {
            if ( ! is_admin() ) {
                return;
            }
            //phpcs:disable
            $base_state = ! empty( $_REQUEST['wpuf_base'] ) && is_array( $_REQUEST['wpuf_base'] ) ? array_map( 'sanitize_text_field', $_REQUEST['wpuf_base'] ) : '';
            $tax_rates  = ! empty( $_REQUEST['wpuf_tax_rates'] ) && is_array( $_REQUEST['wpuf_tax_rates'] ) ? wpuf_recursive_sanitize_text_field( $_REQUEST['wpuf_tax_rates'] ) : '';
            //phpcs:enable
            update_option( 'wpuf_base_country_state', $base_state );
            update_option( 'wpuf_tax_rates', $tax_rates );
        }
    }

    function wpuf_render_tax_field() {
        $tax_rate = $this->wpuf_current_tax_rate() . '%';
        if ( $this->wpuf_tax_enabled() ) {
            ?>
            <div><?php esc_attr_e( 'Tax', 'wp-user-frontend' ); ?>: <strong><span id="wpuf_pay_page_tax"><?php echo $tax_rate; ?></strong></span></div>
            <?php
        }
    }

    /**
     * Retrieve a states drop down
     *
     * @since 4.0.0 moved to TaxableTrait
     *
     * @return void
     */
    function wpuf_tax_get_states_field() {
        $cs        = new Country_State();
        $countries = $cs->countries();
        $country   = isset( $_REQUEST['country'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['country'] ) ) : '';
        $states    = [ 'country_wide' => 'Country Wide' ];
        $states    += $cs->getStates( $countries[ $country ] );

        if ( ! empty( $states ) ) {
            $args = [
                'name'             => isset( $_REQUEST['field_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['field_name'] ) ) : '',
                'id'               => isset( $_REQUEST['field_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['field_name'] ) ) : '',
                'class'            => isset( $_REQUEST['field_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['field_name'] ) ) : '',
                'options'          => $states,
                'show_option_all'  => false,
                'show_option_none' => false,
            ];

            $response = wpuf_select( $args );
        } else {
            $response = 'nostates';
        }

        echo $response;

        wp_die();
    }
}
