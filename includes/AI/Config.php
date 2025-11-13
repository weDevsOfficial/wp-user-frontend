<?php

namespace WeDevs\Wpuf\AI;

/**
 * AI Configuration - Single Source of Truth
 *
 * Centralized configuration for all AI providers and models.
 * This class provides consistent data across the entire plugin.
 *
 * @since 4.2.1
 */
class Config {

    /**
     * Get all provider configurations
     *
     * @return array Provider configurations
     */
    public static function get_providers() {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'requires_key' => true,
                'api_key_field' => 'openai_api_key',
                'api_key_url' => 'https://platform.openai.com/api-keys'
            ],
            'anthropic' => [
                'name' => 'Anthropic',
                'endpoint' => 'https://api.anthropic.com/v1/messages',
                'requires_key' => true,
                'api_key_field' => 'anthropic_api_key',
                'api_key_url' => 'https://console.anthropic.com/settings/keys'
            ],
            'google' => [
                'name' => 'Google',
                'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
                'requires_key' => true,
                'api_key_field' => 'google_api_key',
                'api_key_url' => 'https://aistudio.google.com/app/apikey'
            ]
        ];
    }

    /**
     * Get all model configurations - fully dynamic from cache
     *
     * @return array Model configurations
     */
    public static function get_models() {
        // Get cached models from WordPress transient
        $cached_models = get_transient('wpuf_ai_models_cache');

        // Extract models if they exist
        if (is_array($cached_models) && isset($cached_models['models']) && !empty($cached_models['models'])) {
            return $cached_models['models'];
        }

        // If no cache, return minimal default models so settings page works
        return self::get_default_models();
    }

    /**
     * Get default models (shown when no API keys configured)
     *
     * @return array Default model configurations
     */
    private static function get_default_models() {
        return [
            // OpenAI defaults
            'gpt-4o' => [
                'name' => 'GPT-4o (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4o-mini' => [
                'name' => 'GPT-4o Mini (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4-turbo' => [
                'name' => 'GPT-4 Turbo (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            // Anthropic defaults
            'claude-3-5-sonnet-20241022' => [
                'name' => 'Claude 3.5 Sonnet (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-3-5-haiku-20241022' => [
                'name' => 'Claude 3.5 Haiku (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            // Google defaults
            'gemini-2.5-flash' => [
                'name' => 'Gemini 2.5 Flash (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gemini-2.0-flash' => [
                'name' => 'Gemini 2.0 Flash (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gemini-pro-latest' => [
                'name' => 'Gemini Pro Latest (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ]
        ];
    }

    /**
     * Get models for a specific provider
     *
     * @param string $provider Provider ID
     * @return array Models for the provider
     */
    public static function get_models_by_provider($provider) {
        $all_models = self::get_models();
        $provider_models = [];

        foreach ($all_models as $model_id => $model_config) {
            // Validate model config is an array with required keys
            if (!is_array($model_config) || !isset($model_config['provider'])) {
                continue;
            }

            if ($model_config['provider'] === $provider) {
                $provider_models[$model_id] = $model_config;
            }
        }

        return $provider_models;
    }

    /**
     * Get model configuration
     *
     * @param string $model_id Model ID
     * @return array|null Model configuration or null if not found
     */
    public static function get_model_config($model_id) {
        // Get all models from cache
        $all_models = self::get_models();

        // Check for exact match
        if (isset($all_models[$model_id])) {
            return $all_models[$model_id];
        }

        // Check for pattern matches (e.g., gpt-5-turbo-preview matches gpt-5-turbo)
        foreach ($all_models as $pattern => $config) {
            if (strpos($model_id, $pattern) === 0) {
                return $config;
            }
        }

        // Fallback to provider defaults
        $provider_defaults = [
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

        // Try to detect provider from model ID
        if (strpos($model_id, 'gpt-') === 0 || strpos($model_id, 'o1') === 0) {
            return $provider_defaults['openai'];
        } elseif (strpos($model_id, 'claude-') === 0) {
            return $provider_defaults['anthropic'];
        } elseif (strpos($model_id, 'gemini-') === 0) {
            return $provider_defaults['google'];
        }

        return null;
    }

    /**
     * Get provider configuration
     *
     * @param string $provider_id Provider ID
     * @return array|null Provider configuration or null if not found
     */
    public static function get_provider_config($provider_id) {
        $providers = self::get_providers();
        return $providers[$provider_id] ?? null;
    }

    /**
     * Get models formatted for settings dropdown
     *
     * @return array Model options formatted as id => name
     */
    public static function get_model_options() {
        $models = self::get_models();
        $options = [];

        foreach ($models as $model_id => $model_config) {
            // Validate model config is an array with required keys
            if (!is_array($model_config) || !isset($model_config['name'])) {
                continue;
            }
            $options[$model_id] = $model_config['name'];
        }

        return $options;
    }

    /**
     * Get providers formatted for settings options
     *
     * @return array Provider options formatted as id => name
     */
    public static function get_provider_options() {
        $providers = self::get_providers();
        $options = [];

        foreach ($providers as $provider_id => $provider_config) {
            $options[$provider_id] = $provider_config['name'];
        }

        return $options;
    }

    /**
     * Get provider configurations with their models
     * Useful for FormGenerator initialization
     *
     * @return array Provider configurations with models
     */
    public static function get_provider_configs() {
        $providers = self::get_providers();
        $models = self::get_models();
        $configs = [];

        foreach ($providers as $provider_id => $provider_config) {
            $provider_models = [];

            // Get all models for this provider
            foreach ($models as $model_id => $model_config) {
                // Validate model config is an array with required keys
                if (!is_array($model_config) || !isset($model_config['provider'], $model_config['name'])) {
                    continue;
                }

                if ($model_config['provider'] === $provider_id) {
                    $provider_models[$model_id] = $model_config['name'];
                }
            }

            $configs[$provider_id] = [
                'name' => $provider_config['name'],
                'endpoint' => $provider_config['endpoint'],
                'models' => $provider_models,
                'requires_key' => $provider_config['requires_key']
            ];
        }

        return $configs;
    }

    /**
     * Fetch available models from OpenAI API
     *
     * @param string $api_key OpenAI API key
     * @return array|\WP_Error Array of models or WP_Error on failure
     */
    public static function fetch_openai_models($api_key) {
        if (empty($api_key)) {
            return new \WP_Error('missing_api_key', 'API key is required');
        }

        $endpoint = 'https://api.openai.com/v1/models';

        $response = wp_safe_remote_get($endpoint, [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            return new \WP_Error('api_error', "OpenAI API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error('json_error', 'Invalid JSON response from OpenAI API');
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            return new \WP_Error('invalid_response', 'Invalid response format from OpenAI API');
        }

        $available_models = [];

        foreach ($data['data'] as $model) {
            $model_id = $model['id'] ?? '';

            // Only include GPT models
            if (strpos($model_id, 'gpt-') !== 0 && strpos($model_id, 'o1') !== 0) {
                continue;
            }

            // Get model name
            $model_name = ucwords(str_replace(['-', '_'], ' ', $model_id));

            // Determine token parameter based on model type
            $token_param = (strpos($model_id, 'o1') === 0) ? 'max_completion_tokens' : 'max_tokens';
            $supports_temp = (strpos($model_id, 'o1') !== 0);

            $available_models[$model_id] = [
                'name' => $model_name . ' (OpenAI)',
                'provider' => 'openai',
                'token_param' => $token_param,
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => $supports_temp
            ];
        }

        return $available_models;
    }

    /**
     * Fetch available models from Anthropic (static list)
     *
     * @return array Array of models
     */
    public static function fetch_anthropic_models() {
        // Anthropic doesn't have a models list endpoint, so we return known models
        // No API key required since these are static models
        $available_models = [
            'claude-3-5-sonnet-20241022' => [
                'name' => 'Claude 3.5 Sonnet Latest (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-3-5-haiku-20241022' => [
                'name' => 'Claude 3.5 Haiku (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-3-opus-20240229' => [
                'name' => 'Claude 3 Opus (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-3-sonnet-20240229' => [
                'name' => 'Claude 3 Sonnet (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-3-haiku-20240307' => [
                'name' => 'Claude 3 Haiku (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ]
        ];

        return $available_models;
    }

    /**
     * Fetch available models from Google Gemini API
     *
     * @param string $api_key Google API key
     * @return array|\WP_Error Array of models or WP_Error on failure
     */
    public static function fetch_google_models($api_key) {
        if (empty($api_key)) {
            return new \WP_Error('missing_api_key', 'API key is required');
        }

        // Google API endpoint to list models
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $api_key;

        $response = wp_safe_remote_get($endpoint, [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            return new \WP_Error('api_error', "Google API returned HTTP {$status_code}: {$error_body}");
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error('json_error', 'Invalid JSON response from Google API: ' . json_last_error_msg());
        }

        if (!isset($data['models']) || !is_array($data['models'])) {
            return new \WP_Error('invalid_response', 'Invalid response format from Google API');
        }

        $available_models = [];

        foreach ($data['models'] as $model) {
            // Extract model ID (remove 'models/' prefix if present)
            $model_id = $model['name'] ?? '';
            if (strpos($model_id, 'models/') === 0) {
                $model_id = substr($model_id, 7);
            }

            // Only include Gemini models that support generateContent
            if (strpos($model_id, 'gemini-') !== 0) {
                continue;
            }

            $supported_methods = $model['supportedGenerationMethods'] ?? [];
            if (!in_array('generateContent', $supported_methods)) {
                continue;
            }

            // Get display name
            $display_name = $model['displayName'] ?? $model_id;

            // Get token limits
            $max_input_tokens = $model['inputTokenLimit'] ?? 8192;
            $max_output_tokens = $model['outputTokenLimit'] ?? 2048;

            $available_models[$model_id] = [
                'name' => $display_name . ' (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true, // Most Gemini models support this
                'supports_custom_temperature' => true,
                'max_input_tokens' => $max_input_tokens,
                'max_output_tokens' => $max_output_tokens,
                'description' => $model['description'] ?? ''
            ];
        }

        return $available_models;
    }

    /**
     * Update all AI models from all providers
     *
     * @return bool|\WP_Error True on success, WP_Error on failure
     */
    public static function update_all_models() {
        $settings = get_option('wpuf_ai', []);
        $all_models = [];

        // Get API keys
        $openai_key = $settings['openai_api_key'] ?? '';
        $google_key = $settings['google_api_key'] ?? '';

        // Fetch OpenAI models if key exists
        if (!empty($openai_key)) {
            $openai_models = self::fetch_openai_models($openai_key);
            if (!is_wp_error($openai_models) && is_array($openai_models)) {
                $all_models = array_merge($all_models, $openai_models);
            }
        }

        // Always include Anthropic models (static, no API validation required)
        $anthropic_models = self::fetch_anthropic_models();
        if (is_array($anthropic_models)) {
            $all_models = array_merge($all_models, $anthropic_models);
        }

        // Fetch Google models if key exists
        if (!empty($google_key)) {
            $google_models = self::fetch_google_models($google_key);
            if (!is_wp_error($google_models) && is_array($google_models)) {
                $all_models = array_merge($all_models, $google_models);
            }
        }

        // Store cache if we have models
        if (!empty($all_models)) {
            $cache_data = [
                'models' => $all_models,
                'last_updated' => time(),
            ];

            // Use transient for auto-expiring cache (24 hours)
            set_transient('wpuf_ai_models_cache', $cache_data, DAY_IN_SECONDS);

            return true;
        }

        return new \WP_Error('no_models_fetched', __('Failed to fetch models from any provider.', 'wp-user-frontend'));
    }

    /**
     * Clear models cache
     *
     * @since 1.0.0
     * @return void
     */
    public static function clear_models_cache() {
        delete_transient('wpuf_ai_models_cache');
    }

}

