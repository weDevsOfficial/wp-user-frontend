<?php
/**
 * Plugin Name: WPUF e2e AI provider mock
 * Description: TEST ONLY (never shipped). Answers the AI form builder's provider calls (OpenAI, Anthropic, Google) with canned forms, only when the request carries the mock key, so the AI builder can be tested end to end without a real key.
 *
 * Scenarios, picked from the REST request (`prompt`, `form_type`, the current form in `conversation_context`):
 * - prompt contains "mock-error": the provider answers HTTP 500 (provider error path);
 * - prompt contains "not-a-form": the model answers an error object (generation_failed);
 * - first generation: a contact style form for post forms, a sign-up form for registration;
 *   "pro-fields" in the prompt adds a phone and a date field (Pro fields); with an
 *   integration (WooCommerce, EDD, Dokan...) the title ends with " (<integration>)";
 * - chat (a current form is sent): "website" adds a Website field, "date" adds an Event Date
 *   field, "remove the message" drops the Message field, anything else adds "Extra Field".
 * - Settings "Test Connection": any request with the key gets HTTP 200 (connection successful);
 * - Google model list (Settings "Fetch latest models"): one model, `gemini-mock-flash`;
 * - builder "AI Generate Options" (AJAX `wpuf_ai_generate_field_options`): three options
 *   "Mock Red", "Mock Green", "Mock Blue".
 *
 * Every answered request is counted in the option `wpuf_ai_mock_calls` (tests read it to
 * prove a chat question made no request).
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The key that switches the mock on. A real key never matches, so a site with a real
 * provider key is untouched even with this file installed.
 */
const WPUF_AI_MOCK_KEY = 'sk-wpuf-e2e-mock';

add_filter( 'pre_http_request', 'wpuf_ai_mock_answer', 10, 3 );

/**
 * Answer a provider request that carries the mock key.
 *
 * @param false|array $pre  Short-circuit value.
 * @param array       $args Request arguments.
 * @param string      $url  Request URL.
 *
 * @return false|array
 */
function wpuf_ai_mock_answer( $pre, $args, $url ) {
    $provider = wpuf_ai_mock_provider( $url );

    if ( ! $provider || ! wpuf_ai_mock_has_key( $provider, $args, $url ) ) {
        return $pre;
    }

    update_option( 'wpuf_ai_mock_calls', (int) get_option( 'wpuf_ai_mock_calls', 0 ) + 1, false );

    if ( 'google' === $provider && false !== strpos( $url, '/models?key=' ) ) {
        return wpuf_ai_mock_http(
            200, [
                'models' => [
                    [
                        'name'                       => 'models/gemini-mock-flash',
                        'displayName'                => 'Gemini Mock Flash',
                        'supportedGenerationMethods' => [ 'generateContent' ],
                        'inputTokenLimit'            => 8192,
                        'outputTokenLimit'           => 2048,
                    ],
                ],
            ]
        );
    }

    // The builder's "AI Generate Options": REST `ai-form-builder/generate-options` (the
    // admin-ajax action it replaced is kept for older builds).
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- test mock, reads the action name only.
    if ( false !== strpos( rawurldecode( $uri ), 'ai-form-builder/generate-options' ) || ( wp_doing_ajax() && isset( $_POST['action'] ) && 'wpuf_ai_generate_field_options' === $_POST['action'] ) ) {
        return wpuf_ai_mock_reply( $provider, [ 'options' => [ 'Mock Red', 'Mock Green', 'Mock Blue' ] ] );
    }

    $request = wpuf_ai_mock_rest_body();

    if ( ! isset( $request['prompt'] ) ) {
        // Internal requests (rest_do_request, WP-CLI) have no HTTP body: take the prompt
        // the provider was sent.
        $request['prompt'] = wpuf_ai_mock_provider_prompt( $provider, $args );
    }

    $prompt = strtolower( (string) $request['prompt'] );

    if ( false !== strpos( $prompt, 'mock-error' ) ) {
        return wpuf_ai_mock_http( 500, [ 'error' => [ 'message' => 'Mock provider is down' ] ] );
    }

    if ( false !== strpos( $prompt, 'not-a-form' ) ) {
        return wpuf_ai_mock_reply( $provider, [ 'error' => true, 'message' => 'I can only help with forms (mock).' ] );
    }

    return wpuf_ai_mock_reply( $provider, wpuf_ai_mock_form( $request, $prompt ) );
}

/**
 * Which provider a URL belongs to.
 *
 * @param string $url URL.
 *
 * @return string openai|anthropic|google or ''.
 */
function wpuf_ai_mock_provider( $url ) {
    if ( false !== strpos( $url, 'api.openai.com' ) ) {
        return 'openai';
    }

    if ( false !== strpos( $url, 'api.anthropic.com' ) ) {
        return 'anthropic';
    }

    if ( false !== strpos( $url, 'generativelanguage.googleapis.com' ) ) {
        return 'google';
    }

    return '';
}

/**
 * Whether the request uses the mock key.
 *
 * @param string $provider Provider.
 * @param array  $args     Request arguments.
 * @param string $url      Request URL.
 *
 * @return bool
 */
function wpuf_ai_mock_has_key( $provider, $args, $url ) {
    $headers = isset( $args['headers'] ) ? (array) $args['headers'] : [];

    if ( 'google' === $provider ) {
        return false !== strpos( $url, 'key=' . WPUF_AI_MOCK_KEY );
    }

    if ( 'anthropic' === $provider ) {
        return isset( $headers['x-api-key'] ) && WPUF_AI_MOCK_KEY === $headers['x-api-key'];
    }

    return isset( $headers['Authorization'] ) && 'Bearer ' . WPUF_AI_MOCK_KEY === $headers['Authorization'];
}

/**
 * The JSON body of the current REST request (prompt, form_type, conversation_context).
 *
 * @return array
 */
function wpuf_ai_mock_rest_body() {
    $raw  = file_get_contents( 'php://input' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    $body = json_decode( (string) $raw, true );

    return is_array( $body ) ? $body : [];
}

/**
 * The user prompt inside a provider request body.
 *
 * @param string $provider Provider.
 * @param array  $args     Request arguments.
 *
 * @return string
 */
function wpuf_ai_mock_provider_prompt( $provider, $args ) {
    $body = json_decode( isset( $args['body'] ) ? (string) $args['body'] : '', true );

    if ( 'google' === $provider ) {
        $parts = isset( $body['contents'] ) ? (array) end( $body['contents'] ) : [];

        return isset( $parts['parts'][0]['text'] ) ? $parts['parts'][0]['text'] : '';
    }

    $messages = isset( $body['messages'] ) ? (array) $body['messages'] : [];
    $last     = $messages ? end( $messages ) : [];

    return isset( $last['content'] ) && is_string( $last['content'] ) ? $last['content'] : '';
}

/**
 * The minimal form the model "returns" (Form_Builder turns it into WPUF fields).
 *
 * @param array  $request REST body.
 * @param string $prompt  Prompt, lower case.
 *
 * @return array
 */
function wpuf_ai_mock_form( $request, $prompt ) {
    $current = isset( $request['conversation_context']['current_form']['wpuf_fields'] ) ? (array) $request['conversation_context']['current_form']['wpuf_fields'] : [];

    if ( $current ) {
        $fields = [];

        foreach ( $current as $field ) {
            $template = isset( $field['template'] ) ? $field['template'] : ( isset( $field['type'] ) ? $field['type'] : 'text_field' );
            $label    = isset( $field['label'] ) ? $field['label'] : 'Field';

            if ( false !== strpos( $prompt, 'remove the message' ) && 'Message' === $label ) {
                continue;
            }

            $minimal = [
                'template' => $template,
                'label'    => $label,
                'required' => isset( $field['required'] ) && in_array( $field['required'], [ 'yes', true, 'true' ], true ) ? 'yes' : 'no',
            ];

            if ( ! empty( $field['options'] ) ) {
                $minimal['options'] = $field['options'];
            }

            $fields[] = $minimal;
        }

        if ( false !== strpos( $prompt, 'website' ) ) {
            $fields[] = [ 'template' => 'website_url', 'label' => 'Website', 'required' => 'no' ];
        } elseif ( false !== strpos( $prompt, 'date' ) ) {
            $fields[] = [ 'template' => 'date_field', 'label' => 'Event Date', 'required' => 'no' ];
        } elseif ( false === strpos( $prompt, 'remove the message' ) ) {
            $fields[] = [ 'template' => 'text_field', 'label' => 'Extra Field', 'required' => 'no' ];
        }

        return [
            'form_title'       => isset( $request['conversation_context']['current_form']['form_title'] ) ? $request['conversation_context']['current_form']['form_title'] : 'Mock Form',
            'form_description' => 'Updated by the mock assistant.',
            'fields'           => $fields,
        ];
    }

    $type = isset( $request['form_type'] ) ? $request['form_type'] : 'post';

    if ( in_array( $type, [ 'profile', 'registration' ], true ) ) {
        $fields = [
            [ 'template' => 'user_email', 'label' => 'Email', 'required' => 'yes' ],
            [ 'template' => 'user_login', 'label' => 'Username', 'required' => 'yes' ],
            [ 'template' => 'password', 'label' => 'Password', 'required' => 'yes' ],
            [ 'template' => 'first_name', 'label' => 'First Name', 'required' => 'no' ],
        ];
        $title  = 'Mock Sign Up Form';
    } else {
        $fields = [
            [ 'template' => 'post_title', 'label' => 'Title', 'required' => 'yes' ],
            [ 'template' => 'post_content', 'label' => 'Message', 'required' => 'yes' ],
            [ 'template' => 'email_address', 'label' => 'Email', 'required' => 'yes' ],
            [
                'template' => 'dropdown_field',
                'label'    => 'Topic',
                'required' => 'no',
                'options'  => [
                    'sales'   => 'Sales',
                    'support' => 'Support',
                ],
            ],
        ];
        $title  = 'Mock Contact Form';
    }

    if ( false !== strpos( $prompt, 'pro-fields' ) ) {
        $fields[] = [ 'template' => 'phone_field', 'label' => 'Phone', 'required' => 'no' ];
        $fields[] = [ 'template' => 'date_field', 'label' => 'Visit Date', 'required' => 'no' ];
    }

    if ( ! empty( $request['integration'] ) ) {
        $title .= ' (' . sanitize_key( $request['integration'] ) . ')';
    }

    return [
        'form_title'       => $title,
        'form_description' => 'A form generated by the mock provider.',
        'fields'           => $fields,
    ];
}

/**
 * A 200 provider response whose model text is the JSON of $payload.
 *
 * @param string $provider Provider.
 * @param array  $payload  Model answer.
 *
 * @return array
 */
function wpuf_ai_mock_reply( $provider, $payload ) {
    $text = wp_json_encode( $payload );

    if ( 'anthropic' === $provider ) {
        return wpuf_ai_mock_http( 200, [ 'content' => [ [ 'type' => 'text', 'text' => $text ] ] ] );
    }

    if ( 'google' === $provider ) {
        return wpuf_ai_mock_http( 200, [ 'candidates' => [ [ 'content' => [ 'parts' => [ [ 'text' => $text ] ] ] ] ] ] );
    }

    return wpuf_ai_mock_http( 200, [ 'choices' => [ [ 'message' => [ 'role' => 'assistant', 'content' => $text ] ] ] ] );
}

/**
 * A WP HTTP API response array.
 *
 * @param int   $code Status.
 * @param array $body Body.
 *
 * @return array
 */
function wpuf_ai_mock_http( $code, $body ) {
    return [
        'headers'  => [ 'content-type' => 'application/json' ],
        'body'     => wp_json_encode( $body ),
        'response' => [
            'code'    => $code,
            'message' => 200 === $code ? 'OK' : 'Internal Server Error',
        ],
        'cookies'  => [],
        'filename' => null,
    ];
}
