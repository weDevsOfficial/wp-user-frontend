<?php

/**
 * DropDown Field Class
 */
class WPUF_Form_Field_Dropdown extends WPUF_Field_Contract {

    public function __construct() {
        $this->name       = __( 'Dropdown', 'wp-user-frontend' );
        $this->input_type = 'dropdown_field';
        $this->icon       = 'caret-square-o-down';
    }

    /**
     * Render the Dropdown field
     *
     * @param array  $field_settings
     * @param int    $form_id
     * @param string $type
     * @param int    $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        if ( isset( $post_id ) && $post_id != '0' ) {
            $selected = $this->get_meta( $post_id, $field_settings['name'], $type );
        } else {
            $selected = isset( $field_settings['selected'] ) ? $field_settings['selected'] : '';
        }

        $name  = $field_settings['name'];

        $this->field_print_label( $field_settings, $form_id ); ?>

        <div class="wpuf-fields">
            <select
                class="<?php echo 'wpuf_' . esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                id="<?php echo esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                data-type="select">

                <?php if ( !empty( $field_settings['first'] ) ) { ?>
                    <option value="-1"><?php echo esc_html( $field_settings['first'] ); ?></option>
                <?php } ?>

                <?php
                if ( $field_settings['options'] && count( $field_settings['options'] ) > 0 ) {
                    foreach ( $field_settings['options'] as $value => $option ) {
                        $current_select = selected( $selected, $value, false ); ?>
                        <option value="<?php echo esc_attr( $value ); ?>"<?php echo esc_attr( $current_select ); ?>><?php echo esc_html( $option ); ?></option>
                        <?php
                    }
                } ?>
            </select>
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
        $dropdown_options = [
            $this->get_default_option_dropdown_settings(),

            [
                'name'          => 'first',
                'title'         => __( 'Select Text', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 13,
                'help_text'     => __( "First element of the select dropdown. Leave this empty if you don't want to show this field", 'wp-user-frontend' ),
            ],
        ];

        return array_merge( $default_options, $dropdown_options );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = [
            'input_type'       => 'select',
            'label'            => __( 'Dropdown', 'wp-user-frontend' ),
            'is_meta'          => 'yes',
            'options'          => [ 'Option' => __( 'Option', 'wp-user-frontend' ) ],
            'first'            => __( '- select -', 'wp-user-frontend' ),
            'id'               => 0,
            'is_new'           => true,
            'show_in_post'     => 'yes',
            'hide_field_label' => 'no',
        ];

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
        check_ajax_referer( 'wpuf_form_add' );

        $val = isset( $_POST[$field['name']] ) ? sanitize_text_field( wp_unslash( $_POST[$field['name']] ) ) : '';

        return isset( $field['options'][$val] ) ? $field['options'][$val] : '';
    }
}
