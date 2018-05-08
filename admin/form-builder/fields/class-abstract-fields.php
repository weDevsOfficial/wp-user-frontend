<?php

/**
 * Form field abstract class
 *
 * @since 1.1.0
 */
abstract class WeForms_Field_Contract {

    /**
     * The field name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Type of the field
     *
     * @var string
     */
    protected $input_type = '';

    /**
     * Icon of the field
     *
     * @var string
     */
    protected $icon = '';

    /**
     * Get the name of the field
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get field type
     *
     * @return string
     */
    public function get_type() {
        return $this->input_type;
    }

    /**
     * Get the fontawesome icon for this field
     *
     * @return string
     */
    public function get_icon() {
        return $this->icon;
    }

    /**
     * Render of the field in the frontend
     *
     * @param  array    $field_settings The field configuration from the db
     * @param  integer  $form_id        The form id
     *
     * @return void
     */
    abstract function render( $field_settings, $form_id );

    /**
     * Get the field option settings for form builder
     *
     * @return array
     */
    abstract function get_options_settings();

    /**
     * Get the field props
     *
     * Field props are the field properties that saves in the database
     *
     * @return array
     */
    abstract function get_field_props();

    /**
     * The JS template for using in the form builder
     *
     * @return array
     */
    public function get_js_settings() {
        $settings = array(
            'template'      => $this->get_type(),
            'title'         => $this->get_name(),
            'icon'          => $this->get_icon(),
            'pro_feature'   => $this->is_pro(),
            'settings'      => $this->get_options_settings(),
            'field_props'   => $this->get_field_props(),
            'is_full_width' => $this->is_full_width()
        );

        if ( $validator = $this->get_validator() ) {
            $settings['validator'] = $validator;
        }

        return apply_filters( 'weforms_field_get_js_settings', $settings );
    }

    /**
     * Custom field validator if exists
     *
     * @return boolean|array
     */
    public function get_validator() {
        return false;
    }

    /**
     * Check if it's a pro feature
     *
     * @return boolean
     */
    public function is_pro() {
        return false;
    }

    /**
     * If this field is full width
     *
     * Used in form builder preview (hides the label)
     *
     * @return boolean
     */
    public function is_full_width() {
        return false;
    }

    /**
     * Conditional property for all fields
     *
     * @return array
     */
    public function default_conditional_prop() {
        return array(
            'condition_status'  => 'no',
            'cond_field'        => array(),
            'cond_operator'     => array( '=' ),
            'cond_option'       => array( __( '- select -', 'weforms' ) ),
            'cond_logic'        => 'all'
        );
    }

    /**
     * Default attributes of a field
     *
     * Child classes should use this default setting and extend it by using `get_field_settings()` function
     *
     * @return array
     */
    public function default_attributes() {
        return array(
            'template'    => $this->get_type(),
            'name'        => '',
            'label'       => $this->get_name(),
            'required'    => 'no',
            'id'          => 0,
            'width'       => 'large',
            'css'         => '',
            'placeholder' => '',
            'default'     => '',
            'size'        => 40,
            'help'        => '',
            'is_meta'     => 'yes', // wpuf uses it to differentiate meta fields with core fields, maybe removed
            'is_new'      => true, // introduced by @edi, not sure what it does. Have to remove
            'wpuf_cond'   => $this->default_conditional_prop()
        );
    }

    /**
     * Common properties for all kinds of fields
     *
     * @param boolean $is_meta
     *
     * @return array
     */
    public static function get_default_option_settings( $is_meta = true, $exclude = array() ) {
        $common_properties = array(
            array(
                'name'      => 'label',
                'title'     => __( 'Field Label', 'weforms' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title of this field', 'weforms' ),
            ),

            array(
                'name'      => 'help',
                'title'     => __( 'Help text', 'weforms' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 20,
                'help_text' => __( 'Give the user some information about this field', 'weforms' ),
            ),

            array(
                'name'      => 'required',
                'title'     => __( 'Required', 'weforms' ),
                'type'      => 'radio',
                'options'   => array(
                    'yes'   => __( 'Yes', 'weforms' ),
                    'no'    => __( 'No', 'weforms' ),
                ),
                'section'   => 'basic',
                'priority'  => 21,
                'default'   => 'no',
                'inline'    => true,
                'help_text' => __( 'Check this option to mark the field required. A form will not submit unless all required fields are provided.', 'weforms' ),
            ),

            array(
                'name'      => 'width',
                'title'     => __( 'Field Size', 'weforms' ),
                'type'      => 'radio',
                'options'   => array(
                    'small'     => __( 'Small', 'weforms' ),
                    'medium'    => __( 'Medium', 'weforms' ),
                    'large'     => __( 'Large', 'weforms' ),
                ),
                'section'   => 'advanced',
                'priority'  => 21,
                'default'   => 'large',
                'inline'    => true,
            ),

            array(
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => __( 'Provide a container class name for this field.', 'weforms' ),
            ),

            array(
                'name'          => 'dynamic',
                'title'         => '',
                'type'          => 'dynamic-field',
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => __( 'Check this option to allow field to be populated dynamically using hooks/query string/shortcode', 'weforms' ),
            ),
        );

        if ( $is_meta ) {
            $common_properties[] = array(
                'name'      => 'name',
                'title'     => __( 'Meta Key', 'weforms' ),
                'type'      => 'text-meta',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Name of the meta key this field will save to', 'weforms' ),
            );
        }

        if ( count( $exclude ) ) {
            foreach ( $common_properties as $key => &$option ) {

                if ( in_array( $option['name'] , $exclude) ) {
                    unset( $common_properties[$key] );
                }
            }

        }

        return $common_properties;
    }

    /**
     * Common properties of a text input field
     *
     * @param boolean $word_restriction
     *
     * @return array
     */
    public static function get_default_text_option_settings( $word_restriction = false ) {
        $properties = array(
            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'weforms' ),
                'type'      => 'text-with-tag',
                'tag_filter' => 'no_fields', // we don't want to show any fields with merge tags, just basic tags
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'weforms' ),
            ),

            array(
                'name'       => 'default',
                'title'      => __( 'Default value', 'weforms' ),
                'type'       => 'text-with-tag',
                'tag_filter' => 'no_fields',
                'section'    => 'advanced',
                'priority'   => 11,
                'help_text'  => __( 'The default value this field will have', 'weforms' ),
            ),

            array(
                'name'      => 'size',
                'title'     => __( 'Size', 'weforms' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 20,
                'help_text' => __( 'Size of this input field', 'weforms' ),
            ),
        );

        if ( $word_restriction ) {
            $properties[] = array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 15,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'weforms' ),
            );
        }

        return apply_filters( 'wpuf-form-builder-common-text-fields-properties', $properties );
    }

    /**
     * Option data for option based fields
     *
     * @param boolean $is_multiple
     *
     * @return array
     */
    public function get_default_option_dropdown_settings( $is_multiple = false ) {
        return array(
            'name'          => 'options',
            'title'         => __( 'Options', 'weforms' ),
            'type'          => 'option-data',
            'is_multiple'   => $is_multiple,
            'section'       => 'basic',
            'priority'      => 12,
            'help_text'     => __( 'Add options for the form field', 'weforms' ),
        );
    }

    /**
     * Common properties of a textarea field
     *
     * @return array
     */
    public function get_default_textarea_option_settings() {
        return array(
            array(
                'name'      => 'rows',
                'title'     => __( 'Rows', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Number of rows in textarea', 'weforms' ),
            ),

            array(
                'name'      => 'cols',
                'title'     => __( 'Columns', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'Number of columns in textarea', 'weforms' ),
            ),

            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 12,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'weforms' ),
                'dependencies' => array(
                    'rich' => 'no'
                )
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 13,
                'help_text' => __( 'The default value this field will have', 'weforms' ),
            ),

            array(
                'name'      => 'rich',
                'title'     => __( 'Textarea', 'weforms' ),
                'type'      => 'radio',
                'options'   => array(
                    'no'    => __( 'Normal', 'weforms' ),
                    'yes'   => __( 'Rich textarea', 'weforms' ),
                    'teeny' => __( 'Teeny Rich textarea', 'weforms' ),
                ),
                'section'   => 'advanced',
                'priority'  => 14,
                'default'   => 'no',
            ),

            array(
                'name'      => 'word_restriction',
                'title'     => __( 'Word Restriction', 'weforms' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 15,
                'help_text' => __( 'Numebr of words the author to be restricted in', 'weforms' ),
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Field helper methods
    |--------------------------------------------------------------------------
    |
    | Various helper method for rendering form fields
    */

    public function print_list_attributes( $field ) {
        $label      = isset( $field['label'] ) ? $field['label'] : '';
        $el_name    = !empty( $field['name'] ) ? $field['name'] : '';
        $class_name = !empty( $field['css'] ) ? ' ' . $field['css'] : '';
        $field_size = !empty( $field['width'] ) ? ' field-size-' . $field['width'] : '';

        printf( 'class="wpuf-el %s%s%s" data-label="%s"', $el_name, $class_name, $field_size, $label );
    }

    /**
     * Prints form input label
     *
     * @param array $attr
     * @param integer $form_id
     */
    function print_label( $field, $form_id = 0 ) {
        ?>
        <div class="wpuf-label">
            <label for="<?php echo isset( $field['name'] ) ? $field['name'] . '_' . $form_id : 'cls'; ?>"><?php echo $field['label'] . $this->required_mark( $field ); ?></label>
        </div>
        <?php
    }

    /**
     * Check if a field is required
     *
     * @param  array  $field
     *
     * @return boolean
     */
    public function is_required( $field ) {
        if ( isset( $field['required'] ) && $field['required'] == 'yes' ) {
            return true;
        }

        return false;
    }

    /**
     * Prints required field asterisk
     *
     * @param array $attr
     * @return string
     */
    function required_mark( $field ) {
        if ( $this->is_required( $field ) ) {
            return ' <span class="required">*</span>';
        }
    }

    /**
     * Prints help text for a field
     *
     * @param array $field
     */
    function help_text( $field ) {
        if ( empty( $field['help'] ) ) {
            return;
        }
        ?>
        <span class="wpuf-help"><?php echo stripslashes( $field['help'] ); ?></span>
        <?php
    }

    /**
     * Push logic to conditional array for processing
     *
     * @param  array $form_field
     * @param  integer $form_id
     *
     * @return void
     */
    function conditional_logic( $form_field, $form_id ) {

        if ( !isset( $form_field['wpuf_cond']['condition_status'] ) || $form_field['wpuf_cond']['condition_status'] != 'yes' ) {
            return;
        }

        $cond_inputs                     = $form_field['wpuf_cond'];
        $cond_inputs['condition_status'] = isset( $cond_inputs['condition_status'] ) ? $cond_inputs['condition_status'] : '';

        if ( $cond_inputs['condition_status'] == 'yes') {
            $cond_inputs['type']    = $form_field['template'];
            $cond_inputs['name']    = $form_field['name'];
            $cond_inputs['form_id'] = $form_id;
            $condition              = json_encode( $cond_inputs );

        } else {
            $condition = '';
        }

        // for section break
        if ( $form_field['template'] == 'section_break' ) {
            $cond_inputs['name'] = $form_field['name'] .'_'. $form_field['id'];
            $condition           = json_encode( $cond_inputs );
        }

        ?>
        <script type="text/javascript">
            wpuf_conditional_items.push(<?php echo $condition; ?>);
        </script>
        <?php
    }

    /**
     * Prepare entry default, can be replaced through field classes
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {

        $value = !empty( $_POST[$field['name']] ) ? $_POST[$field['name']] : '';

        if ( is_array( $value ) ) {

            $entry_value = implode( WeForms::$field_separator, $_POST[$field['name']] );

        } else {
            $entry_value = trim( $value  );
        }

        return $entry_value;
    }



    /**
     * Function to check word restriction
     *
     * @param $word_nums number of words allowed
     */
    function check_word_restriction_func($word_nums, $rich_text, $field_name) {
        // bail out if it is dashboard
        if ( is_admin() ) {
            return;
        }
        ?>
        <script type="text/javascript">
            ;(function($) {
                $(document).ready( function(){
                    WP_User_Frontend.editorLimit.bind(<?php printf( '%d, "%s", "%s"', $word_nums, $field_name, $rich_text ); ?>);
                });
            })(jQuery);
        </script>
        <?php

    }
}
