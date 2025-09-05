<?php

namespace WeDevs\Wpuf\AI;

use WeDevs\Wpuf\Lib\AI\PredefinedProvider;

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
        'predefined' => [
            'name' => 'Predefined Provider (Testing)',
            'endpoint' => '',
            'models' => ['predefined'],
            'requires_key' => false
        ],
        'openai' => [
            'name' => 'OpenAI',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'models' => [
                'gpt-4o' => 'GPT-4o',
                'gpt-4o-mini' => 'GPT-4o Mini',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-4' => 'GPT-4',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
            ],
            'requires_key' => true
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'models' => [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
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
                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Free)',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                'gemini-pro' => 'Gemini Pro'
            ],
            'requires_key' => true
        ]
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_settings();
    }

    /**
     * Check if prompt matches predefined templates (using same logic as PredefinedProvider)
     * 
     * @param string $prompt
     * @return bool
     */
    private function isPredefinedPrompt($prompt) {
        // Convert to lowercase for consistent matching
        $prompt_lower = strtolower($prompt);
        
        // Define keyword patterns that match predefined templates
        $predefined_patterns = [
            'paid guest post',
            'guest post',
            'portfolio',
            'classified ad',
            'classified',
            'coupon',
            'real estate',
            'property listing',
            'property',
            'news',
            'press release',
            'product listing',
            'product'
        ];
        
        // Check if prompt contains any predefined pattern
        foreach ($predefined_patterns as $pattern) {
            if (strpos($prompt_lower, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Load settings from WordPress options
     */
    private function load_settings() {
        // Get settings from WPUF settings system
        $settings = get_option('wpuf_ai', []);

        // Get individual settings
        $this->current_provider = $settings['ai_provider'] ?? 'predefined';
        $this->current_model = $settings['ai_model'] ?? 'gpt-3.5-turbo';
        $this->api_key = $settings['ai_api_key'] ?? '';

        // If no provider is set or no API key for non-predefined providers, use predefined
        if (empty($this->current_provider) || 
            ($this->current_provider !== 'predefined' && empty($this->api_key))) {
            $this->current_provider = 'predefined';
            $this->current_model = 'predefined';
        }
    }

    /**
     * Generate form based on prompt
     *
     * @param string $prompt User prompt
     * @param array $options Additional options including conversation context
     * @return array Generated form data
     */
    public function generate_form($prompt, $options = []) {
        try {
            // Check if prompt matches predefined templates
            $is_predefined = $this->isPredefinedPrompt($prompt);
            
            // Debug log
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WPUF AI: Checking prompt "' . $prompt . '" - Is predefined: ' . ($is_predefined ? 'YES' : 'NO'));
            }
            
            if ($is_predefined) {
                // Use predefined provider for matching prompts (saves API costs)
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('WPUF AI: Using predefined provider for prompt: ' . $prompt);
                }
                $predefined_provider = new PredefinedProvider();
                return $predefined_provider->generateForm($prompt, $options['session_id'] ?? '');
            }
            
            // Use predefined provider if explicitly set or no API key
            if ($this->current_provider === 'predefined') {
                $predefined_provider = new PredefinedProvider();
                return $predefined_provider->generateForm($prompt, $options['session_id'] ?? '');
            }

            // Using direct API implementation for AI providers

            // Use manual AI provider implementation as fallback
            switch ($this->current_provider) {
                case 'openai':
                    return $this->generate_with_openai($prompt, $options);

                case 'anthropic':
                    return $this->generate_with_anthropic($prompt, $options);

                case 'google':
                    return $this->generate_with_google($prompt, $options);

                default:
                    throw new \Exception('Unsupported AI provider: ' . $this->current_provider);
            }

        } catch (\Exception $e) {
            error_log('WPUF AI Form Generator Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
                'provider' => $this->current_provider
            ];
        }
    }

    /**
     * Generate form using OpenAI
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_openai($prompt, $options = []) {
        $context = $options['conversation_context'] ?? [];
        $system_prompt = $this->get_system_prompt($context);

        $body = [
            'model' => $this->current_model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => floatval($options['temperature'] ?? 0.7),
            'max_tokens' => intval($options['max_tokens'] ?? 2000),
            'response_format' => ['type' => 'json_object']
        ];

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
            throw new \Exception('OpenAI API request failed: ' . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            throw new \Exception("OpenAI API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new \Exception('OpenAI API Error: ' . $data['error']['message']);
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid OpenAI response format');
        }

        $content = $data['choices'][0]['message']['content'];
        $form_data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response JSON');
        }

        // Add metadata
        $form_data['session_id'] = $options['session_id'] ?? uniqid('wpuf_ai_session_');
        $form_data['response_id'] = uniqid('openai_resp_');
        $form_data['provider'] = 'openai';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time('mysql');
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Generate form using Anthropic Claude
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_anthropic($prompt, $options = []) {
        $context = $options['conversation_context'] ?? [];
        $system_prompt = $this->get_system_prompt($context);

        $body = [
            'model' => $this->current_model,
            'max_tokens' => intval($options['max_tokens'] ?? 2000),
            'system' => $system_prompt,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => floatval($options['temperature'] ?? 0.7)
        ];

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

        if (isset($data['error'])) {
            throw new \Exception('Anthropic API Error: ' . $data['error']['message']);
        }

        if (!isset($data['content'][0]['text'])) {
            throw new \Exception('Invalid Anthropic response format');
        }

        $content = $data['content'][0]['text'];
        
        // Extract JSON from content (Claude may include explanatory text)
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json_content = $matches[0];
        } else {
            $json_content = $content;
        }

        $form_data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response JSON');
        }

        // Add metadata
        $form_data['session_id'] = $options['session_id'] ?? uniqid('wpuf_ai_session_');
        $form_data['response_id'] = uniqid('anthropic_resp_');
        $form_data['provider'] = 'anthropic';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time('mysql');
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Generate form using Google Gemini
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    private function generate_with_google($prompt, $options = []) {
        $context = $options['conversation_context'] ?? [];
        $system_prompt = $this->get_system_prompt($context);
        
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
            'generationConfig' => [
                'temperature' => floatval($options['temperature'] ?? 0.7),
                'maxOutputTokens' => intval($options['max_tokens'] ?? 2000),
                'responseMimeType' => 'application/json'
            ]
        ];

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

        if (isset($data['error'])) {
            throw new \Exception('Google API Error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid Google response format');
        }

        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Extract JSON from content
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json_content = $matches[0];
        } else {
            $json_content = $content;
        }

        $form_data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response JSON');
        }

        // Add metadata
        $form_data['session_id'] = $options['session_id'] ?? uniqid('wpuf_ai_session_');
        $form_data['response_id'] = uniqid('google_resp_');
        $form_data['provider'] = 'google';
        $form_data['model'] = $this->current_model;
        $form_data['generated_at'] = current_time('mysql');
        $form_data['success'] = true;

        return $form_data;
    }

    /**
     * Get system prompt for AI form generation
     *
     * @param array $context Conversation context
     * @return string System prompt
     */
    private function get_system_prompt($context = []) {
        // Load the comprehensive system prompt (required file)
        $prompt_file = plugin_dir_path(dirname(__FILE__)) . 'AI/system-prompt.md';
        
        if (!file_exists($prompt_file)) {
            throw new \Exception('System prompt file not found: ' . $prompt_file);
        }
        
        $system_prompt = file_get_contents($prompt_file);
        
        // Add conversation context if provided
        if (!empty($context)) {
            $system_prompt .= "\n\n## CURRENT CONVERSATION CONTEXT\n";
            $system_prompt .= json_encode($context, JSON_PRETTY_PRINT);
        }
        
        return $system_prompt;
    }
    

    /**
     * Get available providers
     *
     * @return array Provider configurations
     */
    public function get_providers() {
        return $this->provider_configs;
    }

    /**
     * Get current provider
     *
     * @return string Current provider
     */
    public function get_current_provider() {
        return $this->current_provider;
    }

    /**
     * Test connection to current provider
     *
     * @return array Test result
     */
    public function test_connection() {
        if ($this->current_provider === 'predefined') {
            return [
                'success' => true,
                'provider' => 'predefined',
                'message' => 'Predefined provider is always available'
            ];
        }

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