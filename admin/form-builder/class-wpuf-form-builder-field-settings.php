<?php
/**
 * Field settings properties
 */
class WPUF_Form_Builder_Field_Settings {

    /**
     * All field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_field_settings() {
        return apply_filters( 'wpuf-form-builder-field-settings', array(
            'text_field' => self::text_field(),
            'textarea_field'   => self::textarea_field(),
        ) );
    }

    /**
     * Common properties for all kinds of fields
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_common_properties() {
        return array(
            array(
                'name'      => 'label',
                'title'     => __( 'Field Label', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title of this field', 'wpuf' ),
            ),

            array(
                'name'      => 'name',
                'title'     => __( 'Meta Key', 'wpuf' ),
                'type'      => 'text-meta',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Name of the meta key this field will save to', 'wpuf' ),
            ),

            array(
                'name'      => 'help',
                'title'     => __( 'Help text', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 20,
                'help_text' => __( 'Give the user some information about this field', 'wpuf' ),
            ),

            array(
                'name'      => 'required',
                'title'     => __( 'Required', 'wpuf' ),
                'type'      => 'radio',
                'options'   => array(
                    'yes'   => __( 'Yes', 'wpuf' ),
                    'no'    => __( 'No', 'wpuf' ),
                ),
                'section'   => 'basic',
                'priority'  => 21,
                'default'   => 'no',
                'inline'    => true,
                'help_text' => __( 'Check this option to mark the field required. A form will not submit unless all required fields are provided.', 'wpuf' ),
            ),

            array(
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => __( 'Give the user some information about this field', 'wpuf' ),
            ),
        );
    }

    /**
     * Common properties of a text input field
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_common_text_properties() {
        return array(
            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wpuf' ),
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'The default value this field will have', 'wpuf' ),
            ),

            array(
                'name'      => 'size',
                'title'     => __( 'Size', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 20,
                'help_text' => __( 'Size of this input field', 'wpuf' ),
            ),
        );
    }

    /**
     * Text field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function text_field() {
        $settings = self::get_common_properties();
        $settings = array_merge( $settings, self::get_common_text_properties() );

        return array(
            'template'      => 'text_field',
            'title'         => __( 'Text', 'wpuf' ),
            'icon'          => 'text-width',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'text_field',
                'required'      => 'no',
                'label'         => __( 'Text', 'wpuf' ),
                'name'          => '',
                'is_meta'       => 'yes',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'wpuf_cond'     => array(
                    'condition_status'  => 'no',
                    'cond_field'        => array(),
                    'cond_operator'     => array( '=' ),
                    'cond_option'       => array( '- select -' ),
                    'cond_logic'        => 'all'
                )
            )
        );
    }

    /**
     * Textarea field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function textarea_field() {
        $settings = self::get_common_properties();

        $textarea_settings = array(
            array(
                'name'      => 'rows',
                'title'     => __( 'Rows', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Number of rows in textarea', 'wpuf' ),
            ),

            array(
                'name'      => 'cols',
                'title'     => __( 'Columns', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'Number of columns in textarea', 'wpuf' ),
            ),

            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 12,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wpuf' ),
            ),

            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 13,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wpuf' ),
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 14,
                'help_text' => __( 'The default value this field will have', 'wpuf' ),
            ),

            array(
                'name'      => 'rich',
                'title'     => __( 'Textarea', 'wpuf' ),
                'type'      => 'radio',
                'options'   => array(
                    'yes'   => __( 'Yes', 'wpuf' ),
                    'no'    => __( 'No', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 15,
                'default'   => 'no',
                'inline'    => true,
            ),

            array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 16,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'wpuf' ),
            ),
        );

        $settings = array_merge( $settings, $textarea_settings );

        return array(
            'template'      => 'textarea_field',
            'title'         => __( 'Textarea', 'wpuf' ),
            'icon'          => 'paragraph',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'textarea',
                'template'         => 'textarea_field',
                'required'         => 'no',
                'label'            => __( 'Textarea', 'wpuf' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'css'              => '',
                'rows'             => 5,
                'cols'             => 25,
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'no',
                'word_restriction' => '',
                'id'                => 0,
                'wpuf_cond'        => array(
                    'condition_status'  => 'no',
                    'cond_field'        => array(),
                    'cond_operator'     => array( '=' ),
                    'cond_option'       => array( '- select -' ),
                    'cond_logic'        => 'all'
                ),
            )
        );
    }
}
