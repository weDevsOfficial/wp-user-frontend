<?php

namespace WeDevs\Wpuf\AI;

use WeDevs\Wpuf\Lib\AI\PredefinedProvider;

/**
 * AI Form Generator Service
 * 
 * Main service class that handles form generation using different AI providers
 * Uses WordPress native HTTP API for lightweight implementation
 * 
 * @since 1.0.0
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
     * AI Client Loader instance
     *
     * @var AIClientLoader
     */
    private $ai_client_loader;

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
                'gpt-4' => 'GPT-4',
                'gpt-4-turbo-preview' => 'GPT-4 Turbo',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
            ],
            'requires_key' => true
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'models' => [
                'claude-3-haiku-20240307' => 'Claude 3 Haiku',
                'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                'claude-3-opus-20240229' => 'Claude 3 Opus'
            ],
            'requires_key' => true
        ]
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->ai_client_loader = AIClientLoader::getInstance();
        $this->load_settings();
    }

    /**
     * Load settings from WordPress options
     */
    private function load_settings() {
        $settings = get_option('wpuf_ai_settings', []);

        // Default to predefined provider for testing
        $this->current_provider = $settings['provider'] ?? 'predefined';
        $this->current_model = $settings['model'] ?? 'predefined';
        $this->api_key = $settings['api_key'] ?? '';

        // Force predefined provider if no API key is provided for other providers
        if ($this->current_provider !== 'predefined' && empty($this->api_key)) {
            $this->current_provider = 'predefined';
            $this->current_model = 'predefined';
        }
    }

    /**
     * Generate form based on prompt
     *
     * @param string $prompt User prompt
     * @param array $options Additional options
     * @return array Generated form data
     */
    public function generate_form($prompt, $options = []) {
        try {
            // Use predefined provider for testing or when no API key
            if ($this->current_provider === 'predefined') {
                $predefined_provider = new PredefinedProvider();
                return $predefined_provider->generateForm($prompt, $options['session_id'] ?? '');
            }

            // Try to use WordPress AI Client first if available
            if ($this->ai_client_loader->is_available() && !empty($this->api_key)) {
                try {
                    $ai_options = array_merge($options, [
                        'provider' => $this->current_provider,
                        'model' => $this->current_model
                    ]);
                    
                    return $this->ai_client_loader->generate_form($prompt, $ai_options);
                } catch (\Exception $e) {
                    // Fall back to manual implementation if AI Client fails
                    error_log('WPUF AI Client failed, falling back to manual implementation: ' . $e->getMessage());
                }
            }

            // Use manual AI provider implementation as fallback
            switch ($this->current_provider) {
                case 'openai':
                    return $this->generate_with_openai($prompt, $options);

                case 'anthropic':
                    return $this->generate_with_anthropic($prompt, $options);

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
        $system_prompt = $this->get_system_prompt();

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
        $system_prompt = $this->get_system_prompt();

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
     * Get system prompt for AI form generation
     *
     * @return string System prompt
     */
    private function get_system_prompt() {
        return 'You are an expert form builder assistant for WPUF (WP User Frontend). Your task is to generate structured form data based on user requirements using WPUF\'s native field types.

IMPORTANT: You must respond with ONLY valid JSON in the exact format specified below. Do not include any explanations, markdown formatting, or additional text.

Response Format:
{
    "form_title": "Descriptive title for the form",
    "form_description": "Brief description of the form\'s purpose",
    "fields": [
        {
            "id": 1,
            "type": "text_field|email_address|website_url|textarea_field|dropdown_field|radio_field|checkbox_field|multiple_select|image_upload",
            "label": "Field label visible to users",
            "name": "field_name_underscore_format",
            "required": true or false,
            "placeholder": "Placeholder text (optional)",
            "help_text": "Help text for the field (optional)",
            "default": "Default value (optional)",
            "options": [
                {"value": "option_value", "label": "Option Label"}
            ]
        }
    ],
    "settings": {
        "submit_button_text": "Submit button text",
        "success_message": "Message shown after successful submission"
    }
}

WPUF Field Type Guidelines (use these exact field types):
- Use "text_field" for single-line text inputs (names, titles, addresses, etc.)
- Use "email_address" for email addresses
- Use "website_url" for website URLs
- Use "textarea_field" for multi-line text (comments, descriptions, messages)
- Use "dropdown_field" for dropdown menus (single selection)
- Use "multiple_select" for multi-selection dropdowns
- Use "radio_field" for single choice from multiple options (radio buttons)
- Use "checkbox_field" for multiple choices or single yes/no checkboxes
- Use "image_upload" for image file uploads
- Use "featured_image" for featured image uploads

Additional WPUF field types available:
- "post_title" for post/article titles
- "post_content" for post/article content
- "post_tags" for post tags
- "taxonomy" for category selections
- "custom_html" for HTML content blocks
- "section_break" for form sections
- "column_field" for column layouts

Always include appropriate field IDs starting from 1.
For required fields, set "required": true.
Use descriptive, user-friendly labels.
Field names should be lowercase with underscores (e.g., "first_name", "email_address").
Include options array only for dropdown_field, multiple_select, radio_field, and checkbox_field.
For options, use format: [{"value": "option1", "label": "Option 1"}, {"value": "option2", "label": "Option 2"}]

Generate the form based on the user\'s request below:';
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