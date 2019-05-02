<?php

/**
 * Checkbox Field Class
 */
class WPUF_Form_Field_Checkbox extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Checkbox', 'wp-user-frontend' );
        $this->input_type = 'checkbox_field';
        $this->icon       = 'check-square-o';
    }

    /**
     * Render the Text field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {

        $selected = !empty( $field_settings['selected'] ) ? $field_settings['selected'] : array();

        if ( isset($post_id) &&  $post_id != '0'  ) {
            if ( $this->is_meta( $field_settings ) ) {
                if ( $value = $this->get_meta( $post_id, $field_settings['name'], $type, true ) ) {
                    if ( is_serialized( $value ) ) {
                        $selected = maybe_unserialize( $value );
                    } elseif ( is_array( $value ) ) {
                        $selected = $value;
                    } else {
                        $selected = array();
                        $selected_options = explode( "|", $value );

                        foreach ($selected_options as $option) {
                            array_push($selected, trim($option));
                        }
                    }
                } else {

                }
            }
        }
        $this->field_print_label($field_settings, $form_id );

    ?>

        <div class="wpuf-fields" data-required="<?php echo $field_settings['required'] ?>" data-type="radio">

            <?php
            if ( $field_settings['options'] && count( $field_settings['options'] ) > 0 ) {

                foreach ($field_settings['options'] as $value => $option) {

                    ?>
                    <label <?php echo $field_settings['inline'] == 'yes' ? 'class="wpuf-checkbox-inline"' : 'class="wpuf-checkbox-block"'; ?>>
                        <input type="checkbox" class="<?php echo 'wpuf_' . $field_settings['name']. '_'. $form_id; ?>" name="<?php echo $field_settings['name']; ?>[]" value="<?php echo esc_attr( $value ); ?>"<?php echo in_array( $value, $selected ) ? ' checked="checked"' : ''; ?> />
                        <?php echo $option; ?>
                    </label>
                    <?php
                }
            }
            ?>

            <?php $this->help_text( $field_settings ); ?>

        </div>

        <?php $this->after_field_print_label();

    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {

        $default_options  = $this->get_default_option_settings();

        $dropdown_options = array(
            $this->get_default_option_dropdown_settings( true ),

            array(
                'name'          => 'inline',
                'title'         => __( 'Show in inline list', 'wp-user-frontend' ),
                'type'          => 'radio',
                'options'       => array(
                    'yes'   => __( 'Yes', 'wp-user-frontend' ),
                    'no'    => __( 'No', 'wp-user-frontend' ),
                ),
                'default'       => 'no',
                'inline'        => true,
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'Show this option in an inline list', 'wp-user-frontend' ),
            ),

        );

        return array_merge( $default_options, $dropdown_options );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {

        $defaults = $this->default_attributes();

        $props    = array(
            'input_type'       => 'checkbox',
            'is_meta'          => 'yes',
            'selected'          => array(),
            'inline'           => 'no',
            'options'          => array( 'Option' => __( 'Option', 'wp-user-frontend' ) ),
            'id'               => 0,
            'is_new'           => true,
            'show_in_post'     => 'yes',
            'hide_field_label' => 'no',
        );

        return array_merge( $defaults, $props );
    }

    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {

        $entry_value  = ( is_array( $_POST[$field['name']] ) && $_POST[$field['name']] ) ? $_POST[$field['name']] : array();

        if ( $entry_value ) {
            $new_val = array();

            foreach ($entry_value as $option_key) {
                $new_val[] = isset( $field['options'][$option_key] ) ? $field['options'][$option_key] : '';
            }

            $entry_value = implode( "|", $new_val );
        } else {
            $entry_value = '';
        }

        return $entry_value;
    }
}
