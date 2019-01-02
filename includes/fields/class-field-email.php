<?php

/**
 * Email Field Class
 */
class WPUF_Form_Field_Email extends WPUF_Form_Field_Text {

    function __construct() {
        $this->name       = __( 'Email Address', 'wp-user-frontend' );
        $this->input_type = 'email_address';
        $this->icon       = 'envelope-o';
    }

    /**
     * Render the Email field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {


        // let's not show the email field if user choose to auto populate for logged users
        if ( isset( $field_settings['auto_populate'] ) && $field_settings['auto_populate'] == 'yes' && is_user_logged_in() ) {
            return;
        }

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
                    type="email"
                    class="email <?php echo ' wpuf_'.$field_settings['name'].'_'.$form_id; ?>"
                    data-duplicate="<?php echo isset( $field_settings['duplicate'] ) ? $field_settings['duplicate'] : 'no'; ?>"
                    data-required="<?php echo $field_settings['required'] ?>"
                    data-type="email"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                    value="<?php echo esc_attr( $value ) ?>"
                    size="<?php echo esc_attr( $field_settings['size'] ) ?>"
                    autocomplete="email"
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
        $default_options      = $this->get_default_option_settings();
        $default_text_options = $this->get_default_text_option_settings();
        return array_merge( $default_options, $default_text_options);
    }

    /**
     * Prepare entry default, can be replaced through field classes
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {

        if ( isset( $field['auto_populate'] ) && $field['auto_populate'] == 'yes' && is_user_logged_in() ) {

            $user = wp_get_current_user();

            if ( ! empty( $user->user_email ) ) {
                return $user->user_email;
            }
        }

        $value = !empty( $_POST[$field['name']] ) ? $_POST[$field['name']] : '';

        return sanitize_text_field( trim( $value ) );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {

        $defaults = $this->default_attributes();

        $props=array(
            'input_type'        => 'email',
            'size'              => 40,
            'id'                => 0,
            'is_new'            => true,
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
        );

        return array_merge($defaults,$props);

    }
}
