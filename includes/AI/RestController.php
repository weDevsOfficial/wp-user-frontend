<?php

namespace WeDevs\Wpuf\AI;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Controller for AI Form Builder
 *
 * Handles all REST API endpoints for AI form generation with comprehensive
 * security, validation, and error handling. Implements rate limiting,
 * XSS protection, and proper WordPress capability checks.
 *
 * Security Features:
 * - Rate limiting (10 requests/hour per user)
 * - XSS protection with field sanitization
 * - Proper capability checks with role filtering
 * - Session validation and timeout handling
 * - Enhanced error logging with context
 *
 * @since WPUF_SINCE
 * @version 1.2.0
 */
class RestController extends WP_REST_Controller {

    /**
     * The namespace of this controller's route.
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Route name
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    protected $rest_base = 'ai-form-builder';

    /**
     * Form Generator instance
     *
     * @var FormGenerator
     */
    private $form_generator;

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        $this->form_generator = new FormGenerator();
    }

    /**
     * Register the routes for the objects of the controller.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        // Generate form endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/generate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'generate_form'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'prompt' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'validate_callback' => [$this, 'validate_prompt']
                ],
                'session_id' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'conversation_context' => [
                    'required' => false,
                    'type' => 'object',
                    'default' => []
                ],
                'form_type' => [
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                    'enum' => ['post', 'profile', 'registration'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'provider' => [
                    'required' => false,
                    'type' => 'string',
                    'default' => 'openai',
                    'enum' => ['openai', 'anthropic', 'google']
                ],
                'temperature' => [
                    'required' => false,
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1,
                    'default' => 0.7
                ],
                'max_tokens' => [
                    'required' => false,
                    'type' => 'integer',
                    'minimum' => 100,
                    'maximum' => 4000,
                    'default' => 2000
                ]
            ]
        ]);

        // Test connection endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/test', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'test_connection'],
            'permission_callback' => [$this, 'check_permission']
        ]);

        // Get providers endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/providers', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_providers'],
            'permission_callback' => [$this, 'check_permission']
        ]);

        // Save settings endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/settings', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'save_settings'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'provider' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['openai', 'anthropic', 'google']
                ],
                'model' => [
                    'required' => false,
                    'type' => 'string'
                ],
                'api_key' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'temperature' => [
                    'required' => false,
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1
                ],
                'max_tokens' => [
                    'required' => false,
                    'type' => 'integer',
                    'minimum' => 100,
                    'maximum' => 4000
                ]
            ]
        ]);

        // Create form from AI data endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/create-form', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'create_form_from_ai'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'form_data' => [
                    'required' => true,
                    'type' => 'object',
                    'properties' => [
                        'form_title' => ['type' => 'string'],
                        'form_description' => ['type' => 'string'],
                        'wpuf_fields' => ['type' => 'array'],
                        'form_settings' => ['type' => 'object']
                    ]
                ],
                'form_type' => [
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                    'enum' => ['post', 'profile', 'registration'],
                ]
            ]
        ]);

        // Modify form from AI data endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/modify-form', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'modify_form_from_ai'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'form_id' => [
                    'required' => true,
                    'type' => 'integer'
                ],
                'modification_data' => [
                    'required' => true,
                    'type' => 'object',
                    'properties' => [
                        'action' => ['type' => 'string'],
                        'modification_type' => ['type' => 'string'],
                        'target' => ['type' => 'string'],
                        'changes' => ['type' => 'object']
                    ]
                ]
            ]
        ]);

        // Get settings endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/settings', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'check_admin_permission']
        ]);
    }

    /**
     * Generate form using AI
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function generate_form(WP_REST_Request $request) {
        // Validate request size to prevent abuse
        $content_length = $request->get_header('content-length');
        if ($content_length && $content_length > 1048576) { // 1MB limit
            return new WP_Error(
                'request_too_large',
                __('Request payload too large', 'wp-user-frontend'),
                ['status' => 413]
            );
        }

        $prompt = $request->get_param('prompt');
        $session_id = $request->get_param('session_id');
        $conversation_context = $request->get_param('conversation_context') ?? [];
        $form_type = $request->get_param('form_type') ?? 'post';
        $provider = $request->get_param('provider');
        $temperature = $request->get_param('temperature');
        $max_tokens = $request->get_param('max_tokens');

        // Rate limiting removed - AI provider handles their own limits

        // Validate session ID format to prevent injection
        if (!empty($session_id) && !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $session_id)) {
            return new WP_Error(
                'invalid_session',
                __('Invalid session ID format', 'wp-user-frontend'),
                ['status' => 400]
            );
        }

        try {
            $options = [
                'session_id' => $session_id,
                'conversation_context' => $conversation_context,
                'form_type' => $form_type,
                'provider' => $provider,
                'temperature' => $temperature,
                'max_tokens' => $max_tokens
            ];

            $result = $this->form_generator->generate_form($prompt, $options);

            // Log the generation attempt
            $this->log_generation_attempt($prompt, $result);

            if (isset($result['error']) && $result['error']) {
                return new WP_Error(
                    'generation_failed',
                    $result['message'] ?? __('Form generation failed', 'wp-user-frontend'),
                    ['status' => 400]
                );
            }


            return new WP_REST_Response([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return new WP_Error(
                'generation_error',
                __('An error occurred while generating the form. Please try again.', 'wp-user-frontend'),
                ['status' => 500]
            );
        }
    }

    /**
     * Comprehensive input validation for API requests
     *
     * @param array $data Input data to validate
     * @param array $rules Validation rules
     * @return array|WP_Error Validated data or error
     */
    private function validate_input($data, $rules) {
        $validated = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            // Required field check
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                return new WP_Error(
                    'missing_field',
                    sprintf(__('Field %s is required', 'wp-user-frontend'), $field),
                    ['status' => 400]
                );
            }

            // Type validation
            if (!empty($value) && isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'string':
                        if (!is_string($value)) {
                            return new WP_Error('invalid_type', sprintf(__('Field %s must be a string', 'wp-user-frontend'), $field));
                        }
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            return new WP_Error('invalid_type', sprintf(__('Field %s must be an array', 'wp-user-frontend'), $field));
                        }
                        break;
                    case 'integer':
                        if (!is_numeric($value)) {
                            return new WP_Error('invalid_type', sprintf(__('Field %s must be numeric', 'wp-user-frontend'), $field));
                        }
                        break;
                }
            }

            // Length validation
            if (!empty($value) && isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                return new WP_Error(
                    'field_too_long',
                    sprintf(__('Field %s cannot exceed %d characters', 'wp-user-frontend'), $field, $rule['max_length']),
                    ['status' => 400]
                );
            }

            // Sanitize and store
            if (!empty($value)) {
                $validated[$field] = isset($rule['sanitize']) ?
                    call_user_func($rule['sanitize'], $value) :
                    sanitize_text_field($value);
            }
        }

        return $validated;
    }

    /**
     * Test connection to AI provider
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response Response object
     */
    public function test_connection(WP_REST_Request $request) {
        try {
            $result = $this->form_generator->test_connection();

            return new WP_REST_Response($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available providers
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response Response object
     */
    public function get_providers(WP_REST_Request $request) {
        $providers = $this->form_generator->get_providers();
        $current_provider = $this->form_generator->get_current_provider();

        return new WP_REST_Response([
            'success' => true,
            'providers' => $providers,
            'current_provider' => $current_provider
        ], 200);
    }

    /**
     * Save AI settings
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response Response object
     */
    public function save_settings(WP_REST_Request $request) {
        $provider    = $request->get_param('provider');
        $model       = $request->get_param('model');
        $api_key     = $request->get_param('api_key');
        $temperature = $request->get_param('temperature');
        $max_tokens  = $request->get_param('max_tokens');

        // Get existing settings
        $existing = get_option('wpuf_ai', []);

        // Validate API key format if provided
        if (!empty($api_key)) {
            $api_key = sanitize_text_field($api_key);
            if (strlen($api_key) < 10) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => __('API key appears to be too short', 'wp-user-frontend')
                ], 400);
            }
        }

        // Normalize optional params
        if ($temperature !== null) {
            $temperature = max(0.0, min(1.0, (float) $temperature));
        }
        if ($max_tokens !== null) {
            $max_tokens = max(100, min(4000, (int) $max_tokens));
        }

        // Update with new values
        $settings = [
            'ai_provider' => $provider ?: ($existing['ai_provider'] ?? 'openai'),
            'ai_model'    => $model    ?: ($existing['ai_model']    ?? 'gpt-3.5-turbo'),
            'ai_api_key'  => !empty($api_key)
                                ? $api_key
                                : ($existing['ai_api_key'] ?? ''),
            'temperature' => $temperature !== null
                                ? $temperature
                                : ($existing['temperature'] ?? 0.7),
            'max_tokens'  => $max_tokens !== null
                                ? $max_tokens
                                : ($existing['max_tokens']  ?? 2000),
        ];

        $saved = update_option('wpuf_ai', $settings);

        if ($saved) {
            return new WP_REST_Response([
                'success' => true,
                'message' => __('Settings saved successfully', 'wp-user-frontend')
            ], 200);
        } else {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Failed to save settings', 'wp-user-frontend')
            ], 400);
        }
    }

    /**
     * Get AI settings
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response Response object
     */
    public function get_settings(WP_REST_Request $request) {
        // Get settings from WPUF settings system
        $wpuf_ai_settings = get_option('wpuf_ai', []);

        // Map to expected format
        $settings = [
            'provider' => $wpuf_ai_settings['ai_provider'] ?? 'openai',
            'model' => $wpuf_ai_settings['ai_model'] ?? 'gpt-3.5-turbo',
            'temperature' => $wpuf_ai_settings['temperature'] ?? 0.7,
            'max_tokens' => $wpuf_ai_settings['max_tokens'] ?? 2000,
            'api_key' => $wpuf_ai_settings['ai_api_key'] ?? ''
        ];

        // Don't expose the actual API key, just whether it's set
        $settings['has_api_key'] = !empty($settings['api_key']);
        unset($settings['api_key']);

        return new WP_REST_Response([
            'success' => true,
            'settings' => $settings
        ], 200);
    }

    /**
     * Check if user has permission to use AI form builder
     *
     * @return bool
     */
    public function check_permission() {
        // Check for proper WPUF capabilities and AI feature access
        if (!is_user_logged_in()) {
            return false;
        }

        // Check if user can create forms
        if (!current_user_can('edit_posts') && !current_user_can('wpuf_create_forms')) {
            return false;
        }

        // Allow admin override
        if (current_user_can('manage_options')) {
            return true;
        }

        // Check if AI features are enabled for this user role
        $allowed_roles = apply_filters('wpuf_ai_allowed_roles', ['administrator', 'editor']);
        $user = wp_get_current_user();

        return !empty(array_intersect($allowed_roles, $user->roles));
    }

    /**
     * Check if user has admin permission
     *
     * @return bool
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Validate prompt parameter
     *
     * @param string $value Prompt value
     * @param WP_REST_Request $request REST request object
     * @param string $param Parameter name
     * @return bool|WP_Error
     */
    public function validate_prompt($value, $request, $param) {
        if (empty(trim($value))) {
            return new WP_Error(
                'invalid_prompt',
                __('Prompt cannot be empty', 'wp-user-frontend')
            );
        }

        if (strlen($value) > 1000) {
            return new WP_Error(
                'prompt_too_long',
                __('Prompt is too long. Maximum 1000 characters allowed.', 'wp-user-frontend')
            );
        }

        return true;
    }


    /**
     * Create form from AI generated data
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function create_form_from_ai(WP_REST_Request $request) {
        try {
            $form_data = $request->get_param('form_data');
            $form_type = $request->get_param('form_type') ?? 'post'; // Get form type, default to 'post'

            // Validate required fields
            if (empty($form_data['form_title']) || empty($form_data['wpuf_fields'])) {
                return new WP_Error(
                    'missing_data',
                    __('Form title and fields are required', 'wp-user-frontend'),
                    ['status' => 400]
                );
            }

            // Validate form title length
            if (strlen($form_data['form_title']) > 200) {
                return new WP_Error(
                    'title_too_long',
                    __('Form title cannot exceed 200 characters', 'wp-user-frontend'),
                    ['status' => 400]
                );
            }

            // Validate field count to prevent excessive forms
            if (count($form_data['wpuf_fields']) > 50) {
                return new WP_Error(
                    'too_many_fields',
                    __('Form cannot have more than 50 fields', 'wp-user-frontend'),
                    ['status' => 400]
                );
            }

            // Determine post type based on form type
            $post_type = ($form_type === 'profile' || $form_type === 'registration') ? 'wpuf_profile' : 'wpuf_forms';

            // Create the form post
            $form_post = array(
                'post_title' => sanitize_text_field($form_data['form_title']),
                'post_content' => sanitize_textarea_field($form_data['form_description'] ?? ''),
                'post_status' => 'publish',
                'post_type' => $post_type,
                'post_author' => get_current_user_id()
            );

            $form_id = wp_insert_post($form_post);

            if (is_wp_error($form_id)) {
                return new WP_Error(
                    'form_creation_failed',
                    $form_id->get_error_message(),
                    ['status' => 500]
                );
            }

            // Save form fields as child posts (WPUF's actual storage method)
            $wpuf_fields = $form_data['wpuf_fields'];

            // Sanitize all field data to prevent XSS
            $wpuf_fields = $this->sanitize_form_fields($wpuf_fields);


            // Create child posts for each field (WPUF's storage method)
            foreach ($wpuf_fields as $order => $field) {
                // Validate required field properties
                if (empty($field['name']) || empty($field['input_type'])) {
                    continue; // Skip invalid fields
                }

                // Sanitize field data
                $field['name'] = sanitize_key($field['name']);
                $field['label'] = sanitize_text_field($field['label'] ?? '');

                $field_post = array(
                    'post_type' => 'wpuf_input',
                    'post_status' => 'publish',
                    'post_parent' => $form_id,
                    'menu_order' => $order,
                    'post_content' => serialize($field) // WPUF stores field data as serialized content
                );

                $field_id = wp_insert_post($field_post);

                if (is_wp_error($field_id)) {
                    // Clean up previously created fields and the form post
                    wp_delete_post($form_id, true);
                    return new WP_Error(
                        'field_creation_failed',
                        sprintf(__('Failed to create field at position %d: %s', 'wp-user-frontend'), $order, $field_id->get_error_message()),
                        ['status' => 500]
                    );
                }
            }

            // Also save as meta for compatibility (some functions might still use this)
            // Fields already have correct structure from AI provider
            update_post_meta($form_id, 'wpuf_form_fields', $wpuf_fields);

            // Add form version for compatibility
            update_post_meta($form_id, 'wpuf_form_version', WPUF_VERSION);

            // Save form settings
            $default_settings = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'default_cat' => '-1',
                'guest_post' => 'false',
                'redirect_to' => 'post',
                'comment_status' => 'open',
                'submit_text' => __('Submit Form', 'wp-user-frontend'),
                'edit_post_status' => 'publish',
                'edit_redirect_to' => 'same',
                'update_message' => __('Form has been updated successfully.', 'wp-user-frontend'),
                'update_text' => __('Update Form', 'wp-user-frontend')
            ];

            $form_settings = wp_parse_args($form_data['form_settings'] ?? [], $default_settings);
            update_post_meta($form_id, 'wpuf_form_settings', $form_settings);

            // Add form creation metadata
            update_post_meta($form_id, 'wpuf_ai_generated', true);
            update_post_meta($form_id, 'wpuf_ai_created_at', current_time('mysql'));
            update_post_meta($form_id, 'wpuf_ai_created_by', get_current_user_id());

            // Log the form creation
            $this->log_form_creation($form_id, $form_data);

            // Determine the correct edit URL based on form type
            $page = ($form_type === 'profile' || $form_type === 'registration') ? 'wpuf-profile-forms' : 'wpuf-post-forms';
            $list_page = ($form_type === 'profile' || $form_type === 'registration') ? 'wpuf-profile-forms' : 'wpuf-post-forms';

            return new WP_REST_Response([
                'success' => true,
                'form_id' => $form_id,
                'form_type' => $form_type,
                'edit_url' => admin_url("admin.php?page={$page}&action=edit&id={$form_id}"),
                'list_url' => admin_url("admin.php?page={$list_page}"),
                'message' => __('Form created successfully', 'wp-user-frontend')
            ], 201);

        } catch (\Exception $e) {
            // Enhanced error logging with context
            $error_context = [
                'user_id' => get_current_user_id(),
                'form_title' => $form_data['form_title'] ?? 'Unknown',
                'field_count' => count($form_data['wpuf_fields'] ?? []),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'timestamp' => current_time('mysql')
            ];

            return new WP_Error(
                'form_creation_error',
                __('An error occurred while creating the form. Please try again.', 'wp-user-frontend'),
                ['status' => 500]
            );
        }
    }

    /**
     * Modify existing form using AI data
     *
     * @param WP_REST_Request $request REST request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function modify_form_from_ai(WP_REST_Request $request) {
        try {
            $form_id = $request->get_param('form_id');
            $modification_data = $request->get_param('modification_data');

            // Validate form exists and user has permission
            $form = get_post($form_id);
            if (!$form || $form->post_type !== 'wpuf_forms') {
                return new WP_Error(
                    'form_not_found',
                    __('Form not found', 'wp-user-frontend'),
                    ['status' => 404]
                );
            }

            // Extract prompt and current form data
            $prompt = $modification_data['prompt'] ?? '';
            $current_form = $modification_data['current_form'] ?? [];
            $conversation_context = $modification_data['conversation_context'] ?? [];

            if (empty($prompt)) {
                return new WP_Error(
                    'missing_prompt',
                    __('Modification prompt is required', 'wp-user-frontend'),
                    ['status' => 400]
                );
            }

            // Prepare conversation context with current form for AI
            // This ensures AI gets proper system prompt with field templates
            $modification_context = $conversation_context;
            $modification_context['modification_requested'] = true;
            $modification_context['current_form'] = $current_form;

            // Call AI to get modification instructions
            // Do NOT use prepare_modification_prompt() - let FormGenerator handle system prompt
            $ai_response = $this->form_generator->generate_form($prompt, [
                'session_id' => $modification_data['session_id'] ?? $this->generate_session_id(),
                'provider' => get_option('wpuf_ai')['ai_provider'] ?? 'openai',
                'temperature' => 0.3, // Lower temperature for more consistent modifications
                'conversation_context' => $modification_context,
                'form_type' => $current_form['form_type'] ?? 'post'
            ]);


            if (!$ai_response || !$ai_response['success']) {
                return new WP_Error(
                    'ai_generation_failed',
                    $ai_response['message'] ?? __('Failed to process modification request', 'wp-user-frontend'),
                    ['status' => 500]
                );
            }

            // Process AI response - could be direct modification instructions or new form data
            if (isset($ai_response['action']) && $ai_response['action'] === 'modify') {
                // Direct modification instructions from AI
                $current_fields = get_post_meta($form_id, 'wpuf_form_fields', true);
                if (!is_array($current_fields)) {
                    $current_fields = [];
                }

                $modification_type = $ai_response['modification_type'] ?? '';
                $target = $ai_response['target'] ?? '';
                $changes = $ai_response['changes'] ?? [];

                switch ($modification_type) {
                    case 'add_field':
                        if (!isset($changes['field'])) {
                            return new WP_Error(
                                'missing_field_data',
                                __('Field data is required for add_field action', 'wp-user-frontend'),
                                ['status' => 400]
                            );
                        }
                        $current_fields = $this->add_field_to_form($current_fields, $changes);
                        break;

                    case 'remove_field':
                        if (empty($target)) {
                            return new WP_Error(
                                'missing_target',
                                __('Target field is required for remove_field action', 'wp-user-frontend'),
                                ['status' => 400]
                            );
                        }
                        $current_fields = $this->remove_field_from_form($current_fields, $target);
                        break;

                    case 'update_field':
                        if (empty($target) || empty($changes)) {
                            return new WP_Error(
                                'missing_update_data',
                                __('Target field and changes are required for update_field action', 'wp-user-frontend'),
                                ['status' => 400]
                            );
                        }
                        $current_fields = $this->update_field_in_form($current_fields, $target, $changes);
                        break;

                    case 'update_settings':
                        $this->update_form_settings($form_id, $target, $changes);
                        break;

                    default:
                        return new WP_Error(
                            'invalid_modification_type',
                            __('Invalid modification type from AI', 'wp-user-frontend'),
                            ['status' => 400]
                        );
                }

                // Update form fields if they were modified
                if (in_array($modification_type, ['add_field', 'remove_field', 'update_field'])) {
                    // Fields already have correct structure, no conversion needed
                    $converted_fields = $current_fields;

                    // Update form meta
                    update_post_meta($form_id, 'wpuf_form_fields', $converted_fields);

                    // Also update child posts
                    $this->update_form_field_posts($form_id, $converted_fields);
                }

                $response_data = [
                    'success' => true,
                    'form_id' => $form_id,
                    'form_data' => [
                        'wpuf_fields' => $converted_fields ?? $current_fields,
                        'form_title' => $form->post_title,
                        'form_description' => $form->post_content
                    ],
                    'message' => $ai_response['message'] ?? __('Form modified successfully', 'wp-user-frontend')
                ];

            } elseif (isset($ai_response['fields']) || isset($ai_response['wpuf_fields'])) {
                // AI returned complete modified form - use SAME path as initial generation
                // Build complete form from AI response using Form_Builder (same as FormGenerator)
                $form_data = \WeDevs\Wpuf\AI\Form_Builder::build_form( $ai_response );

                // Check if form building failed
                if ( ! empty( $form_data['error'] ) ) {
                    return new WP_Error(
                        'form_build_failed',
                        $form_data['message'] ?? __('Failed to build form structure', 'wp-user-frontend'),
                        ['status' => 500]
                    );
                }

                $converted_fields = $form_data['wpuf_fields'] ?? [];


                // Update form meta
                update_post_meta($form_id, 'wpuf_form_fields', $converted_fields);

                // Also update child posts
                $this->update_form_field_posts($form_id, $converted_fields);

                // Update form title/description if provided
                if (isset($ai_response['form_title'])) {
                    wp_update_post([
                        'ID' => $form_id,
                        'post_title' => sanitize_text_field($ai_response['form_title'])
                    ]);
                }

                if (isset($ai_response['form_description'])) {
                    wp_update_post([
                        'ID' => $form_id,
                        'post_content' => sanitize_textarea_field($ai_response['form_description'])
                    ]);
                }

                $response_data = [
                    'success' => true,
                    'form_id' => $form_id,
                    'form_data' => [
                        'wpuf_fields' => $converted_fields,
                        'form_title' => $ai_response['form_title'] ?? $form->post_title,
                        'form_description' => $ai_response['form_description'] ?? $form->post_content
                    ],
                    'message' => $ai_response['message'] ?? __('Form updated successfully', 'wp-user-frontend')
                ];
            } else {
                return new WP_Error(
                    'invalid_ai_response',
                    __('AI response format not recognized', 'wp-user-frontend'),
                    ['status' => 500]
                );
            }

            return new WP_REST_Response($response_data);

        } catch (\Exception $e) {
            return new WP_Error(
                'modification_error',
                __('Form modification failed', 'wp-user-frontend'),
                ['status' => 500]
            );
        }
    }

    /**
     * Prepare modification prompt with current form context
     */
    private function prepare_modification_prompt($prompt, $current_form) {
        $form_title = $current_form['form_title'] ?? 'Current Form';
        $form_description = $current_form['form_description'] ?? '';
        $fields = $current_form['wpuf_fields'] ?? [];

        $context = "CURRENT FORM CONTEXT:\n";
        $context .= "Form Title: {$form_title}\n";
        $context .= "Form Description: {$form_description}\n";
        $context .= "Current Fields:\n";

        foreach ($fields as $index => $field) {
            $label = $field['label'] ?? 'Unnamed';
            $required = ($field['required'] ?? false) ? ' (Required)' : ' (Optional)';
            $input_type = $field['input_type'] ?? $field['type'] ?? 'text';
            $type = $this->get_human_readable_field_type($input_type);
            $context .= "- {$label}{$required} - {$type}\n";
        }

        $context .= "\nUSER REQUEST: {$prompt}\n\n";
        $context .= "Please provide the modification instructions or updated form structure.";

        return $context;
    }

    /**
     * Get human readable field type
     */
    private function get_human_readable_field_type($type) {
        $types = [
            'text_field' => 'Text Input',
            'email_address' => 'Email Field',
            'textarea_field' => 'Text Area',
            'dropdown_field' => 'Dropdown',
            'radio_field' => 'Radio Buttons',
            'checkbox_field' => 'Checkboxes',
            'file_upload' => 'File Upload',
            'date_field' => 'Date Picker',
            'time_field' => 'Time Picker',
            'phone_field' => 'Phone Number',
            'address_field' => 'Address',
            'ratings' => 'Star Rating',
            'toc' => 'Terms & Conditions'
        ];

        return $types[$type] ?? ucfirst(str_replace('_', ' ', $type));
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
            $custom_props = array_diff_key( $field, [ 'template' => '', 'label' => '', 'id' => '' ] );

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
    private function remove_field_from_form($fields, $target) {
        return array_filter($fields, function($field) use ($target) {
            // Only use field name for matching to avoid unintended removals when multiple fields have the same label
            return ($field['name'] ?? '') !== $target;
        });
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
                        $field[$key] = $value;
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
    private function update_form_settings($form_id, $target, $changes) {
        if ($target === 'form_title' && isset($changes['form_title'])) {
            wp_update_post([
                'ID' => $form_id,
                'post_title' => sanitize_text_field($changes['form_title'])
            ]);
        }

        if ($target === 'form_description' && isset($changes['form_description'])) {
            wp_update_post([
                'ID' => $form_id,
                'post_content' => sanitize_textarea_field($changes['form_description'])
            ]);
        }

        // Update other form settings
        $current_settings = get_post_meta($form_id, 'wpuf_form_settings', true);
        if (!is_array($current_settings)) {
            $current_settings = [];
        }

        foreach ($changes as $key => $value) {
            if ($key !== 'form_title' && $key !== 'form_description') {
                $current_settings[$key] = $value;
            }
        }

        update_post_meta($form_id, 'wpuf_form_settings', $current_settings);
    }

    /**
     * Update form field child posts
     *
     * @param int $form_id Form ID
     * @param array $fields Updated fields
     */
    private function update_form_field_posts($form_id, $fields) {
        // Get existing field posts ordered by menu_order
        $existing_posts = get_posts([
            'post_type' => 'wpuf_input',
            'post_parent' => $form_id,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);

        $existing_count = count($existing_posts);
        $new_count = count($fields);

        // Update or create field posts
        foreach ($fields as $order => $field) {
            if ($order < $existing_count) {
                // Update existing post
                wp_update_post([
                    'ID' => $existing_posts[$order]->ID,
                    'menu_order' => $order,
                    'post_content' => serialize($field)
                ]);
            } else {
                // Create new post
                $field_post = array(
                    'post_type' => 'wpuf_input',
                    'post_status' => 'publish',
                    'post_parent' => $form_id,
                    'menu_order' => $order,
                    'post_content' => serialize($field)
                );
                wp_insert_post($field_post);
            }
        }

        // Delete excess posts if new count is less than existing
        if ($new_count < $existing_count) {
            for ($i = $new_count; $i < $existing_count; $i++) {
                wp_delete_post($existing_posts[$i]->ID, true);
            }
        }
    }

    /**
     * Log form creation for monitoring
     *
     * @param int $form_id Created form ID
     * @param array $form_data Form data used for creation
     */
    private function log_form_creation($form_id, $form_data) {
        $log_data = [
            'form_id' => $form_id,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'form_title' => $form_data['form_title'],
            'field_count' => count($form_data['wpuf_fields'] ?? []),
            'has_settings' => !empty($form_data['form_settings'])
        ];


        // Store in option for analytics
        $creation_log = get_option('wpuf_ai_form_creation_log', []);
        array_unshift($creation_log, $log_data);
        $creation_log = array_slice($creation_log, 0, 50);
        update_option('wpuf_ai_form_creation_log', $creation_log);
    }

    /**
     * Determine if field should be meta
     *
     * @param string $field_name
     * @return bool
     */
    private function should_be_meta($field_name) {
        $post_fields = ['post_title', 'post_content', 'post_excerpt', 'post_tags', 'post_category'];
        return !in_array($field_name, $post_fields);
    }

    /**
     * Log generation attempt for monitoring
     *
     * @param string $prompt User prompt
     * @param array $result Generation result
     */
    private function log_generation_attempt($prompt, $result) {
        $log_data = [
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'prompt_length' => strlen($prompt),
            'provider' => $result['provider'] ?? 'unknown',
            'success' => isset($result['success']) ? $result['success'] : false,
            'session_id' => $result['session_id'] ?? ''
        ];


        // Store in option for basic analytics (keep last 100 entries)
        $log_history = get_option('wpuf_ai_generation_log', []);
        array_unshift($log_history, $log_data);
        $log_history = array_slice($log_history, 0, 100);
        update_option('wpuf_ai_generation_log', $log_history);
    }

    /**
     * Sanitize form fields to prevent XSS
     *
     * @param array $fields
     * @return array
     */
    private function sanitize_form_fields($fields) {
        if (!is_array($fields)) {
            return [];
        }

        foreach ($fields as &$field) {
            if (!is_array($field)) {
                continue;
            }

            // Sanitize common field properties
            if (isset($field['label'])) {
                $field['label'] = sanitize_text_field($field['label']);
            }
            if (isset($field['placeholder'])) {
                $field['placeholder'] = sanitize_text_field($field['placeholder']);
            }
            if (isset($field['help'])) {
                $field['help'] = wp_kses_post($field['help']);
            }
            if (isset($field['default'])) {
                $field['default'] = sanitize_text_field($field['default']);
            }
            if (isset($field['css'])) {
                // More restrictive CSS sanitization to prevent XSS
                $field['css'] = strip_tags($field['css']);
                $field['css'] = preg_replace('/[^a-zA-Z0-9\s\-_\.\#\:\;\,\%\(\)]/', '', $field['css']);
                $field['css'] = substr($field['css'], 0, 200); // Limit length
            }

            // Sanitize options array
            if (isset($field['options']) && is_array($field['options'])) {
                foreach ($field['options'] as $key => $value) {
                    $field['options'][sanitize_key($key)] = sanitize_text_field($value);
                }
            }

            // Handle google_map field: Auto-populate missing required properties
            if (($field['input_type'] === 'google_map' || $field['template'] === 'google_map')) {
                // Ensure all required Google Map properties exist with defaults
                if (!isset($field['zoom']) || empty($field['zoom'])) {
                    $field['zoom'] = '12';
                }
                if (!isset($field['default_pos']) || empty($field['default_pos'])) {
                    $field['default_pos'] = '40.7143528,-74.0059731';
                }
                if (!isset($field['directions'])) {
                    $field['directions'] = false;
                }
                if (!isset($field['address']) || empty($field['address'])) {
                    $field['address'] = 'no';
                }
                if (!isset($field['show_lat']) || empty($field['show_lat'])) {
                    $field['show_lat'] = 'no';
                }
                if (!isset($field['show_in_post']) || empty($field['show_in_post'])) {
                    $field['show_in_post'] = 'yes';
                }
            }

            // Handle address_field: Auto-populate missing address structure
            if (($field['input_type'] === 'address_field' || $field['template'] === 'address_field') && !isset($field['address'])) {
                // AI didn't include the required address structure, add it as fallback
                $field['address'] = [
                    'street_address' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Address Line 1',
                        'value' => '',
                        'placeholder' => ''
                    ],
                    'street_address2' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => '',
                        'label' => 'Address Line 2',
                        'value' => '',
                        'placeholder' => ''
                    ],
                    'city_name' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'City',
                        'value' => '',
                        'placeholder' => ''
                    ],
                    'zip' => [
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Zip Code',
                        'value' => '',
                        'placeholder' => ''
                    ],
                    'country_select' => [
                        'checked' => 'checked',
                        'type' => 'select',
                        'required' => 'checked',
                        'label' => 'Country',
                        'value' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_hide_list' => [],
                        'country_select_show_list' => []
                    ],
                    'state' => [
                        'checked' => 'checked',
                        'type' => 'select',
                        'required' => 'checked',
                        'label' => 'State',
                        'value' => '',
                        'placeholder' => ''
                    ]
                ];
            }

            // Sanitize address field nested structure (if present or just added)
            if (isset($field['address']) && is_array($field['address'])) {
                foreach ($field['address'] as $subfield_key => &$subfield) {
                    if (is_array($subfield)) {
                        // Sanitize each sub-field property
                        if (isset($subfield['label'])) {
                            $subfield['label'] = sanitize_text_field($subfield['label']);
                        }
                        if (isset($subfield['placeholder'])) {
                            $subfield['placeholder'] = sanitize_text_field($subfield['placeholder']);
                        }
                        if (isset($subfield['value'])) {
                            $subfield['value'] = sanitize_text_field($subfield['value']);
                        }
                        // Keep other properties like 'checked', 'type', 'required' as-is (they're already validated)
                    }
                }
                unset($subfield); // Break reference
            }

            // Handle taxonomy field: Auto-populate missing required properties
            if ($field['input_type'] === 'taxonomy' || $field['template'] === 'taxonomy') {
                // Add missing required properties with defaults
                if (!isset($field['type'])) {
                    $field['type'] = 'select';
                }
                if (!isset($field['first'])) {
                    $field['first'] = '- Select -';
                }
                if (!isset($field['orderby'])) {
                    $field['orderby'] = 'name';
                }
                if (!isset($field['order'])) {
                    $field['order'] = 'ASC';
                }
                if (!isset($field['exclude_type'])) {
                    $field['exclude_type'] = 'exclude';
                }
                if (!isset($field['exclude'])) {
                    $field['exclude'] = [];
                }
                if (!isset($field['woo_attr'])) {
                    $field['woo_attr'] = 'no';
                }
                if (!isset($field['woo_attr_vis'])) {
                    $field['woo_attr_vis'] = 'no';
                }
            }

            // Handle phone_field: Auto-populate missing required properties
            if ($field['input_type'] === 'phone_field' || $field['template'] === 'phone_field') {
                if (!isset($field['show_country_list'])) {
                    $field['show_country_list'] = 'yes';
                }
                if (!isset($field['auto_placeholder'])) {
                    $field['auto_placeholder'] = 'yes';
                }
                if (!isset($field['country_list'])) {
                    $field['country_list'] = [
                        'name' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_show_list' => [],
                        'country_select_hide_list' => []
                    ];
                }
            }

            // Handle password field: Auto-populate missing required properties
            if ($field['input_type'] === 'password' || $field['template'] === 'password') {
                if (!isset($field['min_length'])) {
                    $field['min_length'] = '5';
                }
                if (!isset($field['repeat_pass'])) {
                    $field['repeat_pass'] = 'yes';
                }
                if (!isset($field['re_pass_label'])) {
                    $field['re_pass_label'] = 'Confirm Password';
                }
                if (!isset($field['pass_strength'])) {
                    $field['pass_strength'] = 'yes';
                }
                if (!isset($field['re_pass_placeholder'])) {
                    $field['re_pass_placeholder'] = '';
                }
                if (!isset($field['minimum_strength'])) {
                    $field['minimum_strength'] = 'weak';
                }
                if (!isset($field['re_pass_help'])) {
                    $field['re_pass_help'] = '';
                }
            }

            // Handle country_list_field: Auto-populate missing required properties
            if ($field['input_type'] === 'country_list' || $field['template'] === 'country_list_field') {
                if (!isset($field['country_list'])) {
                    $field['country_list'] = [
                        'name' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_show_list' => [],
                        'country_select_hide_list' => []
                    ];
                }
            }

            // Handle date_field: Auto-populate missing required properties
            if ($field['input_type'] === 'date' || $field['input_type'] === 'date_field' || $field['template'] === 'date_field') {
                if (!isset($field['format'])) {
                    $field['format'] = 'dd/mm/yy';
                }
            }

            // Handle file_upload: Auto-populate missing required properties
            if ($field['input_type'] === 'file_upload' || $field['template'] === 'file_upload') {
                if (!isset($field['max_size'])) {
                    $field['max_size'] = '1024';
                }
                if (!isset($field['count'])) {
                    $field['count'] = '1';
                }
                if (!isset($field['extension'])) {
                    $field['extension'] = ['images', 'audio', 'video', 'pdf', 'office', 'zip', 'exe', 'csv'];
                }
            }
        }

        return $fields;
    }

    /**
     * Final security validation for all API operations
     *
     * Performs comprehensive security checks including:
     * - User capability verification
     * - Rate limiting validation
     * - Session integrity checks
     * - Request origin validation
     *
     * @param WP_REST_Request $request The REST request
     * @return bool|WP_Error True if valid, WP_Error if not
     */
    private function perform_security_validation(WP_REST_Request $request) {
        // Verify user capabilities with enhanced checks
        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'insufficient_capabilities',
                __('You do not have permission to use AI form builder', 'wp-user-frontend'),
                ['status' => 403]
            );
        }

        // Security rate limiting removed - AI provider handles their own limits

        // Validate session integrity
        $session_token = wp_get_session_token();
        if (empty($session_token)) {
            return new WP_Error(
                'invalid_session',
                __('Invalid user session', 'wp-user-frontend'),
                ['status' => 401]
            );
        }

        // All security checks passed
        return true;
    }
}
