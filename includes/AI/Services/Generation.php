<?php
/**
 * AI form builder: generation service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\AI\Services;

use WeDevs\Wpuf\AI\Services\Provider_Settings;

use WeDevs\Wpuf\AI\FormGenerator;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;

/**
 * Generating a form from a prompt: input checks, the provider call and the
 * attempt log. Takes plain arguments; AI\RestController reads the request
 * and answers with the result.
 *
 * @since WPUF_SINCE Moved out of AI\RestController.
 */
class Generation {

    /**
     * Option holding the last generation attempts (basic analytics).
     */
    const LOG_OPTION = 'wpuf_ai_generation_log';

    /**
     * Attempts kept in the log.
     */
    const LOG_SIZE = 100;

    /**
     * The provider client.
     *
     * @var FormGenerator
     */
    protected $form_generator;

    /**
     * The settings store (the `wpuf_ai` section).
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
     * Generate a form from a prompt.
     *
     * @since WPUF_SINCE
     *
     * @param array $args {
     *     @type string $prompt               The prompt (required)
     *     @type string $session_id           Chat session id (`[A-Za-z0-9_-]{1,64}`)
     *     @type array  $conversation_context Conversation context
     *     @type string $form_type            post | profile | registration
     *     @type string $integration          Integration id or ''
     *     @type string $provider             Provider id (stored provider when empty)
     *     @type float  $temperature          Temperature
     *     @type int    $max_tokens           Max tokens
     *     @type string $language             Language name
     * }
     *
     * @return array|WP_Error `[ 'success' => true, 'data' => $result ]` or the error (status in its data)
     */
    public function generate( array $args ) {
        $args = wp_parse_args(
            $args,
            [
                'prompt'               => '',
                'session_id'           => '',
                'conversation_context' => [],
                'form_type'            => 'post',
                'integration'          => '',
                'provider'             => '',
                'temperature'          => null,
                'max_tokens'           => null,
                'language'             => 'English',
            ]
        );

        $prompt               = (string) $args['prompt'];
        $session_id           = (string) $args['session_id'];
        $conversation_context = is_array( $args['conversation_context'] ) ? $args['conversation_context'] : [];
        $integration          = (string) $args['integration'];
        $provider             = $args['provider'];

        // Validate session ID format to prevent injection.
        if ( '' !== $session_id && ! preg_match( '/^[a-zA-Z0-9_-]{1,64}$/', $session_id ) ) {
            return new WP_Error(
                'invalid_session',
                __( 'Invalid session ID format', 'wp-user-frontend' ),
                [ 'status' => 400 ]
            );
        }

        // No key for the provider: say so instead of sending a request the provider rejects.
        $stored_ai    = $this->settings->read( 'wpuf_ai' );
        $stored_ai    = is_array( $stored_ai ) ? $stored_ai : [];
        $key_provider = ! empty( $provider ) && is_string( $provider ) ? $provider : ( isset( $stored_ai['ai_provider'] ) ? $stored_ai['ai_provider'] : 'openai' );

        if ( '' === Provider_Settings::api_key_for( $stored_ai, $key_provider ) ) {
            return new WP_Error(
                'generation_failed',
                __( 'No API key is set for the selected AI provider. Add one in the AI settings.', 'wp-user-frontend' ),
                [ 'status' => 400 ]
            );
        }

        try {
            if ( '' !== $integration ) {
                $conversation_context['integration'] = $integration;
            }

            $result = $this->form_generator->generate_form(
                $prompt,
                [
                    'session_id'           => $session_id,
                    'conversation_context' => $conversation_context,
                    'form_type'            => $args['form_type'] ? $args['form_type'] : 'post',
                    'provider'             => $provider,
                    'temperature'          => $args['temperature'],
                    'max_tokens'           => $args['max_tokens'],
                    'language'             => $args['language'] ? $args['language'] : 'English',
                ]
            );

            $this->log_attempt( $prompt, $result );

            if ( isset( $result['error'] ) && $result['error'] ) {
                return new WP_Error(
                    'generation_failed',
                    isset( $result['message'] ) ? $result['message'] : __( 'Form generation failed', 'wp-user-frontend' ),
                    [ 'status' => 400 ]
                );
            }

            // The integration travels with the answer so a regeneration keeps it.
            if ( '' !== $integration ) {
                $result['integration'] = $integration;
            }

            return [
                'success' => true,
                'data'    => $result,
            ];
        } catch ( \Exception $e ) {
            return new WP_Error(
                'generation_error',
                __( 'An error occurred while generating the form. Please try again.', 'wp-user-frontend' ),
                [ 'status' => 500 ]
            );
        }
    }

    /**
     * Log a generation attempt (last LOG_SIZE entries, option LOG_OPTION).
     *
     * @since WPUF_SINCE
     *
     * @param string $prompt The prompt
     * @param array  $result Generation result
     *
     * @return void
     */
    private function log_attempt( $prompt, $result ) {
        $result = is_array( $result ) ? $result : [];
        $entry  = [
            'user_id'       => get_current_user_id(),
            'timestamp'     => current_time( 'mysql' ),
            'prompt_length' => strlen( $prompt ),
            'provider'      => isset( $result['provider'] ) ? $result['provider'] : 'unknown',
            'success'       => isset( $result['success'] ) ? $result['success'] : false,
            'session_id'    => isset( $result['session_id'] ) ? $result['session_id'] : '',
        ];

        $log = get_option( self::LOG_OPTION, [] );
        $log = is_array( $log ) ? $log : [];
        array_unshift( $log, $entry );

        update_option( self::LOG_OPTION, array_slice( $log, 0, self::LOG_SIZE ) );
    }
}
