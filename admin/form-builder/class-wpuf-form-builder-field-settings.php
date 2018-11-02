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
                'title'     => __( 'Field Label', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title of this field', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'help',
                'title'     => __( 'Help text', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 20,
                'help_text' => __( 'Give the user some information about this field', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'required',
                'title'     => __( 'Required', 'wp-user-frontend' ),
                'type'      => 'radio',
                'options'   => array(
                    'yes'   => __( 'Yes', 'wp-user-frontend' ),
                    'no'    => __( 'No', 'wp-user-frontend' ),
                ),
                'section'   => 'basic',
                'priority'  => 21,
                'default'   => 'no',
                'inline'    => true,
                'help_text' => __( 'Check this option to mark the field required. A form will not submit unless all required fields are provided.', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'width',
                'title'     => __( 'Field Size', 'wp-user-frontend' ),
                'type'      => 'radio',
                'options'   => array(
                    'small'     => __( 'Small', 'wp-user-frontend' ),
                    'medium'    => __( 'Medium', 'wp-user-frontend' ),
                    'large'     => __( 'Large', 'wp-user-frontend' ),
                ),
                'section'   => 'advanced',
                'priority'  => 23,
                'default'   => 'large',
                'inline'    => true,
            ),

            array(
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => __( 'Provide a container class name for this field. Available classes: wpuf-col-half, wpuf-col-half-last, wpuf-col-one-third, wpuf-col-one-third-last', 'wp-user-frontend' ),
            )
        );

        if ( is_wpuf_post_form_builder() ) {
            $common_properties = array_merge($common_properties, array(
                array(
                    'name'      => 'wpuf_visibility',
                    'title'     => __( 'Visibility', 'wp-user-frontend' ),
                    'type'      => 'visibility',
                    'section'   => 'advanced',
                    'options'   => array(
                        'everyone'          => __( 'Everyone', 'wp-user-frontend' ),
                        'hidden'            => __( 'Hidden', 'wp-user-frontend' ),
                        'logged_in'         => __( 'Logged in users only', 'wp-user-frontend' ),
                        'subscribed_users'  => __( 'Subscription users only', 'wp-user-frontend' ),
                    ),
                    'priority'  => 30,
                    'inline'    => true,
                    'help_text' => __( 'Select option', 'wp-user-frontend' ),
                )
            ));
        }

        if ( $is_meta ) {
            $common_properties = array_merge($common_properties, array(
                array(
                    'name'      => 'name',
                    'title'     => __( 'Meta Key', 'wp-user-frontend' ),
                    'type'      => 'text-meta',
                    'section'   => 'basic',
                    'priority'  => 11,
                    'help_text' => __( 'Name of the meta key this field will save to', 'wp-user-frontend' ),
                )
            ));

            if ( is_wpuf_post_form_builder() ) {
                $common_properties = array_merge($common_properties, array(
                    array(
                        'name'      => 'show_in_post',
                        'title'     => __( 'Show Data in Post', 'wp-user-frontend' ),
                        'type'      => 'radio',
                        'options'   => array(
                            'yes'   => __( 'Yes', 'wp-user-frontend' ),
                            'no'    => __( 'No', 'wp-user-frontend' ),
                        ),
                        'section'   => 'advanced',
                        'priority'  => 24,
                        'default'   => 'yes',
                        'inline'    => true,
                        'help_text' => __( 'Select Yes if you want to show the field data in single post.', 'wp-user-frontend' ),
                    ),
                    array(
                        'name'      => 'hide_field_label',
                        'title'     => __( 'Hide Field Label in Post', 'wp-user-frontend' ),
                        'type'      => 'radio',
                        'options'   => array(
                            'yes'   => __( 'Yes', 'wp-user-frontend' ),
                            'no'    => __( 'No', 'wp-user-frontend' ),
                        ),
                        'section'   => 'advanced',
                        'priority'  => 24,
                        'default'   => 'no',
                        'inline'    => true,
                        'help_text' => __( 'Select Yes if you want to hide the field label in single post.', 'wp-user-frontend' ),
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
                'title'     => __( 'Placeholder text', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'The default value this field will have', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'size',
                'title'     => __( 'Size', 'wp-user-frontend' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 20,
                'help_text' => __( 'Size of this input field', 'wp-user-frontend' ),
            )
        );

        if ( $word_restriction ) {
            $properties[] = array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 15,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'wp-user-frontend' ),
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
                'title'     => __( 'Rows', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Number of rows in textarea', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'cols',
                'title'     => __( 'Columns', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'Number of columns in textarea', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 12,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wp-user-frontend' ),
                'dependencies' => array(
                    'rich' => 'no'
                )
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 13,
                'help_text' => __( 'The default value this field will have', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'rich',
                'title'     => __( 'Textarea', 'wp-user-frontend' ),
                'type'      => 'radio',
                'options'   => array(
                    'no'    => __( 'Normal', 'wp-user-frontend' ),
                    'yes'   => __( 'Rich textarea', 'wp-user-frontend' ),
                    'teeny' => __( 'Teeny Rich textarea', 'wp-user-frontend' ),
                ),
                'section'   => 'advanced',
                'priority'  => 14,
                'default'   => 'no',
            ),

            array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 15,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'wp-user-frontend' ),
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
            'cond_option'       => array( __( '- select -', 'wp-user-frontend' ) ),
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
            'title'         => __( 'Text', 'wp-user-frontend' ),
            'icon'          => 'text-width',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'text',
                'template'          => 'text_field',
                'required'          => 'no',
                'label'             => __( 'Text', 'wp-user-frontend' ),
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
                'hide_field_label'  => 'no',
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
            'title'         => __( 'Textarea', 'wp-user-frontend' ),
            'icon'          => 'paragraph',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'textarea',
                'template'         => 'textarea_field',
                'required'         => 'no',
                'label'            => __( 'Textarea', 'wp-user-frontend' ),
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
                'hide_field_label'  => 'no',
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
            'title'         => __( 'Options', 'wp-user-frontend' ),
            'type'          => 'option-data',
            'is_multiple'   => $is_multiple,
            'section'       => 'basic',
            'priority'      => 12,
            'help_text'     => __( 'Add options for the form field', 'wp-user-frontend' ),
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
                'title'         => __( 'Select Text', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 13,
                'help_text'     => __( "First element of the select dropdown. Leave this empty if you don't want to show this field", 'wp-user-frontend' ),
            ),
        );

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'dropdown_field',
            'title'         => __( 'Dropdown', 'wp-user-frontend' ),
            'icon'          => 'caret-square-o-down',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'select',
                'template'         => 'dropdown_field',
                'required'         => 'no',
                'label'            => __( 'Dropdown', 'wp-user-frontend' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => '',
                'options'          => array( 'Option' => __( 'Option', 'wp-user-frontend' ) ),
                'first'            => __( '- select -', 'wp-user-frontend' ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'hide_field_label' => 'no',
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
            'title'         => __( 'Multi Select', 'wp-user-frontend' ),
            'icon'          => 'list-ul',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'multiselect',
                'template'         => 'multiple_select',
                'required'         => 'no',
                'label'            => __( 'Multi Select', 'wp-user-frontend' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => array(),
                'options'          => array( 'Option' => __( 'Option', 'wp-user-frontend' ) ),
                'first'            => __( '- select -', 'wp-user-frontend' ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'hide_field_label' => 'no',
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
            )
        );

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'radio_field',
            'title'         => __( 'Radio', 'wp-user-frontend' ),
            'icon'          => 'dot-circle-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'radio',
                'template'         => 'radio_field',
                'required'         => 'no',
                'label'            => __( 'Radio Field', 'wp-user-frontend' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => '',
                'inline'           => 'no',
                'options'          => array( 'Option' => __( 'Option', 'wp-user-frontend' ) ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'hide_field_label' => 'no',
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
            )
        );

        $settings = array_merge( $settings, $dropdown_settings );

        return array(
            'template'      => 'checkbox_field',
            'title'         => __( 'Checkbox', 'wp-user-frontend' ),
            'icon'          => 'check-square-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'checkbox',
                'template'         => 'checkbox_field',
                'required'         => 'no',
                'label'            => __( 'Checkbox Field', 'wp-user-frontend' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => array(),
                'inline'           => 'no',
                'options'          => array( 'Option' => __( 'Option', 'wp-user-frontend' ) ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'hide_field_label' => 'no',
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

        return array(
            'template'      => 'website_url',
            'title'         => __( 'URL', 'wp-user-frontend' ),
            'icon'          => 'link',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'url',
                'template'          => 'website_url',
                'required'          => 'no',
                'label'             => __( 'URL', 'wp-user-frontend' ),
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
                'hide_field_label'  => 'no',
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
            'title'         => __( 'Email Address', 'wp-user-frontend' ),
            'icon'          => 'envelope-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'email',
                'template'          => 'email_address',
                'required'          => 'no',
                'label'             => __( 'Email', 'wp-user-frontend' ),
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
                'hide_field_label'  => 'no',
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
                'title'     => __( 'Meta Key', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Name of the meta key this field will save to', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'meta_value',
                'title'     => __( 'Meta Value', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Enter the meta value', 'wp-user-frontend' ),
            ),
        );

        return array(
            'template'      => 'custom_hidden_field',
            'title'         => __( 'Hidden Field', 'wp-user-frontend' ),
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
                'hide_field_label'  => 'no',
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
                'title'         => __( 'Button Label', 'wp-user-frontend' ),
                'type'          => 'text',
                'default'       => __( 'Select Image', 'wp-user-frontend' ),
                'section'       => 'basic',
                'priority'      => 30,
                'help_text'     => __( 'Enter a label for the Select button', 'wp-user-frontend' ),
            ),
            array(
                'name'          => 'max_size',
                'title'         => __( 'Max. file size', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'wp-user-frontend' ),
            ),
            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'wp-user-frontend' ),
            ),
        ) );

        return array(
            'template'      => 'image_upload',
            'title'         => __( 'Image Upload', 'wp-user-frontend' ),
            'icon'          => 'file-image-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'image_upload',
                'template'          => 'image_upload',
                'required'          => 'no',
                'label'             => __( 'Image Upload', 'wp-user-frontend' ),
                'name'              => '',
                'button_label'      => __( 'Select Image', 'wp-user-frontend' ),
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => '',
                'css'               => '',
                'max_size'          => '1024',
                'count'             => '1',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'hide_field_label'  => 'no',
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
                'title'     => __( 'Title', 'wp-user-frontend' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Title of the section', 'wp-user-frontend' ),
            ),

            array(
                'name'      => 'description',
                'title'     => __( 'Description', 'wp-user-frontend' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Some details text about the section', 'wp-user-frontend' ),
            ),
        );

        return array(
            'template'      => 'section_break',
            'title'         => __( 'Section Break', 'wp-user-frontend' ),
            'icon'          => 'columns',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'section_break',
                'template'          => 'section_break',
                'label'             => __( 'Section Break', 'wp-user-frontend' ),
                'description'       => __( 'Some description about this section', 'wp-user-frontend' ),
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'hide_field_label'  => 'no',
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
                'title'     => __( 'HTML Codes', 'wp-user-frontend' ),
                'type'      => 'textarea',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Paste your HTML codes, WordPress shortcodes will also work here', 'wp-user-frontend' ),
            ),
        );

        return array(
            'template'      => 'custom_html',
            'title'         => __( 'Custom HTML', 'wp-user-frontend' ),
            'icon'          => 'code',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'html',
                'template'          => 'custom_html',
                'label'             => __( 'Custom HTML', 'wp-user-frontend' ),
                'html'              => sprintf( '<p>%s</p>', __( 'Some description about this section', 'wp-user-frontend' ) ),
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
                'title'         => __( 'Title', 'wp-user-frontend' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Title of the section', 'wp-user-frontend' ),
            ),

            array(
                'name'          => 'recaptcha_type',
                'title'         => 'reCaptcha type',
                'type'          => 'radio',
                'options'       => array(
                    'enable_no_captcha'    => __( 'Enable noCaptcha', 'wp-user-frontend' ),
                    'invisible_recaptcha'  => __( 'Enable Invisible reCaptcha', 'wp-user-frontend' ),
                ),
                'default'       => 'enable_no_captcha',
                'section'       => 'basic',
                'priority'      => 11,
                'help_text'     => __( 'Select reCaptcha type', 'wp-user-frontend' ),
            )
        );

        return array(
            'template'      => 'recaptcha',
            'title'         => __( 'reCaptcha', 'wp-user-frontend' ),
            'icon'          => 'qrcode',
            'validator'     => array(
                'callback'      => 'has_recaptcha_api_keys',
                'button_class'  => 'button-faded',
                'msg_title'     => __( 'Site key and Secret key', 'wp-user-frontend' ),
                'msg'           => sprintf(
                    __( 'You need to set Site key and Secret key in <a href="%s" target="_blank">Settings</a> in order to use "Recaptcha" field. <a href="%s" target="_blank">Click here to get the these key</a>.', 'wp-user-frontend' ),
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
