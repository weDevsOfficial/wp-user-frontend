<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_SectionBreak extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Section Break', 'wp-user-frontend' );
        $this->input_type = 'section_break';
        $this->icon       = 'columns';
    }

    /**
     * Render the text field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id ) {

        $field_settings['description'] = isset( $field_settings['description'] ) ? $field_settings['description'] : '';
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <div class="wpuf-section-wrap <?php echo 'section_' . $form_id; ?>">
                <h2 class="wpuf-section-title"><?php echo $field_settings['label']; ?></h2>
                <div class="wpuf-section-details"><?php echo $field_settings['description']; ?></div>
            </div>

        </li>
        <?php
    }

    /**
     * It's a full width block
     *
     * @return boolean
     */
    public function is_full_width() {
        return true;
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $options = array(
            array(
                'name'      => 'label',
                'title'     => __( 'Title', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Title of the section', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'description',
                'title'     => __( 'Description', 'wp-user-frontend' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Some details text about the section', 'wp-user-frontend' ),
            ),
        );

        return $options;
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props = array(
            'input_type'   => 'section_break',
        );

        return array_merge( $defaults, $props );
    }
}
