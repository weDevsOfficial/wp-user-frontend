<?php
/**
 * AI form builder: provider settings service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\AI\Services;

use WeDevs\Wpuf\AI\Config;
use WeDevs\Wpuf\AI\FormGenerator;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * The AI settings and providers: connection test, provider and integration
 * lists, settings read and save, model lists. The `wpuf_ai` section is read
 * and written through the settings store; takes plain arguments.
 *
 * @since WPUF_SINCE Moved out of AI\RestController.
 */
class Provider_Settings {

    /**
     * The settings section.
     */
    const SECTION = 'wpuf_ai';

    /**
     * Transient caching the provider model lists.
     */
    const MODELS_CACHE = 'wpuf_ai_models_cache';

    /**
     * The provider client.
     *
     * @var FormGenerator
     */
    protected $form_generator;

    /**
     * The settings store.
     *
     * @var SettingsStore
     */
    protected $settings;

    /**
     * @since WPUF_SINCE
     *
     * @param FormGenerator      $form_generator The provider client.
     * @param SettingsStore|null $settings       The settings store (container default).
     */
    public function __construct( FormGenerator $form_generator, $settings = null ) {
        $this->form_generator = $form_generator;
        $this->settings       = $settings instanceof SettingsStore ? $settings : Stores::settings();
    }

    /**
     * The stored `wpuf_ai` section, [] when none.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    /**
     * The stored API key of a provider: the settings screen stores `{provider}_api_key`,
     * the AI settings REST route used to store a single `ai_api_key`; both are read.
     *
     * @since WPUF_SINCE
     *
     * @param array  $settings The `wpuf_ai` option
     * @param string $provider Provider id (openai, anthropic, google)
     *
     * @return string '' when none is stored
     */
    public static function api_key_for( $settings, $provider ) {
        $settings = is_array( $settings ) ? $settings : [];
        $provider = (string) $provider;

        if ( '' !== $provider && ! empty( $settings[ $provider . '_api_key' ] ) ) {
            return (string) $settings[ $provider . '_api_key' ];
        }

        return ! empty( $settings['ai_api_key'] ) ? (string) $settings['ai_api_key'] : '';
    }

    /**
     * Provider, model and whether a key is stored: the one answer to "is AI configured?"
     * for the builder, the forms lists, the AI builder screen and the manager.
     *
     * @since WPUF_SINCE
     *
     * @return array { @type string $provider ('' when unset) @type string $model ('' when unset)
     *                 @type bool $has_api_key @type bool $configured (provider, model and key present)
     *                 @type float $temperature @type int $max_tokens }
     */
    public function status() {
        $stored   = $this->stored();
        $provider = isset( $stored['ai_provider'] ) ? (string) $stored['ai_provider'] : '';
        $model    = isset( $stored['ai_model'] ) ? (string) $stored['ai_model'] : '';
        $has_key  = '' !== self::api_key_for( $stored, $provider );

        return [
            'provider'    => $provider,
            'model'       => $model,
            'has_api_key' => $has_key,
            'configured'  => '' !== $provider && '' !== $model && $has_key,
            'temperature' => isset( $stored['temperature'] ) ? (float) $stored['temperature'] : 0.7,
            'max_tokens'  => isset( $stored['max_tokens'] ) ? (int) $stored['max_tokens'] : 2000,
        ];
    }

    public function stored() {
        $stored = $this->settings->read( self::SECTION );

        return is_array( $stored ) ? $stored : [];
    }

    /**
     * Test the connection to a provider.
     *
     * The settings screen only has the masked key, so a masked copy of the
     * stored key means "use the stored one".
     *
     * @since WPUF_SINCE
     *
     * @param string $api_key  API key (or its masked copy)
     * @param string $provider Provider id
     * @param string $model    Model id
     *
     * @return array `[ 'success' => bool, 'message' => string, ... ]`
     */
    public function test_connection( $api_key, $provider, $model ) {
        try {
            $stored_ai = $this->stored();
            $stored    = is_string( $provider ) && isset( $stored_ai[ $provider . '_api_key' ] ) ? $stored_ai[ $provider . '_api_key' ] : '';

            if ( function_exists( 'wpuf_settings_is_masked_secret' ) && wpuf_settings_is_masked_secret( $api_key, $stored, 4 ) ) {
                $api_key = $stored;
            }

            return $this->form_generator->test_connection( $api_key, $provider, $model );
        } catch ( \Exception $e ) {
            return [
                'success'   => false,
                'message'   => $e->getMessage(),
                'exception' => true,
            ];
        }
    }

    /**
     * The providers and the current one.
     *
     * @since WPUF_SINCE
     *
     * @return array `[ 'success' => true, 'providers' => [...], 'current_provider' => string ]`
     */
    public function providers() {
        return [
            'success'          => true,
            'providers'        => $this->form_generator->get_providers(),
            'current_provider' => $this->form_generator->get_current_provider(),
        ];
    }

    /**
     * The integrations available for a form type (installed plugins), filtered
     * through `wpuf_ai_integrations`.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type post | profile | registration
     *
     * @return array `[ 'success' => true, 'integrations' => [...], 'form_type' => string ]`
     */
    public function integrations( $form_type = 'post' ) {
        $form_type = $form_type ? $form_type : 'post';

        $all_integrations = [
            // Post form integrations.
            'woocommerce'     => [
                'id'          => 'woocommerce',
                'label'       => __( 'WooCommerce Product', 'wp-user-frontend' ),
                'description' => __( 'Create WooCommerce product submission forms', 'wp-user-frontend' ),
                'enabled'     => class_exists( 'WooCommerce', false ),
                'form_types'  => [ 'post' ],
                'icon'        => 'woocommerce',
            ],
            'edd'             => [
                'id'          => 'edd',
                'label'       => __( 'Easy Digital Downloads', 'wp-user-frontend' ),
                'description' => __( 'Create EDD download submission forms', 'wp-user-frontend' ),
                'enabled'     => class_exists( 'Easy_Digital_Downloads' ),
                'form_types'  => [ 'post' ],
                'icon'        => 'download',
            ],
            'events_calendar' => [
                'id'          => 'events_calendar',
                'label'       => __( 'The Events Calendar', 'wp-user-frontend' ),
                'description' => __( 'Create event submission forms', 'wp-user-frontend' ),
                'enabled'     => class_exists( 'Tribe__Events__Main' ),
                'form_types'  => [ 'post' ],
                'icon'        => 'calendar',
            ],
            // Registration form integrations.
            'dokan'           => [
                'id'          => 'dokan',
                'label'       => __( 'Dokan Vendor', 'wp-user-frontend' ),
                'description' => __( 'Create Dokan vendor registration forms', 'wp-user-frontend' ),
                'enabled'     => class_exists( 'WeDevs_Dokan' ),
                'form_types'  => [ 'profile', 'registration' ],
                'icon'        => 'store',
            ],
            'wc_vendors'      => [
                'id'          => 'wc_vendors',
                'label'       => __( 'WC Vendors', 'wp-user-frontend' ),
                'description' => __( 'Create WC Vendors registration forms', 'wp-user-frontend' ),
                'enabled'     => class_exists( 'WC_Vendors' ),
                'form_types'  => [ 'profile', 'registration' ],
                'icon'        => 'store',
            ],
            'wcfm'            => [
                'id'          => 'wcfm',
                'label'       => __( 'WCFM Membership', 'wp-user-frontend' ),
                'description' => __( 'Create WCFM vendor registration forms', 'wp-user-frontend' ),
                'enabled'     => class_exists( 'WCFMvm' ),
                'form_types'  => [ 'profile', 'registration' ],
                'icon'        => 'store',
            ],
        ];

        $available = [];

        foreach ( $all_integrations as $integration ) {
            if ( $integration['enabled'] && in_array( $form_type, $integration['form_types'], true ) ) {
                $available[] = $integration;
            }
        }

        /**
         * Filter available AI form builder integrations
         *
         * Allows pro plugin to add or modify available integrations.
         *
         * @since 4.2.2
         *
         * @param array  $available_integrations Array of integration objects
         * @param string $form_type              Form type ('post' or 'profile')
         */
        $available = apply_filters( 'wpuf_ai_integrations', $available, $form_type );

        return [
            'success'      => true,
            'integrations' => $available,
            'form_type'    => $form_type,
        ];
    }

    /**
     * Save the AI settings (provider, model, key, temperature, max tokens) and
     * refresh the cached model lists when they are older than an hour.
     *
     * @since WPUF_SINCE
     *
     * @param array $input {
     *     @type string     $provider    Provider id
     *     @type string     $model       Model id
     *     @type string     $api_key     API key ('' keeps the stored one)
     *     @type float|null $temperature 0..1
     *     @type int|null   $max_tokens  100..4000
     * }
     *
     * @return array `[ 'success' => bool, 'message' => string ]`
     */
    public function save( array $input ) {
        $input = wp_parse_args(
            $input,
            [
                'provider'    => '',
                'model'       => '',
                'api_key'     => '',
                'temperature' => null,
                'max_tokens'  => null,
            ]
        );

        $existing    = $this->stored();
        $api_key     = $input['api_key'];
        $temperature = $input['temperature'];
        $max_tokens  = $input['max_tokens'];

        if ( ! empty( $api_key ) ) {
            $api_key = sanitize_text_field( $api_key );

            if ( strlen( $api_key ) < 10 ) {
                return [
                    'success' => false,
                    'message' => __( 'API key appears to be too short', 'wp-user-frontend' ),
                ];
            }
        }

        if ( null !== $temperature ) {
            $temperature = max( 0.0, min( 1.0, (float) $temperature ) );
        }

        if ( null !== $max_tokens ) {
            $max_tokens = max( 100, min( 4000, (int) $max_tokens ) );
        }

        $provider = $input['provider'] ? sanitize_key( $input['provider'] ) : ( isset( $existing['ai_provider'] ) ? $existing['ai_provider'] : 'openai' );

        // Merge into the stored section: the settings screen keeps one key per provider
        // (`{provider}_api_key`) and other fields there; this route must not drop them.
        $settings = array_merge(
            $existing,
            [
                'ai_provider' => $provider,
                'ai_model'    => $input['model'] ? $input['model'] : ( isset( $existing['ai_model'] ) ? $existing['ai_model'] : 'gpt-3.5-turbo' ),
                'temperature' => null !== $temperature ? $temperature : ( isset( $existing['temperature'] ) ? $existing['temperature'] : 0.7 ),
                'max_tokens'  => null !== $max_tokens ? $max_tokens : ( isset( $existing['max_tokens'] ) ? $existing['max_tokens'] : 2000 ),
            ]
        );

        if ( ! empty( $api_key ) ) {
            $settings[ $provider . '_api_key' ] = $api_key;
            $settings['ai_api_key']             = $api_key;
        }

        // Unchanged settings are not a failure to save (update_option() returns false then).
        $saved = $settings === $existing || $this->settings->write_section( self::SECTION, $settings ) === $settings;

        if ( ! $saved ) {
            return [
                'success' => false,
                'message' => __( 'Failed to save settings', 'wp-user-frontend' ),
            ];
        }

        $this->refresh_models_if_stale();

        return [
            'success' => true,
            'message' => __( 'Settings saved successfully', 'wp-user-frontend' ),
        ];
    }

    /**
     * The settings as the screens read them (the key only as "is it set").
     *
     * @since WPUF_SINCE
     *
     * @return array `[ 'success' => true, 'settings' => [ provider, model, temperature, max_tokens, has_api_key ] ]`
     */
    public function read() {
        $stored = $this->stored();
        $status = $this->status();

        return [
            'success'  => true,
            'settings' => [
                'provider'    => '' !== $status['provider'] ? $status['provider'] : 'openai',
                'model'       => '' !== $status['model'] ? $status['model'] : 'gpt-3.5-turbo',
                'temperature' => isset( $stored['temperature'] ) ? $stored['temperature'] : 0.7,
                'max_tokens'  => isset( $stored['max_tokens'] ) ? $stored['max_tokens'] : 2000,
                'has_api_key' => $status['has_api_key'],
            ],
        ];
    }

    /**
     * Refresh the model lists of every provider (needs the stored Google key).
     *
     * @since WPUF_SINCE
     *
     * @return array `[ 'success' => bool, 'message' => string, 'models' => [...] ]`
     */
    public function refresh_google_models() {
        $stored = $this->stored();

        if ( empty( $stored['google_api_key'] ) ) {
            return [
                'success' => false,
                'message' => __( 'Google API key not configured. Please save your Google API key first.', 'wp-user-frontend' ),
            ];
        }

        $result = Config::update_all_models();

        if ( is_wp_error( $result ) ) {
            return [
                'success' => false,
                'message' => $result->get_error_message(),
            ];
        }

        return [
            'success' => true,
            'message' => __( 'Google models updated successfully', 'wp-user-frontend' ),
            'models'  => Config::get_models(),
        ];
    }

    /**
     * The cached model lists.
     *
     * @since WPUF_SINCE
     *
     * @return array `[ 'success' => true, 'models' => [...] ]`
     */
    public function models() {
        return [
            'success' => true,
            'models'  => Config::get_models(),
        ];
    }

    /**
     * Fetch the model lists again when the cache is missing or older than an hour.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function refresh_models_if_stale() {
        $cached = get_transient( self::MODELS_CACHE );

        if ( false !== $cached && is_array( $cached ) && isset( $cached['last_updated'] ) && ( time() - $cached['last_updated'] ) <= HOUR_IN_SECONDS ) {
            return;
        }

        // Every provider with a key; a failure here does not fail the save.
        Config::update_all_models();
    }
}
