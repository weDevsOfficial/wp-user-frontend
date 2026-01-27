<?php

namespace WeDevs\Wpuf\AI;

/**
 * AI Form Generator Service
 *
 * Main service class that handles form generation using different AI providers
 * Uses WordPress native HTTP API for lightweight implementation
 *
 * @since 4.2.1
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
     * @since 4.2.1
     */
    public function __construct() {
        $this->init_provider_configs();
        $this->load_settings();
    }

    /**
     * Initialize provider configurations from centralized Config
     *
     * @since 4.2.1
     */
    private function init_provider_configs() {
        // Get provider configurations from centralized Config class
        $this->provider_configs = Config::get_provider_configs();
    }

    /**
     * Load settings from WordPress options
     *
     * @since 4.2.1
     */
    private function load_settings() {
        // Get settings from WPUF settings system
        $settings = get_option( 'wpuf_ai', [] );

        // Get individual settings
        $this->current_provider = isset( $settings['ai_provider'] ) ? $settings['ai_provider'] : 'openai';
        $this->current_model = isset( $settings['ai_model'] ) ? $settings['ai_model'] : 'gpt-3.5-turbo';

        // Get provider-specific API key
        $provider_key = $this->current_provider . '_api_key';
        $this->api_key = isset( $settings[ $provider_key ] ) ? $settings[ $provider_key ] : '';
    }

    /**
     * Generate form based on prompt
     *
     * @since 4.2.1
     *
     * @param string $prompt User prompt
     * @param array $options Additional options including conversation context
     * @return array Generated form data
     */
    public function generate_form( $prompt, $options = [] ) {
        try {
            // Store original provider, model, and API key for restoration
            $original_provider = $this->current_provider;
            $original_model = $this->current_model;
            $original_api_key = $this->api_key;

            // Load settings for defaults
            $settings = get_option( 'wpuf_ai', [] );

            // Apply per-request overrides if provided
            if ( isset( $options['provider'] ) && ! empty( $options['provider'] ) ) {
                $this->current_provider = $options['provider'];

                // Update API key to match the new provider
                $provider_key = $this->current_provider . '_api_key';
                $this->api_key = isset( $settings[ $provider_key ] ) ? $settings[ $provider_key ] : '';
            }
            if ( isset( $options['model'] ) && ! empty( $options['model'] ) ) {
                $this->current_model = $options['model'];
            }

            // Set default temperature from settings if not provided in options
            // Clamp to valid range 0.0–1.0 to prevent malformed values from leaking through
            if ( ! isset( $options['temperature'] ) ) {
                $raw_temp = isset( $settings['temperature'] ) ? floatval( $settings['temperature'] ) : 0.7;
                $options['temperature'] = min( max( $raw_temp, 0.0 ), 1.0 );
            }

            // All prompts now go through AI providers

            // Using direct API implementation for AI providers

            // Use manual AI provider implementation as fallback
            switch ( $this->current_provider ) {
                case 'openai':
                    $result = $this->generate_with_openai( $prompt, $options );
                    break;

                case 'anthropic':
                    $result = $this->generate_with_anthropic( $prompt, $options );
                    break;

                case 'google':
                    $result = $this->generate_with_google( $prompt, $options );
                    break;

                default:
                    throw new \Exception('Unsupported AI provider: ' . $this->current_provider);
            }

            // Restore original provider, model, and API key
            $this->current_provider = $original_provider;
            $this->current_model = $original_model;
            $this->api_key = $original_api_key;

            return $result;

        } catch ( \Exception $e ) {
            // Ensure full restoration even on exception
            $this->current_provider = isset( $original_provider ) ? $original_provider : $this->current_provider;
            $this->current_model = isset( $original_model ) ? $original_model : $this->current_model;
            $this->api_key = isset( $original_api_key ) ? $original_api_key : $this->api_key;

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
     * @since 4.2.1
     *
     * @param string $provider Provider name (openai, anthropic, google)
     * @param string $model Model name (e.g., gpt-5, claude-4.1-opus, gemini-2.5-pro)
     * @return array Model configuration with parameter restrictions and requirements
     */
    private function get_model_config( $provider, $model ) {
        // Get configuration from centralized Config class
        $config = Config::get_model_config( $model );

        // Return config if found, otherwise use safe defaults
        if ( $config !== null ) {
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

        return isset( $defaults[ $provider ] ) ? $defaults[ $provider ] : $defaults['openai'];
    }


    /**
     * Generate form using OpenAI
     *
     * @since 4.2.1
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_openai( $prompt, $options = [] ) {
        $context = isset( $options['conversation_context'] ) ? $options['conversation_context'] : [];
        $form_type = isset( $options['form_type'] ) ? $options['form_type'] : 'post';
        $language = isset( $options['language'] ) ? $options['language'] : 'English';

        // Add language to context for system prompt
        $context['language'] = $language;

        $system_prompt = $this->get_system_prompt( $context, $form_type );

        // Get model-specific configuration
        $model_config = $this->get_model_config( 'openai', $this->current_model );

        $body = [
            'model' => $this->current_model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt,
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ]
        ];

        // Set temperature based on model capabilities
        if ( $model_config['supports_custom_temperature'] ) {
            $body['temperature'] = floatval( isset( $options['temperature'] ) ? $options['temperature'] : 0.7 );
        } else {
            // Use fixed temperature for models that don't support custom temperature
            $body['temperature'] = isset( $model_config['temperature'] ) ? $model_config['temperature'] : 1.0;
        }

        // Set response format based on model capabilities
        if ( $model_config['supports_json_mode'] ) {
            $body['response_format'] = [
                'type' => 'json_object',
            ];
        } else {
            // For models that don't support JSON mode, add explicit instruction to system prompt
            $system_prompt .= "\n\nIMPORTANT: You must respond with ONLY valid JSON. Do not include any explanatory text, markdown formatting, or code blocks. Return ONLY the JSON object.";
            // Update the message payload with the modified system prompt
            $body['messages'][0]['content'] = $system_prompt;
        }

        // Set token parameter based on model
        if ( $model_config['token_location'] === 'body' ) {
            // GPT-5 needs significantly more tokens for reasoning + output
            if ( strpos( $this->current_model, 'gpt-5' ) === 0 ) {
                $body[ $model_config['token_param'] ] = intval( isset( $options['max_tokens'] ) ? $options['max_tokens'] : 65536 );
            } else {
                $body[ $model_config['token_param'] ] = intval( isset( $options['max_tokens'] ) ? $options['max_tokens'] : 2000 );
            }
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode( $body ),
            'timeout' => 120
        ];

        $response = wp_safe_remote_request( $this->provider_configs['openai']['endpoint'], $args );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();

            // Check for specific timeout errors
            if ( strpos( $error_message, 'timeout' ) !== false || strpos( $error_message, 'timed out' ) !== false ) {
                throw new \Exception( 'OpenAI API request timed out. Please try again later.' );
            }

            throw new \Exception( 'OpenAI API request failed: ' . $error_message );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            $error_body = wp_remote_retrieve_body( $response );
            throw new \Exception( "OpenAI API returned HTTP {$status_code}: {$error_body}" );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Validate JSON response
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new \Exception( 'Invalid JSON response from AI provider: ' . json_last_error_msg() );
        }

        if ( isset( $data['error'] ) ) {
            throw new \Exception( 'OpenAI API Error: ' . $data['error']['message'] );
        }

        if ( ! isset( $data['choices'][0]['message']['content'] ) ) {
            throw new \Exception( 'Invalid OpenAI response format. Response: ' . wp_json_encode( $data ) );
        }

        $content = $data['choices'][0]['message']['content'];

        // Check for empty response
        if ( empty( $content ) ) {
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
                'message' => isset( $ai_response['message'] ) ? $ai_response['message'] : 'AI returned an error response',
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
                'message' => isset( $form_data['message'] ) ? $form_data['message'] : 'Failed to build form structure',
                'provider' => 'openai',
                'model' => $this->current_model
            ];
        }

        // Add metadata with better uniqueness
        $timestamp = microtime( true );
        $random = bin2hex( random_bytes( 5 ) );
        $form_data['session_id'] = isset( $options['session_id'] ) ? $options['session_id'] : 'wpuf_ai_session_' . $timestamp . '_' . $random;
        $form_data['response_id'] = 'openai_resp_' . $timestamp . '_' . $random;
        $form_data['provider'] = 'openai';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time( 'mysql' );
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Generate form using Anthropic Claude
     *
     * @since 4.2.1
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_anthropic( $prompt, $options = [] ) {
        $context = isset( $options['conversation_context'] ) ? $options['conversation_context'] : [];
        $form_type = isset( $options['form_type'] ) ? $options['form_type'] : 'post';
        $language = isset( $options['language'] ) ? $options['language'] : 'English';

        // Add language to context for system prompt
        $context['language'] = $language;

        $system_prompt = $this->get_system_prompt( $context, $form_type );

        // Get model-specific configuration
        $model_config = $this->get_model_config( 'anthropic', $this->current_model );

        $body = [
            'model' => $this->current_model,
            'system' => $system_prompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ]
        ];

        // Set temperature based on model capabilities
        if ( $model_config['supports_custom_temperature'] ) {
            $body['temperature'] = floatval( isset( $options['temperature'] ) ? $options['temperature'] : 0.7 );
        } else {
            // Use fixed temperature for models that don't support custom temperature
            $body['temperature'] = isset( $model_config['temperature'] ) ? $model_config['temperature'] : 1.0;
        }

        // Set token parameter based on model
        if ( $model_config['token_location'] === 'body' ) {
            // GPT-5 needs significantly more tokens for reasoning + output
            if ( strpos( $this->current_model, 'gpt-5' ) === 0 ) {
                $body[ $model_config['token_param'] ] = intval( isset( $options['max_tokens'] ) ? $options['max_tokens'] : 65536 );
            } else {
                $body[ $model_config['token_param'] ] = intval( isset( $options['max_tokens'] ) ? $options['max_tokens'] : 2000 );
            }
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode( $body ),
            'timeout' => 120
        ];

        $response = wp_safe_remote_request( $this->provider_configs['anthropic']['endpoint'], $args );

        if ( is_wp_error( $response ) ) {
            throw new \Exception( 'Anthropic API request failed: ' . $response->get_error_message() );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            $error_body = wp_remote_retrieve_body( $response );
            throw new \Exception( "Anthropic API returned HTTP {$status_code}: {$error_body}" );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Validate JSON response
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new \Exception( 'Invalid JSON response from Anthropic API: ' . json_last_error_msg() );
        }

        if ( isset( $data['error'] ) ) {
            throw new \Exception( 'Anthropic API Error: ' . $data['error']['message'] );
        }

        if ( ! isset( $data['content'][0]['text'] ) ) {
            throw new \Exception( 'Invalid Anthropic response format' );
        }

        $content = $data['content'][0]['text'];

        // Clean and extract JSON from the response (Claude may include explanatory text)
        $json_content = trim($content);
        $json_content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json_content);
        $json_content = preg_replace('/^[^{]*/', '', $json_content);
        $json_content = preg_replace('/[^}]*$/', '', $json_content);

        $start = strpos( $json_content, '{' );
        $end = strrpos( $json_content, '}' );
        if ( $start !== false && $end !== false && $end > $start ) {
            $json_content = substr( $json_content, $start, $end - $start + 1 );
        }

        // Decode JSON
        $ai_response = json_decode( $json_content, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new \Exception( 'Unable to generate form. Please try again or rephrase your request.' );
        }

        // Check for error response from AI
        if ( ! empty( $ai_response['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => isset( $ai_response['message'] ) ? $ai_response['message'] : 'AI returned an error response',
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
                'message' => isset( $form_data['message'] ) ? $form_data['message'] : 'Failed to build form structure',
                'provider' => 'anthropic',
                'model' => $this->current_model
            ];
        }

        // Add metadata
        $timestamp = microtime( true );
        $random = bin2hex( random_bytes( 5 ) );
        $form_data['session_id'] = isset( $options['session_id'] ) ? $options['session_id'] : 'wpuf_ai_session_' . $timestamp . '_' . $random;
        $form_data['response_id'] = 'anthropic_resp_' . $timestamp . '_' . $random;
        $form_data['provider'] = 'anthropic';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time( 'mysql' );
        $form_data['success'] = true;

        return $form_data;
    }


    /**
     * Generate form using Google Gemini
     *
     * @since 4.2.1
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_google( $prompt, $options = [] ) {
        $context = isset( $options['conversation_context'] ) ? $options['conversation_context'] : [];
        $form_type = isset( $options['form_type'] ) ? $options['form_type'] : 'post';
        $language = isset( $options['language'] ) ? $options['language'] : 'English';

        // Add language to context for system prompt
        $context['language'] = $language;

        $system_prompt = $this->get_system_prompt( $context, $form_type );

        // Get model-specific configuration
        $model_config = $this->get_model_config( 'google', $this->current_model );

        // Build endpoint with model
        $endpoint = str_replace( '{model}', $this->current_model, $this->provider_configs['google']['endpoint'] );
        $endpoint .= '?key=' . $this->api_key;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $system_prompt . "\n\nUser request: " . $prompt,
                        ],
                    ],
                ],
            ],
            'generationConfig' => []
        ];

        // Set temperature based on model capabilities
        if ( $model_config['supports_custom_temperature'] ) {
            $body['generationConfig']['temperature'] = floatval( isset( $options['temperature'] ) ? $options['temperature'] : 0.7 );
        } else {
            // Use fixed temperature for models that don't support custom temperature
            $body['generationConfig']['temperature'] = isset( $model_config['temperature'] ) ? $model_config['temperature'] : 1.0;
        }

        // Set response format based on model capabilities
        if ( $model_config['supports_json_mode'] ) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        // Set token parameter based on model
        if ( $model_config['token_location'] === 'generationConfig' ) {
            $body['generationConfig'][ $model_config['token_param'] ] = intval( isset( $options['max_tokens'] ) ? $options['max_tokens'] : 2000 );
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode( $body ),
            'timeout' => 120
        ];

        $response = wp_safe_remote_request( $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            throw new \Exception( 'Google API request failed: ' . $response->get_error_message() );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            $error_body = wp_remote_retrieve_body( $response );
            throw new \Exception( "Google API returned HTTP {$status_code}: {$error_body}" );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Validate JSON response
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new \Exception( 'Invalid JSON response from Google API: ' . json_last_error_msg() );
        }

        if ( isset( $data['error'] ) ) {
            throw new \Exception( 'Google API Error: ' . ( isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown error' ) );
        }

        if ( ! isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
            throw new \Exception( 'Invalid Google response format' );
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
        $start = strpos( $json_content, '{' );
        $end = strrpos( $json_content, '}' );

        if ( $start !== false && $end !== false && $end > $start ) {
            $json_content = substr( $json_content, $start, $end - $start + 1 );
        }

        // Attempt to decode JSON
        $ai_response = json_decode( $json_content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new \Exception( 'Unable to generate form. Please try again or rephrase your request.' );
        }

        // Check for error response from AI
        if ( ! empty( $ai_response['error'] ) ) {
            return [
                'success' => false,
                'error' => true,
                'message' => isset( $ai_response['message'] ) ? $ai_response['message'] : 'AI returned an error response',
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
                'message' => isset( $form_data['message'] ) ? $form_data['message'] : 'Failed to build form structure',
                'provider' => 'google',
                'model' => $this->current_model
            ];
        }

        // Add metadata with better uniqueness
        $timestamp = microtime( true );
        $random = bin2hex( random_bytes( 5 ) );
        $form_data['session_id'] = isset( $options['session_id'] ) ? $options['session_id'] : 'wpuf_ai_session_' . $timestamp . '_' . $random;
        $form_data['response_id'] = 'google_resp_' . $timestamp . '_' . $random;
        $form_data['provider'] = 'google';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time( 'mysql' );
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Get system prompt for AI form generation
     *
     * @since 4.2.1
     *
     * @param array $context Conversation context
     * @param string $form_type Form type ('post' or 'profile')
     * @return string System prompt
     */
    private function get_system_prompt( $context = [], $form_type = 'post' ) {
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

        // Load the prompt file (local file, not remote URL)
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $system_prompt = file_get_contents( $prompt_file );

        // Add form type context (informational, not restrictive)
        $system_prompt .= "\n\n## FORM TYPE CONTEXT\n";
        if ( 'profile' === $form_type || 'registration' === $form_type ) {
            $system_prompt .= "You are working with a REGISTRATION/PROFILE form.\n";
            $system_prompt .= "- Use registration/profile fields: user_email, user_login, password, first_name, last_name, biography, user_avatar, secondary_email, social fields, phone_field, address_field, dropdown_field, radio_field, checkbox_field, etc.\n";
            $system_prompt .= "- secondary_email is for collecting an alternate/backup email address (meta key: wpuf_secondary_email)\n";
            $system_prompt .= "- Custom fields like dropdown, radio, checkbox, text fields are fully supported for additional profile information\n";
            $system_prompt .= "- Focus on helping users collect user registration and profile data\n";
        } else {
            $system_prompt .= "You are working with a POST submission form.\n";
            $system_prompt .= "- Use post fields: post_title, post_content, post_excerpt, featured_image, taxonomy, custom fields, etc.\n";
            $system_prompt .= "- Focus on helping users collect content submission data\n";
        }

        // Add language context if provided or extract from user message
        $target_language = isset( $context['language'] ) ? $context['language'] : 'English';

        // Extract language from user message if they're requesting conversion/translation
        if ( ! empty( $context['chat_history'] ) && is_array( $context['chat_history'] ) && count( $context['chat_history'] ) > 0 ) {
            $last_message = end( $context['chat_history'] );
            $last_user_message = isset( $last_message['content'] ) ? $last_message['content'] : '';

            // Detect language conversion/translation requests
            if ( preg_match( '/(?:convert|translate|change|make).*?(?:to|in|into)\s+(\w+)/i', $last_user_message, $matches ) ) {
                $detected_language = ucfirst( strtolower( $matches[1] ) );
                $target_language = $detected_language;
            }
        }

        if ( ! empty( $target_language ) && $target_language !== 'English' ) {
            $system_prompt .= "\n\n## TARGET LANGUAGE\n";
            $system_prompt .= "**CRITICAL: The user has selected '{$target_language}' as their target language.**\n";
            $system_prompt .= "- Generate ALL field labels in {$target_language}\n";
            $system_prompt .= "- Generate ALL field placeholders in {$target_language}\n";
            $system_prompt .= "- Generate ALL field help text in {$target_language}\n";
            $system_prompt .= "- Generate ALL dropdown/radio/checkbox options in {$target_language}\n";
            $system_prompt .= "- Generate form_title and form_description in {$target_language}\n";
            $system_prompt .= "- When adding new fields, use {$target_language} for all text content\n";
            $system_prompt .= "- Even if the user's message is in English, generate field content in {$target_language}\n";
            $system_prompt .= "- This is a language conversion request - update ALL existing field labels to {$target_language}\n\n";
        }

        // Add conversation context if provided
        if ( ! empty( $context ) ) {
            $system_prompt .= "\n\n## CURRENT CONVERSATION CONTEXT\n";

            // Safely extract last user message
            $last_user_message = '';
            if ( isset( $context['chat_history'] ) && is_array( $context['chat_history'] ) && count( $context['chat_history'] ) > 0 ) {
                $last_message = end( $context['chat_history'] );
                $last_user_message = isset( $last_message['content'] ) ? $last_message['content'] : '';
            }

            // Determine modification intent
            $modification_requested = false;
            if ( isset( $context['modification_requested'] ) ) {
                $modification_requested = (bool) $context['modification_requested'];
            } else {
                $modification_keywords = [ 'edit', 'modify', 'update', 'change', 'add', 'remove', 'delete', 'replace', 'convert', 'translate', 'make' ];
                foreach ( $modification_keywords as $keyword ) {
                    if ( false !== stripos( $last_user_message, $keyword ) ) {
                        $modification_requested = true;
                        break;
                    }
                }
            }

            // Build MINIMAL context - only send template + label, not full structures
            $context_for_ai = [
                'session_id'             => isset( $context['session_id'] ) ? $context['session_id'] : '',
                'last_user_message'      => $last_user_message,
                'modification_requested' => $modification_requested,
                'form_type'              => $form_type, // Pass form type to AI for validation
            ];

            // Include MINIMAL current fields (template + label + custom props only)
            if ( ! empty( $context['current_form'] ) ) {
                $minimal_fields = Form_Builder::extract_minimal_fields( $context['current_form'] );
                if ( ! empty( $minimal_fields ) ) {
                    $context_for_ai['current_fields']     = $minimal_fields;
                    $context_for_ai['form_title']         = isset( $context['current_form']['form_title'] ) ? $context['current_form']['form_title'] : '';
                    $context_for_ai['form_description']   = isset( $context['current_form']['form_description'] ) ? $context['current_form']['form_description'] : '';
                }
            }

            $system_prompt .= wp_json_encode( $context_for_ai, JSON_PRETTY_PRINT );

            // Add specific instruction for modifications
            $system_prompt .= "\n\n## MODIFICATION INSTRUCTION\n";
            if ( $modification_requested ) {
                $system_prompt .= "The user wants to MODIFY the existing form. You MUST:\n";
                $system_prompt .= "1. Return ALL existing fields from current_fields array (keep template + label + custom props)\n";
                $system_prompt .= "2. Apply the requested modification (add/remove/edit specific fields)\n";
                $system_prompt .= "3. Return COMPLETE field list with all fields in the 'fields' array\n";
                $system_prompt .= "4. Include form_title and form_description from context\n\n";
                $system_prompt .= "### Modification Types:\n";
                $system_prompt .= "- **Add field**: Return existing fields + new field\n";
                $system_prompt .= "- **Remove field**: Return existing fields without the specified field\n";
                $system_prompt .= "- **Edit field**: Update the field's properties (label, required, placeholder, etc.)\n";
                $system_prompt .= "- **Change field type**: Replace the field with a new template (e.g., checkbox_field → dropdown_field)\n";
                $system_prompt .= "  Example: 'change skills checkbox to dropdown' → Replace checkbox_field template with dropdown_field template\n\n";
                $system_prompt .= "Examples:\n";
                $system_prompt .= "- 'add email field' with 3 existing fields → return ALL 4 fields\n";
                $system_prompt .= "- 'change skills from checkbox to dropdown' → return all fields with skills field having template: 'dropdown_field' instead of 'checkbox_field'\n";
            } else {
                $system_prompt .= "The user is asking a question. Return an error response with helpful message.";
            }
        }

        return $system_prompt;
    }


    /**
     * Get available providers
     *
     * @since 4.2.1
     *
     * @return array Provider configurations
     */
    public function get_providers() {
        return $this->provider_configs;
    }

    /**
     * Get current provider
     *
     * @since 4.2.1
     *
     * @return string Current provider
     */
    public function get_current_provider() {
        return $this->current_provider;
    }

    /**
     * Test connection to provider with optional overrides
     *
     * @since 4.2.1
     *
     * @param string $api_key Optional API key to test (if not provided, uses saved settings)
     * @param string $provider Optional provider to test (if not provided, uses saved settings)
     * @param string $model Optional model to test (if not provided, uses saved settings)
     * @return array Test result
     */
    public function test_connection( $api_key = '', $provider = '', $model = '' ) {
        try {
            // Only reload settings if we need to use saved values (reduces DB call)
            if ( empty( $api_key ) || empty( $provider ) || empty( $model ) ) {
                $this->load_settings();
            }

            // Store originals for restoration
            $original_provider = $this->current_provider;
            $original_model    = $this->current_model;
            $original_key      = $this->api_key;

            // Use provided values or fall back to saved settings
            if ( ! empty( $provider ) ) {
                $this->current_provider = $provider;
            }

            if ( ! empty( $model ) ) {
                $this->current_model = $model;
            }

            $test_api_key = ! empty( $api_key ) ? $api_key : $this->api_key;

            // Validate API key exists
            if ( empty( $test_api_key ) ) {
                return [
                    'success'  => false,
                    'provider' => $this->current_provider,
                    'message'  => __( 'No API key provided', 'wp-user-frontend' ),
                ];
            }

            // Temporarily override the API key for testing
            $this->api_key = $test_api_key;

            // Make a simple API call based on provider
            $result = $this->test_provider_api();

            // Restore original values
            $this->current_provider = $original_provider;
            $this->current_model    = $original_model;
            $this->api_key          = $original_key;

            return $result;

        } catch ( \Exception $e ) {
            return [
                'success'  => false,
                'provider' => $this->current_provider,
                'message'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Test provider API with minimal request
     *
     * @since 4.2.7
     *
     * @return array Test result
     */
    private function test_provider_api() {
        switch ( $this->current_provider ) {
            case 'openai':
                return $this->test_openai_connection();

            case 'anthropic':
                return $this->test_anthropic_connection();

            case 'google':
                return $this->test_google_connection();

            default:
                return [
                    'success'  => false,
                    'provider' => $this->current_provider,
                    'message'  => __( 'Unknown provider', 'wp-user-frontend' ),
                ];
        }
    }

    /**
     * Test OpenAI connection
     */
    private function test_openai_connection() {
        $response = wp_remote_post(
            'https://api.openai.com/v1/chat/completions',
            [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => wp_json_encode(
                    [
                        'model'      => $this->current_model,
                        'messages'   => [
                            [
                                'role'    => 'user',
                                'content' => 'Hi',
                            ],
                        ],
                        'max_tokens' => 5,
                    ]
                ),
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'success'  => false,
                'provider' => 'openai',
                'message'  => $response->get_error_message(),
            ];
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        if ( $status_code === 200 ) {
            return [
                'success'  => true,
                'provider' => 'openai',
                'message'  => __( 'OpenAI connection successful!', 'wp-user-frontend' ),
            ];
        }

        $body          = json_decode( wp_remote_retrieve_body( $response ), true );
        $error_message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Connection failed', 'wp-user-frontend' );

        return [
            'success'  => false,
            'provider' => 'openai',
            'message'  => $error_message,
        ];
    }

    /**
     * Test Anthropic connection
     */
    private function test_anthropic_connection() {
        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            [
                'timeout' => 30,
                'headers' => [
                    'x-api-key'         => $this->api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ],
                'body'    => wp_json_encode(
                    [
                        'model'      => $this->current_model,
                        'messages'   => [
                            [
                                'role'    => 'user',
                                'content' => 'Hi',
                            ],
                        ],
                        'max_tokens' => 5,
                    ]
                ),
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'success'  => false,
                'provider' => 'anthropic',
                'message'  => $response->get_error_message(),
            ];
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        if ( $status_code === 200 ) {
            return [
                'success'  => true,
                'provider' => 'anthropic',
                'message'  => __( 'Anthropic connection successful!', 'wp-user-frontend' ),
            ];
        }

        $body          = json_decode( wp_remote_retrieve_body( $response ), true );
        $error_message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Connection failed', 'wp-user-frontend' );

        return [
            'success'  => false,
            'provider' => 'anthropic',
            'message'  => $error_message,
        ];
    }

    /**
     * Test Google connection
     */
    private function test_google_connection() {
        $endpoint = str_replace( '{model}', $this->current_model, 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent' );

        $response = wp_remote_post(
            $endpoint,
            [
                'timeout' => 30,
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'x-goog-api-key' => $this->api_key,
                ],
                'body'    => wp_json_encode(
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => 'Hi',
                                    ],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'maxOutputTokens' => 5,
                        ],
                    ]
                ),
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'success'  => false,
                'provider' => 'google',
                'message'  => $response->get_error_message(),
            ];
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        if ( $status_code === 200 ) {
            return [
                'success'  => true,
                'provider' => 'google',
                'message'  => __( 'Google AI connection successful!', 'wp-user-frontend' ),
            ];
        }

        $body          = json_decode( wp_remote_retrieve_body( $response ), true );
        $error_message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Connection failed', 'wp-user-frontend' );

        return [
            'success'  => false,
            'provider' => 'google',
            'message'  => $error_message,
        ];
    }

    /**
     * Generate field options using AI
     *
     * @since 4.2.2
     *
     * @param string $prompt User prompt describing the options to generate
     * @param array $options Additional options
     * @return array Generated options
     */
    public function generate_field_options($prompt, $options = []) {
        try {
            $field_type = $options['field_type'] ?? 'dropdown_field';
            $output_format = $options['output_format'] ?? 'one_per_line';
            $tone = $options['tone'] ?? 'casual';
            $max_options = $options['max_options'] ?? 20;

            // Build system prompt for option generation
            $system_prompt = $this->get_field_options_system_prompt($field_type, $output_format, $tone, $max_options);

            // Store original provider settings
            $original_provider = $this->current_provider;
            $original_model = $this->current_model;
            $original_api_key = $this->api_key;

            // Call AI provider
            $ai_response = null;
            switch ($this->current_provider) {
                case 'openai':
                    $ai_response = $this->call_openai_for_options($system_prompt, $prompt, $options);
                    break;

                case 'anthropic':
                    $ai_response = $this->call_anthropic_for_options($system_prompt, $prompt, $options);
                    break;

                case 'google':
                    $ai_response = $this->call_google_for_options($system_prompt, $prompt, $options);
                    break;

                default:
                    throw new \Exception('Unsupported AI provider: ' . $this->current_provider);
            }

            if (isset($ai_response['error']) && $ai_response['error']) {
                return [
                    'success' => false,
                    'error' => true,
                    'message' => $ai_response['message'] ?? 'Failed to generate options',
                    'provider' => $this->current_provider
                ];
            }

            return [
                'success' => true,
                'options' => $ai_response['options'] ?? [],
                'provider' => $this->current_provider,
                'model' => $this->current_model
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
                'provider' => $this->current_provider
            ];
        } finally {
            // Always restore original provider settings
            $this->current_provider = $original_provider;
            $this->current_model = $original_model;
            $this->api_key = $original_api_key;
        }
    }

    /**
     * Get system prompt for field options generation
     *
     * @param string $field_type Field type
     * @param string $output_format Output format
     * @param string $tone Tone of options
     * @param int $max_options Maximum number of options
     * @return string System prompt
     */
    private function get_field_options_system_prompt($field_type, $output_format, $tone, $max_options) {
        $prompt = "You are an AI assistant helping to generate field options for a WordPress form.\n\n";
        $prompt .= "**Task:** Generate a list of options based on the user's request.\n\n";
        $prompt .= "**Field Type:** {$field_type}\n";
        $prompt .= "**Output Format:** {$output_format}\n";
        $prompt .= "**Tone:** {$tone}\n";
        $prompt .= "**Maximum Options:** {$max_options}\n\n";

        $prompt .= "**Requirements:**\n";
        $prompt .= "1. Generate between 1 and {$max_options} options\n";
        $prompt .= "2. Each option should be clear, concise, and relevant\n";
        $prompt .= "3. Options should be appropriate for the specified tone\n";
        $prompt .= "4. Avoid duplicate or very similar options\n";
        $prompt .= "5. Use proper capitalization and formatting\n\n";

        if ($output_format === 'value_label') {
            $prompt .= "**Output Format:** Return a JSON object with 'options' array containing objects with 'value' and 'label' keys.\n";
            $prompt .= "Example:\n";
            $prompt .= "{\n";
            $prompt .= "  \"options\": [\n";
            $prompt .= "    {\"value\": \"option_1\", \"label\": \"Option 1\"},\n";
            $prompt .= "    {\"value\": \"option_2\", \"label\": \"Option 2\"}\n";
            $prompt .= "  ]\n";
            $prompt .= "}\n\n";
        } else {
            $prompt .= "**Output Format:** Return a JSON object with 'options' array containing simple strings (one option per item).\n";
            $prompt .= "Example:\n";
            $prompt .= "{\n";
            $prompt .= "  \"options\": [\"Option 1\", \"Option 2\", \"Option 3\"]\n";
            $prompt .= "}\n\n";
        }

        $prompt .= "**IMPORTANT:** Respond ONLY with valid JSON. Do not include any explanatory text, markdown formatting, or code blocks.\n";

        return $prompt;
    }

    /**
     * Call OpenAI for field options generation
     *
     * @param string $system_prompt System prompt
     * @param string $user_prompt User prompt
     * @param array $options Additional options
     * @return array Result
     */
    private function call_openai_for_options($system_prompt, $user_prompt, $options = []) {
        $model_config = $this->get_model_config('openai', $this->current_model);

        $body = [
            'model' => $this->current_model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $user_prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];

        if ($model_config['supports_json_mode']) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 60
        ];

        $response = wp_safe_remote_request($this->provider_configs['openai']['endpoint'], $args);

        if (is_wp_error($response)) {
            throw new \Exception('OpenAI API request failed: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            throw new \Exception("OpenAI API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from OpenAI API');
        }

        if (isset($data['error'])) {
            throw new \Exception('OpenAI API Error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid OpenAI response format');
        }

        return $this->parse_options_response($data['choices'][0]['message']['content'], $options);
    }

    /**
     * Call Anthropic for field options generation
     *
     * @param string $system_prompt System prompt
     * @param string $user_prompt User prompt
     * @param array $options Additional options
     * @return array Result
     */
    private function call_anthropic_for_options($system_prompt, $user_prompt, $options = []) {
        $body = [
            'model' => $this->current_model,
            'system' => $system_prompt,
            'messages' => [
                ['role' => 'user', 'content' => $user_prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];

        $args = [
            'method' => 'POST',
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 60
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Anthropic API');
        }

        if (isset($data['error'])) {
            throw new \Exception('Anthropic API Error: ' . $data['error']['message']);
        }

        if (!isset($data['content'][0]['text'])) {
            throw new \Exception('Invalid Anthropic response format');
        }

        return $this->parse_options_response($data['content'][0]['text'], $options);
    }

    /**
     * Call Google for field options generation
     *
     * @param string $system_prompt System prompt
     * @param string $user_prompt User prompt
     * @param array $options Additional options
     * @return array Result
     */
    private function call_google_for_options($system_prompt, $user_prompt, $options = []) {
        $model_config = $this->get_model_config('google', $this->current_model);

        $endpoint = str_replace('{model}', $this->current_model, $this->provider_configs['google']['endpoint']);

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $system_prompt . "\n\nUser request: " . $user_prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000
            ]
        ];

        if ($model_config['supports_json_mode']) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key
            ],
            'body' => json_encode($body),
            'timeout' => 60
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Google API');
        }

        if (isset($data['error'])) {
            throw new \Exception('Google API Error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid Google response format');
        }

        return $this->parse_options_response($data['candidates'][0]['content']['parts'][0]['text'], $options);
    }

    /**
     * Parse options from AI response
     *
     * @param string $content AI response content
     * @param array $options Request options
     * @return array Parsed options
     */
    private function parse_options_response($content, $options = []) {
        // Clean and extract JSON from the response
        $json_content = trim($content);

        // Remove any markdown code blocks if present
        $json_content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json_content);

        // Remove any text before the first { or after the last }
        $json_content = preg_replace('/^[^{]*/', '', $json_content);
        $json_content = preg_replace('/[^}]*$/', '', $json_content);

        // Try to find the JSON object
        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Attempt to decode JSON
        $parsed = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'message' => 'Unable to parse AI response. Please try again.'
            ];
        }

        // Extract options from parsed response
        $field_options = [];
        $output_format = $options['output_format'] ?? 'one_per_line';

        if (isset($parsed['options']) && is_array($parsed['options'])) {
            if ($output_format === 'value_label') {
                // Expecting array of objects with 'value' and 'label'
                foreach ($parsed['options'] as $option) {
                    if (is_array($option) && isset($option['value']) && isset($option['label'])) {
                        $field_options[] = [
                            'label' => $option['label'],
                            'value' => $option['value']
                        ];
                    }
                }
            } else {
                // Expecting array of strings (one per line)
                foreach ($parsed['options'] as $index => $option) {
                    if (is_string($option)) {
                        $field_options[] = [
                            'label' => $option,
                            'value' => sanitize_title( $option )
                        ];
                    }
                }
            }
        }

        return [
            'error' => false,
            'options' => $field_options
        ];
    }
}
