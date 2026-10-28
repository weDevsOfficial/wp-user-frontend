<?php
/**
 * Lets a legacy admin-ajax handler answer a service call instead of dying
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Http;

use Exception;
use WP_Error;

/**
 * The frontend submit handlers end every error path with `wp_send_json_error()`
 * and the success path with `wp_send_json()`, both of which stop PHP. A REST
 * controller needs the same code to return instead. While `collect()` runs a
 * callable, `send()` throws this exception in place of answering, and the
 * exception becomes a `WP_Error` carrying the exact payload the AJAX client
 * would have received. Outside `collect()` nothing changes: `send()` answers
 * and dies as before.
 *
 * @since WPUF_SINCE
 */
class Ajax_Abort extends Exception {

    /**
     * Nesting depth of collect() calls (0 = answer and die, as always).
     *
     * @var int
     */
    private static $depth = 0;

    /**
     * The payload the AJAX client would have received.
     *
     * @var array
     */
    private $payload = [];

    /**
     * HTTP status the payload would have carried.
     *
     * @var int
     */
    private $status = 400;

    /**
     * @since WPUF_SINCE
     *
     * @param array $payload Payload (`success`, `error` or `data`, ...)
     * @param int   $status  HTTP status
     */
    public function __construct( array $payload, $status = 400 ) {
        $this->payload = $payload;
        $this->status  = (int) $status;

        parent::__construct( self::message_of( $payload ) );
    }

    /**
     * Whether a service is collecting the answer right now.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public static function collecting() {
        return self::$depth > 0;
    }

    /**
     * Run a legacy handler and return what it would have answered.
     *
     * The handler returns its success payload when it sees `collecting()`;
     * every `send()` on the way becomes the `WP_Error` returned here.
     *
     * @since WPUF_SINCE
     *
     * @param callable $handler The legacy handler
     *
     * @return mixed|WP_Error
     */
    public static function collect( callable $handler ) {
        ++self::$depth;

        try {
            return $handler();
        } catch ( Ajax_Abort $abort ) {
            return $abort->to_error();
        } finally {
            --self::$depth;
        }
    }

    /**
     * Answer the AJAX client, or hand the answer to the collecting service.
     *
     * @since WPUF_SINCE
     *
     * @param array $payload The JSON payload exactly as the AJAX client gets it
     * @param int   $status  HTTP status for the collected error
     *
     * @return void
     * @throws Ajax_Abort While collecting.
     */
    // phpcs:disable WordPress.Security.EscapeOutput -- the payload is JSON data handed to the caller or to wp_send_json(), not HTML output.
    public static function send( array $payload, $status = 400 ) {
        if ( self::collecting() ) {
            throw new self( $payload, $status );
        }

        wpuf_clear_buffer();
        wp_send_json( $payload ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the JSON answer, as wp_send_json_error() sends it.
    }

    /**
     * `wp_send_json_error( $data )` with the same collect behaviour.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $data   Error data
     * @param int   $status HTTP status for the collected error
     *
     * @return void
     * @throws Ajax_Abort While collecting.
     */
    public static function send_error( $data, $status = 400 ) {
        if ( self::collecting() ) {
            throw new self(
                [
                    'success' => false,
                    'data'    => $data,
                ],
                $status
            );
        }

        wp_send_json_error( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the JSON answer.
    }

    /**
     * The collected answer as a WP_Error (code `wpuf_submit_rejected`, the
     * message the client would have shown, the payload under `data`).
     *
     * @since WPUF_SINCE
     *
     * @return WP_Error
     */
    // phpcs:enable WordPress.Security.EscapeOutput
    public function to_error() {
        // wp_send_json_error() wraps its data; the client reads `error`,
        // `type`, `redirect_to` inside it, so the REST error carries them flat.
        $data = isset( $this->payload['data'] ) && is_array( $this->payload['data'] ) ? $this->payload['data'] : $this->payload;

        unset( $data['success'] );
        $data['status'] = $this->status;

        return new WP_Error( 'wpuf_submit_rejected', $this->getMessage(), $data );
    }

    /**
     * The human message inside an AJAX payload (`error`, `data.error`,
     * `data.message`, `message` or a bare string).
     *
     * @since WPUF_SINCE
     *
     * @param array $payload Payload
     *
     * @return string
     */
    public static function message_of( array $payload ) {
        foreach ( [ 'error', 'message' ] as $key ) {
            if ( isset( $payload[ $key ] ) && is_scalar( $payload[ $key ] ) ) {
                return (string) $payload[ $key ];
            }
        }

        if ( isset( $payload['data'] ) ) {
            if ( is_scalar( $payload['data'] ) ) {
                return (string) $payload['data'];
            }

            if ( is_array( $payload['data'] ) ) {
                return self::message_of( $payload['data'] );
            }
        }

        return __( 'Something went wrong', 'wp-user-frontend' );
    }
}
