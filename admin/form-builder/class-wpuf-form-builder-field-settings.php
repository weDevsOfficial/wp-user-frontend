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
            'text_field'            => self::text_field(),
            'textarea_field'        => self::textarea_field(),
            'dropdown_field'        => self::dropdown_field(),
            'multiple_select'       => self::multiple_select(),
            'radio_field'           => self::radio_field(),
            'checkbox_field'        => self::checkbox_field(),
            'website_url'           => self::website_url(),
            'email_address'         => self::email_address(),
            'custom_hidden_field'   => self::custom_hidden_field(),
            'image_upload'          => self::image_upload(),
            'section_break'         => self::section_break(),
            'custom_html'           => self::custom_html(),
            'recaptcha'             => self::recaptcha(),
        ) );
    }

    /**
     * Common properties for all kinds of fields
     *
     * @since 2.5
     *
     * @param boolean $is_meta
     *
     * @return array
     */
    public static function get_common_properties( $is_meta = true ) {
        $common_properties = array(
            array(
                'name'      => 'label',
                'title'     => __( 'Field Label', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title of this field', 'wpuf' ),
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
                'name'      => 'width',
                'title'     => __( 'Field Size', 'wpuf' ),
                'type'      => 'radio',
                'options'   => array(
                    'small'     => __( 'Small', 'wpuf' ),
                    'medium'    => __( 'Medium', 'wpuf' ),
                    'large'     => __( 'Large', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 23,
                'default'   => 'large',
                'inline'    => true,
            ),

            array(
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => __( 'Provide a container class name for this field.', 'wpuf' ),
            )
        );

        if ( is_wpuf_post_form_builder() ) {
            $common_properties = array_merge($common_properties, array(
                array(
                    'name'      => 'wpuf_visibility',
                    'title'     => __( 'Visibility', 'wpuf' ),
                    'type'      => 'visibility',
                    'section'   => 'advanced',
                    'options'   => array(
                        'everyone'          => __( 'Everyone', 'wpuf' ),
                        'hidden'            => __( 'Hidden', 'wpuf' ),
                        'logged_in'         => __( 'Logged in users only', 'wpuf' ),
                        'subscribed_users'  => __( 'Subscription users only', 'wpuf' ),
                    ),
                    'priority'  => 30,
                    'inline'    => true,
                    'help_text' => __( 'Select option', 'wpuf' ),
                )
            ));
        }

        if ( $is_meta ) {
            $common_properties = array_merge($common_properties, array(
                array(
                    'name'      => 'name',
                    'title'     => __( 'Meta Key', 'wpuf' ),
                    'type'      => 'text-meta',
                    'section'   => 'basic',
                    'priority'  => 11,
                    'help_text' => __( 'Name of the meta key this field will save to', 'wpuf' ),
                )
            ));

            if ( is_wpuf_post_form_builder() ) {
                $common_properties = array_merge($common_properties, array(
                    array(
                        'name'      => 'show_in_post',
                        'title'     => __( 'Show Data in Post', 'wpuf' ),
                        'type'      => 'radio',
                        'options'   => array(
                            'yes'   => __( 'Yes', 'wpuf' ),
                            'no'    => __( 'No', 'wpuf' ),
                        ),
                        'section'   => 'advanced',
                        'priority'  => 24,
                        'default'   => 'yes',
                        'inline'    => true,
                        'help_text' => __( 'Select Yes if you want to show the field data in single post.', 'wpuf' ),
                    )
                ));
            }
        }

        return apply_filters( 'wpuf-form-builder-fields-common-properties', $common_properties );
    }

    /**
     * Common properties of a text input field
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_common_text_properties( $word_restriction = false ) {
        $properties = array(
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
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 20,
                'help_text' => __( 'Size of this input field', 'wpuf' ),
            )
        );

        if ( $word_restriction ) {
            $properties[] = array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 15,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'wpuf' ),
            );
        }

        return apply_filters( 'wpuf-form-builder-common-text-fields-properties', $properties );
    }

    /**
     * Common properties of a textarea field
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_common_textarea_properties() {
        return array(
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
                'dependencies' => array(
                    'rich' => 'no'
                )
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 13,
                'help_text' => __( 'The default value this field will have', 'wpuf' ),
            ),

            array(
                'name'      => 'rich',
                'title'     => __( 'Textarea', 'wpuf' ),
                'type'      => 'radio',
                'options'   => array(
                    'no'    => __( 'Normal', 'wpuf' ),
                    'yes'   => __( 'Rich textarea', 'wpuf' ),
                    'teeny' => __( 'Teeny Rich textarea', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 14,
                'default'   => 'no',
            ),

            array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 15,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'wpuf' ),
            ),
        );
    }

    /**
     * wpuf_visibility property for all fields
     *
     * @since 2.6
     *
     * @return array
     */
    public static function get_wpuf_visibility_prop( $default = 'everyone' ) {
        return array(
            'selected'         => $default,
            'choices'          => array()
        );
    }

    /**
     * wpuf_cond property for all fields
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_wpuf_cond_prop() {
        return array(
            'condition_status'  => 'no',
            'cond_field'        => array(),
            'cond_operator'     => array( '=' ),
            'cond_option'       => array( __( '- select -', 'wpuf' ) ),
            'cond_logic'        => 'all'
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
        $settings = array_merge( $settings, self::get_common_text_properties( true ) );

        return array(
            'template'      => 'text_field',
            'title'         => __( 'Text', 'wpuf' ),
            'icon'          => 'text-width',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'text',
                'template'          => 'text_field',
                'required'          => 'no',
                'label'             => __( 'Text', 'wpuf' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => '',
                'css'               => '',
                'placeholder'       => '',
                'default'           => '',
                'size'              => 40,
                'word_restriction'  => '',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
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
        $settings = array_merge( $settings, self::get_common_textarea_properties() );

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
                'width'            => '',
                'css'              => '',
                'rows'             => 5,
                'cols'             => 25,
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'no',
                'word_restriction' => '',
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'wpuf_visibility'  => self::get_wpuf_visibility_prop(),
                'wpuf_cond'        => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Option data for option based fields
     *
     * @since 2.5
     *
     * @param boolean $is_multiple
     *
     * @return array
     */
    public static function get_option_data_setting( $is_multiple = false ) {
        return array(
            'name'          => 'options',
            'title'         => __( 'Options', 'wpuf' ),
            'type'          => 'option-data',
            'is_multiple'   => $is_multiple,
            'section'       => 'basic',
            'priority'      => 12,
            'help_text'     => __( 'Add options for the form field', 'wpuf' ),
        );
    }

    /**
     * Dropdown/Select field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function dropdown_field() {
        $settings = self::get_common_properties();

        $dropdown_settings = array(
            self::get_option_data_setting(),

            array(
                'name'          => 'first',
                'title'         => __( 'Select Text', 'wpuf' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 13,
                'help_text'     => __( "First element of the select dropdown. Leave this empty if you don't want to show this field", 'wpuf' ),
            ),
        );

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'dropdown_field',
            'title'         => __( 'Dropdown', 'wpuf' ),
            'icon'          => 'caret-square-o-down',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'select',
                'template'         => 'dropdown_field',
                'required'         => 'no',
                'label'            => __( 'Dropdown', 'wpuf' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => '',
                'options'          => array( 'Option' => __( 'Option', 'wpuf' ) ),
                'first'            => __( '- select -', 'wpuf' ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'wpuf_visibility'  => self::get_wpuf_visibility_prop(),
                'wpuf_cond'        => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Multiselect field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function multiple_select() {
        $settings = self::get_common_properties();

        $dropdown_settings = array(
            self::get_option_data_setting( true )
        );

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'multiple_select',
            'title'         => __( 'Multi Select', 'wpuf' ),
            'icon'          => 'list-ul',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'multiselect',
                'template'         => 'multiple_select',
                'required'         => 'no',
                'label'            => __( 'Multi Select', 'wpuf' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => array(),
                'options'          => array( 'Option' => __( 'Option', 'wpuf' ) ),
                'first'            => __( '- select -', 'wpuf' ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'wpuf_visibility'  => self::get_wpuf_visibility_prop(),
                'wpuf_cond'        => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Radio field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function radio_field() {
        $settings = self::get_common_properties();

        $dropdown_settings = array(
            self::get_option_data_setting(),

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

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'radio_field',
            'title'         => __( 'Radio', 'wpuf' ),
            'icon'          => 'dot-circle-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'radio',
                'template'         => 'radio_field',
                'required'         => 'no',
                'label'            => __( 'Radio Field', 'wpuf' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => '',
                'inline'           => 'no',
                'options'          => array( 'Option' => __( 'Option', 'wpuf' ) ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'wpuf_visibility'  => self::get_wpuf_visibility_prop(),
                'wpuf_cond'        => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Checkbox field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function checkbox_field() {
        $settings = self::get_common_properties();

        $dropdown_settings = array(
            self::get_option_data_setting( true ),

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

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'checkbox_field',
            'title'         => __( 'Checkbox', 'wpuf' ),
            'icon'          => 'check-square-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'checkbox',
                'template'         => 'checkbox_field',
                'required'         => 'no',
                'label'            => __( 'Checkbox Field', 'wpuf' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => array(),
                'inline'           => 'no',
                'options'          => array( 'Option' => __( 'Option', 'wpuf' ) ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'wpuf_visibility'  => self::get_wpuf_visibility_prop(),
                'wpuf_cond'        => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Website URL field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function website_url() {
        $settings = self::get_common_properties();
        $settings = array_merge( $settings, self::get_common_text_properties() );
        $settings[] =  array(
            'name'      => 'open_window',
            'title'     => __( 'Open in : ', 'wpuf' ),
            'type'      => 'radio',
            'options'   => array(
                'same'   => __( 'Same Window', 'wpuf' ),
                'new'    => __( 'New Window', 'wpuf' ),
            ),
            'section'   => 'basic',
            'default'   => 'same',
            'inline'    => true,
            'priority'  => 32,
            'help_text' => __( 'Choose whether the link will open in new tab or same window', 'wpuf' ),
        );

        return array(
            'template'      => 'website_url',
            'title'         => __( 'URL', 'wpuf' ),
            'icon'          => 'link',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'url',
                'template'          => 'website_url',
                'required'          => 'no',
                'label'             => __( 'URL', 'wpuf' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => 'large',
                'css'               => '',
                'placeholder'       => '',
                'default'           => '',
                'open_window'       => 'same',
                'size'              => 40,
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Email field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function email_address() {
        $settings = self::get_common_properties();
        $settings = array_merge( $settings, self::get_common_text_properties() );

        return array(
            'template'      => 'email_address',
            'title'         => __( 'Email Address', 'wpuf' ),
            'icon'          => 'envelope-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'email',
                'template'          => 'email_address',
                'required'          => 'no',
                'label'             => __( 'Email', 'wpuf' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => 'large',
                'css'               => '',
                'placeholder'       => '',
                'default'           => '',
                'size'              => 40,
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Hidden field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function custom_hidden_field() {
        $settings = array(
            array(
                'name'      => 'name',
                'title'     => __( 'Meta Key', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Name of the meta key this field will save to', 'wpuf' ),
            ),

            array(
                'name'      => 'meta_value',
                'title'     => __( 'Meta Value', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Enter the meta value', 'wpuf' ),
            ),
        );

        return array(
            'template'      => 'custom_hidden_field',
            'title'         => __( 'Hidden Field', 'wpuf' ),
            'icon'          => 'eye-slash',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'hidden',
                'template'          => 'custom_hidden_field',
                'label'             => '',
                'name'              => '',
                'meta_value'        => '',
                'is_meta'           => 'yes',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => null
            )
        );
    }

    /**
     * Image field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function image_upload() {
        $settings = self::get_common_properties();

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'button_label',
                'title'         => __( 'Button Label', 'wpuf' ),
                'type'          => 'text',
                'default'       => __( 'Select Image', 'wpuf' ),
                'section'       => 'basic',
                'priority'      => 30,
                'help_text'     => __( 'Enter a label for the Select button', 'wpuf' ),
            ),
            array(
                'name'          => 'max_size',
                'title'         => __( 'Max. file size', 'wpuf' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'wpuf' ),
            ),
            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'wpuf' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'wpuf' ),
            ),
        ) );

        return array(
            'template'      => 'image_upload',
            'title'         => __( 'Image Upload', 'wpuf' ),
            'icon'          => 'file-image-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'image_upload',
                'template'          => 'image_upload',
                'required'          => 'no',
                'label'             => __( 'Image Upload', 'wpuf' ),
                'name'              => '',
                'button_label'      => __( 'Select Image', 'wpuf' ),
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => '',
                'css'               => '',
                'max_size'          => '1024',
                'count'             => '1',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Section break field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function section_break() {
        $settings = array(
            array(
                'name'      => 'label',
                'title'     => __( 'Title', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Title of the section', 'wpuf' ),
            ),

            array(
                'name'      => 'description',
                'title'     => __( 'Description', 'wpuf' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Some details text about the section', 'wpuf' ),
            ),
        );

        return array(
            'template'      => 'section_break',
            'title'         => __( 'Section Break', 'wpuf' ),
            'icon'          => 'columns',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'section_break',
                'template'          => 'section_break',
                'label'             => __( 'Section Break', 'wpuf' ),
                'description'       => __( 'Some description about this section', 'wpuf' ),
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * HTML field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function custom_html() {
        $settings = array(
            array(
                'name'      => 'html',
                'title'     => __( 'HTML Codes', 'wpuf' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Paste your HTML codes, WordPress shortcodes will also work here', 'wpuf' ),
            ),
        );

        return array(
            'template'      => 'custom_html',
            'title'         => __( 'Custom HTML', 'wpuf' ),
            'icon'          => 'code',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'html',
                'template'          => 'custom_html',
                'label'             => __( 'Custom HTML', 'wpuf' ),
                'html'              => sprintf( '<p>%s</p>', __( 'Some description about this section', 'wpuf' ) ),
                'id'                => 0,
                'is_new'            => true,
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Recaptcha
     *
     * @since 2.5
     *
     * @return array
     */
    public static function recaptcha() {
        $settings = array(
            array(
                'name'          => 'label',
                'title'         => __( 'Title', 'wpuf' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Title of the section', 'wpuf' ),
            ),

            array(
                'name'          => 'recaptcha_type',
                'title'         => 'reCaptcha type',
                'type'          => 'radio',
                'options'       => array(
                    'enable_no_captcha'    => __( 'Enable noCaptcha', 'wpuf' ),
                    'invisible_recaptcha'  => __( 'Enable Invisible reCaptcha', 'wpuf' ),
                ),
                'default'       => 'enable_no_captcha',
                'section'       => 'basic',
                'priority'      => 11,
                'help_text'     => __( 'Select reCaptcha type', 'wpuf' ),
            )
        );

        return array(
            'template'      => 'recaptcha',
            'title'         => __( 'reCaptcha', 'wpuf' ),
            'icon'          => 'qrcode',
            'validator'     => array(
                'callback'      => 'has_recaptcha_api_keys',
                'button_class'  => 'button-faded',
                'msg_title'     => __( 'Site key and Secret key', 'wpuf' ),
                'msg'           => sprintf(
                    __( 'You need to set Site key and Secret key in <a href="%s" target="_blank">Settings</a> in order to use "Recaptcha" field. <a href="%s" target="_blank">Click here to get the these key</a>.', 'wpuf' ),
                    admin_url( 'admin.php?page=wpuf-settings' ),
                    'https://www.google.com/recaptcha/'
                ),
            ),
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'recaptcha',
                'template'          => 'recaptcha',
                'label'             => '',
                'recaptcha_type'    => 'enable_no_captcha',
                'id'                => 0,
                'is_new'            => true,
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop(),
            )
        );
    }
}
