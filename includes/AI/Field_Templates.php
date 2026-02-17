<?php

namespace WeDevs\Wpuf\AI;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Field Templates Helper
 *
 * Provides complete field structures for POST form fields only.
 * AI returns minimal data, this class builds the full structure.
 *
 * @since 4.2.1
 */
class Field_Templates {

    /**
     * Get complete field structure
     *
     * @param string $template Field template name
     * @param string $label Field label
     * @param string $field_id Field ID
     * @param array $custom_props Custom properties from AI
     * @return array Complete field structure
     */
    public static function get_field_structure( $template, $label, $field_id, $custom_props = [] ) {
        // Map AI field names to actual registered field types
        $field_mapping = [
            'biography' => class_exists( 'WPUF_PRO' ) ? 'user_bio' : 'textarea_field',
            'bio' => class_exists( 'WPUF_PRO' ) ? 'user_bio' : 'textarea_field',
            'avatar' => 'user_avatar',
            'profile_photo' => 'profile_photo',
            'gender' => 'gender',
        ];

        // Apply field mapping if exists
        if ( isset( $field_mapping[ $template ] ) ) {
            $template = $field_mapping[ $template ];

            // For textarea fallback in free version, set name to 'description' for WordPress compatibility
            if ( 'textarea_field' === $template && ! isset( $custom_props['name'] ) ) {
                $custom_props['name'] = 'description';
            }
        }

        $method = 'get_' . $template . '_template';

        if ( method_exists( self::class, $method ) ) {
            $field = call_user_func( [ self::class, $method ], $label, $field_id );

            // Filter out empty custom props to prevent overriding template defaults
            // This ensures fields like google_map keep their required properties (zoom, default_pos, etc.)
            // Remove keys with null, empty string, or empty array values
            $filtered_custom_props = [];
            foreach ( $custom_props as $key => $value ) {
                // Keep the value if it's meaningful:
                // - Not null
                // - Not empty string
                // - Not empty array
                // - Allow boolean false (valid value)
                // - Allow number 0 (valid value)
                // - Allow non-empty strings
                if ( $value !== null && $value !== '' && $value !== [] ) {
                    $filtered_custom_props[$key] = $value;
                }
            }

            // Merge filtered custom props - AI properties override template defaults only if non-empty
            // This ensures 'required', 'placeholder', 'options', etc. from AI take precedence
            $field = array_merge( $field, $filtered_custom_props );

            // CRITICAL FIX: For gender_field, ALWAYS ensure name and meta_key are 'wpuf_gender'
            // These are fixed values and should never be overridden by custom props
            if ( $template === 'gender' || $template === 'gender_field' ) {
                $field['name'] = 'wpuf_gender';
                $field['meta_key'] = 'wpuf_gender';
            }

            // CRITICAL FIX: For social media fields, ALWAYS ensure name and meta_key are hardcoded
            // These are fixed values and should never be overridden by custom props
            if ( $template === 'facebook_url' ) {
                $field['name'] = 'wpuf_social_facebook';
                $field['meta_key'] = 'wpuf_social_facebook';
            }

            if ( $template === 'twitter_url' ) {
                $field['name'] = 'wpuf_social_twitter';
                $field['meta_key'] = 'wpuf_social_twitter';
            }

            if ( $template === 'instagram_url' ) {
                $field['name'] = 'wpuf_social_instagram';
                $field['meta_key'] = 'wpuf_social_instagram';
            }

            if ( $template === 'linkedin_url' ) {
                $field['name'] = 'wpuf_social_linkedin';
                $field['meta_key'] = 'wpuf_social_linkedin';
            }

            // CRITICAL FIX: For profile_photo, ALWAYS ensure name and meta_key are hardcoded
            if ( $template === 'profile_photo' ) {
                $field['name'] = 'wpuf_profile_photo';
                $field['meta_key'] = 'wpuf_profile_photo';
            }

            // CRITICAL FIX: For secondary_email, ALWAYS ensure name and meta_key are hardcoded
            // @since 4.2.6
            if ( 'secondary_email' === $template ) {
                $field['name'] = 'wpuf_secondary_email';
                $field['meta_key'] = 'wpuf_secondary_email';
            }

            // CRITICAL FIX: For shortcode template, ensure shortcode property always exists
            if ( 'shortcode' === $template && empty( $field['shortcode'] ) ) {
                $field['shortcode'] = '[your_shortcode]';
            }

            // CRITICAL FIX: For column_field, dynamically adjust inner_fields and inner_columns_size based on columns count
            if ( 'column_field' === $template && isset( $field['columns'] ) ) {
                $columns = intval( $field['columns'] );

                // Enforce max 3 columns limit - clamp between 1 and 3
                if ( $columns < 1 ) {
                    $columns = 1;
                } elseif ( $columns > 3 ) {
                    $columns = 3;
                }

                // Update the field's columns property to the validated value (as INTEGER to match normal builder)
                $field['columns'] = $columns;

                // Ensure min_column and max_column are always set (as INTEGERS to match normal builder)
                if ( ! isset( $field['min_column'] ) ) {
                    $field['min_column'] = 1;
                }
                if ( ! isset( $field['max_column'] ) ) {
                    $field['max_column'] = 3;
                }

                // Regenerate inner_fields and inner_columns_size based on validated columns count
                $inner_fields = [];
                $inner_columns_size = [];
                $column_width = ( 100 / $columns );

                for ( $i = 1; $i <= $columns; $i++ ) {
                    $column_key = 'column-' . $i;
                    $inner_fields[ $column_key ] = isset( $filtered_custom_props['inner_fields'][ $column_key ] )
                        ? $filtered_custom_props['inner_fields'][ $column_key ]
                        : [];
                    $inner_columns_size[ $column_key ] = isset( $filtered_custom_props['inner_columns_size'][ $column_key ] )
                        ? $filtered_custom_props['inner_columns_size'][ $column_key ]
                        : number_format( $column_width, 2, '.', '' ) . '%';
                }

                $field['inner_fields'] = $inner_fields;
                $field['inner_columns_size'] = $inner_columns_size;
            }

            // CRITICAL FIX: Enforce 'show_in_post' = 'yes' for meta fields generated by AI
            // This ensures that select/multiselect and other meta fields ALWAYS show their data in posts
            // Follows WPUF default behavior where get_field_props() returns 'show_in_post' => 'yes'
            // and Field_Contract settings have default => 'yes' (see Field_Contract.php:445)
            // Without this, AI-generated fields may not display data causing user confusion
            if ( isset( $field['is_meta'] ) && 'yes' === $field['is_meta'] ) {
                $field['show_in_post'] = 'yes';
            }

            // CRITICAL FIX: Enforce 'hide_field_label' = 'no' for fields that can show in posts
            // This ensures field labels are shown by default (matching WPUF default behavior)
            // All predefined fields have 'hide_field_label' => 'no' as default
            // Field_Contract settings have default => 'no' (see Field_Contract.php:459)
            // 'no' means "don't hide" = show the label (default behavior)
            if ( ! isset( $field['hide_field_label'] ) || empty( $field['hide_field_label'] ) || $field['hide_field_label'] === null ) {
                $field['hide_field_label'] = 'no';
            }

            // CRITICAL FIX: Enforce required properties for upload fields (avatar, profile_photo, image_upload, file_upload, featured_image)
            // These fields MUST have 'readonly' and 'show_icon' properties to function correctly
            // Force these properties even if AI or custom props don't include them
            $upload_fields = [ 'avatar', 'user_avatar', 'profile_photo', 'image_upload', 'file_upload', 'featured_image' ];
            if ( in_array( $template, $upload_fields, true ) || ( isset( $field['input_type'] ) && $field['input_type'] === 'image_upload' ) ) {
                if ( ! isset( $field['readonly'] ) ) {
                    $field['readonly'] = 'no';
                }
                if ( ! isset( $field['show_icon'] ) ) {
                    $field['show_icon'] = 'no';
                }
                if ( ! isset( $field['button_label'] ) || empty( $field['button_label'] ) ) {
                    $field['button_label'] = ( in_array( $template, [ 'file_upload' ], true ) ) ? 'Select File' : 'Select Image';
                }
                if ( ! isset( $field['max_size'] ) ) {
                    $field['max_size'] = '2048';
                }
                if ( ! isset( $field['count'] ) ) {
                    $field['count'] = '1';
                }
            }

            return $field;
        }

        return [];
    }

    /**
     * Get common properties
     */
    private static function get_common() {
        return [
            'wpuf_cond' => [
                'condition_status' => 'no',
                'cond_field' => [],
                'cond_operator' => [ '=' ],
                'cond_option' => [ '- Select -' ],
                'cond_logic' => 'all',
            ],
            'wpuf_visibility' => [
                'selected' => 'everyone',
                'choices' => [],
            ],
        ];
    }

    /**
     * Generate field name from label
     */
    private static function name( $label ) {
        // Convert to lowercase FIRST, then replace non-alphanumeric with underscore
        $name = strtolower( trim( $label ) );
        $name = preg_replace( '/[^a-z0-9]+/', '_', $name );
        $name = trim( $name, '_' ); // Remove leading/trailing underscores
        return $name;
    }

    // ========== POST FIELDS ==========

    private static function get_post_title_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'post_title',
            'id' => $field_id,
            'input_type' => 'post_title',
            'template' => 'post_title',
            'required' => 'yes',
            'label' => $label,
            'name' => 'post_title',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_post_content_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'post_content',
            'id' => $field_id,
            'input_type' => 'post_content',
            'template' => 'post_content',
            'required' => 'yes',
            'label' => $label,
            'name' => 'post_content',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'rows' => '5',
            'cols' => '25',
            'rich' => 'yes',
            'insert_image' => 'yes',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_post_excerpt_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'post_excerpt',
            'id' => $field_id,
            'input_type' => 'post_excerpt',
            'template' => 'post_excerpt',
            'required' => 'no',
            'label' => $label,
            'name' => 'post_excerpt',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'rows' => '5',
            'cols' => '25',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_post_tags_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'post_tags',
            'id' => $field_id,
            'input_type' => 'post_tags',
            'template' => 'post_tags',
            'required' => 'no',
            'label' => $label,
            'name' => 'post_tags',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_taxonomy_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'select',
            'id' => $field_id,
            'input_type' => 'taxonomy',
            'template' => 'taxonomy',
            'required' => 'no',
            'label' => $label,
            'name' => 'category',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'first' => '- Select -',
            'orderby' => 'name',
            'order' => 'ASC',
            'exclude_type' => 'exclude',
            'exclude' => [],
            'woo_attr' => 'no',
            'woo_attr_vis' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_featured_image_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'image_upload',
            'id' => $field_id,
            'input_type' => 'image_upload',
            'template' => 'featured_image',
            'required' => 'no',
            'label' => $label,
            'name' => 'featured_image',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'button_label' => 'Select Image',
            'max_size' => '2048',
            'count' => '1',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    // ========== BASIC FIELDS ==========

    private static function get_text_field_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'text',
            'id' => $field_id,
            'input_type' => 'text',
            'template' => 'text_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'show_in_post' => 'yes',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_email_address_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'email',
            'id' => $field_id,
            'input_type' => 'email',
            'template' => 'email_address',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'show_in_post' => 'yes',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_textarea_field_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'textarea',
            'id' => $field_id,
            'input_type' => 'textarea',
            'template' => 'textarea_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'rows' => '5',
            'show_in_post' => 'yes',
            'cols' => '25',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_website_url_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'url',
            'id' => $field_id,
            'input_type' => 'url',
            'template' => 'website_url',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => 'https://',
            'show_in_post' => 'yes',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_dropdown_field_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'select',
            'id' => $field_id,
            'input_type' => 'select',
            'template' => 'dropdown_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_in_post' => 'yes',
            'show_icon' => 'no',
            'first' => '- Select -',
            'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ],
            'selected' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_multiple_select_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'multiselect',
            'id' => $field_id,
            'input_type' => 'multiselect',
            'template' => 'multiple_select',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'show_in_post' => 'yes',
            'css' => '',
            'show_icon' => 'no',
            'first' => '- Select -',
            'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ],
            'selected' => [],
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_radio_field_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'radio',
            'id' => $field_id,
            'input_type' => 'radio',
            'template' => 'radio_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'show_in_post' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'inline' => 'no',
            'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ],
            'selected' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_checkbox_field_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'checkbox',
            'id' => $field_id,
            'input_type' => 'checkbox',
            'template' => 'checkbox_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'show_in_post' => 'yes',
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'inline' => 'no',
            'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ],
            'selected' => [],
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_image_upload_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'image_upload',
            'id' => $field_id,
            'input_type' => 'image_upload',
            'template' => 'image_upload',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_in_post' => 'yes',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'button_label' => 'Select Image',
            'max_size' => '2048',
            'count' => '1',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_recaptcha_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'recaptcha',
            'template' => 'recaptcha',
            'required' => 'no',
            'label' => $label,
            'name' => 'recaptcha',
            'is_meta' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_cloudflare_turnstile_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'cloudflare_turnstile',
            'template' => 'cloudflare_turnstile',
            'required' => 'no',
            'label' => $label,
            'name' => 'cloudflare_turnstile',
            'is_meta' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_custom_html_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'html',
            'template' => 'custom_html',
            'required' => 'no',
            'label' => $label,
            'name' => '',
            'is_meta' => 'no',
            'html' => '<p>Custom HTML content</p>',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_custom_hidden_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'hidden',
            'template' => 'custom_hidden_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'default' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_section_break_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'section_break',
            'template' => 'section_break',
            'required' => 'no',
            'label' => $label,
            'name' => '',
            'is_meta' => 'no',
            'description' => '',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_column_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'column_field',
            'template' => 'column_field',
            'required' => 'no',
            'label' => $label,
            'name' => '',
            'is_meta' => 'no',
            'columns' => 2,  // Integer to match normal builder
            'min_column' => 1,  // Integer to match normal builder
            'max_column' => 3,  // Integer to match normal builder
            'column_space' => '5',
            'inner_fields' => [ 'column-1' => [], 'column-2' => [] ],
            'inner_columns_size' => [ 'column-1' => '50%', 'column-2' => '50%' ],
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    // ========== PRO FIELDS ==========

    private static function get_file_upload_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'file_upload',
            'id' => $field_id,
            'input_type' => 'file_upload',
            'template' => 'file_upload',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'button_label' => 'Select File',
            'max_size' => '2048',
            'count' => '1',
            'extension' => [ 'images', 'pdf', 'office' ],
            'readonly' => 'no',
            'show_in_post' => 'yes',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_date_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'date_field',
            'template' => 'date_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'format' => 'mm/dd/yy',
            'show_in_post' => 'yes',
            'time' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_time_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'time_field',
            'template' => 'time_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'show_in_post' => 'yes',
            'css' => '',
            'time_format' => 'g:i a',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_numeric_text_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'numeric_text_field',
            'template' => 'numeric_text_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'show_icon' => 'no',
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'placeholder' => '',
            'default' => '',
            'show_in_post' => 'yes',
            'step_text_field' => '1',
            'min_value_field' => '0',
            'max_value_field' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_address_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'address_field',
            'template' => 'address_field',
            'show_in_post' => 'yes',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'width' => 'large',
            'address' => [
                'street_1' => [
                    'checked' => 'checked',
                    'type' => 'text',
                    'required' => '',
                    'label' => 'Street',
                    'value' => '',
                    'placeholder' => '',
                ],
                'city' => [
                    'checked' => 'checked',
                    'type' => 'text',
                    'required' => '',
                    'label' => 'City',
                    'value' => '',
                    'placeholder' => '',
                ],
                'zip' => [
                    'checked' => 'checked',
                    'type' => 'text',
                    'required' => '',
                    'label' => 'Zip Code',
                    'value' => '',
                    'placeholder' => '',
                ],
                'country_select' => [
                    'checked' => 'checked',
                    'type' => 'select',
                    'required' => '',
                    'label' => 'Country',
                    'value' => '',
                    'country_list_visibility_opt_name' => 'all',
                    'country_select_hide_list' => [],
                    'country_select_show_list' => [],
                ],
                'state' => [
                    'checked' => 'checked',
                    'type' => 'select',
                    'required' => '',
                    'label' => 'State',
                    'value' => '',
                    'placeholder' => '',
                ],
            ],
        ], self::get_common() );
    }

    private static function get_country_list_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'country_list_field',
            'template' => 'country_list_field',
            'required' => 'no',
            'label' => $label,
            'show_in_post' => 'yes',
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'first' => '- Select Country -',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_phone_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'phone_field',
            'template' => 'phone_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'show_icon' => 'no',
            'show_in_post' => 'yes',
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_repeat_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'repeat_field',
            'template' => 'repeat_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'show_in_post' => 'yes',
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'multiple' => '',
            'columns' => '1',
            'inner_fields' => [],
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_google_map_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'google_map',
            'id' => $field_id,
            'input_type' => 'google_map',
            'template' => 'google_map',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'zoom' => '12',
            'default_pos' => '40.7143528,-74.0059731',
            'directions' => false,
            'address' => 'no',
            'show_lat' => 'no',
            'show_in_post' => 'yes',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_shortcode_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'shortcode',
            'template' => 'shortcode',
            'required' => 'no',
            'label' => $label,
            'name' => '',
            'is_meta' => 'yes',
            'shortcode' => '', // Default empty - will be set by AI or custom props
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_action_hook_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'action_hook',
            'template' => 'action_hook',
            'required' => 'no',
            'label' => $label,
            'name' => '',
            'is_meta' => 'no',
            'hook_name' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_ratings_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'ratings',
            'template' => 'ratings',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'selected' => '',
            'options' => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ],
            'inline' => 'no',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_step_start_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'step_start',
            'template' => 'step_start',
            'required' => 'no',
            'label' => $label,
            'name' => '',
            'is_meta' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_toc_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'show_in_post' => 'yes',
            'input_type' => 'toc',
            'template' => 'toc',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_embed_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'embed',
            'template' => 'embed',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'preview_width' => '123',
            'preview_height' => '456',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_really_simple_captcha_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'really_simple_captcha',
            'template' => 'really_simple_captcha',
            'required' => 'no',
            'label' => $label,
            'name' => 'really_simple_captcha',
            'is_meta' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_math_captcha_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'math_captcha',
            'template' => 'math_captcha',
            'required' => 'no',
            'label' => $label,
            'name' => 'math_captcha',
            'is_meta' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    // ========== REGISTRATION/PROFILE FIELDS ==========

    private static function get_user_email_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'user_email',
            'id' => $field_id,
            'input_type' => 'user_email',
            'template' => 'user_email',
            'required' => 'no',
            'label' => $label,
            'name' => 'user_email',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    /**
     * Get secondary email field template
     *
     * @since 4.2.6
     *
     * @param string $label
     * @param int    $field_id
     *
     * @return array
     */
    private static function get_secondary_email_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'secondary_email',
            'id' => $field_id,
            'input_type' => 'secondary_email',
            'template' => 'secondary_email',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_secondary_email',
            'meta_key' => 'wpuf_secondary_email',
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'placeholder' => 'example@domain.com',
            'default' => '',
            'size' => '40',
            'read_only' => 'no',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_user_login_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'user_login',
            'id' => $field_id,
            'input_type' => 'user_login',
            'template' => 'user_login',
            'required' => 'no',
            'label' => $label,
            'name' => 'user_login',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_password_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'password',
            'id' => $field_id,
            'input_type' => 'password',
            'template' => 'password',
            'required' => 'no',
            'label' => $label,
            'name' => 'password',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'min_length' => '5',
            'repeat_pass' => 'yes',
            're_pass_label' => 'Confirm Password',
            'pass_strength' => 'yes',
            're_pass_placeholder' => '',
            'minimum_strength' => 'weak',
            're_pass_help' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_first_name_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'first_name',
            'id' => $field_id,
            'input_type' => 'first_name',
            'template' => 'first_name',
            'required' => 'no',
            'label' => $label,
            'name' => 'first_name',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_last_name_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'last_name',
            'id' => $field_id,
            'input_type' => 'last_name',
            'template' => 'last_name',
            'required' => 'no',
            'label' => $label,
            'name' => 'last_name',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_nickname_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'nickname',
            'id' => $field_id,
            'input_type' => 'nickname',
            'template' => 'nickname',
            'required' => 'no',
            'label' => $label,
            'name' => 'nickname',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_display_name_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'display_name',
            'id' => $field_id,
            'input_type' => 'display_name',
            'template' => 'display_name',
            'required' => 'no',
            'label' => $label,
            'name' => 'display_name',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_user_url_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'user_url',
            'id' => $field_id,
            'input_type' => 'user_url',
            'template' => 'user_url',
            'required' => 'no',
            'label' => $label,
            'name' => 'user_url',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => 'https://',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_biography_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'biography',
            'id' => $field_id,
            'input_type' => 'biography',
            'template' => 'biography',
            'required' => 'no',
            'label' => $label,
            'name' => 'description',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'rows' => '5',
            'cols' => '25',
            'readonly' => 'no',
            'word_restriction' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_user_bio_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'user_bio',
            'id' => $field_id,
            'input_type' => 'user_bio',
            'template' => 'user_bio',
            'required' => 'no',
            'label' => $label,
            'name' => 'description',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'rows' => '5',
            'cols' => '25',
            'readonly' => 'no',
            'word_restriction' => '',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_user_avatar_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'avatar',
            'id' => $field_id,
            'input_type' => 'image_upload',
            'template' => 'avatar',
            'required' => 'no',
            'label' => $label,
            'name' => 'avatar',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'button_label' => 'Select Image',
            'max_size' => '1024',
            'count' => '1',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_profile_photo_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'profile_photo',
            'id' => $field_id,
            'input_type' => 'profile_photo',
            'template' => 'profile_photo',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_profile_photo',
            'meta_key' => 'wpuf_profile_photo',
            'is_meta' => 'no',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'button_label' => 'Select Image',
            'max_size' => '2048',
            'count' => '1',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_gender_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'gender',
            'id' => $field_id,
            'input_type' => 'gender_field',
            'template' => 'gender_field',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_gender',
            'meta_key' => 'wpuf_gender',
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'placeholder' => '',
            'default' => '',
            'size' => '40',
            'options' => [
                'male' => 'Male',
                'female' => 'Female',
                'non_binary' => 'Non-binary',
                'prefer_not_say' => 'Prefer not to say',
            ],
            'first' => 'Select your gender',
            'selected' => '',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'readonly' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }


    // ========== SOCIAL MEDIA FIELDS ==========

    private static function get_facebook_url_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'facebook_url',
            'id' => $field_id,
            'input_type' => 'facebook_url',
            'template' => 'facebook_url',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_social_facebook',
            'meta_key' => 'wpuf_social_facebook',
            'is_meta' => 'yes',
            'help' => 'Enter your Facebook username or full URL',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => 'username',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'open_in_new_window' => 'yes',
            'nofollow' => 'yes',
            'username_validation' => 'strict',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_twitter_url_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'twitter_url',
            'id' => $field_id,
            'input_type' => 'twitter_url',
            'template' => 'twitter_url',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_social_twitter',
            'meta_key' => 'wpuf_social_twitter',
            'is_meta' => 'yes',
            'help' => 'Enter your X (Twitter) username (without @) or full URL',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => 'username',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'open_in_new_window' => 'yes',
            'nofollow' => 'yes',
            'username_validation' => 'strict',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_instagram_url_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'instagram_url',
            'id' => $field_id,
            'input_type' => 'instagram_url',
            'template' => 'instagram_url',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_social_instagram',
            'meta_key' => 'wpuf_social_instagram',
            'is_meta' => 'yes',
            'help' => 'Enter your Instagram username or full URL',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => 'username',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'open_in_new_window' => 'yes',
            'nofollow' => 'yes',
            'username_validation' => 'strict',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_linkedin_url_template( $label, $field_id ) {
        return array_merge( [
            'type' => 'linkedin_url',
            'id' => $field_id,
            'input_type' => 'linkedin_url',
            'template' => 'linkedin_url',
            'required' => 'no',
            'label' => $label,
            'name' => 'wpuf_social_linkedin',
            'meta_key' => 'wpuf_social_linkedin',
            'is_meta' => 'yes',
            'help' => 'Enter your LinkedIn username or full URL',
            'css' => '',
            'show_icon' => 'no',
            'placeholder' => 'john-doe',
            'default' => '',
            'size' => '40',
            'readonly' => 'no',
            'open_in_new_window' => 'yes',
            'nofollow' => 'yes',
            'username_validation' => 'strict',
            'width' => 'large',
        ], self::get_common() );
    }

    // ========== PRICING FIELDS ==========

    private static function get_pricing_radio_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'pricing_radio',
            'template' => 'pricing_radio',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'selected' => '',
            'inline' => 'no',
            'show_price_label' => 'yes',
            'enable_quantity' => 'no',
            'options' => [
                'first_item' => 'First Item',
                'second_item' => 'Second Item',
                'third_item' => 'Third Item',
            ],
            'prices' => [
                'first_item' => '10',
                'second_item' => '25',
                'third_item' => '50',
            ],
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_pricing_checkbox_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'pricing_checkbox',
            'template' => 'pricing_checkbox',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'selected' => [],
            'inline' => 'no',
            'show_price_label' => 'yes',
            'enable_quantity' => 'no',
            'options' => [
                'first_item' => 'First Item',
                'second_item' => 'Second Item',
                'third_item' => 'Third Item',
            ],
            'prices' => [
                'first_item' => '10',
                'second_item' => '25',
                'third_item' => '50',
            ],
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_pricing_dropdown_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'pricing_dropdown',
            'template' => 'pricing_dropdown',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'enable_quantity' => 'no',
            'options' => [
                'first_item' => 'First Item',
                'second_item' => 'Second Item',
                'third_item' => 'Third Item',
            ],
            'prices' => [
                'first_item' => '10',
                'second_item' => '25',
                'third_item' => '50',
            ],
            'first' => '- Select -',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_pricing_multiselect_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'pricing_multiselect',
            'template' => 'pricing_multiselect',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_icon' => 'no',
            'selected' => [],
            'enable_quantity' => 'no',
            'options' => [
                'first_item' => 'First Item',
                'second_item' => 'Second Item',
                'third_item' => 'Third Item',
            ],
            'prices' => [
                'first_item' => '10',
                'second_item' => '25',
                'third_item' => '50',
            ],
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_price_field_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'price_field',
            'template' => 'price_field',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'price_input_mode' => 'user_input',
            'price_hidden' => 'no',
            'price_min' => '0',
            'price_max' => '',
            'default' => '',
            'placeholder' => 'Enter amount',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }

    private static function get_cart_total_template( $label, $field_id ) {
        return array_merge( [
            'id' => $field_id,
            'input_type' => 'cart_total',
            'template' => 'cart_total',
            'required' => 'no',
            'label' => $label,
            'name' => self::name( $label ),
            'is_meta' => 'yes',
            'help' => '',
            'css' => '',
            'show_summary' => 'yes',
            'show_in_post' => 'yes',
            'hide_field_label' => 'no',
            'width' => 'large',
        ], self::get_common() );
    }
}


