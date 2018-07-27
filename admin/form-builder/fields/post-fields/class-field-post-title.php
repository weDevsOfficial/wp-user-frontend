<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_Post_Title extends WPUF_Form_Field_Text {

    function __construct() {
        $this->name       = __( 'Post Title', 'wp-user-frontend' );
        $this->input_type = 'post_title';
        $this->icon       = 'header';
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props = array(
            'input_type'       => 'text',
            'name'             => $this->get_type(),
            'is_meta'          => 'no',
            'word_restriction' => ''
        );

        return array_merge( $defaults, $props );
    }
}
