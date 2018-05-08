<?php

/**
 * Pro fields wrapper class
 */
class WPUF_Form_Field_Pro extends WPUF_Field_Contract {

    /**
     * Render the text field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id ) {
        echo __( 'This is a premium field. You need to upgrade.', 'wpuf' );
    }

    /**
     * Check if it's a pro feature
     *
     * @return boolean
     */
    public function is_pro() {
        return true;
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        return __return_empty_array();
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        return __return_empty_array();
    }
}
