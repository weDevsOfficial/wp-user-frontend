<?php

/**
 * Text Field Class
 */
class WeForms_Form_Field_Email extends WeForms_Form_Field_Text {

    function __construct() {
        $this->name       = __( 'Email Address', 'weforms' );
        $this->input_type = 'email_address';
        $this->icon       = 'envelope-o';
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

        // let's not show the email field if user choose to auto populate for logged users
        if ( isset( $field_settings['auto_populate'] ) && $field_settings['auto_populate'] == 'yes' && is_user_logged_in() ) {
            return;
        }

        $value = $field_settings['default'];
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

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
        $default_text_options = $this->get_default_text_option_settings();
        $check_duplicate      = array(
            array(
                'name'          => 'duplicate',
                'title'         => 'No Duplicates',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'no'   => __( 'Unique Values Only', 'weforms' )
                ),
                'default'       => '',
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'Select this option to limit user input to unique values only. This will require that a value entered in a field does not currently exist in the entry database for that field.', 'weforms' ),
            ),
            array(
                'name'          => 'auto_populate',
                'title'         => 'Auto-populate email for logged users',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'yes'   => __( 'Auto-populate Email', 'weforms' )
                ),
                'default'       => '',
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'If a user is logged into the site, this email field will be auto-populated with his email. And form\'s email field will be hidden.', 'weforms' ),
            ),
        );

        return array_merge( $default_options, $default_text_options, $check_duplicate );
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
        $defaults['duplicate'] = '';
        return $defaults;
    }
}
