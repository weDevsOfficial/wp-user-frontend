<?php

namespace WeDevs\Wpuf\AI;

/**
 * AI Form Generator Service
 *
 * Main service class that handles form generation using different AI providers
 * Uses WordPress native HTTP API for lightweight implementation
 *
 * @since WPUF_SINCE
 */
class FormGenerator {

    /**
     * Current AI provider
     *
     * @var string
     */
    private $current_provider;

    /**
     * Current AI model
     *
     * @var string
     */
    private $current_model;

    /**
     * API key for the current provider
     *
     * @var string
     */
    private $api_key;


    /**
     * Provider configurations
     *
     * @var array
     */
    private $provider_configs = [];

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        $this->init_provider_configs();
        $this->load_settings();
    }

    /**
     * Initialize provider configurations from centralized Config
     *
     * @since WPUF_SINCE
     */
    private function init_provider_configs() {
        // Get provider configurations from centralized Config class
        $this->provider_configs = Config::get_provider_configs();
    }

    /**
     * Load settings from WordPress options
     *
     * @since WPUF_SINCE
     */
    private function load_settings() {
        // Get settings from WPUF settings system
        $settings = get_option('wpuf_ai', []);

        // Get individual settings
        $this->current_provider = $settings['ai_provider'] ?? 'openai';
        $this->current_model = $settings['ai_model'] ?? 'gpt-3.5-turbo';

        // Get provider-specific API key
        $provider_key = $this->current_provider . '_api_key';
        $this->api_key = $settings[$provider_key] ?? '';
    }

    /**
     * Generate form based on prompt
     *
     * @since WPUF_SINCE
     *
     * @param string $prompt User prompt
     * @param array $options Additional options including conversation context
     * @return array Generated form data
     */
    public function generate_form($prompt, $options = []) {
        try {
            // Store original provider, model, and API key for restoration
            $original_provider = $this->current_provider;
            $original_model = $this->current_model;
            $original_api_key = $this->api_key;

            // Apply per-request overrides if provided
            if ( isset($options['provider']) && ! empty($options['provider']) ) {
                $this->current_provider = $options['provider'];

                // Update API key to match the new provider
                $settings = get_option('wpuf_ai', []);
                $provider_key = $this->current_provider . '_api_key';
                $this->api_key = $settings[$provider_key] ?? '';
            }
            if ( isset($options['model']) && ! empty($options['model']) ) {
                $this->current_model = $options['model'];
            }

            // All prompts now go through AI providers

            // Using direct API implementation for AI providers

            // Use manual AI provider implementation as fallback
            switch ($this->current_provider) {
                case 'openai':
                    $result = $this->generate_with_openai($prompt, $options);
                    break;

                case 'anthropic':
                    $result = $this->generate_with_anthropic($prompt, $options);
                    break;

                case 'google':
                    $result = $this->generate_with_google($prompt, $options);
                    break;

                default:
                    throw new \Exception('Unsupported AI provider: ' . $this->current_provider);
            }

            // Restore original provider, model, and API key
            $this->current_provider = $original_provider;
            $this->current_model = $original_model;
            $this->api_key = $original_api_key;

            return $result;

        } catch (\Exception $e) {
            // Ensure full restoration even on exception
            $this->current_provider = $original_provider ?? $this->current_provider;
            $this->current_model = $original_model ?? $this->current_model;
            $this->api_key = $original_api_key ?? $this->api_key;

            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
                'provider' => $this->current_provider
            ];
        }
    }

    /**
     * Get model-specific parameter configuration
     *
     * This centralizes all model-specific parameter mappings for easy maintenance.
     * Different AI providers and models have different parameter requirements:
     *
     * - Token parameters: 'max_tokens' vs 'max_completion_tokens' vs 'maxOutputTokens'
     * - Temperature: Some models only support default temperature (1.0)
     * - Response format: Some models don't support JSON mode
     *
     * @since WPUF_SINCE
     *
     * @param string $provider Provider name (openai, anthropic, google)
     * @param string $model Model name (e.g., gpt-5, claude-4.1-opus, gemini-2.5-pro)
     * @return array Model configuration with parameter restrictions and requirements
     */
    private function get_model_config($provider, $model) {
        // Get configuration from centralized Config class
        $config = Config::get_model_config($model);

        // Return config if found, otherwise use safe defaults
        if ($config !== null) {
            return $config;
        }

        // Fallback defaults for each provider if model not found
        $defaults = [
            'openai' => [
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'anthropic' => [
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'google' => [
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ]
        ];

        return $defaults[$provider] ?? $defaults['openai'];
    }


    /**
     * Generate form using OpenAI
     *
     * @since WPUF_SINCE
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_openai($prompt, $options = []) {
        $context = $options['conversation_context'] ?? [];
        $form_type = $options['form_type'] ?? 'post';
        $system_prompt = $this->get_system_prompt($context, $form_type);

        // Get model-specific configuration
        $model_config = $this->get_model_config('openai', $this->current_model);

        $body = [
            'model' => $this->current_model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        // Set temperature based on model capabilities
        if ($model_config['supports_custom_temperature']) {
            $body['temperature'] = floatval($options['temperature'] ?? 0.7);
        } else {
            // Use fixed temperature for models that don't support custom temperature
            $body['temperature'] = $model_config['temperature'] ?? 1.0;
        }

        // Set response format based on model capabilities
        if ($model_config['supports_json_mode']) {
            $body['response_format'] = ['type' => 'json_object'];
        } else {
            // For models that don't support JSON mode, add explicit instruction to system prompt
            $system_prompt .= "\n\nIMPORTANT: You must respond with ONLY valid JSON. Do not include any explanatory text, markdown formatting, or code blocks. Return ONLY the JSON object.";
            // Update the message payload with the modified system prompt
            $body['messages'][0]['content'] = $system_prompt;
        }

        // Set token parameter based on model
        if ($model_config['token_location'] === 'body') {
            // GPT-5 needs significantly more tokens for reasoning + output
            if (strpos($this->current_model, 'gpt-5') === 0) {
                $body[$model_config['token_param']] = intval($options['max_tokens'] ?? 65536);
            } else {
                $body[$model_config['token_param']] = intval($options['max_tokens'] ?? 2000);
            }
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 120
        ];

        $response = wp_safe_remote_request($this->provider_configs['openai']['endpoint'], $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();

            // Check for specific timeout errors
            if (strpos($error_message, 'timeout') !== false || strpos($error_message, 'timed out') !== false) {
                throw new \Exception('OpenAI API request timed out. Please try again later.');
            }

            throw new \Exception('OpenAI API request failed: ' . $error_message);
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            throw new \Exception("OpenAI API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate JSON response
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from AI provider: ' . json_last_error_msg());
        }

        if (isset($data['error'])) {
            throw new \Exception('OpenAI API Error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid OpenAI response format. Response: ' . json_encode($data));
        }

        $content = $data['choices'][0]['message']['content'];

        // Check for empty response
        if (empty($content)) {
            // Return error response instead of fallback
            return [
                'success' => false,
                'error' => true,
                'message' => 'AI model returned empty response. Please try again.',
                'provider' => 'openai',
                'model' => $this->current_model
            ];
        }

        // Clean and extract JSON from the response
        $json_content = trim($content);

        // Remove any markdown code blocks if present
        $json_content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json_content);

        // Remove any text before the first { or after the last }
        $json_content = preg_replace('/^[^{]*/', '', $json_content);
        $json_content = preg_replace('/[^}]*$/', '', $json_content);

        // Try to find the JSON object (handle nested braces properly)
        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Attempt to decode JSON
        $ai_response = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => true,
                'message' => 'Unable to generate form. Please try again or rephrase your request.',
                'provider' => 'openai',
                'model' => $this->current_model
            ];
        }

        // Check for error response from AI
        if ( ! empty( $ai_response['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => $ai_response['message'] ?? 'AI returned an error response',
                'provider' => 'openai',
                'model' => $this->current_model
            ];
        }

        // Build complete form from minimal AI response
        $form_data = Form_Builder::build_form( $ai_response );

        // Check if form building failed
        if ( ! empty( $form_data['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => $form_data['message'] ?? 'Failed to build form structure',
                'provider' => 'openai',
                'model' => $this->current_model
            ];
        }

        // Add metadata with better uniqueness
        $timestamp = microtime(true);
        $random = bin2hex(random_bytes(5));
        $form_data['session_id'] = $options['session_id'] ?? 'wpuf_ai_session_' . $timestamp . '_' . $random;
        $form_data['response_id'] = 'openai_resp_' . $timestamp . '_' . $random;
        $form_data['provider'] = 'openai';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time('mysql');
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Generate form using Anthropic Claude
     *
     * @since WPUF_SINCE
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_anthropic($prompt, $options = []) {
        $context = $options['conversation_context'] ?? [];
        $form_type = $options['form_type'] ?? 'post';
        $system_prompt = $this->get_system_prompt($context, $form_type);

        // Get model-specific configuration
        $model_config = $this->get_model_config('anthropic', $this->current_model);

        $body = [
            'model' => $this->current_model,
            'system' => $system_prompt,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        // Set temperature based on model capabilities
        if ($model_config['supports_custom_temperature']) {
            $body['temperature'] = floatval($options['temperature'] ?? 0.7);
        } else {
            // Use fixed temperature for models that don't support custom temperature
            $body['temperature'] = $model_config['temperature'] ?? 1.0;
        }

        // Set token parameter based on model
        if ($model_config['token_location'] === 'body') {
            // GPT-5 needs significantly more tokens for reasoning + output
            if (strpos($this->current_model, 'gpt-5') === 0) {
                $body[$model_config['token_param']] = intval($options['max_tokens'] ?? 65536);
            } else {
                $body[$model_config['token_param']] = intval($options['max_tokens'] ?? 2000);
            }
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 120
        ];

        $response = wp_safe_remote_request($this->provider_configs['anthropic']['endpoint'], $args);

        if (is_wp_error($response)) {
            throw new \Exception('Anthropic API request failed: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            throw new \Exception("Anthropic API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate JSON response
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Anthropic API: ' . json_last_error_msg());
        }

        if (isset($data['error'])) {
            throw new \Exception('Anthropic API Error: ' . $data['error']['message']);
        }

        if (!isset($data['content'][0]['text'])) {
            throw new \Exception('Invalid Anthropic response format');
        }

        $content = $data['content'][0]['text'];

        // Clean and extract JSON from the response (Claude may include explanatory text)
        $json_content = trim($content);
        $json_content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json_content);
        $json_content = preg_replace('/^[^{]*/', '', $json_content);
        $json_content = preg_replace('/[^}]*$/', '', $json_content);

        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Decode JSON
        $ai_response = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Unable to generate form. Please try again or rephrase your request.');
        }

        // Check for error response from AI
        if ( ! empty( $ai_response['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => $ai_response['message'] ?? 'AI returned an error response',
                'provider' => 'anthropic',
                'model' => $this->current_model
            ];
        }

        // Build complete form from minimal AI response
        $form_data = Form_Builder::build_form( $ai_response );

        // Check if form building failed
        if ( ! empty( $form_data['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => $form_data['message'] ?? 'Failed to build form structure',
                'provider' => 'anthropic',
                'model' => $this->current_model
            ];
        }

        // Add metadata
        $timestamp = microtime(true);
        $random = bin2hex(random_bytes(5));
        $form_data['session_id'] = $options['session_id'] ?? 'wpuf_ai_session_' . $timestamp . '_' . $random;
        $form_data['response_id'] = 'anthropic_resp_' . $timestamp . '_' . $random;
        $form_data['provider'] = 'anthropic';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time('mysql');
        $form_data['success'] = true;

        return $form_data;
    }


    /**
     * Generate form using Google Gemini
     *
     * @since WPUF_SINCE
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_google($prompt, $options = []) {
        $context = $options['conversation_context'] ?? [];
        $form_type = $options['form_type'] ?? 'post';
        $system_prompt = $this->get_system_prompt($context, $form_type);

        // Get model-specific configuration
        $model_config = $this->get_model_config('google', $this->current_model);

        // Build endpoint with model
        $endpoint = str_replace('{model}', $this->current_model, $this->provider_configs['google']['endpoint']);
        $endpoint .= '?key=' . $this->api_key;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $system_prompt . "\n\nUser request: " . $prompt]
                    ]
                ]
            ],
            'generationConfig' => []
        ];

        // Set temperature based on model capabilities
        if ($model_config['supports_custom_temperature']) {
            $body['generationConfig']['temperature'] = floatval($options['temperature'] ?? 0.7);
        } else {
            // Use fixed temperature for models that don't support custom temperature
            $body['generationConfig']['temperature'] = $model_config['temperature'] ?? 1.0;
        }

        // Set response format based on model capabilities
        if ($model_config['supports_json_mode']) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        // Set token parameter based on model
        if ($model_config['token_location'] === 'generationConfig') {
            $body['generationConfig'][$model_config['token_param']] = intval($options['max_tokens'] ?? 2000);
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 120
        ];

        $response = wp_safe_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            throw new \Exception('Google API request failed: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            throw new \Exception("Google API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate JSON response
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Google API: ' . json_last_error_msg());
        }

        if (isset($data['error'])) {
            throw new \Exception('Google API Error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid Google response format');
        }

        $content = $data['candidates'][0]['content']['parts'][0]['text'];

        // Clean and extract JSON from content
        $json_content = trim($content);

        // Remove any markdown code blocks if present
        $json_content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json_content);

        // Remove any text before the first { or after the last }
        $json_content = preg_replace('/^[^{]*/', '', $json_content);
        $json_content = preg_replace('/[^}]*$/', '', $json_content);

        // Try to find the JSON object (handle nested braces properly)
        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Attempt to decode JSON
        $ai_response = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Unable to generate form. Please try again or rephrase your request.');
        }

        // Check for error response from AI
        if ( ! empty( $ai_response['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => $ai_response['message'] ?? 'AI returned an error response',
                'provider' => 'google',
                'model' => $this->current_model
            ];
        }

        // Build complete form from minimal AI response
        $form_data = Form_Builder::build_form( $ai_response );

        // Check if form building failed
        if ( ! empty( $form_data['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => $form_data['message'] ?? 'Failed to build form structure',
                'provider' => 'google',
                'model' => $this->current_model
            ];
        }

        // Add metadata with better uniqueness
        $timestamp = microtime(true);
        $random = bin2hex(random_bytes(5));
        $form_data['session_id'] = $options['session_id'] ?? 'wpuf_ai_session_' . $timestamp . '_' . $random;
        $form_data['response_id'] = 'google_resp_' . $timestamp . '_' . $random;
        $form_data['provider'] = 'google';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time('mysql');
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Get system prompt for AI form generation
     *
     * @since WPUF_SINCE
     *
     * @param array $context Conversation context
     * @param string $form_type Form type ('post' or 'profile')
     * @return string System prompt
     */
    private function get_system_prompt($context = [], $form_type = 'post') {
        // Determine which prompt file to use based on form type
        if ( 'profile' === $form_type || 'registration' === $form_type ) {
            // Registration/Profile form prompt - USE MINIMAL REGISTRATION PROMPT
            $prompt_file = WPUF_ROOT . '/includes/AI/wpuf-ai-minimal-prompt-registration.md';
        } else {
            // Post form prompt - USE MINIMAL PROMPT
            $prompt_file = WPUF_ROOT . '/includes/AI/wpuf-ai-minimal-prompt.md';
        }

        // Check if file exists
        if ( ! file_exists( $prompt_file ) ) {
            throw new \Exception( 'System prompt file not found: ' . $prompt_file );
        }

        // Load the prompt file
        $system_prompt = file_get_contents( $prompt_file );

        // Add form type enforcement instruction
        $system_prompt .= "\n\n## FORM TYPE ENFORCEMENT\n";
        if ( 'profile' === $form_type || 'registration' === $form_type ) {
            $system_prompt .= "CRITICAL: You are in REGISTRATION/PROFILE form mode.\n";
            $system_prompt .= "- You can ONLY create registration/profile forms with user fields (user_email, user_login, password, first_name, last_name, biography, social fields, etc.)\n";
            $system_prompt .= "- You CANNOT create post forms with post fields (post_title, post_content, post_excerpt, featured_image, taxonomy, etc.)\n";
            $system_prompt .= "- If user asks for a post form, blog form, article form, or content submission form, return an error:\n";
            $system_prompt .= '  {"error": true, "message": "This is a registration form builder. To create post forms, please use the Post Form builder instead. Registration forms are for user sign-up and profile editing only."}' . "\n";
        } else {
            $system_prompt .= "CRITICAL: You are in POST form mode.\n";
            $system_prompt .= "- You can ONLY create post forms with post fields (post_title, post_content, post_excerpt, featured_image, taxonomy, etc.)\n";
            $system_prompt .= "- You CANNOT create registration/profile forms with user fields (user_email, user_login, password, first_name, etc.)\n";
            $system_prompt .= "- If user asks for a registration form, sign-up form, or user profile form, return an error:\n";
            $system_prompt .= '  {"error": true, "message": "This is a post form builder. To create registration forms, please use the Registration Form builder instead. Post forms are for content submission only."}' . "\n";
        }

        // Add conversation context if provided
        if ( ! empty( $context ) ) {
            $system_prompt .= "\n\n## CURRENT CONVERSATION CONTEXT\n";

            // Safely extract last user message
            $last_user_message = '';
            if ( isset( $context['chat_history'] ) && is_array( $context['chat_history'] ) && count( $context['chat_history'] ) > 0 ) {
                $last_message = end( $context['chat_history'] );
                $last_user_message = $last_message['content'] ?? '';
            }

            // Determine modification intent
            $modification_requested = false;
            if ( isset( $context['modification_requested'] ) ) {
                $modification_requested = (bool) $context['modification_requested'];
            } else {
                $modification_keywords = [ 'edit', 'modify', 'update', 'change', 'add', 'remove', 'delete', 'replace' ];
                foreach ( $modification_keywords as $keyword ) {
                    if ( false !== stripos( $last_user_message, $keyword ) ) {
                        $modification_requested = true;
                        break;
                    }
                }
            }

            // Build MINIMAL context - only send template + label, not full structures
            $context_for_ai = [
                'session_id'             => $context['session_id'] ?? '',
                'last_user_message'      => $last_user_message,
                'modification_requested' => $modification_requested,
                'form_type'              => $form_type, // Pass form type to AI for validation
            ];

            // Include MINIMAL current fields (template + label + custom props only)
            if ( ! empty( $context['current_form'] ) ) {
                $minimal_fields = Form_Builder::extract_minimal_fields( $context['current_form'] );
                if ( ! empty( $minimal_fields ) ) {
                    $context_for_ai['current_fields']     = $minimal_fields;
                    $context_for_ai['form_title']         = $context['current_form']['form_title'] ?? '';
                    $context_for_ai['form_description']   = $context['current_form']['form_description'] ?? '';
                }
            }

            $system_prompt .= json_encode( $context_for_ai, JSON_PRETTY_PRINT );

            // Add specific instruction for modifications
            $system_prompt .= "\n\n## MODIFICATION INSTRUCTION\n";
            if ( $modification_requested ) {
                $system_prompt .= "The user wants to MODIFY the existing form. You MUST:\n";
                $system_prompt .= "1. Return ALL existing fields from current_fields array (keep template + label + custom props)\n";
                $system_prompt .= "2. Apply the requested modification (add/remove/edit specific fields)\n";
                $system_prompt .= "3. Return COMPLETE field list with all fields in the 'fields' array\n";
                $system_prompt .= "4. Include form_title and form_description from context\n\n";
                $system_prompt .= "Example: If asked to 'add email field' and current_fields has 3 fields, return ALL 4 fields (existing 3 + new email field).\n";
            } else {
                $system_prompt .= "The user is asking a question. Return an error response with helpful message.";
            }
        }

        return $system_prompt;
    }


    /**
     * Get available providers
     *
     * @since WPUF_SINCE
     *
     * @return array Provider configurations
     */
    public function get_providers() {
        return $this->provider_configs;
    }

    /**
     * Get current provider
     *
     * @since WPUF_SINCE
     *
     * @return string Current provider
     */
    public function get_current_provider() {
        return $this->current_provider;
    }

    /**
     * Test connection to current provider
     *
     * @since WPUF_SINCE
     *
     * @return array Test result
     */
    public function test_connection() {
        try {
            $test_prompt = 'Generate a simple contact form with name and email fields.';
            $result = $this->generate_form($test_prompt, ['max_tokens' => 500]);

            if (isset($result['success']) && $result['success']) {
                return [
                    'success' => true,
                    'provider' => $this->current_provider,
                    'message' => 'Connection successful'
                ];
            } else {
                return [
                    'success' => false,
                    'provider' => $this->current_provider,
                    'message' => $result['message'] ?? 'Test failed'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'provider' => $this->current_provider,
                'message' => $e->getMessage()
            ];
        }
    }
}
