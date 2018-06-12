<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_HTML extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Custom HTML', 'wp-user-frontend' );
        $this->input_type = 'custom_html';
        $this->icon       = 'code';
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
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>

            <div class="wpuf-fields <?php echo 'html_' . $form_id; ?><?php echo ' wpuf_'.$field_settings['name'].'_'.$form_id; ?>">
                <?php echo $field_settings['html']; ?>
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
        $settings = array(
            array(
                'name'      => 'html',
                'title'     => __( 'HTML Codes', 'wp-user-frontend' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Paste your HTML codes, WordPress shortcodes will also work here', 'wp-user-frontend' ),
            ),
        );

        return $settings;
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'input_type'  => 'html',
            'html'        => sprintf( '<p>%s</p>', __( 'Some description about this section', 'wp-user-frontend' ) ),
        );

        return array_merge( $defaults, $props );
    }
}
