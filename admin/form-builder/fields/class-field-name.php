<?php

/**
 * Text Field Class
 */
class WPUF_Form_Field_Name extends WPUF_Field_Contract {

    function __construct() {
        $this->name       = __( 'Name', 'wpuf' );
        $this->input_type = 'name_field';
        $this->icon       = 'user';
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


        // let's not show the name field if user choose to auto populate for logged users
        if ( isset( $field_settings['auto_populate'] ) && $field_settings['auto_populate'] == 'yes' && is_user_logged_in() ) {
            return;
        }

        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <div class="wpuf-fields">
                <div class="wpuf-name-field-wrap format-<?php echo $field_settings['format']; ?>">
                    <div class="wpuf-name-field-first-name">
                        <input
                            name="<?php echo $field_settings['name'] ?>[first]"
                            type="text"
                            placeholder="<?php echo esc_attr( $field_settings['first_name']['placeholder'] ); ?>"
                            value="<?php echo esc_attr( $field_settings['first_name']['default'] ); ?>"
                            size="40"
                            data-required="<?php echo $field_settings['required'] ?>"
                            data-type="text"
                            class="textfield wpuf_<?php echo $field_settings['name']; ?>_<?php echo $form_id; ?>"
                            autocomplete="given-name"
                        >

                        <?php if ( ! $field_settings['hide_subs'] ) : ?>
                            <label class="wpuf-form-sub-label"><?php _e( 'First', 'wpuf' ); ?></label>
                        <?php endif; ?>
                    </div>

                    <?php if ( $field_settings['format'] != 'first-last' ) : ?>
                        <div class="wpuf-name-field-middle-name">
                            <input
                                name="<?php echo $field_settings['name'] ?>[middle]"
                                type="text" class="textfield"
                                placeholder="<?php echo esc_attr( $field_settings['middle_name']['placeholder'] ); ?>"
                                value="<?php echo esc_attr( $field_settings['middle_name']['default'] ); ?>"
                                size="40"
                                autocomplete="additional-name"
                            >

                            <?php if ( ! $field_settings['hide_subs'] ) : ?>
                                <label class="wpuf-form-sub-label"><?php _e( 'Middle', 'wpuf' ); ?></label>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="<?php echo $field_settings['name'] ?>[middle]" value="">
                    <?php endif; ?>

                    <div class="wpuf-name-field-last-name">
                        <input
                            name="<?php echo $field_settings['name'] ?>[last]"
                            type="text" class="textfield"
                            placeholder="<?php echo esc_attr( $field_settings['last_name']['placeholder'] ); ?>"
                            value="<?php echo esc_attr( $field_settings['last_name']['default'] ); ?>"
                            size="40"
                            autocomplete="family-name"
                        >
                        <?php if ( ! $field_settings['hide_subs'] ) : ?>
                            <label class="wpuf-form-sub-label"><?php _e( 'Last', 'wpuf' ); ?></label>
                        <?php endif; ?>
                    </div>
                </div>
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
        $default_options = $this->get_default_option_settings( true, array( 'width' ) );

        $name_settings = array(
            array(
                'name'      => 'format',
                'title'     => __( 'Format', 'wpuf' ),
                'type'      => 'radio',
                'options'   => array(
                    'first-last'        => __( 'First and Last name', 'wpuf' ),
                    'first-middle-last' => __( 'First, Middle and Last name', 'wpuf' )
                ),
                'selected'  => 'first-last',
                'section'   => 'advanced',
                'priority'  => 20,
                'help_text' => __( 'Select format to use for the name field', 'wpuf' ),
            ),
            array(
                'name'          => 'auto_populate',
                'title'         => 'Auto-populate name for logged users',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'yes'   => __( 'Auto-populate Name', 'wpuf' )
                ),
                'default'       => '',
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'If a user is logged into the site, this name field will be auto-populated with his first-last/display name. And form\'s name field will be hidden.', 'wpuf' ),
            ),
            array(
                'name'      => 'sub-labels',
                'title'     => __( 'Label', 'wpuf' ),
                'type'      => 'name',
                'section'   => 'advanced',
                'priority'  => 21,
                'help_text' => __( 'Select format to use for the name field', 'wpuf' ),
            ),
            array(
                'name'          => 'hide_subs',
                'title'         => '',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'true'   => __( 'Hide Sub Labels', 'wpuf' )
                ),
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => '',
            ),
            array(
                'name'          => 'inline',
                'title'         => __( 'Show in inline list', 'wpuf' ),
                'type'          => 'radio',
                'options'       => array(
                    'yes'   => __( 'Yes', 'wpuf' ),
                    'no'    => __( 'No', 'wpuf' ),
                ),
                'default'       => 'no',
                'inline'        => true,
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'Show this option in an inline list', 'wpuf' ),
            )
        );

        return array_merge( $default_options, $name_settings );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'format'     => 'first-last',
            'first_name' => array(
                'placeholder' => '',
                'default'     => '',
                'sub'         => __( 'First', 'wpuf' )
            ),
            'middle_name' => array(
                'placeholder' => '',
                'default'     => '',
                'sub'         => __( 'Middle', 'wpuf' )
            ),
            'last_name' => array(
                'placeholder' => '',
                'default'     => '',
                'sub'         => __( 'Last', 'wpuf' )
            ),
            'inline'   => 'yes',
            'hide_subs'        => false,
        );

        return array_merge( $defaults, $props );
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

            if ( ! empty( $user->ID ) ) {

                if ( $user->first_name || $user->last_name ) {

                    $name = array();
                    $name[] = $user->first_name;
                    $name[] = $user->last_name;

                    return implode( WPUF::$field_separator, $name );
                } else {

                    return $user->display_name;
                }

            }
        }

        $value = !empty( $_POST[$field['name']] ) ? $_POST[$field['name']] : '';

        if ( is_array( $value ) ) {

            $entry_value = implode( WPUF::$field_separator, $_POST[$field['name']] );

        } else {
            $entry_value = trim( $value  );
        }

        return $entry_value;
    }

}
