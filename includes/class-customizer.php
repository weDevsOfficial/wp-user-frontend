<?php
/**
 * WPUF_Customizer_Options class
 *
 * @since 2.8.9
 *
 */

class WPUF_Customizer_Options{

    /**
     * Class constructor
     */
    public function __construct() {
        add_action( 'customize_register', array( $this, 'customizer_options' ) );
        add_action( 'wp_head', array( $this, 'save_customizer_options' ), 5 );
    }

    public function save_customizer_options() {
        $address_options = array();

        $fields = array(
            'show_address'  => __( 'Show Billing Address', 'wpuf' ),
            'country'       => __( 'Country', 'wpuf' ),
            'state'         => __( 'State/Province/Region', 'wpuf' ),
            'address_1'     => __( 'Address line 1', 'wpuf' ),
            'address_2'     => __( 'Address line 2', 'wpuf' ),
            'city'          => __( 'City', 'wpuf' ),
            'zip'           => __( 'Postal Code/ZIP', 'wpuf' ),
        );
        foreach( $fields as $field => $label ) {
            $settings_name = 'wpuf_address_' . $field . '_settings';
            $address_options[$field] = get_theme_mod( $settings_name );
        }

        update_option( 'wpuf_address_options', $address_options );

        $info_fields = array(
            'success'  => __( 'Success Color', 'wpuf' ),
            'error'    => __( 'Error Color', 'wpuf' ),
            'message'  => __( 'Message Color', 'wpuf' ),
            'info'     => __( 'Warning COlor', 'wpuf' ),
        );

        $info_options = array();
        foreach( $info_fields as $field => $label ) {
            $info_options[$field] = get_theme_mod( 'wpuf_messages_' . $field . '_settings' );
        }

        ?>
        <style>
            .wpuf-success {
                background-color: <?php echo $info_options['success'] ?> !important;
                border: 1px solid <?php echo $info_options['success'] ?> !important;
            }
            .wpuf-error {
                background-color: <?php echo $info_options['error'] ?> !important;
                border: 1px solid <?php echo $info_options['error'] ?> !important;
            }
            .wpuf-message {
                background: <?php echo $info_options['message'] ?> !important;
                border: 1px solid <?php echo $info_options['message'] ?> !important;
            }
            .wpuf-info {
                background-color: <?php echo $info_options['info'] ?> !important;
                border: 1px solid <?php echo $info_options['info'] ?> !important;
            }
        </style>
        <?php
    }

    public function customizer_options( $wp_customize ) {

        /* Add WPUF Panel to Customizer */

        $wp_customize->add_panel( 'wpuf_panel', array(
            'title'					=> __( 'WP User Frontend', 'wpuf' ),
            'description'			=> __( 'Customize WPUF Settings', 'wpuf' ),
            'priority'				=> 25,
        ) );

        /* WPUF Billing Address Customizer */
        $wp_customize->add_section(
            'wpuf_billing_address',
            array(
                'title'       => __( 'Billing Address', 'wpuf' ),
                'priority'    => 20,
                'panel'       => 'wpuf_panel',
                'description' => __( 'These options let you change the appearance of the billing address.', 'wpuf' ),
            )
        );
        /* WPUF Error/Warning Messages Customizer */
        $wp_customize->add_section(
            'wpuf_customize_messages',
            array(
                'title'       => __( 'Notice Colors', 'wpuf' ),
                'priority'    => 21,
                'panel'       => 'wpuf_panel',
                'description' => __( 'These options let you customize the look of Info Messages like Error, Warning etc..', 'wpuf' ),
            )
        );

        // Billing Address field controls.
        $fields = array(
            'show_address'  => __( 'Show Billing Address', 'wpuf' ),
            'country'       => __( 'Country', 'wpuf' ),
            'state'         => __( 'State/Province/Region', 'wpuf' ),
            'address_1'     => __( 'Address line 1', 'wpuf' ),
            'address_2'     => __( 'Address line 2', 'wpuf' ),
            'city'          => __( 'City', 'wpuf' ),
            'zip'           => __( 'Postal Code/ZIP', 'wpuf' ),
        );
        foreach ( $fields as $field => $label ) {
            $wp_customize->add_setting(
                'wpuf_address_' . $field . '_settings',
                array(
                    'type'       => 'theme_mod',
                    'section'    => 'wpuf_billing_address',
                )
            );
            if ( $field == 'show_address' ) {
                $wp_customize->add_control(
                    'wpuf_address_' . $field . '_control',
                    array(
                        /* Translators: %s field name. */
                        'label'    => sprintf( __( '%s field', 'wpuf' ), $label ),
                        'section'  => 'wpuf_billing_address',
                        'settings' => 'wpuf_address_' . $field . '_settings',
                        'type'     => 'checkbox',
                    )
                );
            } else {
                $wp_customize->add_control(
                    'wpuf_address_' . $field . '_control',
                    array(
                        /* Translators: %s field name. */
                        'label'    => sprintf( __( '%s field', 'wpuf' ), $label ),
                        'section'  => 'wpuf_billing_address',
                        'settings' => 'wpuf_address_' . $field . '_settings',
                        'type'     => 'select',
                        'choices'  => array(
                            'hidden'   => __( 'Hidden', 'wpuf' ),
                            'optional' => __( 'Optional', 'wpuf' ),
                            'required' => __( 'Required', 'wpuf' ),
                        ),
                    )
                );
            }
        }

        // Info messages field controls.
        $info_fields = array(
            'success'  => __( 'Success Background', 'wpuf' ),
            'error'    => __( 'Error Background', 'wpuf' ),
            'message'  => __( 'Message Background', 'wpuf' ),
            'info'     => __( 'Info Background', 'wpuf' ),
        );
        $default_field_bg = array( '#dff0d8',  '#f2dede', '#fcf8e3', '#fef5be' );

        $idx = 0;
        foreach( $info_fields as $field => $label ) {
            $wp_customize->add_setting(
                'wpuf_messages_' . $field . '_settings',
                array(
                    'type'       => 'theme_mod',
                    'default'    => $default_field_bg[$idx++],
                    'section'    => 'wpuf_billing_address',
                    'transport'  => 'refresh',
                )
            );
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpuf_messages_' . $field . '_control', array(
                    /* Translators: %s field name. */
                    'label'    => sprintf( __( '%s field', 'wpuf' ), $label ),
                    'section'  => 'wpuf_customize_messages',
                    'settings' => 'wpuf_messages_' . $field . '_settings',
                )
            ) );
        }

    }
}
