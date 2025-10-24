<?php

namespace WeDevs\Wpuf\AI;

/**
 * AI Configuration - Single Source of Truth
 *
 * Centralized configuration for all AI providers and models.
 * This class provides consistent data across the entire plugin.
 *
 * @since WPUF_SINCE
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
     * Get all model configurations with their technical parameters
     *
     * @return array Model configurations
     */
    public static function get_models() {
        return [
            // OpenAI GPT-4.1 Series (Latest - December 2024)
            'gpt-4.1' => [
                'name' => 'GPT-4.1 - Latest Flagship (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4.1-mini' => [
                'name' => 'GPT-4.1 Mini - Fast & Smart (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4.1-nano' => [
                'name' => 'GPT-4.1 Nano - Fastest & Cheapest (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],

            // OpenAI O1 Series (Reasoning Models)
            'o1' => [
                'name' => 'O1 - Full Reasoning Model (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_completion_tokens',
                'token_location' => 'body',
                'temperature' => 1.0,
                'supports_json_mode' => false,
                'supports_custom_temperature' => false
            ],
            'o1-mini' => [
                'name' => 'O1 Mini - Cost-Effective Reasoning (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_completion_tokens',
                'token_location' => 'body',
                'temperature' => 1.0,
                'supports_json_mode' => false,
                'supports_custom_temperature' => false
            ],
            'o1-preview' => [
                'name' => 'O1 Preview - Limited Access (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_completion_tokens',
                'token_location' => 'body',
                'temperature' => 1.0,
                'supports_json_mode' => false,
                'supports_custom_temperature' => false
            ],

            // OpenAI GPT-4o Series (Multimodal)
            'gpt-4o' => [
                'name' => 'GPT-4o - Multimodal (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4o-mini' => [
                'name' => 'GPT-4o Mini - Efficient Multimodal (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4o-2024-08-06' => [
                'name' => 'GPT-4o Latest Snapshot (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],

            // OpenAI GPT-4 Turbo & Legacy
            'gpt-4-turbo' => [
                'name' => 'GPT-4 Turbo (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4-turbo-2024-04-09' => [
                'name' => 'GPT-4 Turbo Latest (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-4' => [
                'name' => 'GPT-4 (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-3.5-turbo' => [
                'name' => 'GPT-3.5 Turbo (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gpt-3.5-turbo-0125' => [
                'name' => 'GPT-3.5 Turbo Latest (OpenAI)',
                'provider' => 'openai',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],

            // Anthropic Claude 4 Series (Latest Generation)
            'claude-4-opus' => [
                'name' => 'Claude 4 Opus - Best Coding Model (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-4-sonnet' => [
                'name' => 'Claude 4 Sonnet - Advanced Reasoning (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-4.1-opus' => [
                'name' => 'Claude 4.1 Opus - Most Capable (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],

            // Anthropic Claude 3.7 Series
            'claude-3.7-sonnet' => [
                'name' => 'Claude 3.7 Sonnet - Hybrid Reasoning (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],

            // Anthropic Claude 3.5 Series (Current Available)
            'claude-3-5-sonnet-20241022' => [
                'name' => 'Claude 3.5 Sonnet Latest (Anthropic)',
                'provider' => 'anthropic',
                'token_param' => 'max_tokens',
                'token_location' => 'body',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'claude-3-5-sonnet-20240620' => [
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

            // Anthropic Claude 3 Legacy
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
            ],

            // Google Gemini (Current Models)
            'gemini-2.0-flash-exp' => [
                'name' => 'Gemini 2.0 Flash Experimental - Latest (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gemini-1.5-flash' => [
                'name' => 'Gemini 1.5 Flash - Fast & Free (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gemini-1.5-flash-8b' => [
                'name' => 'Gemini 1.5 Flash 8B - Fast & Free (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gemini-1.5-pro' => [
                'name' => 'Gemini 1.5 Pro - Most Capable (Google)',
                'provider' => 'google',
                'token_param' => 'maxOutputTokens',
                'token_location' => 'generationConfig',
                'supports_json_mode' => true,
                'supports_custom_temperature' => true
            ],
            'gemini-1.0-pro' => [
                'name' => 'Gemini 1.0 Pro - Stable (Google)',
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
        $models = self::get_models();
        
        // Check for exact match
        if (isset($models[$model_id])) {
            return $models[$model_id];
        }

        // Check for pattern matches (e.g., gpt-5-turbo-preview matches gpt-5-turbo)
        foreach ($models as $pattern => $config) {
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
}

