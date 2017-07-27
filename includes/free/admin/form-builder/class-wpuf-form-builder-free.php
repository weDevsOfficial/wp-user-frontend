<?php
/**
 * Form Builder framework
 */
class WPUF_Admin_Form_Builder_Free {

    /**
     * Class construction
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'wpuf-form-builder-field-settings', array( $this, 'add_field_settings' ) );
        add_filter( 'wpuf-form-builder-fields-common-properties', array( $this, 'add_fields_common_properties' ) );
        add_filter( 'wpuf-form-builder-fields-custom-fields', array( $this, 'add_custom_fields' ) );
        add_filter( 'wpuf-form-builder-fields-others-fields', array( $this, 'add_others_fields' ) );
    }

    /**
     * Field settings hook
     *
     * @since 2.5
     *
     * @param array $settings
     *
     * @return array
     */
    public function add_field_settings( $settings ) {
        require_once WPUF_ROOT . '/includes/free/admin/form-builder/class-wpuf-form-builder-field-settings-free.php';

        return array_merge( $settings, WPUF_Form_Builder_Field_Settings_Free::get_field_settings() );
    }

    /**
     * Add common properties
     *
     * @since 2.5
     *
     * @param array $common_properties
     */
    public function add_fields_common_properties( $common_properties ) {
        require_once WPUF_ROOT . '/includes/free/admin/form-builder/class-wpuf-form-builder-field-settings-free.php';

        array_push( $common_properties, WPUF_Form_Builder_Field_Settings_Free::get_field_wpuf_cond() );
        return $common_properties;
    }

    /**
     * Add fields in Custom Fields
     *
     * @since 2.5
     *
     * @param array $fields
     *
     * @return void
     */
    public function add_custom_fields( $fields ) {
        return array_merge( $fields, array(
            'repeat_field', 'date_field', 'file_upload', 'country_list_field',
            'numeric_text_field', 'address_field', 'step_start', 'google_map'
        ) );
    }

    /**
     * Add fields in Others Fields
     *
     * @since 2.5
     *
     * @param array $fields
     *
     * @return void
     */
    public function add_others_fields( $fields ) {
        return array_merge( $fields, array(
            'shortcode', 'really_simple_captcha', 'action_hook', 'toc', 'ratings'
        ) );
    }

}
