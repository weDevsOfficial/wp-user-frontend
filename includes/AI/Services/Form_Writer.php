<?php
/**
 * AI form builder: form writer service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\AI\Services;

use WeDevs\Wpuf\AI\FormGenerator;
use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;

/**
 * Writing the forms the AI builder produces: creating a form from the
 * generated data and applying AI modifications to one. Persists through the
 * form and field stores; takes plain arguments.
 *
 * @since WPUF_SINCE Moved out of AI\RestController.
 */
class Form_Writer {

    /**
     * The provider client.
     *
     * @var FormGenerator
     */
    protected $form_generator;

    /**
     * The form store.
     *
     * @var FormStore
     */
    protected $forms;

    /**
     * The field store.
     *
     * @var FieldStore
     */
    protected $fields;

    /**
     * @since WPUF_SINCE
     *
     * @param FormGenerator   $form_generator The provider client.
     * @param FormStore|null  $forms          The form store (container default).
     * @param FieldStore|null $fields         The field store (container default).
     */
    public function __construct( FormGenerator $form_generator, $forms = null, $fields = null ) {
        $this->form_generator = $form_generator;
        $this->forms          = $forms instanceof FormStore ? $forms : Stores::forms();
        $this->fields         = $fields instanceof FieldStore ? $fields : Stores::fields();
    }

    /**
     * Create a form from the generated data.
     *
     * @since WPUF_SINCE
     *
     * @param array  $form_data { form_title, form_description, wpuf_fields, form_settings }
     * @param string $form_type post | profile | registration
     *
     * @return array|WP_Error The created form (`form_id`, `form_type`, `form_data`, `edit_url`, `list_url`, `message`) or the error (status in its data)
     */
    public function create( array $form_data, $form_type = 'post' ) {
        try {
            $form_type = $form_type ? $form_type : 'post';

            // Validate required fields
            if ( empty( $form_data['form_title'] ) || empty( $form_data['wpuf_fields'] ) ) {
                return new WP_Error(
                    'missing_data',
                    __( 'Form title and fields are required', 'wp-user-frontend' ),
                    [ 'status' => 400 ]
                );
            }

            // Validate form title length
            if ( strlen( $form_data['form_title'] ) > 200 ) {
                return new WP_Error(
                    'title_too_long',
                    __( 'Form title cannot exceed 200 characters', 'wp-user-frontend' ),
                    [ 'status' => 400 ]
                );
            }

            // Validate field count to prevent excessive forms
            if ( count( $form_data['wpuf_fields'] ) > 50 ) {
                return new WP_Error(
                    'too_many_fields',
                    __( 'Form cannot have more than 50 fields', 'wp-user-frontend' ),
                    [ 'status' => 400 ]
                );
            }

            // Determine post type based on form type
            $post_type = ( $form_type === 'profile' || $form_type === 'registration' ) ? 'wpuf_profile' : 'wpuf_forms';

            // Validate and sanitize the fields before anything is stored; the
            // child posts get the registered input type, like a form built by hand.
            $wpuf_fields = $this->sanitize_form_fields( $form_data['wpuf_fields'] );
            $field_posts = [];

            foreach ( $wpuf_fields as $field ) {
                // Skip a field without the required properties.
                if ( empty( $field['name'] ) || empty( $field['input_type'] ) ) {
                    continue;
                }

                $field = $this->normalize_field_input_type( $field );

                // Reject a field whose input_type does not match its template
                // (the AI form-builder object-injection primitive).
                if ( ! $this->is_valid_field_definition( $field ) ) {
                    return new WP_Error(
                        'invalid_field_definition',
                        __( 'A submitted field has a template and input type that do not match.', 'wp-user-frontend' ),
                        [ 'status' => 400 ]
                    );
                }

                $field['name']  = sanitize_key( $field['name'] );
                $field['label'] = sanitize_text_field( isset( $field['label'] ) ? $field['label'] : '' );
                $field_posts[]  = $field;
            }

            $default_settings = [
                'post_type'        => 'post',
                'post_status'      => 'publish',
                'default_cat'      => '-1',
                'guest_post'       => 'false',
                'redirect_to'      => 'post',
                'comment_status'   => 'open',
                'submit_text'      => __( 'Submit Form', 'wp-user-frontend' ),
                'edit_post_status' => 'publish',
                'edit_redirect_to' => 'same',
                'update_message'   => __( 'Form has been updated successfully.', 'wp-user-frontend' ),
                'update_text'      => __( 'Update Form', 'wp-user-frontend' ),
            ];

            $form_settings = wp_parse_args( isset( $form_data['form_settings'] ) ? $form_data['form_settings'] : [], $default_settings );

            // AI forms are open to editors; the registration role is a site
            // decision, so only users who can promote users may pick one, and
            // never one that can take the site over.
            if ( isset( $form_settings['role'] ) ) {
                $role = sanitize_key( $form_settings['role'] );

                if ( ! current_user_can( 'promote_users' ) || ! wpuf_is_role_allowed_for_registration( $role ) ) {
                    unset( $form_settings['role'] );
                } else {
                    $form_settings['role'] = $role;
                }
            }

            // The form template of the post type lets the integrations save
            // their meta through their hooks (WooCommerce's price, ...).
            if ( ! empty( $form_settings['post_type'] ) ) {
                $form_template = $this->get_form_template_for_post_type( $form_settings['post_type'] );

                if ( $form_template ) {
                    $form_settings['form_template'] = $form_template;
                }
            }

            // Form post, field posts, settings, version and the AI meta through the store.
            $form_id = $this->forms->create(
                [
                    'post_title'     => sanitize_text_field( $form_data['form_title'] ),
                    'post_content'   => sanitize_textarea_field( isset( $form_data['form_description'] ) ? $form_data['form_description'] : '' ),
                    'post_status'    => 'publish',
                    'post_type'      => $post_type,
                    'post_author'    => get_current_user_id(),
                    'fields'         => $field_posts,
                    'unslash_fields' => false,
                    'settings'       => $form_settings,
                    'meta'           => [
                        'wpuf_form_fields'   => $wpuf_fields,
                        'wpuf_ai_generated'  => true,
                        'wpuf_ai_created_at' => current_time( 'mysql' ),
                        'wpuf_ai_created_by' => get_current_user_id(),
                    ],
                ]
            );

            if ( is_wp_error( $form_id ) ) {
                return new WP_Error(
                    'form_creation_failed',
                    $form_id->get_error_message(),
                    [ 'status' => 500 ]
                );
            }

            // Log the form creation
            $this->log_form_creation( $form_id, $form_data );

            // Determine the correct edit URL based on form type
            $page = ( $form_type === 'profile' || $form_type === 'registration' ) ? 'wpuf-profile-forms' : 'wpuf-post-forms';
            $list_page = ( $form_type === 'profile' || $form_type === 'registration' ) ? 'wpuf-profile-forms' : 'wpuf-post-forms';

            return [
                'success'   => true,
                'form_id'   => $form_id,
                'form_type' => $form_type,
                'form_data' => [
                    'wpuf_fields'      => $wpuf_fields,
                    'form_title'       => $form_data['form_title'],
                    'form_description' => isset( $form_data['form_description'] ) ? $form_data['form_description'] : '',
                ],
                'edit_url'  => admin_url( "admin.php?page={$page}&action=edit&id={$form_id}" ),
                'list_url'  => admin_url( "admin.php?page={$list_page}" ),
                'message'   => __( 'Form created successfully', 'wp-user-frontend' ),
            ];
        } catch ( \Exception $e ) {
            return new WP_Error(
                'form_creation_error',
                __( 'An error occurred while creating the form. Please try again.', 'wp-user-frontend' ),
                [ 'status' => 500 ]
            );
        }
    }

    /**
     * Apply an AI modification to a form: the provider answers with field
     * changes or a whole new form, both stored through the stores.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id           Form id (a post form)
     * @param array $modification_data { prompt, current_form, conversation_context, session_id }
     *
     * @return array|WP_Error The modified form (`form_id`, `form_data`, `message`) or the error (status in its data)
     */
    public function modify( $form_id, array $modification_data ) {
        try {
            $form_id = absint( $form_id );

            // Validate form exists and user has permission
            $form = get_post( $form_id );
            if ( ! $form || $form->post_type !== 'wpuf_forms' ) {
                return new WP_Error(
                    'form_not_found',
                    __( 'Form not found', 'wp-user-frontend' ),
                    [ 'status' => 404 ]
                );
            }

            // Extract prompt and current form data
            $prompt = $modification_data['prompt'] ?? '';
            $current_form = $modification_data['current_form'] ?? [];
            $conversation_context = $modification_data['conversation_context'] ?? [];

            if ( empty( $prompt ) ) {
                return new WP_Error(
                    'missing_prompt',
                    __( 'Modification prompt is required', 'wp-user-frontend' ),
                    [ 'status' => 400 ]
                );
            }

            // Prepare conversation context with current form for AI
            // This ensures AI gets proper system prompt with field templates
            $modification_context = $conversation_context;
            $modification_context['modification_requested'] = true;
            $modification_context['current_form'] = $current_form;

            // Call AI to get modification instructions
            // Do NOT use prepare_modification_prompt() - let FormGenerator handle system prompt
            $ai_response = $this->form_generator->generate_form(
                $prompt, [
                    'session_id' => $modification_data['session_id'] ?? $this->generate_session_id(),
                    'provider' => $this->stored_provider(),
                    'temperature' => 0.3, // Lower temperature for more consistent modifications
                    'conversation_context' => $modification_context,
                    'form_type' => $current_form['form_type'] ?? 'post',
                ]
            );

            if ( ! $ai_response || ! $ai_response['success'] ) {
                return new WP_Error(
                    'ai_generation_failed',
                    $ai_response['message'] ?? __( 'Failed to process modification request', 'wp-user-frontend' ),
                    [ 'status' => 500 ]
                );
            }

            // Process AI response - could be direct modification instructions or new form data
            if ( isset( $ai_response['action'] ) && $ai_response['action'] === 'modify' ) {
                // Direct modification instructions from AI
                $current_fields = $this->forms->read_meta( $form_id, 'wpuf_form_fields' );
                if ( ! is_array( $current_fields ) ) {
                    $current_fields = [];
                }

                $modification_type = $ai_response['modification_type'] ?? '';
                $target = $ai_response['target'] ?? '';
                $changes = $ai_response['changes'] ?? [];

                switch ( $modification_type ) {
                    case 'add_field':
                        if ( ! isset( $changes['field'] ) ) {
                            return new WP_Error(
                                'missing_field_data',
                                __( 'Field data is required for add_field action', 'wp-user-frontend' ),
                                [ 'status' => 400 ]
                            );
                        }
                        $current_fields = $this->add_field_to_form( $current_fields, $changes );
                        break;

                    case 'remove_field':
                        if ( empty( $target ) ) {
                            return new WP_Error(
                                'missing_target',
                                __( 'Target field is required for remove_field action', 'wp-user-frontend' ),
                                [ 'status' => 400 ]
                            );
                        }
                        $current_fields = $this->remove_field_from_form( $current_fields, $target );
                        break;

                    case 'update_field':
                        if ( empty( $target ) || empty( $changes ) ) {
                            return new WP_Error(
                                'missing_update_data',
                                __( 'Target field and changes are required for update_field action', 'wp-user-frontend' ),
                                [ 'status' => 400 ]
                            );
                        }
                        $current_fields = $this->update_field_in_form( $current_fields, $target, $changes );
                        break;

                    case 'update_settings':
                        $this->update_form_settings( $form_id, $target, $changes );
                        break;

                    default:
                        return new WP_Error(
                            'invalid_modification_type',
                            __( 'Invalid modification type from AI', 'wp-user-frontend' ),
                            [ 'status' => 400 ]
                        );
                }

                // Update form fields if they were modified
                if ( in_array( $modification_type, [ 'add_field', 'remove_field', 'update_field' ], true ) ) {
                    // Sanitize fields to ensure show_in_post and other properties are properly set
                    $converted_fields = $this->sanitize_form_fields( $current_fields );

                    $this->store_fields( $form_id, $converted_fields );
                }

                $response_data = [
                    'success' => true,
                    'form_id' => $form_id,
                    'form_data' => [
                        'wpuf_fields' => $converted_fields ?? $current_fields,
                        'form_title' => $form->post_title,
                        'form_description' => $form->post_content,
                    ],
                    'message' => $ai_response['message'] ?? __( 'Form modified successfully', 'wp-user-frontend' ),
                ];
            } elseif ( isset( $ai_response['fields'] ) || isset( $ai_response['wpuf_fields'] ) ) {
                // AI returned complete modified form - use SAME path as initial generation
                // Build complete form from AI response using Form_Builder (same as FormGenerator)
                $form_data = \WeDevs\Wpuf\AI\Form_Builder::build_form( $ai_response );

                // Check if form building failed
                if ( ! empty( $form_data['error'] ) ) {
                    return new WP_Error(
                        'form_build_failed',
                        $form_data['message'] ?? __( 'Failed to build form structure', 'wp-user-frontend' ),
                        [ 'status' => 500 ]
                    );
                }

                // Sanitize fields to ensure show_in_post and other properties are properly set
                $converted_fields = $this->sanitize_form_fields( $form_data['wpuf_fields'] ?? [] );

                $this->store_fields( $form_id, $converted_fields );

                if ( isset( $ai_response['form_title'] ) ) {
                    $this->forms->update( $form_id, [ 'post_title' => sanitize_text_field( $ai_response['form_title'] ) ] );
                }

                if ( isset( $ai_response['form_description'] ) ) {
                    $this->forms->update( $form_id, [ 'post_content' => sanitize_textarea_field( $ai_response['form_description'] ) ] );
                }

                $response_data = [
                    'success' => true,
                    'form_id' => $form_id,
                    'form_data' => [
                        'wpuf_fields' => $converted_fields,
                        'form_title' => $ai_response['form_title'] ?? $form->post_title,
                        'form_description' => $ai_response['form_description'] ?? $form->post_content,
                    ],
                    'message' => $ai_response['message'] ?? __( 'Form updated successfully', 'wp-user-frontend' ),
                ];
            } else {
                return new WP_Error(
                    'invalid_ai_response',
                    __( 'AI response format not recognized', 'wp-user-frontend' ),
                    [ 'status' => 500 ]
                );
            }

            return $response_data;
        } catch ( \Exception $e ) {
            return new WP_Error(
                'modification_error',
                __( 'Form modification failed', 'wp-user-frontend' ),
                [ 'status' => 500 ]
            );
        }
    }



    /**
     * Generate session ID
     */
    private function generate_session_id() {
        return 'wpuf_ai_session_' . time() . '_' . wp_generate_uuid4();
    }

    /**
     * Convert minimal field to complete structure
     *
     * @param array $field Field data
     * @param string $field_id Field ID
     * @return array Complete field structure
     */
    private function convert_field_to_complete( $field, $field_id ) {
        if ( ! isset( $field['template'] ) || ! isset( $field['label'] ) ) {
            return $field;
        }

        // AI always returns minimal fields - check if this field needs conversion to complete structure
        // The primary indicator is the absence of wpuf_cond (required for all complete WPUF fields)
        $needs_conversion = ! isset( $field['wpuf_cond'] );

        // Additional checks for template-specific required properties
        if ( ! $needs_conversion ) {
            $template = $field['template'];

            switch ( $template ) {
                case 'google_map':
                    // Google map requires zoom, default_pos, and other properties
                    if ( empty( $field['zoom'] ) || empty( $field['default_pos'] ) ) {
                        $needs_conversion = true;
                    }
                    break;

                case 'file_upload':
                case 'image_upload':
                    // File fields require count and max_size
                    if ( empty( $field['count'] ) || empty( $field['max_size'] ) ) {
                        $needs_conversion = true;
                    }
                    break;

                case 'repeat_field':
                case 'column_field':
                    // Layout fields require columns
                    if ( empty( $field['columns'] ) ) {
                        $needs_conversion = true;
                    }
                    break;
            }
        }

        if ( $needs_conversion ) {
            // Extract custom properties (everything except template, label, and id)
            $custom_props = array_diff_key(
                $field, [
                    'template' => '',
                    'label' => '',
                    'id' => '',
                ]
            );

            // Build complete field structure using Field_Templates
            // This ensures all required properties (zoom, default_pos, wpuf_cond, etc.) are added
            return \WeDevs\Wpuf\AI\Field_Templates::get_field_structure(
                $field['template'],
                $field['label'],
                $field_id,
                $custom_props
            );
        }

        // Already complete
        return $field;
    }

    /**
     * Add field to form
     *
     * @param array $fields Current fields
     * @param array $changes Changes containing new field
     * @return array Updated fields
     */
    private function add_field_to_form( $fields, $changes ) {
        if ( isset( $changes['field'] ) ) {
            $field_id = 'field_' . ( count( $fields ) + 1 );
            $complete_field = $this->convert_field_to_complete( $changes['field'], $field_id );

            if ( ! empty( $complete_field ) ) {
                $fields[] = $complete_field;
            }
        }
        return $fields;
    }

    /**
     * Remove field from form
     *
     * @param array $fields Current fields
     * @param string $target Field name to remove
     * @return array Updated fields
     */
    private function remove_field_from_form( $fields, $target ) {
        return array_filter(
            $fields, function ( $field ) use ( $target ) {
                // Only use field name for matching to avoid unintended removals when multiple fields have the same label
                return ( $field['name'] ?? '' ) !== $target;
            }
        );
    }

    /**
     * Update field in form
     *
     * @param array $fields Current fields
     * @param string $target Field name to update
     * @param array $changes Changes to apply
     * @return array Updated fields
     */
    private function update_field_in_form( $fields, $target, $changes ) {
        foreach ( $fields as $index => &$field ) {
            if ( ( $field['name'] ?? '' ) === $target || ( $field['label'] ?? '' ) === $target ) {
                if ( isset( $changes['field'] ) ) {
                    // Replace entire field - convert if minimal
                    $field_id = $field['id'] ?? 'field_' . ( $index + 1 );
                    $field = $this->convert_field_to_complete( $changes['field'], $field_id );
                } else {
                    // Apply individual property changes
                    foreach ( $changes as $key => $value ) {
                        $field[ $key ] = $value;
                    }
                }
                break;
            }
        }
        return $fields;
    }

    /**
     * Update form settings
     *
     * @param int $form_id Form ID
     * @param string $target Setting to update
     * @param array $changes Changes to apply
     */
    private function update_form_settings( $form_id, $target, $changes ) {
        if ( $target === 'form_title' && isset( $changes['form_title'] ) ) {
            $this->forms->update( $form_id, [ 'post_title' => sanitize_text_field( $changes['form_title'] ) ] );
        }

        if ( $target === 'form_description' && isset( $changes['form_description'] ) ) {
            $this->forms->update( $form_id, [ 'post_content' => sanitize_textarea_field( $changes['form_description'] ) ] );
        }

        // Update other form settings
        $current_settings = $this->forms->read_settings( $form_id );

        foreach ( $changes as $key => $value ) {
            if ( $key !== 'form_title' && $key !== 'form_description' ) {
                $current_settings[ $key ] = $value;
            }
        }

        // Set form_template based on post_type for proper integration handling
        if ( ! empty( $current_settings['post_type'] ) ) {
            $form_template = $this->get_form_template_for_post_type( $current_settings['post_type'] );

            if ( $form_template ) {
                $current_settings['form_template'] = $form_template;
            } else {
                // Clear stale form_template when post_type has no mapped template
                $current_settings['form_template'] = '';
            }
        }

        $this->forms->write_settings( $form_id, $current_settings );
    }

    /**
     * Build the allowed "template => input_type" map from the field registry.
     *
     * Derived from the canonical registry (free + pro via the `wpuf_form_fields`
     * filter) so a submitted field cannot pair a template with a mismatched
     * input_type — the primitive behind the AI form-builder object injection.
     *
     * @since 4.3.11
     *
     * @return array<string, string> Template slug => canonical input type.
     */
    private function get_allowed_field_type_map() {
        static $map = null;

        if ( null !== $map ) {
            return $map;
        }

        $map           = [];
        $field_manager = wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Forms\Field_Manager::class );

        foreach ( $field_manager->get_fields() as $template => $field_object ) {
            if ( is_object( $field_object ) && method_exists( $field_object, 'get_field_props' ) ) {
                $props = $field_object->get_field_props();

                if ( isset( $props['input_type'] ) ) {
                    $map[ $template ] = $props['input_type'];
                }
            }
        }

        return $map;
    }

    /**
     * Give an AI-built field the input_type its registered template uses.
     *
     * The AI field templates set input_type to the template name ("post_title",
     * "date_field"), while every registered field, and every form built in the form
     * builder, stores the field's own input type ("text", "date"). Without this the
     * template/input_type check rejects every AI form. Only that exact shape is
     * rewritten, so a genuinely mismatched pairing is still rejected.
     *
     * @since 4.3.13
     *
     * @param array $field Field definition.
     *
     * @return array
     */
    private function normalize_field_input_type( $field ) {
        if ( ! is_array( $field ) || empty( $field['template'] ) || empty( $field['input_type'] ) ) {
            return $field;
        }

        $allowed  = $this->get_allowed_field_type_map();
        $template = sanitize_key( $field['template'] );

        // Only the AI shape is rewritten: input_type spelled exactly as the template.
        // Any other pairing is left for is_valid_field_definition() to judge.
        if ( isset( $allowed[ $template ] ) && $template === $field['input_type'] ) {
            $field['input_type'] = $allowed[ $template ];
        }

        return $field;
    }

    /**
     * Whether a submitted field's input_type is consistent with its template.
     *
     * Rejects mismatched definitions (e.g. input_type "text" on a "section_break"
     * template) so an attacker cannot route a field through the wrong renderer.
     * Unknown templates pass through — the render layer no longer instantiates
     * objects, and unregistered templates are handled by their own renderers.
     *
     * @since 4.3.11
     *
     * @param array $field Field definition.
     *
     * @return bool
     */
    private function is_valid_field_definition( $field ) {
        if ( empty( $field['template'] ) || empty( $field['input_type'] ) ) {
            return true;
        }

        $allowed  = $this->get_allowed_field_type_map();
        $template = sanitize_key( $field['template'] );

        if ( ! isset( $allowed[ $template ] ) ) {
            return true;
        }

        return $allowed[ $template ] === $field['input_type'];
    }


    /**
     * Store a form's fields: the `wpuf_form_fields` meta and the field posts,
     * after dropping any field whose template / input_type pairing is invalid
     * (a mismatched, object-injection definition is never persisted).
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param array $fields  Fields in order
     *
     * @return void
     */
    private function store_fields( $form_id, $fields ) {
        $this->forms->write_meta( $form_id, [ 'wpuf_form_fields' => $fields ] );

        $fields = array_map( [ $this, 'normalize_field_input_type' ], $fields );
        $fields = array_values( array_filter( $fields, [ $this, 'is_valid_field_definition' ] ) );

        $this->fields->replace( $form_id, $fields );
    }

    /**
     * The stored provider id (`wpuf_ai`), openai by default.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function stored_provider() {
        $stored = Stores::settings()->read( 'wpuf_ai' );

        return is_array( $stored ) && ! empty( $stored['ai_provider'] ) ? $stored['ai_provider'] : 'openai';
    }

    /**
     * Log form creation for monitoring
     *
     * @param int $form_id Created form ID
     * @param array $form_data Form data used for creation
     */
    private function log_form_creation( $form_id, $form_data ) {
        $log_data = [
            'form_id' => $form_id,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time( 'mysql' ),
            'form_title' => $form_data['form_title'],
            'field_count' => count( $form_data['wpuf_fields'] ?? [] ),
            'has_settings' => ! empty( $form_data['form_settings'] ),
        ];

        // Store in option for analytics
        $creation_log = get_option( 'wpuf_ai_form_creation_log', [] );
        array_unshift( $creation_log, $log_data );
        $creation_log = array_slice( $creation_log, 0, 50 );
        update_option( 'wpuf_ai_form_creation_log', $creation_log );
    }


    /**
     * Get form template for a given post type
     *
     * Maps integration post types to their corresponding form templates.
     * This is critical for integrations to properly trigger their hooks
     * (e.g., WooCommerce's update_price() hook to set _price meta).
     *
     * @since 4.2.9
     *
     * @param string $post_type The post type
     *
     * @return string|null Form template identifier or null if no mapping exists
     */
    private function get_form_template_for_post_type( $post_type ) {
        $post_type_to_template_map = [
            'product'      => 'post_form_template_woocommerce',     // WooCommerce products
            'download'     => 'post_form_template_edd',             // Easy Digital Downloads
            'tribe_events' => 'post_form_template_events_calendar', // The Events Calendar
        ];

        return $post_type_to_template_map[ $post_type ] ?? null;
    }

    /**
     * Sanitize form fields to prevent XSS
     *
     * @param array $fields
     * @return array
     */
    private function sanitize_form_fields( $fields ) {
        if ( ! is_array( $fields ) ) {
            return [];
        }

        foreach ( $fields as &$field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            // Sanitize common field properties
            if ( isset( $field['label'] ) ) {
                $field['label'] = sanitize_text_field( $field['label'] );
            }
            if ( isset( $field['placeholder'] ) ) {
                $field['placeholder'] = sanitize_text_field( $field['placeholder'] );
            }

            // CRITICAL FIX: Map 'help_text' to 'help' if needed (standardize property name)
            // WPUF uses 'help' as the standard property name, but AI might generate 'help_text'
            if ( isset( $field['help_text'] ) && ! isset( $field['help'] ) ) {
                $field['help'] = $field['help_text'];
            }

            if ( isset( $field['help'] ) ) {
                $field['help'] = wp_kses_post( $field['help'] );
            }
            if ( isset( $field['default'] ) ) {
                $field['default'] = sanitize_text_field( $field['default'] );
            }
            if ( isset( $field['css'] ) ) {
                // More restrictive CSS sanitization to prevent XSS
                $field['css'] = strip_tags( $field['css'] );
                $field['css'] = preg_replace( '/[^a-zA-Z0-9\s\-_\.\#\:\;\,\%\(\)]/', '', $field['css'] );
                $field['css'] = substr( $field['css'], 0, 200 ); // Limit length
            }

            // Sanitize options array
            if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
                foreach ( $field['options'] as $key => $value ) {
                    $field['options'][ sanitize_key( $key ) ] = sanitize_text_field( $value );
                }
            }

            // Handle google_map field: Auto-populate missing required properties
            if ( ( $field['input_type'] === 'google_map' || $field['template'] === 'google_map' ) ) {
                // Ensure all required Google Map properties exist with defaults
                if ( ! isset( $field['zoom'] ) || empty( $field['zoom'] ) ) {
                    $field['zoom'] = '12';
                }
                if ( ! isset( $field['default_pos'] ) || empty( $field['default_pos'] ) ) {
                    $field['default_pos'] = '40.7143528,-74.0059731';
                }
                if ( ! isset( $field['directions'] ) ) {
                    $field['directions'] = false;
                }
                if ( ! isset( $field['address'] ) || empty( $field['address'] ) ) {
                    $field['address'] = 'no';
                }
                if ( ! isset( $field['show_lat'] ) || empty( $field['show_lat'] ) ) {
                    $field['show_lat'] = 'no';
                }
                if ( ! isset( $field['show_in_post'] ) || empty( $field['show_in_post'] ) ) {
                    $field['show_in_post'] = 'yes';
                }
            }

            // CRITICAL FIX: Ensure 'show_in_post' is set for ALL meta fields
            // This is a safety net in case Field_Templates didn't set it (shouldn't happen but ensures consistency)
            // Matches WPUF default behavior where all meta fields show data in posts by default
            if ( isset( $field['is_meta'] ) && $field['is_meta'] === 'yes' ) {
                if ( ! isset( $field['show_in_post'] ) || empty( $field['show_in_post'] ) || $field['show_in_post'] === null ) {
                    $field['show_in_post'] = 'yes';
                }
            }

            // CRITICAL FIX: Ensure 'hide_field_label' is set to 'no' for ALL fields
            // This is a safety net to ensure field labels are shown by default
            // Matches WPUF default behavior where all fields show labels by default
            // 'no' means "don't hide" = show the label (default behavior)
            if ( ! isset( $field['hide_field_label'] ) || empty( $field['hide_field_label'] ) || $field['hide_field_label'] === null ) {
                $field['hide_field_label'] = 'no';
            }

            // CRITICAL FIX: Ensure 'button_label' is set for upload fields
            // Matches WPUF default behavior for image/file upload fields
            if ( ! isset( $field['button_label'] ) || empty( $field['button_label'] ) ) {
                $template = $field['template'] ?? '';
                $defaults = [
                    'image_upload' => 'Select Image',
                    'file_upload' => 'Select File',
                    'featured_image' => 'Select Image',
                    'avatar' => 'Select Image',
                    'user_avatar' => 'Select Image',
                    'profile_photo' => 'Select Image',
                ];
                if ( isset( $defaults[ $template ] ) ) {
                    $field['button_label'] = $defaults[ $template ];
                }
            }

            // CRITICAL ENFORCEMENT: Ensure column_field ALWAYS has min_column and max_column
            // This fixes old AI-generated forms that were created before these properties were added
            // Matches normal builder behavior where these are REQUIRED for slider to work
            if ( ( $field['template'] ?? '' ) === 'column_field' || ( $field['input_type'] ?? '' ) === 'column_field' ) {
                // Enforce columns as integer
                if ( isset( $field['columns'] ) ) {
                    $field['columns'] = intval( $field['columns'] );
                    // Clamp between 1 and 3
                    if ( $field['columns'] < 1 ) {
                        $field['columns'] = 1;
                    } elseif ( $field['columns'] > 3 ) {
                        $field['columns'] = 3;
                    }
                } else {
                    $field['columns'] = 2; // Default
                }

                // HARD ENFORCE: Always set min_column and max_column
                $field['min_column'] = 1;
                $field['max_column'] = 3;

                // Ensure column_space exists
                if ( ! isset( $field['column_space'] ) || $field['column_space'] === '' ) {
                    $field['column_space'] = '5';
                }

                // Ensure inner_fields and inner_columns_size are properly structured
                if ( ! isset( $field['inner_fields'] ) || ! is_array( $field['inner_fields'] ) ) {
                    $field['inner_fields'] = [];
                    for ( $i = 1; $i <= $field['columns']; $i++ ) {
                        $field['inner_fields'][ 'column-' . $i ] = [];
                    }
                }

                if ( ! isset( $field['inner_columns_size'] ) || ! is_array( $field['inner_columns_size'] ) ) {
                    $field['inner_columns_size'] = [];
                    $column_width = 100 / $field['columns'];
                    for ( $i = 1; $i <= $field['columns']; $i++ ) {
                        $field['inner_columns_size'][ "column-$i" ] = number_format( $column_width, 2, '.', '' ) . '%';
                    }
                }
            }

            // Handle address_field: Auto-populate missing address structure
            if ( ( $field['input_type'] === 'address_field' || $field['template'] === 'address_field' ) && ! isset( $field['address'] ) ) {
                // AI didn't include the required address structure, add it as fallback
                $field['address'] = [
                    'street_address' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Address Line 1',
                        'value' => '',
                        'placeholder' => '',
                    ],
                    'street_address2' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => '',
                        'label' => 'Address Line 2',
                        'value' => '',
                        'placeholder' => '',
                    ],
                    'city_name' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'City',
                        'value' => '',
                        'placeholder' => '',
                    ],
                    'zip' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Zip Code',
                        'value' => '',
                        'placeholder' => '',
                    ],
                    'country_select' => [
                        'checked' => 'checked',
                        'type' => 'select',
                        'required' => 'checked',
                        'label' => 'Country',
                        'value' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_hide_list' => [],
                        'country_select_show_list' => [],
                    ],
                    'state' => [
                        'checked' => 'checked',
                        'type' => 'select',
                        'required' => 'checked',
                        'label' => 'State',
                        'value' => '',
                        'placeholder' => '',
                    ],
                ];
            }

            // Sanitize address field nested structure (if present or just added)
            if ( isset( $field['address'] ) && is_array( $field['address'] ) ) {
                foreach ( $field['address'] as $subfield_key => &$subfield ) {
                    if ( is_array( $subfield ) ) {
                        // Sanitize each sub-field property
                        if ( isset( $subfield['label'] ) ) {
                            $subfield['label'] = sanitize_text_field( $subfield['label'] );
                        }
                        if ( isset( $subfield['placeholder'] ) ) {
                            $subfield['placeholder'] = sanitize_text_field( $subfield['placeholder'] );
                        }
                        if ( isset( $subfield['value'] ) ) {
                            $subfield['value'] = sanitize_text_field( $subfield['value'] );
                        }
                        // Keep other properties like 'checked', 'type', 'required' as-is (they're already validated)
                    }
                }
                unset( $subfield ); // Break reference
            }

            // Handle taxonomy field: Auto-populate missing required properties
            if ( $field['input_type'] === 'taxonomy' || $field['template'] === 'taxonomy' ) {
                // Add missing required properties with defaults
                if ( ! isset( $field['type'] ) ) {
                    $field['type'] = 'select';
                }
                if ( ! isset( $field['first'] ) ) {
                    $field['first'] = '- Select -';
                }
                if ( ! isset( $field['orderby'] ) ) {
                    $field['orderby'] = 'name';
                }
                if ( ! isset( $field['order'] ) ) {
                    $field['order'] = 'ASC';
                }
                if ( ! isset( $field['exclude_type'] ) ) {
                    $field['exclude_type'] = 'exclude';
                }
                if ( ! isset( $field['exclude'] ) ) {
                    $field['exclude'] = [];
                }
                if ( ! isset( $field['woo_attr'] ) ) {
                    $field['woo_attr'] = 'no';
                }
                if ( ! isset( $field['woo_attr_vis'] ) ) {
                    $field['woo_attr_vis'] = 'no';
                }
            }

            // Handle phone_field: Auto-populate missing required properties
            if ( $field['input_type'] === 'phone_field' || $field['template'] === 'phone_field' ) {
                if ( ! isset( $field['show_country_list'] ) ) {
                    $field['show_country_list'] = 'yes';
                }
                if ( ! isset( $field['auto_placeholder'] ) ) {
                    $field['auto_placeholder'] = 'yes';
                }
                if ( ! isset( $field['country_list'] ) ) {
                    $field['country_list'] = [
                        'name' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_show_list' => [],
                        'country_select_hide_list' => [],
                    ];
                }
            }

            // Handle password field: Auto-populate missing required properties
            if ( $field['input_type'] === 'password' || $field['template'] === 'password' ) {
                if ( ! isset( $field['min_length'] ) ) {
                    $field['min_length'] = '5';
                }
                if ( ! isset( $field['repeat_pass'] ) ) {
                    $field['repeat_pass'] = 'yes';
                }
                if ( ! isset( $field['re_pass_label'] ) ) {
                    $field['re_pass_label'] = 'Confirm Password';
                }
                if ( ! isset( $field['pass_strength'] ) ) {
                    $field['pass_strength'] = 'yes';
                }
                if ( ! isset( $field['re_pass_placeholder'] ) ) {
                    $field['re_pass_placeholder'] = '';
                }
                if ( ! isset( $field['minimum_strength'] ) ) {
                    $field['minimum_strength'] = 'weak';
                }
                if ( ! isset( $field['re_pass_help'] ) ) {
                    $field['re_pass_help'] = '';
                }
            }

            // Handle country_list_field: Auto-populate missing required properties
            if ( $field['input_type'] === 'country_list' || $field['template'] === 'country_list_field' ) {
                if ( ! isset( $field['country_list'] ) ) {
                    $field['country_list'] = [
                        'name' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_show_list' => [],
                        'country_select_hide_list' => [],
                    ];
                }
            }

            // Handle date_field: Auto-populate missing required properties
            if ( $field['input_type'] === 'date' || $field['input_type'] === 'date_field' || $field['template'] === 'date_field' ) {
                if ( ! isset( $field['format'] ) ) {
                    $field['format'] = 'dd/mm/yy';
                }
            }

            // Handle file_upload: Auto-populate missing required properties
            if ( $field['input_type'] === 'file_upload' || $field['template'] === 'file_upload' ) {
                if ( ! isset( $field['max_size'] ) ) {
                    $field['max_size'] = '1024';
                }
                if ( ! isset( $field['count'] ) ) {
                    $field['count'] = '1';
                }
                if ( ! isset( $field['extension'] ) ) {
                    $field['extension'] = [ 'images', 'audio', 'video', 'pdf', 'office', 'zip', 'csv' ];
                }
            }

            // Handle image_upload and other image upload variants: Auto-populate missing required properties
            if ( $field['input_type'] === 'image_upload' || $field['template'] === 'image_upload' ||
                $field['template'] === 'featured_image' || $field['template'] === 'avatar' ||
                $field['template'] === 'user_avatar' || $field['template'] === 'profile_photo' ) {
                if ( ! isset( $field['max_size'] ) ) {
                    $field['max_size'] = '2048';
                }
                if ( ! isset( $field['count'] ) ) {
                    $field['count'] = '1';
                }
                if ( ! isset( $field['readonly'] ) ) {
                    $field['readonly'] = 'no';
                }
                if ( ! isset( $field['show_icon'] ) ) {
                    $field['show_icon'] = 'no';
                }
            }

            // Handle embed: Auto-populate missing required properties
            if ( $field['input_type'] === 'embed' || $field['template'] === 'embed' ) {
                if ( ! isset( $field['preview_width'] ) ) {
                    $field['preview_width'] = '123';
                }
                if ( ! isset( $field['preview_height'] ) ) {
                    $field['preview_height'] = '456';
                }
            }

            // Handle ratings: Auto-populate missing required properties
            if ( $field['input_type'] === 'ratings' || $field['template'] === 'ratings' ) {
                if ( ! isset( $field['options'] ) || empty( $field['options'] ) ) {
                    $field['options'] = [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                    ];
                }
                if ( ! isset( $field['selected'] ) ) {
                    $field['selected'] = '';
                }
                if ( ! isset( $field['inline'] ) ) {
                    $field['inline'] = 'no';
                }
            }

            // Handle shortcode: Validate format and ensure square brackets
            if ( $field['input_type'] === 'shortcode' || $field['template'] === 'shortcode' ) {
                if ( isset( $field['shortcode'] ) && ! empty( $field['shortcode'] ) ) {
                    // Ensure shortcode has proper format with square brackets
                    $shortcode = trim( $field['shortcode'] );
                    if ( ! empty( $shortcode ) && $shortcode[0] !== '[' ) {
                        $field['shortcode'] = "[$shortcode]";
                    }
                } else {
                    // FALLBACK: If still empty after all processing, set a placeholder
                    $field['shortcode'] = '[your_shortcode]';
                }
            }

            // Handle column_field: Auto-populate missing required properties
            if ( $field['input_type'] === 'column_field' || $field['template'] === 'column_field' ) {
                if ( ! isset( $field['columns'] ) || empty( $field['columns'] ) ) {
                    $field['columns'] = '2';
                }
                if ( ! isset( $field['inner_fields'] ) || empty( $field['inner_fields'] ) ) {
                    $field['inner_fields'] = [
                        'column-1' => [],
                        'column-2' => [],
                    ];
                }
            }

            // Handle pricing fields: Auto-populate missing required properties
            if ( in_array( $field['input_type'], [ 'pricing_radio', 'pricing_checkbox', 'pricing_dropdown', 'pricing_multiselect' ], true ) ||
                in_array( $field['template'], [ 'pricing_radio', 'pricing_checkbox', 'pricing_dropdown', 'pricing_multiselect' ], true ) ) {
                if ( ! isset( $field['options'] ) || empty( $field['options'] ) ) {
                    $field['options'] = [
                        'first_item' => 'First Item',
                        'second_item' => 'Second Item',
                        'third_item' => 'Third Item',
                    ];
                }
                if ( ! isset( $field['prices'] ) || empty( $field['prices'] ) ) {
                    $field['prices'] = [
                        'first_item' => '10',
                        'second_item' => '25',
                        'third_item' => '50',
                    ];
                }
                if ( ! isset( $field['enable_quantity'] ) ) {
                    $field['enable_quantity'] = 'no';
                }
                // Pricing radio and checkbox specific
                if ( in_array( $field['template'], [ 'pricing_radio', 'pricing_checkbox' ], true ) ) {
                    if ( ! isset( $field['inline'] ) ) {
                        $field['inline'] = 'no';
                    }
                    if ( ! isset( $field['show_price_label'] ) ) {
                        $field['show_price_label'] = 'yes';
                    }
                }
                // Pricing dropdown specific
                if ( $field['template'] === 'pricing_dropdown' ) {
                    if ( ! isset( $field['first'] ) ) {
                        $field['first'] = '- Select -';
                    }
                }
            }

            // Handle cart_total: Auto-populate missing required properties
            if ( $field['input_type'] === 'cart_total' || $field['template'] === 'cart_total' ) {
                if ( ! isset( $field['show_summary'] ) ) {
                    $field['show_summary'] = 'yes';
                }
            }
        }

        return $fields;
    }
}
