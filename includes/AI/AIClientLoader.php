<?php

namespace WeDevs\Wpuf\AI;

/**
 * AI Client Loader
 * 
 * Loads the WordPress PHP AI Client SDK without Composer
 * Provides a lightweight interface to the AI Client
 * 
 * @since 1.0.0
 */
class AIClientLoader {

    /**
     * Instance of this class
     *
     * @var AIClientLoader
     */
    private static $instance = null;

    /**
     * Whether the AI Client is loaded
     *
     * @var bool
     */
    private $is_loaded = false;

    /**
     * AI Client path
     *
     * @var string
     */
    private $ai_client_path;

    /**
     * Constructor
     */
    private function __construct() {
        $this->ai_client_path = WPUF_ROOT . '/Lib/AI/php-ai-client';
        $this->maybe_load_ai_client();
    }

    /**
     * Get singleton instance
     *
     * @return AIClientLoader
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if AI Client is available and load it
     */
    private function maybe_load_ai_client() {
        if (!file_exists($this->ai_client_path . '/src/AiClient.php')) {
            return;
        }

        // Load polyfills first
        if (file_exists($this->ai_client_path . '/src/polyfills.php')) {
            require_once $this->ai_client_path . '/src/polyfills.php';
        }

        // Register simple autoloader for the AI Client
        spl_autoload_register([$this, 'autoload_ai_client']);

        $this->is_loaded = true;
    }

    /**
     * Simple autoloader for AI Client classes
     *
     * @param string $class_name Class name to autoload
     */
    private function autoload_ai_client($class_name) {
        // Only handle WordPress\AiClient namespace
        if (strpos($class_name, 'WordPress\\AiClient\\') !== 0) {
            return;
        }

        // Convert namespace to file path
        $relative_class = substr($class_name, strlen('WordPress\\AiClient\\'));
        $file_path = $this->ai_client_path . '/src/' . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /**
     * Check if AI Client is available
     *
     * @return bool
     */
    public function is_available() {
        return $this->is_loaded && class_exists('WordPress\\AiClient\\AiClient');
    }

    /**
     * Create AI Client instance with system prompt for form generation
     *
     * @param string $prompt User prompt
     * @return object|null AI Client PromptBuilder instance or null
     */
    public function create_form_prompt($prompt) {
        if (!$this->is_available()) {
            return null;
        }

        try {
            $system_instruction = 'You are an expert form builder assistant for WPUF (WP User Frontend). Generate structured form data as valid JSON using WPUF native field types:

{
    "form_title": "Form title",
    "form_description": "Form description", 
    "fields": [
        {
            "id": 1,
            "type": "text_field|email_address|website_url|textarea_field|dropdown_field|radio_field|checkbox_field|multiple_select|image_upload",
            "label": "Field label",
            "name": "field_name",
            "required": true|false,
            "placeholder": "Placeholder text",
            "help_text": "Help text (optional)",
            "options": [{"value": "val", "label": "Label"}]
        }
    ]
}

WPUF Field Types:
- text_field: Single-line text input
- email_address: Email input
- website_url: URL input  
- textarea_field: Multi-line text
- dropdown_field: Single select dropdown
- multiple_select: Multi-select dropdown
- radio_field: Radio button group
- checkbox_field: Checkbox group
- image_upload: Image file upload

Always include field IDs starting from 1. For dropdown_field/radio_field/checkbox_field/multiple_select, include options array.';

            return \WordPress\AiClient\AiClient::prompt($prompt)
                ->usingSystemInstruction($system_instruction)
                ->usingTemperature(0.7);

        } catch (\Exception $e) {
            error_log('WPUF AI Client Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate form using AI Client
     *
     * @param string $prompt User prompt
     * @param array $options Additional options (provider, model, etc.)
     * @return array Generated form data
     */
    public function generate_form($prompt, $options = []) {
        $prompt_builder = $this->create_form_prompt($prompt);
        
        if (!$prompt_builder) {
            throw new \Exception('AI Client is not available');
        }

        // Set provider if specified
        if (!empty($options['provider'])) {
            $prompt_builder = $prompt_builder->usingProvider($options['provider']);
        }

        // Set model if specified
        if (!empty($options['model'])) {
            $prompt_builder = $prompt_builder->usingModel($options['model']);
        }

        // Set temperature if specified
        if (isset($options['temperature'])) {
            $prompt_builder = $prompt_builder->usingTemperature(floatval($options['temperature']));
        }

        try {
            // Generate text
            $response_text = $prompt_builder->generateText();

            // Try to extract JSON from the response
            if (preg_match('/\{.*\}/s', $response_text, $matches)) {
                $json_content = $matches[0];
            } else {
                $json_content = $response_text;
            }

            $form_data = json_decode($json_content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to parse AI response JSON: ' . json_last_error_msg());
            }

            // Add metadata
            $form_data['session_id'] = $options['session_id'] ?? uniqid('wpuf_ai_session_');
            $form_data['response_id'] = uniqid('ai_client_resp_');
            $form_data['provider'] = $options['provider'] ?? 'auto';
            $form_data['generated_at'] = current_time('mysql');
            $form_data['success'] = true;

            return $form_data;

        } catch (\Exception $e) {
            throw new \Exception('AI Client generation failed: ' . $e->getMessage());
        }
    }
}