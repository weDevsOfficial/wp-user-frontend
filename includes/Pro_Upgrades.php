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
        add_filter( 'wpuf_form_fields_groups', [ $this, 'add_pricing_fields_section' ], 15 );
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
            'time_field',
            'file_upload',
            'country_list_field',
            'numeric_text_field',
            'phone_field',
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
            'really_simple_captcha',
            'math_captcha',
            'toc',
            'shortcode',
            'action_hook',
            'ratings',
            'embed',
            'qr_code',
        ];

        return array_merge( $fields, $pro_fields );
    }

    /**
     * Add pricing fields section (PRO preview)
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_pricing_fields_section( $sections ) {
        $pricing_section = [
            'title'  => __( 'Pricing Fields', 'wp-user-frontend' ),
            'id'     => 'pricing-fields',
            'fields' => [
                'pricing_radio',
                'pricing_checkbox',
                'pricing_dropdown',
                'pricing_multiselect',
                'cart_total',
            ],
        ];

        // Insert Pricing Fields after Custom Fields (position 2-3 depending on taxonomies)
        $new_sections = [];
        $inserted = false;

        foreach ( $sections as $section ) {
            $new_sections[] = $section;

            // Insert after Custom Fields section
            if ( isset( $section['id'] ) && $section['id'] === 'custom-fields' && ! $inserted ) {
                $new_sections[] = $pricing_section;
                $inserted = true;
            }
        }

        // If not inserted (Custom Fields not found), append at end before Others
        if ( ! $inserted ) {
            array_splice( $new_sections, -1, 0, [ $pricing_section ] );
        }

        return $new_sections;
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
