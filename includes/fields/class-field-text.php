<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_Text extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Text', 'wp-user-frontend' );
        $this->input_type = 'text_field';
        $this->icon       = 'text-width';
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
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null) {


        if ( isset ( $post_id ) &&  $post_id != '0' ) {
            if ( $this->is_meta( $field_settings ) ) {
                $value = $this->get_meta( $post_id, $field_settings['name'], $type );
            }

        }  else {
            $value = $field_settings['default'];
        }

        $this->field_print_label($field_settings, $form_id );

    ?>

            <div class="wpuf-fields">
                <input
                    class="textfield <?php echo 'wpuf_' . $field_settings['name'] . '_' . $form_id; ?>"
                    id="<?php echo $field_settings['name'] . '_' . $form_id; ?>"
                    type="text"
                    data-required="<?php echo $field_settings['required'] ?>"
                    data-type="text" name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ) ?>"
                    size="<?php echo esc_attr( $field_settings['size'] ) ?>"
                />

                <span class="wpuf-wordlimit-message wpuf-help"></span>
                <?php $this->help_text( $field_settings ); ?>
            </div>

            <?php

            if ( isset( $field_settings['word_restriction'] ) && $field_settings['word_restriction'] ) {
                $this->check_word_restriction_func(
                    $field_settings['word_restriction'],
                    'no',
                    $field_settings['name'] . '_' . $form_id
                );
            }

            $mask_option = isset( $field_settings['mask_options'] ) ? $field_settings['mask_options'] : '';

            if ( $mask_option ) {

            ?>
                <script>
                    jQuery(document).ready(function($) {
                        var text_field = $( "input[name*=<?php echo esc_attr( $field_settings['name'] ); ?>]" );
                        switch ( '<?php echo $mask_option; ?>' ) {
                            case 'us_phone':
                                text_field.mask('(999) 999-9999');
                                break;
                            case 'date':
                                text_field.mask('99/99/9999');
                                break;
                            case 'tax_id':
                                text_field.mask('99-9999999');
                                break;
                            case 'ssn':
                                text_field.mask('999-99-9999');
                                break;
                            case 'zip':
                                text_field.mask('99999');
                                break;
                            default:
                                break;
                        }
                    });
                </script>

            <?php } ?>

        </li>

        <?php $this->after_field_print_label();
    }

    /**
     * Get field options setting
     *
     * @return array
    */

    public function get_options_settings() {

        $default_options      = $this->get_default_option_settings();

        $default_text_options = $this->get_default_text_option_settings( true );

        $text_options = array_merge( $default_options, $default_text_options);

        return apply_filters( 'wpuf_text_field_option_settings', $text_options );
    }

    /**
     * Get the field props
     *
     * @return array
    */


    public function get_field_props() {

        $defaults = $this->default_attributes();

        $props    = array(
            'input_type'       => 'text',
            'label'             => __( 'Text', 'wp-user-frontend' ),
            'is_meta'           => 'yes',
            'size'              => 40,
            'id'                => 0,
            'is_new'            => true,
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
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
       $value = isset( $_POST[$field['name']] ) ? $_POST[$field['name']] : '';
       // return sanitize_text_field( trim( $_POST[$field['name']] ) );
       return sanitize_text_field( $value );

    }
}
