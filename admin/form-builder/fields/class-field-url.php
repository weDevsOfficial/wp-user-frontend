<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_URL extends WPUF_Form_Field_Text {

    function __construct() {
        $this->name       = __( 'Website URL', 'wpuf' );
        $this->input_type = 'website_url';
        $this->icon       = 'link';
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
        $value = $field_settings['default'];
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <div class="wpuf-fields">
                <input
                    id="<?php echo $field_settings['name'] . '_' . $form_id; ?>"
                    type="url" class="url <?php echo ' wpuf_'.$field_settings['name'].'_'.$form_id; ?>"
                    data-duplicate="<?php echo $field_settings['duplicate'] ? $field_settings['duplicate'] : 'no'; ?>"
                    data-required="<?php echo $field_settings['required'] ?>"
                    data-type="text"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $field_settings['size'] ) ?>"
                    autocomplete="url"
                />
                <?php $this->help_text( $field_settings ); ?>
            </div>

        </li>
        <?php
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings();
        $default_text_options = $this->get_default_text_option_settings( false ); // word_restriction = false
        $check_duplicate      = array(
            array(
                'name'          => 'duplicate',
                'title'         => 'No Duplicates',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'no'   => __( 'Unique Values Only', 'wpuf' )
                ),
                'default'       => '',
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'Select this option to limit user input to unique values only. This will require that a value entered in a field does not currently exist in the entry database for that field.', 'wpuf' ),
            )
        );
        return array_merge( $default_options, $default_text_options, $check_duplicate );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $defaults['duplicate'] = '';
        return $defaults;
    }


    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {
       return esc_url( trim( $_POST[$field['name']] ) );
    }
}