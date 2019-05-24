<?php

/**
 * Url Field Class
 */
class WPUF_Form_Field_URL extends WPUF_Form_Field_Text {

    function __construct() {
        $this->name       = __( 'Website URL', 'wp-user-frontend' );
        $this->input_type = 'website_url';
        $this->icon       = 'link';
    }

    /**
     * Render the URL field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null) {

        if ( isset( $post_id ) &&  $post_id != '0' ) {

            if ( $this->is_meta( $field_settings ) ) {
                $value = $this->get_meta( $post_id, $field_settings['name'], $type );
            }

        } else {

            $value = $field_settings['default'];
        }

        $this->field_print_label($field_settings, $form_id );

    ?>
            <div class="wpuf-fields">
                <input
                    id="<?php echo $field_settings['name'] . '_' . $form_id; ?>"
                    type="url" class="url <?php echo ' wpuf_'.$field_settings['name'].'_'.$form_id; ?>"
                    data-required="<?php echo $field_settings['required'] ?>"
                    data-type="text"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $field_settings['size'] ) ?>"
                    autocomplete="url"
                />
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
        $default_options = $this->get_default_option_settings();
        $settings        = $this->get_default_text_option_settings( false ); // word_restriction = false

        $settings[] =  array(
            'name'      => 'open_window',
            'title'     => __( 'Open in : ', 'wp-user-frontend' ),
            'type'      => 'radio',
            'options'   => array(
                'same'   => __( 'Same Window', 'wp-user-frontend' ),
                'new'    => __( 'New Window', 'wp-user-frontend' ),
            ),
            'section'   => 'basic',
            'default'   => 'same',
            'inline'    => true,
            'priority'  => 32,
            'help_text' => __( 'Choose whether the link will open in new tab or same window', 'wp-user-frontend' ),
        );
        return array_merge( $default_options, $settings);
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {

        $defaults = $this->default_attributes();

        $props=array(
            'input_type'        => 'url',
            'is_meta'           => 'yes',
            'width'             => 'large',
            'open_window'       => 'same',
            'size'              => 40,
            'id'                => 0,
            'is_new'            => true,
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
        );

        return  array_merge($defaults,$props) ;

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
