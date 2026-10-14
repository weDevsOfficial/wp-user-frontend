<?php
/**
 * Plugin Name: WPUF contract runtime recorder (tests only)
 * Description: Records per admin screen the WPUF hooks fired (with argument count), script/style handles and localized globals with their keys. Copy into wp-content/mu-plugins on a TEST site; enable with `wp option update wpuf_contract_record <label>`.
 *
 * Output: wp-content/uploads/wpuf-contracts/<label>/<screen>.json
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_contract_label = get_option( 'wpuf_contract_record' );

// The admin app's builder boot (REST) belongs to the builder screen it opens.
$wpuf_contract_rest_builder = isset( $_SERVER['REQUEST_URI'] ) && preg_match( '#wpuf/v1/admin/forms/\d+/builder#', rawurldecode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

if ( ! $wpuf_contract_label || wp_doing_ajax() || ( ! is_admin() && ! $wpuf_contract_rest_builder ) ) {
    return;
}

$GLOBALS['wpuf_contract_hooks'] = [];

add_action(
    'all',
    function ( $name ) {
        if ( ! preg_match( '/wpuf|user_frontend|user-frontend/i', $name ) ) {
            return;
        }

        $count = func_num_args() - 1;

        if ( ! isset( $GLOBALS['wpuf_contract_hooks'][ $name ] ) ) {
            $GLOBALS['wpuf_contract_hooks'][ $name ] = [
                'args'  => $count,
                'calls' => 0,
                'kind'  => doing_filter( $name ) && ! did_action( $name ) ? 'filter' : 'action',
            ];
        }

        $GLOBALS['wpuf_contract_hooks'][ $name ]['calls']++;
        $GLOBALS['wpuf_contract_hooks'][ $name ]['args'] = max( $GLOBALS['wpuf_contract_hooks'][ $name ]['args'], $count );
    }
);

/**
 * Returns the key tree of a decoded value, two levels deep.
 *
 * @param mixed $value Value.
 * @param int   $depth Remaining depth.
 *
 * @return mixed
 */
function wpuf_contract_keys( $value, $depth = 2 ) {
    if ( ! is_array( $value ) ) {
        return gettype( $value );
    }

    if ( array_values( $value ) === $value ) {
        return 'list';
    }

    if ( $depth <= 0 ) {
        return 'map';
    }

    $out = [];

    foreach ( $value as $key => $item ) {
        $out[ $key ] = wpuf_contract_keys( $item, $depth - 1 );
    }

    ksort( $out );

    return $out;
}

/**
 * Collects the WPUF handles of a dependency registry.
 *
 * @param WP_Dependencies $deps Registry.
 *
 * @return array
 */
function wpuf_contract_handles( $deps ) {
    $out = [];

    foreach ( $deps->registered as $handle => $item ) {
        $src = is_string( $item->src ) ? $item->src : '';

        if ( ! preg_match( '/wpuf/i', $handle ) && ! preg_match( '#plugins/(wp-user-frontend|wpuf-pro)/#', $src ) ) {
            continue;
        }

        $globals = [];

        if ( ! empty( $item->extra['data'] ) && preg_match_all( '/var\s+([A-Za-z0-9_]+)\s*=\s*(.*?);\s*(?=var\s|$)/s', $item->extra['data'], $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $globals[ $match[1] ] = wpuf_contract_keys( json_decode( $match[2], true ) );
            }
        }

        $out[ $handle ] = [
            'src'      => preg_replace( '#^.*?/wp-content/plugins/#', '', $src ),
            'deps'     => array_values( $item->deps ),
            'enqueued' => in_array( $handle, $deps->done, true ) || in_array( $handle, $deps->queue, true ),
            'globals'  => $globals,
        ];
    }

    ksort( $out );

    return $out;
}

/**
 * Whether the site runs the single admin app (old screen URLs redirect to it).
 *
 * @return bool
 */
function wpuf_contract_app_on() {
    return function_exists( 'wpuf_admin_app_enabled' ) && wpuf_admin_app_enabled();
}

/**
 * The route window globals of the admin app (printed as `window.wpufAdmin`
 * boot data on the runtime script, not as localized `var`s): name => keys.
 *
 * @return array
 */
function wpuf_contract_app_globals() {
    $runtime = wp_scripts()->query( 'wpuf-admin-runtime' );
    $before  = $runtime && ! empty( $runtime->extra['before'] ) ? implode( "\n", array_filter( (array) $runtime->extra['before'] ) ) : '';
    $globals = [];

    if ( ! preg_match( '/window\.wpufAdmin = (.*?);$/s', $before, $match ) ) {
        return $globals;
    }

    $boot = json_decode( $match[1], true );

    foreach ( isset( $boot['app']['globals'] ) ? (array) $boot['app']['globals'] : [] as $group ) {
        foreach ( (array) $group as $name => $value ) {
            // Placeholders before a route opens (the builder's `{}`): the
            // route's real data is recorded where it comes from (REST boot).
            if ( [] === $value ) {
                continue;
            }

            $globals[ $name ] = wpuf_contract_merge_keys( isset( $globals[ $name ] ) ? $globals[ $name ] : null, wpuf_contract_keys( $value ) );
        }
    }

    return $globals;
}

/**
 * Union of two key trees.
 *
 * @param mixed $a Tree.
 * @param mixed $b Tree.
 *
 * @return mixed
 */
function wpuf_contract_merge_keys( $a, $b ) {
    if ( ! is_array( $a ) || ! is_array( $b ) ) {
        return is_array( $b ) || null === $a ? $b : $a;
    }

    foreach ( $b as $key => $value ) {
        $a[ $key ] = wpuf_contract_merge_keys( isset( $a[ $key ] ) ? $a[ $key ] : null, $value );
    }

    ksort( $a );

    return $a;
}

/**
 * Union of two recordings (hooks, handles, globals).
 *
 * @param array $a Recording.
 * @param array $b Recording.
 *
 * @return array
 */
function wpuf_contract_merge( array $a, array $b ) {
    foreach ( [ 'scripts', 'styles' ] as $type ) {
        foreach ( isset( $b[ $type ] ) ? $b[ $type ] : [] as $handle => $item ) {
            if ( ! isset( $a[ $type ][ $handle ] ) ) {
                $a[ $type ][ $handle ] = $item;
                continue;
            }

            $a[ $type ][ $handle ]['enqueued'] = $a[ $type ][ $handle ]['enqueued'] || $item['enqueued'];
            $a[ $type ][ $handle ]['deps']     = array_values( array_unique( array_merge( $a[ $type ][ $handle ]['deps'], $item['deps'] ) ) );
            $a[ $type ][ $handle ]['globals']  = wpuf_contract_merge_keys( $a[ $type ][ $handle ]['globals'], $item['globals'] );
        }

        if ( isset( $a[ $type ] ) ) {
            ksort( $a[ $type ] );
        }
    }

    foreach ( isset( $b['hooks'] ) ? $b['hooks'] : [] as $hook => $meta ) {
        if ( ! isset( $a['hooks'][ $hook ] ) ) {
            $a['hooks'][ $hook ] = $meta;
            continue;
        }

        $a['hooks'][ $hook ]['calls'] += $meta['calls'];
        $a['hooks'][ $hook ]['args']   = max( $a['hooks'][ $hook ]['args'], $meta['args'] );
    }

    ksort( $a['hooks'] );

    return $a;
}

/**
 * Write a recording (merged into what this label already holds for the name
 * when $merge).
 *
 * @param string $label Label.
 * @param string $name  File name.
 * @param array  $data  Recording.
 * @param bool   $merge Merge into the existing file.
 *
 * @return void
 */
function wpuf_contract_write( $label, $name, array $data, $merge = false ) {
    $dir  = WP_CONTENT_DIR . '/uploads/wpuf-contracts/' . sanitize_key( $label );
    $file = $dir . '/' . sanitize_key( $name ) . '.json';

    wp_mkdir_p( $dir );

    if ( $merge && file_exists( $file ) ) {
        $data = wpuf_contract_merge( json_decode( file_get_contents( $file ), true ), $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    }

    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    file_put_contents( $file, wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
}

// Builder boot over REST: its data is the builder screen's localized globals.
$GLOBALS['wpuf_contract_rest_globals'] = [];

add_filter(
    'rest_post_dispatch',
    function ( $response ) {
        $data = $response instanceof WP_REST_Response ? $response->get_data() : null;

        foreach ( isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : [] as $name => $value ) {
            if ( 0 === strpos( $name, 'wpuf_' ) ) {
                // As the browser receives it (objects such as WP_Post become maps).
                $GLOBALS['wpuf_contract_rest_globals'][ $name ] = wpuf_contract_keys( json_decode( wp_json_encode( $value ), true ) );
            }
        }

        return $response;
    }
);

add_action(
    'shutdown',
    function () use ( $wpuf_contract_label, $wpuf_contract_rest_builder ) {
        $hooks = $GLOBALS['wpuf_contract_hooks'];
        ksort( $hooks );

        // REST builder boot: merge into the builder screen the app page opened.
        if ( $wpuf_contract_rest_builder ) {
            foreach ( (array) get_option( 'wpuf_contract_last', [] ) as $last ) {
                wpuf_contract_write(
                    $wpuf_contract_label,
                    $last,
                    [
                        'scripts' => [
                            'wpuf-form-builder-react' => [
                                'src'      => '',
                                'deps'     => [],
                                'enqueued' => true,
                                'globals'  => $GLOBALS['wpuf_contract_rest_globals'],
                            ],
                        ],
                        'styles'  => [],
                        'hooks'   => $hooks,
                    ],
                    true
                );
            }

            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        if ( ! $screen || ! preg_match( '/wpuf|user-frontend|user_frontend/i', $screen->id . ' ' . $screen->post_type ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $suffix = isset( $_GET['action'] ) ? '-' . sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $suffix .= isset( $_GET['tab'] ) ? '-' . sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $data = [
            'screen'  => $screen->id,
            'scripts' => wpuf_contract_handles( wp_scripts() ),
            'styles'  => wpuf_contract_handles( wp_styles() ),
            'hooks'   => $hooks,
        ];
        $name = $screen->id . $suffix;

        if ( wpuf_contract_app_on() ) {
            $redirect = '';

            foreach ( headers_list() as $header ) {
                if ( 0 === stripos( $header, 'Location:' ) ) {
                    $redirect = trim( substr( $header, 9 ) );
                }
            }

            // An old screen URL that redirects (to the app page, or through the
            // builder page to it): its screen is shown by the request it leads
            // to, so its recording waits and is merged there.
            if ( $redirect && false !== strpos( $redirect, 'wp-admin/admin.php' ) ) {
                $pending   = (array) get_option( 'wpuf_contract_pending', [] );
                $pending[] = [ $name, $data ];
                update_option( 'wpuf_contract_pending', $pending, false );

                return;
            }

            if ( 'toplevel_page_wp-user-frontend' === $screen->id ) {
                $data['scripts']['wpuf-admin-runtime']['globals'] = wpuf_contract_merge_keys(
                    isset( $data['scripts']['wpuf-admin-runtime']['globals'] ) ? $data['scripts']['wpuf-admin-runtime']['globals'] : [],
                    wpuf_contract_app_globals()
                );
            }

            $pending = (array) get_option( 'wpuf_contract_pending', [] );
            $shown   = $pending ? [] : [ $name ];
            delete_option( 'wpuf_contract_pending' );

            foreach ( $pending as $item ) {
                wpuf_contract_write( $wpuf_contract_label, $item[0], wpuf_contract_merge( $item[1], $data ) );
                $shown[] = $item[0];
            }

            // The builder boot that follows merges into the screens shown here.
            update_option( 'wpuf_contract_last', $shown, false );

            if ( $pending ) {
                return;
            }
        }

        wpuf_contract_write( $wpuf_contract_label, $name, $data );
    },
    PHP_INT_MAX
);
