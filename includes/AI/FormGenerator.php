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
    private $provider_configs = [
        'openai' => [
            'name' => 'OpenAI',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'models' => [
                'gpt-4o' => 'GPT-4o - Most Capable Multimodal',
                'gpt-4o-mini' => 'GPT-4o Mini - Efficient & Fast',
                'gpt-4-turbo' => 'GPT-4 Turbo - High Performance',
                'gpt-4' => 'GPT-4 - Advanced Reasoning',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo - Fast & Affordable'
            ],
            'requires_key' => true
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'models' => [
                'claude-4.1-opus' => 'Claude 4.1 Opus - Most Capable',
                'claude-4-opus' => 'Claude 4 Opus - Best Coding Model',
                'claude-4-sonnet' => 'Claude 4 Sonnet - Advanced Reasoning',
                'claude-3.7-sonnet' => 'Claude 3.7 Sonnet - Hybrid Reasoning',
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet Latest',
                'claude-3-5-sonnet-20240620' => 'Claude 3.5 Sonnet',
                'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku',
                'claude-3-opus-20240229' => 'Claude 3 Opus',
                'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                'claude-3-haiku-20240307' => 'Claude 3 Haiku'
            ],
            'requires_key' => true
        ],
        'google' => [
            'name' => 'Google Gemini',
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
            'models' => [
                'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash Experimental - Latest',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash - Fast & Free',
                'gemini-1.5-flash-8b' => 'Gemini 1.5 Flash 8B - Fast & Free',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro - Most Capable',
                'gemini-1.0-pro' => 'Gemini 1.0 Pro - Stable'
            ],
            'requires_key' => true
        ]
    ];

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        $this->load_settings();
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
        $model_configs = [
            'openai' => [
                // Models with special requirements
                'o1' => [
                    'token_param' => 'max_completion_tokens',
                    'token_location' => 'body',
                    'temperature' => 1.0, // Fixed temperature
                    'supports_json_mode' => false,
                    'supports_custom_temperature' => false
                ],
                'o1-mini' => [
                    'token_param' => 'max_completion_tokens',
                    'token_location' => 'body',
                    'temperature' => 1.0,
                    'supports_json_mode' => false,
                    'supports_custom_temperature' => false
                ],
                'o1-preview' => [
                    'token_param' => 'max_completion_tokens',
                    'token_location' => 'body',
                    'temperature' => 1.0,
                    'supports_json_mode' => false,
                    'supports_custom_temperature' => false
                ],
                // GPT-4 Series - All support JSON mode and custom temperature
                'gpt-4o' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gpt-4o-mini' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gpt-4-turbo' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gpt-4' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gpt-3.5-turbo' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ]
            ],
            'anthropic' => [
                // Anthropic models with special requirements
                'claude-4.1-opus' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-4-opus' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                // All other Anthropic models support standard configuration
                'claude-4-sonnet' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3.7-sonnet' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3-5-sonnet-20241022' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3-5-sonnet-20240620' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3-5-haiku-20241022' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3-opus-20240229' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3-sonnet-20240229' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'claude-3-haiku-20240307' => [
                    'token_param' => 'max_tokens',
                    'token_location' => 'body',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ]
            ],
            'google' => [
                // All Google models support JSON mode and custom temperature
                'gemini-2.0-flash-exp' => [
                    'token_param' => 'maxOutputTokens',
                    'token_location' => 'generationConfig',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gemini-1.5-flash' => [
                    'token_param' => 'maxOutputTokens',
                    'token_location' => 'generationConfig',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gemini-1.5-flash-8b' => [
                    'token_param' => 'maxOutputTokens',
                    'token_location' => 'generationConfig',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gemini-1.5-pro' => [
                    'token_param' => 'maxOutputTokens',
                    'token_location' => 'generationConfig',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                'gemini-1.0-pro' => [
                    'token_param' => 'maxOutputTokens',
                    'token_location' => 'generationConfig',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ],
                // Default fallback for any Google model not explicitly listed
                'default' => [
                    'token_param' => 'maxOutputTokens',
                    'token_location' => 'generationConfig',
                    'supports_json_mode' => true,
                    'supports_custom_temperature' => true
                ]
            ]
        ];

        // Check for exact model match first
        if (isset($model_configs[$provider][$model])) {
            return $model_configs[$provider][$model];
        }

        // Check for pattern matches (e.g., gpt-5-turbo-preview matches gpt-5-turbo)
        if (isset($model_configs[$provider])) {
            foreach ($model_configs[$provider] as $pattern => $config) {
                if ($pattern !== 'default' && strpos($model, $pattern) === 0) {
                    return $config;
                }
            }
        }

        // Use provider default if available
        if (isset($model_configs[$provider]['default'])) {
            return $model_configs[$provider]['default'];
        }

        // Fallback defaults for each provider
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
        $system_prompt = $this->get_system_prompt($context);

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

        // Try to find the JSON object (handle nested braces properly)
        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Attempt to decode JSON
        $form_data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Return user-friendly error message
            return [
                'success' => false,
                'error' => true,
                'message' => 'Unable to generate form. Please try again or simplify your request.',
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
        $system_prompt = $this->get_system_prompt($context);

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

        // Remove any markdown code blocks if present
        $json_content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $json_content);

        // Try to find the JSON object (handle nested braces properly)
        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Attempt to decode JSON
        $form_data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Unable to generate form. Please try again or simplify your request.');
        }

        // Add metadata with better uniqueness
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
        $system_prompt = $this->get_system_prompt($context);
        
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

        // Try to find the JSON object (handle nested braces properly)
        $start = strpos($json_content, '{');
        $end = strrpos($json_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json_content = substr($json_content, $start, $end - $start + 1);
        }

        // Attempt to decode JSON
        $form_data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Unable to generate form. Please try again or simplify your request.');
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
     * @return string System prompt
     */
    private function get_system_prompt($context = []) {
        // Use the compact prompt file to avoid truncation
        $prompt_file = plugin_dir_path(dirname(__FILE__)) . 'AI/wpuf-ai-system-prompt-compact.md';

        // Fallback to optimized version if compact doesn't exist
        if (!file_exists($prompt_file)) {
            $prompt_file = plugin_dir_path(dirname(__FILE__)) . 'AI/wpuf-ai-system-prompt-optimized.md';
        }

        // Check if file exists
        if (!file_exists($prompt_file)) {
            throw new \Exception('System prompt file not found: ' . $prompt_file);
        }

        // Load the prompt file
        $system_prompt = file_get_contents($prompt_file);

        // Check prompt size to prevent truncation (max ~40KB for safety)
        if (strlen($system_prompt) > 40000) {
            // Try to load compact version as emergency fallback
            $compact_file = plugin_dir_path(dirname(__FILE__)) . 'AI/wpuf-ai-system-prompt-compact.md';
            if (file_exists($compact_file) && $compact_file !== $prompt_file) {
                $system_prompt = file_get_contents($compact_file);
            }
        }

        // Add conversation context if provided
        if (!empty($context)) {
            $system_prompt .= "\n\n## CURRENT CONVERSATION CONTEXT\n";
            
            // Safely extract last user message
            $last_user_message = '';
            if (isset($context['chat_history']) && is_array($context['chat_history']) && count($context['chat_history']) > 0) {
                $last_message = end($context['chat_history']);
                $last_user_message = $last_message['content'] ?? '';
            }
            
            // Determine modification intent
            $modification_requested = false;
            if (isset($context['modification_requested'])) {
                // Use explicit value when provided
                $modification_requested = (bool) $context['modification_requested'];
            } else {
                // Infer intent conservatively from last user message
                $modification_keywords = ['edit', 'modify', 'update', 'change', 'add', 'remove', 'delete', 'replace'];
                $modification_requested = false;
                foreach ($modification_keywords as $keyword) {
                    if (stripos($last_user_message, $keyword) !== false) {
                        $modification_requested = true;
                        break;
                    }
                }
            }
            
            // Simplify context for better AI understanding
            $simplified_context = [
                'session_id' => $context['session_id'] ?? '',
                'current_form_title' => $context['current_form']['form_title'] ?? '',
                'current_fields_count' => count($context['current_form']['wpuf_fields'] ?? []),
                'last_user_message' => $last_user_message,
                'modification_requested' => $modification_requested
            ];
            
            // Include current fields summary
            if (!empty($context['current_form']['wpuf_fields'])) {
                $field_summary = [];
                foreach ($context['current_form']['wpuf_fields'] as $field) {
                    $field_summary[] = [
                        'label' => $field['label'] ?? '',
                        'type' => $field['type'] ?? $field['template'] ?? '',
                        'name' => $field['name'] ?? ''
                    ];
                }
                $simplified_context['current_fields'] = $field_summary;
            }
            
            $system_prompt .= json_encode($simplified_context, JSON_PRETTY_PRINT);
            
            // Add specific instruction for modifications
            $system_prompt .= "\n\n## MODIFICATION INSTRUCTION\n";
            $system_prompt .= "The user is requesting a modification to the existing form. ";
            $system_prompt .= "Return the COMPLETE form with ALL existing fields PLUS the requested changes. ";
            $system_prompt .= "DO NOT remove existing fields unless explicitly asked.";
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