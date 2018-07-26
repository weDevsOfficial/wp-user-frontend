<?php

class WPUF_Form_Field_Featured_Image extends WPUF_Form_Field_Image{

    public function __construct() {
        $this->name       = __( 'Featured Image', 'wp-user-frontend' );
        $this->input_type = 'featured_image';
        $this->icon       = 'header';
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = parent::get_field_props();
        $props = array(
            'input_type'   => 'image_upload',
            'label'        => $this->get_name(),
            'name'         => $this->get_type(),
            'is_meta'      => 'no',
            'button_label' => __( 'Select Image', 'wp-user-frontend' ),
            'max_size'     => '1024',
            'width'        => ''
        );

        return array_merge( $defaults, $props );
    }
}