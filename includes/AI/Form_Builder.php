<?php

namespace WeDevs\Wpuf\AI;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Form Builder Helper
 *
 * Processes minimal AI output and constructs complete WPUF form structures.
 *
 * @since WPUF_SINCE
 */
class Form_Builder {

    /**
     * Build complete form from minimal AI response
     *
     * @param array $ai_response Minimal response from AI
     * @return array Complete WPUF form structure
     */
    public static function build_form( $ai_response ) {
        if ( empty( $ai_response['fields'] ) || ! is_array( $ai_response['fields'] ) ) {
            return [
                'success' => false,
                'error'   => true,
                'message' => 'Invalid AI response: missing fields array',
            ];
        }

        $complete_fields = [];
        $field_counter = 1;

        foreach ( $ai_response['fields'] as $minimal_field ) {
            if ( empty( $minimal_field['template'] ) ) {
                continue;
            }

            $template = $minimal_field['template'];
            $label = $minimal_field['label'] ?? 'Untitled Field';
            $field_id = 'field_' . $field_counter;

            // Extract custom properties (everything except template and label)
            $raw_custom_props = array_diff_key( $minimal_field, [ 'template' => '', 'label' => '' ] );

            // Filter out null/empty values to prevent overriding template defaults
            // This is critical for fields like google_map that have automatic defaults
            $custom_props = [];
            foreach ( $raw_custom_props as $key => $value ) {
                // Only keep meaningful values (not null, not empty string, not empty array)
                if ( $value !== null && $value !== '' && $value !== [] ) {
                    $custom_props[$key] = $value;
                }
            }

            // Get complete field structure
            $complete_field = Field_Templates::get_field_structure( $template, $label, $field_id, $custom_props );

            if ( ! empty( $complete_field ) ) {
                $complete_fields[] = $complete_field;
                $field_counter++;
            }
        }

        return [
            'form_title'       => $ai_response['form_title'] ?? 'Untitled Form',
            'form_description' => $ai_response['form_description'] ?? '',
            'wpuf_fields'      => $complete_fields,
            'form_settings'    => self::get_default_settings(),
        ];
    }

    /**
     * Get default form settings
     *
     * @return array
     */
    private static function get_default_settings() {
        return [
            'submit_text'      => 'Submit',
            'draft_post'       => false,
            'enable_captcha'   => false,
            'guest_post'       => false,
            'message_restrict' => 'This form is restricted',
            'redirect_to'      => 'same',
            'comment_status'   => false,
            'default_cat'      => -1,
            'notification'     => [
                'new'         => 'on',
                'new_to'      => '{admin_email}',
                'new_subject' => 'New submission',
                'new_body'    => 'A new submission has been received',
            ],
        ];
    }

    /**
     * Extract minimal fields from complete form (for AI context)
     *
     * @param array $complete_form Complete WPUF form structure
     * @return array Minimal field definitions
     */
    public static function extract_minimal_fields( $complete_form ) {
        if ( empty( $complete_form['wpuf_fields'] ) ) {
            return [];
        }

        $minimal_fields = [];

        foreach ( $complete_form['wpuf_fields'] as $field ) {
            $minimal_field = [
                'template' => $field['template'] ?? '',
                'label'    => $field['label'] ?? '',
            ];

            // Add custom properties based on template
            $minimal_field = array_merge( $minimal_field, self::extract_custom_props( $field ) );

            $minimal_fields[] = $minimal_field;
        }

        return $minimal_fields;
    }

    /**
     * Extract custom properties from field
     *
     * @param array $field Complete field structure
     * @return array Custom properties
     */
    private static function extract_custom_props( $field ) {
        $custom_props = [];
        $template = $field['template'] ?? '';

        // Extract 'required' if it's 'yes'
        if ( ! empty( $field['required'] ) && 'yes' === $field['required'] ) {
            $custom_props['required'] = 'yes';
        }

        // Extract placeholder if present
        if ( ! empty( $field['placeholder'] ) ) {
            $custom_props['placeholder'] = $field['placeholder'];
        }

        // Extract help text if present
        if ( ! empty( $field['help'] ) ) {
            $custom_props['help'] = $field['help'];
        }

        switch ( $template ) {
            case 'dropdown_field':
            case 'multiple_select':
            case 'radio_field':
            case 'checkbox_field':
                if ( ! empty( $field['options'] ) ) {
                    $custom_props['options'] = $field['options'];
                }
                if ( ! empty( $field['inline'] ) ) {
                    $custom_props['inline'] = $field['inline'];
                }
                if ( ! empty( $field['show_icon'] ) ) {
                    $custom_props['show_icon'] = $field['show_icon'];
                }
                break;

            case 'image_upload':
            case 'file_upload':
            case 'featured_image':
            case 'user_avatar':
            case 'profile_photo':
                if ( ! empty( $field['count'] ) ) {
                    $custom_props['count'] = $field['count'];
                }
                if ( ! empty( $field['max_size'] ) ) {
                    $custom_props['max_size'] = $field['max_size'];
                }
                if ( ! empty( $field['button_label'] ) ) {
                    $custom_props['button_label'] = $field['button_label'];
                }
                break;

            case 'date_field':
                if ( ! empty( $field['format'] ) ) {
                    $custom_props['format'] = $field['format'];
                }
                if ( ! empty( $field['time'] ) ) {
                    $custom_props['time'] = $field['time'];
                }
                break;

            case 'time_field':
                if ( ! empty( $field['time_format'] ) ) {
                    $custom_props['time_format'] = $field['time_format'];
                }
                break;

            case 'numeric_text_field':
                if ( ! empty( $field['min_value_field'] ) ) {
                    $custom_props['min_value_field'] = $field['min_value_field'];
                }
                if ( ! empty( $field['max_value_field'] ) ) {
                    $custom_props['max_value_field'] = $field['max_value_field'];
                }
                if ( ! empty( $field['step_text_field'] ) ) {
                    $custom_props['step_text_field'] = $field['step_text_field'];
                }
                break;

            case 'taxonomy':
                if ( ! empty( $field['type'] ) ) {
                    $custom_props['type'] = $field['type'];
                }
                break;

            case 'text_field':
            case 'textarea_field':
            case 'biography':
                if ( ! empty( $field['size'] ) ) {
                    $custom_props['size'] = $field['size'];
                }
                if ( ! empty( $field['rows'] ) ) {
                    $custom_props['rows'] = $field['rows'];
                }
                if ( ! empty( $field['cols'] ) ) {
                    $custom_props['cols'] = $field['cols'];
                }
                break;

            case 'custom_html':
                if ( ! empty( $field['html'] ) ) {
                    $custom_props['html'] = $field['html'];
                }
                break;

            case 'shortcode':
                if ( ! empty( $field['shortcode'] ) ) {
                    $custom_props['shortcode'] = $field['shortcode'];
                }
                break;

            case 'action_hook':
                if ( ! empty( $field['hook_name'] ) ) {
                    $custom_props['hook_name'] = $field['hook_name'];
                }
                break;

            case 'repeat_field':
                if ( ! empty( $field['columns'] ) ) {
                    $custom_props['columns'] = $field['columns'];
                }
                if ( ! empty( $field['multiple'] ) ) {
                    $custom_props['multiple'] = $field['multiple'];
                }
                break;

            case 'column_field':
                if ( ! empty( $field['columns'] ) ) {
                    $custom_props['columns'] = $field['columns'];
                }
                break;

            case 'section_break':
                if ( ! empty( $field['description'] ) ) {
                    $custom_props['description'] = $field['description'];
                }
                break;

            case 'ratings':
                if ( ! empty( $field['options'] ) ) {
                    $custom_props['options'] = $field['options'];
                }
                if ( ! empty( $field['selected'] ) ) {
                    $custom_props['selected'] = $field['selected'];
                }
                if ( ! empty( $field['inline'] ) ) {
                    $custom_props['inline'] = $field['inline'];
                }
                if ( ! empty( $field['show_icon'] ) ) {
                    $custom_props['show_icon'] = $field['show_icon'];
                }
                break;

            case 'google_map':
                if ( ! empty( $field['zoom'] ) ) {
                    $custom_props['zoom'] = $field['zoom'];
                }
                if ( ! empty( $field['default_pos'] ) ) {
                    $custom_props['default_pos'] = $field['default_pos'];
                }
                if ( ! empty( $field['directions'] ) ) {
                    $custom_props['directions'] = $field['directions'];
                }
                if ( ! empty( $field['address'] ) ) {
                    $custom_props['address'] = $field['address'];
                }
                if ( ! empty( $field['show_lat'] ) ) {
                    $custom_props['show_lat'] = $field['show_lat'];
                }
                break;

            case 'embed':
                if ( ! empty( $field['preview_width'] ) ) {
                    $custom_props['preview_width'] = $field['preview_width'];
                }
                if ( ! empty( $field['preview_height'] ) ) {
                    $custom_props['preview_height'] = $field['preview_height'];
                }
                break;
        }

        return $custom_props;
    }
}

