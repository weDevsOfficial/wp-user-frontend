<?php

namespace WeDevs\Wpuf\Fields;

/**
 * Turnstile Field Class
 */
class Form_Field_Cloudflare_Turnstile extends Field_Contract {
    public function __construct() {
        $this->name       = __( 'Cloudflare Turnstile', 'wp-user-frontend' );
        $this->input_type = 'cloudflare_turnstile';
        $this->icon       = 'cloud';

        wp_enqueue_script( 'wpuf-turnstile' );
    }

    /**
     * Render the field in frontend
     *
     * @since 4.0.13
     *
     * @param array $field_settings
     * @param int   $form_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $turnstile = wpuf_get_option( 'login_form_turnstile', 'wpuf_profile', 'off' );
        $site_key  = wpuf_get_option( 'turnstile_site_key', 'wpuf_general', '' );
        $theme     = ! empty( $field_settings['turnstile_theme'] ) ? $field_settings['turnstile_theme'] : 'light';
        $size      = ! empty( $field_settings['turnstile_size'] ) ? $field_settings['turnstile_size'] : 'normal';
        $action    = ! empty( $field_settings['turnstile_type'] ) ? $field_settings['turnstile_type'] : 'non-interactive';

        if ( 'off' === $turnstile || empty( $site_key ) ) {
            return;
        }

        $action = 'non_interactive' === $action ? 'non-interactive' : $action;
        ?>

        <div
            <?php if ( 'invisible' === $action ) { ?>
                style="display: none;"
            <?php } ?>
            id="wpuf-turnstile"
            class="wpuf-turnstile"></div>

        <script>
            window.onloadTurnstileCallback = function () {
                turnstile.render("#wpuf-turnstile", {
                    sitekey: "<?php echo esc_js( $site_key ); ?>",
                    theme:"<?php echo esc_js( $theme ); ?>",
                    size:"<?php echo esc_js( $size ); ?>"
                });
            };
        </script>

        <?php
    }

    /**
     * Custom validator
     *
     * @since 4.0.13
     *
     * @return array
     */
    public function get_validator() {
        return [
            'callback'      => 'has_turnstile_api_keys',
            'button_class'  => 'button-faded',
            'msg_title'     => __( 'Site key and Secret key', 'wp-user-frontend' ),
            'msg'           => sprintf(
                // translators: %s: settings url
                __( 'You need to set Site key and Secret key in <a href="%1$s" target="_blank">Settings</a> in order to use "Cloudflare Turnstile" field. <a href="%2$s" target="_blank">Click here to get the these key</a>.', 'wp-user-frontend' ),
                admin_url( 'admin.php?page=wpuf-settings' ),
                'https://developers.cloudflare.com/turnstile/'
            ),
        ];
    }

    /**
     * Get field options setting
     *
     * @since 4.0.13
     *
     * @return array
     */
    public function get_options_settings() {
        $settings = [
            [
                'name'          => 'label',
                'title'         => __( 'Title', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Title of the section', 'wp-user-frontend' ),
            ],
            [
                'name'          => 'turnstile_theme',
                'title'         => 'Turnstile Theme',
                'type'          => 'radio',
                'options'       => [
                    'light' => __( 'Light', 'wp-user-frontend' ),
                    'dark'  => __( 'Dark', 'wp-user-frontend' ),
                ],
                'default'       => 'light',
                'section'       => 'basic',
                'priority'      => 12,
                'help_text'     => __( 'Select turnstile theme', 'wp-user-frontend' ),
            ],
            [
                'name'          => 'turnstile_size',
                'title'         => 'Turnstile Size',
                'type'          => 'radio',
                'options'       => [
                    'normal'   => __( 'Normal [Width: 300px, Height: 65px]', 'wp-user-frontend' ),
                    'flexible' => __( 'Flexible [Width: 100% (min: 300px), Height: 65px]', 'wp-user-frontend' ),
                    'compact'  => __( 'Compact [Width: 150px, Height: 140px]', 'wp-user-frontend' ),
                ],
                'default'       => 'normal',
                'section'       => 'basic',
                'priority'      => 13,
                'help_text'     => __( 'Select turnstile size', 'wp-user-frontend' ),
            ],
            [
                'name'      => 'turnstile_type',
                'title'     => 'Turnstile type',
                'type'      => 'radio',
                'options'   => [
                    'managed'         => __( 'Managed (recommended)', 'wp-user-frontend' ),
                    'non_interactive' => __( 'Non-Interactive', 'wp-user-frontend' ),
                    'invisible'       => __( 'Invisible', 'wp-user-frontend' ),
                ],
                'default'   => 'managed',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'Select turnstile type', 'wp-user-frontend' ),
            ],
        ];

        return apply_filters( 'wpuf_turnstile_field_option_settings', $settings );
    }

    /**
     * Get the field props
     *
     * @since 4.0.13
     *
     * @return array
     */
    public function get_field_props() {

        $props = [
            'input_type'      => 'cloudflare_turnstile',
            'template'        => $this->get_type(),
            'label'           => '',
            'name'            => '',
            'turnstile_type'  => 'managed',
            'turnstile_theme' => 'light',
            'turnstile_size'  => 'normal',
            'is_new'          => true,
            'is_meta'         => 'yes',
            'id'              => 0,
            'wpuf_cond'       => null,
        ];

        return $props;
    }
}
