<?php
/**
 * Platform REST controller base
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\Contracts\RestRoute;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;

/**
 * Base of every WPUF REST controller: capability permission callbacks with
 * explicit 401/403, pagination headers, typed casts and `wpuf_{resource}_{reason}`
 * errors. The frozen controllers (FormListController, SubscriptionController, SettingsController)
 * extend it too and keep their own paths, arguments, permissions and responses.
 *
 * @since WPUF_SINCE
 */
abstract class RestController extends WP_REST_Controller implements RestRoute {

    /**
     * Route namespace.
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Resource name used in error codes, e.g. 'form' -> wpuf_form_not_found.
     *
     * @var string
     */
    protected $resource = 'resource';

    /**
     * A permission callback for a WPUF capability: logged out 401, missing
     * capability 403.
     *
     * @since WPUF_SINCE
     *
     * @param string $cap One of the Caps constants
     *
     * @return callable
     */
    public function permission( $cap ) {
        return function () use ( $cap ) {
            if ( ! is_user_logged_in() ) {
                return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
            }

            if ( ! Caps::can( $cap ) ) {
                return new WP_Error( 'wpuf_rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'wp-user-frontend' ), [ 'status' => 403 ] );
            }

            return true;
        };
    }

    /**
     * Register one route of this controller's namespace.
     *
     * @since WPUF_SINCE
     *
     * @param string   $path       Route (with its leading slash)
     * @param string   $methods    Methods (WP_REST_Server constants)
     * @param string   $callback   Method of this class
     * @param callable $permission Permission callback
     * @param array    $args       Argument schema
     *
     * @return void
     */
    protected function route( $path, $methods, $callback, $permission, $args = [] ) {
        register_rest_route(
            $this->namespace,
            $path,
            [
                [
                    'methods'             => $methods,
                    'callback'            => [ $this, $callback ],
                    'permission_callback' => $permission,
                    'args'                => $args,
                ],
            ]
        );
    }

    /**
     * The argument schema of a list route, the same on every list endpoint:
     * `page`, `per_page` (1-100), `orderby` (from the given columns), `order`
     * (asc / desc) and the search string.
     *
     * @since WPUF_SINCE
     *
     * @param string[] $orderby         Sortable columns
     * @param string   $default_orderby Column sorted on by default
     * @param array    $options         order (default 'desc'), per_page (default 20,
     *                                  null for none), search (the search argument's
     *                                  name, default 'search', '' for none)
     *
     * @return array
     */
    protected function collection_args( array $orderby, $default_orderby, array $options = [] ) {
        $options = array_merge(
            [
                'order'    => 'desc',
                'per_page' => 20,
                'search'   => 'search',
            ],
            $options
        );

        $args = [
            'page'     => [
                'type'    => 'integer',
                'minimum' => 1,
                'default' => 1,
            ],
            'per_page' => [
                'type'    => 'integer',
                'minimum' => 1,
                'maximum' => 100,
            ],
            'orderby'  => [
                'type'    => 'string',
                'enum'    => array_values( $orderby ),
                'default' => $default_orderby,
            ],
            'order'    => [
                'type'    => 'string',
                'enum'    => [ 'asc', 'desc' ],
                'default' => $options['order'],
            ],
        ];

        if ( null !== $options['per_page'] ) {
            $args['per_page']['default'] = (int) $options['per_page'];
        }

        if ( '' !== $options['search'] ) {
            $args[ $options['search'] ] = [
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ];
        }

        return $args;
    }

    /**
     * A response with `X-WP-Total` / `X-WP-TotalPages` headers.
     *
     * @since WPUF_SINCE
     *
     * @param array $items    Items of the current page
     * @param int   $total    Total items
     * @param int   $per_page Items per page
     *
     * @return WP_REST_Response
     */
    protected function paginate( $items, $total, $per_page ) {
        $total    = max( 0, (int) $total );
        $per_page = max( 1, (int) $per_page );
        $response = rest_ensure_response( array_values( (array) $items ) );

        $response->header( 'X-WP-Total', (string) $total );
        $response->header( 'X-WP-TotalPages', (string) (int) ceil( $total / $per_page ) );

        return $response;
    }

    /**
     * A `wpuf_{resource}_{reason}` error.
     *
     * @since WPUF_SINCE
     *
     * @param string $reason  Short reason, e.g. 'not_found'
     * @param string $message Translated message
     * @param int    $status  HTTP status
     *
     * @return WP_Error
     */
    protected function error( $reason, $message, $status = 400 ) {
        return new WP_Error( 'wpuf_' . $this->resource . '_' . $reason, $message, [ 'status' => (int) $status ] );
    }

    /**
     * Cast to int.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Value
     *
     * @return int
     */
    protected function cast_int( $value ) {
        return is_numeric( $value ) ? (int) $value : 0;
    }

    /**
     * Cast to bool; 'yes', 'on', 'true', '1' are true.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    protected function cast_bool( $value ) {
        if ( is_string( $value ) ) {
            return in_array( strtolower( $value ), [ 'yes', 'on', 'true', '1' ], true );
        }

        return (bool) $value;
    }

    /**
     * Cast to string (arrays and objects become '').
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Value
     *
     * @return string
     */
    protected function cast_string( $value ) {
        return is_scalar( $value ) ? (string) $value : '';
    }

    /**
     * Cast to array ('' and null become [], a scalar becomes [ scalar ]).
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Value
     *
     * @return array
     */
    protected function cast_array( $value ) {
        if ( null === $value || '' === $value ) {
            return [];
        }

        return is_array( $value ) ? $value : (array) $value;
    }
}
