<?php
/**
 * Stray output guard for JSON answers
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Http;

use WeDevs\Wpuf\Platform\Contracts\Hookable;

/**
 * Keeps the plugin's own REST and AJAX answers valid JSON when something
 * else prints before them: another plugin's deprecation notice shown
 * because display_errors is on, a stray echo or whitespace after a
 * closing PHP tag, a warning while WP_DEBUG_DISPLAY is on.
 *
 * On a request for a `wpuf/v1` route or a `wpuf_*` AJAX action the guard
 * opens an output buffer when the platform boots (plugins_loaded).
 *
 * - REST: right before WordPress prints the answer (`rest_pre_serve_request`
 *   at the lowest priority) everything buffered so far is dropped. Headers
 *   are not sent yet, so the status and `Content-Type` still apply.
 * - AJAX: `wp_send_json()` prints its JSON and calls `wp_die()`. The
 *   wrapped AJAX die handler takes the buffer, and when it is "noise + one
 *   JSON document" prints only the JSON. Anything else (HTML partials,
 *   `-1`/`0`, plain text) is printed as it was.
 *
 * Dropped output goes to the PHP error log when WP_DEBUG is on, so the
 * cause stays visible. Core's `wp_debug_mode()` already mutes display_errors
 * for AJAX and `Accept: application/json` requests; this covers the rest.
 *
 * Filters: `wpuf_json_output_guard` (bool, default true),
 * `wpuf_json_output_guard_rest_namespaces`, `wpuf_json_output_guard_ajax_prefixes`.
 *
 * @since WPUF_SINCE
 */
class JsonOutputGuard implements Hookable {

    /**
     * REST namespaces the guard protects.
     */
    const REST_NAMESPACES = [ 'wpuf/v1' ];

    /**
     * AJAX action prefixes the guard protects.
     */
    const AJAX_PREFIXES = [ 'wpuf_', 'wpuf-' ];

    /**
     * How many `{` / `[` positions are tried when looking for the JSON tail.
     */
    const MAX_JSON_CANDIDATES = 64;

    /**
     * Output buffer level of the guard's buffer, 0 when not buffering.
     *
     * @var int
     */
    private $level = 0;

    /**
     * Request kind: 'rest', 'ajax' or '' when the guard does nothing.
     *
     * @var string
     */
    private $kind = '';

    /**
     * Open the buffer for a guarded request and hook its release.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks() {
        if ( ! $this->enabled() ) {
            return;
        }

        $this->kind = $this->request_kind();

        if ( '' === $this->kind || ! $this->start() ) {
            return;
        }

        if ( 'rest' === $this->kind ) {
            add_filter( 'rest_pre_serve_request', [ $this, 'drop_before_rest_answer' ], -9999, 4 );

            return;
        }

        add_filter( 'wp_die_ajax_handler', [ $this, 'wrap_ajax_die_handler' ], 9999 );
    }

    /**
     * Whether the guard is on (filterable, off under WP-CLI).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function enabled() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return false;
        }

        /**
         * Turn the stray output guard off.
         *
         * @since WPUF_SINCE
         *
         * @param bool $enabled Default true.
         */
        return (bool) apply_filters( 'wpuf_json_output_guard', true );
    }

    /**
     * Whether this request is a guarded REST or AJAX request.
     *
     * @since WPUF_SINCE
     *
     * @return string 'rest', 'ajax' or ''.
     */
    public function request_kind() {
        if ( wp_doing_ajax() ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only reads the action name to decide on buffering.
            $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

            return $this->is_guarded_ajax_action( $action ) ? 'ajax' : '';
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only reads the route to decide on buffering.
        $route = isset( $_GET['rest_route'] ) ? sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ) : '';

        if ( '' === $route && isset( $_SERVER['REQUEST_URI'] ) ) {
            $path   = (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
            $prefix = '/' . trim( rest_get_url_prefix(), '/' ) . '/';
            $at     = strpos( $path, $prefix );

            if ( false !== $at ) {
                $route = '/' . substr( $path, $at + strlen( $prefix ) );
            }
        }

        return $this->is_guarded_rest_route( $route ) ? 'rest' : '';
    }

    /**
     * Whether a REST route belongs to a guarded namespace.
     *
     * @since WPUF_SINCE
     *
     * @param string $route Route, like `/wpuf/v1/forms`.
     *
     * @return bool
     */
    public function is_guarded_rest_route( $route ) {
        $route = '/' . ltrim( (string) $route, '/' );

        /**
         * REST namespaces whose answers are guarded against stray output.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $namespaces Default `[ 'wpuf/v1' ]`.
         */
        $namespaces = (array) apply_filters( 'wpuf_json_output_guard_rest_namespaces', self::REST_NAMESPACES );

        foreach ( $namespaces as $namespace ) {
            $namespace = '/' . trim( (string) $namespace, '/' ) . '/';

            if ( 0 === strpos( $route, $namespace ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether an AJAX action is guarded.
     *
     * @since WPUF_SINCE
     *
     * @param string $action Action name.
     *
     * @return bool
     */
    public function is_guarded_ajax_action( $action ) {
        if ( '' === $action ) {
            return false;
        }

        /**
         * AJAX action prefixes whose answers are guarded against stray output.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $prefixes Default `[ 'wpuf_', 'wpuf-' ]`.
         */
        $prefixes = (array) apply_filters( 'wpuf_json_output_guard_ajax_prefixes', self::AJAX_PREFIXES );

        foreach ( $prefixes as $prefix ) {
            if ( '' !== $prefix && 0 === strpos( $action, $prefix ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the guard's buffer is open.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_buffering() {
        return $this->level > 0 && ob_get_level() >= $this->level;
    }

    /**
     * Open the guard's buffer.
     *
     * @since WPUF_SINCE
     *
     * @return bool Whether it opened.
     */
    public function start() {
        if ( $this->level > 0 ) {
            return true;
        }

        if ( ! ob_start() ) {
            return false;
        }

        $this->level = ob_get_level();

        return true;
    }

    /**
     * Drop everything printed before a guarded REST answer.
     *
     * `rest_pre_serve_request` callback; never serves the request itself.
     *
     * @since WPUF_SINCE
     *
     * @param bool             $served  Whether the request was served already.
     * @param mixed            $result  Response.
     * @param \WP_REST_Request $request Request.
     * @param \WP_REST_Server  $server  Server.
     *
     * @return bool The same $served.
     */
    public function drop_before_rest_answer( $served, $result, $request, $server ) {
        if ( $request instanceof \WP_REST_Request && ! $this->is_guarded_rest_route( $request->get_route() ) ) {
            // Not ours after all: print what was buffered, untouched.
            echo $this->release(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buffered output printed as it was.

            return $served;
        }

        $this->log( $this->release(), 'REST' );

        return $served;
    }

    /**
     * Wrap the AJAX die handler so the buffered answer is cleaned first.
     *
     * `wp_die_ajax_handler` callback.
     *
     * @since WPUF_SINCE
     *
     * @param callable $handler Die handler.
     *
     * @return callable
     */
    public function wrap_ajax_die_handler( $handler ) {
        $guard = $this;

        return function ( $message = '', $title = '', $args = [] ) use ( $handler, $guard ) {
            $guard->print_ajax_answer();

            return call_user_func( $handler, $message, $title, $args );
        };
    }

    /**
     * Print the buffered AJAX answer: only its JSON when stray output came first.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function print_ajax_answer() {
        $output = $this->release();

        if ( '' === $output ) {
            return;
        }

        $json = self::json_tail( $output );

        if ( null === $json ) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buffered output printed as it was.

            return;
        }

        $this->log( substr( $output, 0, strlen( $output ) - strlen( $json ) ), 'AJAX' );

        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the JSON document the handler printed.
    }

    /**
     * The JSON document at the end of some output.
     *
     * @since WPUF_SINCE
     *
     * @param string $output Output.
     *
     * @return string|null The output itself when it is JSON already, its JSON tail, or null when it has none.
     */
    public static function json_tail( $output ) {
        $output = (string) $output;

        if ( '' === trim( $output ) ) {
            return null;
        }

        if ( self::is_json( $output ) ) {
            return $output;
        }

        $length = strlen( $output );
        $tries  = 0;

        for ( $at = 0; $at < $length && $tries < self::MAX_JSON_CANDIDATES; $at++ ) {
            if ( '{' !== $output[ $at ] && '[' !== $output[ $at ] ) {
                continue;
            }

            $tries++;
            $candidate = substr( $output, $at );

            if ( self::is_json( $candidate ) ) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Close the guard's buffer and return what it holds.
     *
     * Buffers opened above the guard's are flushed into it first, as PHP
     * would do at shutdown, so the result is what the response would have
     * carried. Returns '' when the buffer is not open any more.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function release() {
        if ( $this->level <= 0 ) {
            return '';
        }

        while ( ob_get_level() > $this->level ) {
            ob_end_flush();
        }

        if ( ob_get_level() < $this->level ) {
            $this->level = 0;

            return '';
        }

        $output      = (string) ob_get_clean();
        $this->level = 0;

        return $output;
    }

    /**
     * Whether a string is one JSON document (objects and arrays only).
     *
     * @since WPUF_SINCE
     *
     * @param string $value Value.
     *
     * @return bool
     */
    private static function is_json( $value ) {
        $value = trim( $value );

        if ( '' === $value || ( '{' !== $value[0] && '[' !== $value[0] ) ) {
            return false;
        }

        json_decode( $value );

        return JSON_ERROR_NONE === json_last_error();
    }

    /**
     * Log dropped output when WP_DEBUG is on.
     *
     * @since WPUF_SINCE
     *
     * @param string $dropped Dropped output.
     * @param string $kind    'REST' or 'AJAX'.
     *
     * @return void
     */
    private function log( $dropped, $kind ) {
        $dropped = trim( (string) $dropped );

        if ( '' === $dropped || ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $excerpt = preg_replace( '/\s+/', ' ', substr( wp_strip_all_tags( $dropped ), 0, 300 ) );

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- support trace, WP_DEBUG only.
        error_log( sprintf( 'WPUF: dropped %d bytes of stray output before a %s JSON answer: %s', strlen( $dropped ), $kind, $excerpt ) );
    }
}
