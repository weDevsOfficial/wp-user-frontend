<?php

namespace WeDevs\Wpuf;

class Pro_Upgrades {

    /**
     * Initialize
     */
    public function __construct() {
        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            return;
        }

        // form fields
        add_filter( 'wpuf_field_get_js_settings', [ $this, 'add_conditional_field_prompt' ] );
        add_filter( 'wpuf_form_fields', [ $this, 'register_pro_fields' ] );
        add_filter( 'wpuf_form_fields_custom_fields', [ $this, 'add_to_custom_fields' ] );
        add_filter( 'wpuf_form_fields_others_fields', [ $this, 'add_to_others_fields' ] );
    }

    /**
     * Register pro fields
     *
     * @param array $fields
     *
     * @return array
     */
    public function register_pro_fields( $fields ) {
        wpuf()->container['pro_fields'] = new Fields\Form_Pro_Upgrade_Fields();

        $preview_fields = wpuf()->pro_fields->get_fields();

        return array_merge( $fields, $preview_fields );
    }

    /**
     * Register fields to custom field section
     *
     * @param array $fields
     */
    public function add_to_custom_fields( $fields ) {
        $pro_fields = [
            'repeat_field',
            'date_field',
            'file_upload',
            'country_list_field',
            'numeric_text_field',
            'address_field',
            'google_map',
            'step_start',
        ];

        return array_merge( $fields, $pro_fields );
    }

    /**
     * Register fields to others field section
     *
     * @param array $fields
     */
    public function add_to_others_fields( $fields ) {
        $pro_fields = [
            'shortcode',
            'action_hook',
            'toc',
            'ratings',
            'embed',
            'really_simple_captcha',
            'math_captcha',
            'qr_code',
        ];

        return array_merge( $fields, $pro_fields );
    }

    /**
     * Add conditional logic prompt
     *
     * @param array $settings
     */
    public function add_conditional_field_prompt( $settings ) {
        $settings['settings'][] = [
            'name'           => 'wpuf_cond',
            'title'          => __( 'Conditional Logic', 'wp-user-frontend' ),
            'type'           => 'option-pro-feature-alert',
            'section'        => 'advanced',
            'priority'       => 30,
            'help_text'      => '',
            'is_pro_feature' => true,
        ];

        return $settings;
    }
}
