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
        add_filter( 'wpuf-form-builder-fields-custom-fields', array( $this, 'add_custom_fields' ) );
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

}
