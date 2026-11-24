<?php

namespace WeDevs\Wpuf\Admin;

/**
 * Customizer_Options class
 *
 * @since 2.8.9
 */
class Customizer_Options {

    /**
     * Class constructor
     */
    public function __construct() {
        // add_filter( 'customize_panel_active', '__return_true', 11 );
        add_action( 'customize_register', [ $this, 'customizer_options' ], 15 );
        add_action( 'customize_save_after', [ $this, 'save_customizer_options' ] );
        add_action( 'wp_head', [ $this, 'maybe_migrate_customizer_options' ] );

        add_filter( 'default_option_wpuf_panel', [ $this, 'maybe_fallback_get_option' ] );
    }

    /**
     * Backwards compatibility for the old Customizer Option Save
     *
     * @since 4.0.0
     *
     * @param  mixed $sections
     *
     * @return mixed
     */
    public function maybe_fallback_get_option( $sections ) {
        // Return if there is something there
        if ( ! empty( $sections ) ) {
            return $sections;
        }

        return get_option( 'wpuf_address_options', [] );
    }

    /**
     * Build the billing address option once on sites that never stored it
     *
     * The option used to be rebuilt on every page view, so existing sites already have it.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function maybe_migrate_customizer_options() {
        if ( false === get_option( 'wpuf_address_options' ) ) {
            $this->save_customizer_options();
        }
    }

    /**
     * Copy the billing address theme mods into the wpuf_address_options option
     *
     * Runs after a Customizer save. Only the mods the active theme has are copied,
     * so a theme without them keeps the stored settings.
     *
     * @return void
     */
    public function save_customizer_options() {
        $theme_mods      = (array) get_theme_mods();
        $address_options = (array) get_option( 'wpuf_address_options', [] );
        $fields = [
            'show_address' => __( 'Show Billing Address', 'wp-user-frontend' ),
            'country'      => __( 'Country', 'wp-user-frontend' ),
            'state'        => __( 'State/Province/Region', 'wp-user-frontend' ),
            'address_1'    => __( 'Address line 1', 'wp-user-frontend' ),
            'address_2'    => __( 'Address line 2', 'wp-user-frontend' ),
            'city'         => __( 'City', 'wp-user-frontend' ),
            'zip'          => __( 'Postal Code/ZIP', 'wp-user-frontend' ),
        ];
        foreach ( $fields as $field => $label ) {
            $settings_name = 'wpuf_address_' . $field . '_settings';

            if ( array_key_exists( $settings_name, $theme_mods ) ) {
                $address_options[ $field ] = $theme_mods[ $settings_name ];
            }
        }
        update_option( 'wpuf_address_options', $address_options );
    }

    public function customizer_options( $wp_customize ) {
        /* Add WPUF Panel to Customizer */
        $wp_customize->add_panel(
            'wpuf_panel', [
                'title'       => __( 'WP User Frontend', 'wp-user-frontend' ),
                'description' => __( 'Customize WPUF Settings', 'wp-user-frontend' ),
                'priority'    => 25,
            ]
        );
        /* WPUF Billing Address Customizer */
        $wp_customize->add_section(
            'wpuf_billing_address', [
                'title'       => __( 'Billing Address', 'wp-user-frontend' ),
                'priority'    => 20,
                'panel'       => 'wpuf_panel',
                'description' => __(
                    'These options let you change the appearance of the billing address.', 'wp-user-frontend'
                ),
            ]
        );
        // Billing Address field controls.
        $fields = [
            'show_address' => __( 'Show Billing Address', 'wp-user-frontend' ),
            'country'      => __( 'Country', 'wp-user-frontend' ),
            'state'        => __( 'State/Province/Region', 'wp-user-frontend' ),
            'address_1'    => __( 'Address line 1', 'wp-user-frontend' ),
            'address_2'    => __( 'Address line 2', 'wp-user-frontend' ),
            'city'         => __( 'City', 'wp-user-frontend' ),
            'zip'          => __( 'Postal Code/ZIP', 'wp-user-frontend' ),
        ];
        foreach ( $fields as $field => $label ) {
            $wp_customize->add_setting(
                'wpuf_address_' . $field . '_settings', [
                    'type'    => 'theme_mod',
                    'section' => 'wpuf_billing_address',
                ]
            );
            if ( $field == 'show_address' ) {
                $wp_customize->add_control(
                    'wpuf_address_' . $field . '_control', [
                        /* Translators: %s field name. */
                        'label'    => sprintf( __( '%s field', 'wp-user-frontend' ), $label ),
                        'section'  => 'wpuf_billing_address',
                        'settings' => 'wpuf_address_' . $field . '_settings',
                        'type'     => 'checkbox',
                    ]
                );
            } else {
                $wp_customize->add_control(
                    'wpuf_address_' . $field . '_control', [
                        /* Translators: %s field name. */
                        'label'    => sprintf( __( '%s field', 'wp-user-frontend' ), $label ),
                        'section'  => 'wpuf_billing_address',
                        'settings' => 'wpuf_address_' . $field . '_settings',
                        'type'     => 'select',
                        'choices'  => [
                            'hidden'   => __( 'Hidden', 'wp-user-frontend' ),
                            'optional' => __( 'Optional', 'wp-user-frontend' ),
                            'required' => __( 'Required', 'wp-user-frontend' ),
                        ],
                    ]
                );
            }
        }
    }
}
