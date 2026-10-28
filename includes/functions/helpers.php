<?php
/**
 * General helpers (templates, dates, sanitizing, buffering, encryption)
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

use WeDevs\Wpuf\Encryption_Helper;

/**
 * Start output buffering
 *
 * This is needed for redirecting to post when a new post has made
 *
 * @since 0.8
 */
function wpuf_buffer_start() {
    ob_start();
}

/**
 * Unserialize a value without ever instantiating PHP objects.
 *
 * Stored post meta may hold a serialized object payload (e.g. submitted through a
 * form field). Passing it to maybe_unserialize() would instantiate arbitrary
 * classes, enabling PHP object injection. This restricts deserialization to plain
 * data and strips any object, so a serialized-object payload can never be revived.
 *
 * @since 4.3.11
 *
 * @param mixed $value Possibly-serialized value.
 *
 * @return mixed Unserialized data with objects removed, or the original value.
 */
function wpuf_safe_unserialize( $value ) {
    if ( ! is_string( $value ) || ! is_serialized( $value ) ) {
        return $value;
    }

    if ( PHP_VERSION_ID >= 70000 ) {
        return @unserialize( $value, [ 'allowed_classes' => false ] );
    }

    // PHP 5.6 fallback: refuse anything carrying an object marker (O:/C:).
    if ( preg_match( '/(?:^|;|\{)[OC]:[0-9]+:/', $value ) ) {
        return $value;
    }

    return maybe_unserialize( $value );
}

/**
 * Option dropdown helper
 *
 * @param array  $options
 * @param string $selected
 *
 * @return string
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_dropdown_helper( $options, $selected = '' ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $string = '';

    foreach ( $options as $key => $label ) {
        $string .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $selected, $key, false ), $label );
    }

    return $string;
}

/**
 * Include a template file
 *
 * Looks up first on the theme directory, if not found
 * lods from plugins folder
 *
 * @since 2.2
 *
 * @param string $file file name or path to file
 */
function wpuf_load_template( $file, $args = [] ) {
    if ( $args && is_array( $args ) ) {
        extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- the template variables, as the templates have always received them.
    }

    $child_theme_dir  = get_stylesheet_directory() . '/wpuf/';
    $parent_theme_dir = get_template_directory() . '/wpuf/';
    $wpuf_dir         = WPUF_ROOT . '/templates/';

    if ( file_exists( $child_theme_dir . $file ) ) {
        include $child_theme_dir . $file;
    } elseif ( file_exists( $parent_theme_dir . $file ) ) {
        include $parent_theme_dir . $file;
    } elseif ( file_exists( $wpuf_dir . $file ) ) {
        include $wpuf_dir . $file;
    }
}

/**
 * Helper function for formatting date field
 *
 * @since 0.1
 *
 * @param string $date
 * @param bool   $show_time
 *
 * @return string
 */
function wpuf_get_date( $date, $show_time = false, $format = false ) {
    if ( empty( $date ) ) {
        return $date;
    }

    $timestamp = strtotime( $date );

    if ( $format ) {
        $dateobj = DateTime::createFromFormat( $format, $date );

        if ( $dateobj ) {
            $timestamp = $dateobj->getTimestamp();
        }
    }

    $format = get_option( 'date_format' );

    if ( $show_time ) {
        $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    }

    return date_i18n( $format, $timestamp );
}

/**
 * Helper function for converting a normal date string to unix date/time string
 *
 * @since 0.1
 *
 * @param string $date
 * @param int    $gmt
 *
 * @return string
 */
function wpuf_date2mysql( $date, $gmt = 0 ) {
    if ( empty( $date ) ) {
        return;
    }
    $time = strtotime( $date );

    return ( $gmt ) ? gmdate( 'Y-m-d H:i:s', $time ) : gmdate( 'Y-m-d H:i:s', ( $time + ( intval( get_option( 'timezone_string' ) ) * 3600 ) ) );
}

/**
 * Clear the buffer
 *
 * Prevents ajax breakage and endless loading icon. A LIFE SAVER!!!
 *
 * @return void
 */
function wpuf_clear_buffer() {
    ob_clean();
}

if ( ! function_exists( 'array_column' ) ) {
    function array_column( $input, $column_key, $index_key = null ) {
        $result = [];

        foreach ( $input as $k => $v ) {
            $result[ $index_key ? $v[ $index_key ] : $k ] = $v[ $column_key ];
        }

        return $result;
    }
}

/**
 * Get the client IP address
 *
 * @since 2.5.2
 *
 * @return string
 */
function wpuf_get_client_ip() {
    $ipaddress = '';

    if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
    } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
    } elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
    } elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
    } elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
    } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

/**
 * Encryption function for various usage
 *
 * @since 2.5.8
 * @since 2.5.29 param $nonce added
 *
 * @param string $id
 * @param string $nonce
 *
 * @return string|bool encoded string or false if encryption failed
 */
function wpuf_encryption( $id, $nonce = null ) {
    $auth_keys  = WeDevs\Wpuf\Encryption_Helper::get_encryption_auth_keys();
    $secret_key = $auth_keys['auth_key'];
    $secret_iv  = ! empty( $nonce ) ? base64_decode( $nonce ) : $auth_keys['auth_salt'];

    if ( function_exists( 'sodium_crypto_secretbox' ) ) {
        try {
            return base64_encode( sodium_crypto_secretbox( $id, $secret_iv, $secret_key ) );
        } catch ( Exception $e ) {
            delete_option( 'wpuf_auth_keys' );
            return false;
        }
    }

    $ciphertext_raw = openssl_encrypt( $id, Encryption_Helper::get_encryption_method(), $secret_key, OPENSSL_RAW_DATA, $secret_iv );
    // Authenticate the IV together with the ciphertext so a tampered IV is
    // rejected on the way back in (see wpuf_decryption()).
    $hmac = hash_hmac( 'sha256', $secret_iv . $ciphertext_raw, $secret_key, true );

    return base64_encode( $secret_iv . $hmac . $ciphertext_raw );
}

/**
 * Decryption function for various usage
 *
 * @since 2.5.8
 * @since 2.5.29 param $nonce added
 *
 * @param string $id
 * @param string $nonce
 *
 * @return string|bool decrypted string or false if decryption failed
 */
function wpuf_decryption( $id, $nonce = null ) {
    // get auth keys
    $auth_keys = Encryption_Helper::get_encryption_auth_keys();
    if ( empty( $auth_keys ) ) {
        return false;
    }

    $secret_key = $auth_keys['auth_key'];
    $secret_iv  = ! empty( $nonce ) ? base64_decode( $nonce ) : $auth_keys['auth_salt'];

    // should we use sodium_crypto_secretbox_open
    if ( function_exists( 'sodium_crypto_secretbox_open' ) ) {
        try {
            return sodium_crypto_secretbox_open( base64_decode( $id ), $secret_iv, $secret_key );
        } catch ( Exception $e ) {
            delete_option( 'wpuf_auth_keys' );
            return false;
        }
    }

    $c              = base64_decode( $id );
    $ivlen          = Encryption_Helper::get_encryption_nonce_length();
    $secret_iv      = substr( $c, 0, $ivlen );
    $hmac           = substr( $c, $ivlen, 32 );
    $ciphertext_raw = substr( $c, $ivlen + 32 );
    // The IV travels inside the payload, so it must be authenticated too: an
    // unauthenticated IV lets an attacker flip the first CBC plaintext block and
    // forge a different value under the unchanged ciphertext/HMAC (e.g. a higher
    // role in registration). Cover IV + ciphertext and verify BEFORE decrypting.
    // Reported by Murad Akhmedov (WPScan).
    $calcmac = hash_hmac( 'sha256', $secret_iv . $ciphertext_raw, $secret_key, true );

    // timing attack safe comparison
    if ( ! hash_equals( $hmac, $calcmac ) ) {
        return false;
    }

    return openssl_decrypt( $ciphertext_raw, Encryption_Helper::get_encryption_method(), $secret_key, OPENSSL_RAW_DATA, $secret_iv );
}

function wpuf_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'wpuf_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( wp_unslash( $var ) ) : $var;
    }
}

/**
 * Calculate ini directives in bytes
 *
 * @since 3.3.0
 *
 * @param string|int $val
 *
 * @return int
 */
function wpuf_ini_get_byte( $val ) {
    $byte = absint( $val );
    $char = strtolower( str_replace( $byte, '', $val ) );

    switch ( $char ) {
        case 'g':
            $byte *= GB_IN_BYTES;
            break;

        case 'm':
            $byte *= MB_IN_BYTES;
            break;

        case 'k':
            $byte *= KB_IN_BYTES;
            break;
    }

    return $byte;
}

/**
 * The maximum file size allowed to upload
 *
 * To upload large files, `post_max_size` value must be larger than `upload_max_filesize`
 *
 * @see https://www.php.net/manual/en/ini.core.php#ini.post-max-size
 *
 * @since 3.3.0
 *
 * @return string|int
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_max_upload_size() {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $post_max_size       = ini_get( 'post_max_size' );
    $upload_max_filesize = ini_get( 'upload_max_filesize' );

    if ( wpuf_ini_get_byte( $upload_max_filesize ) > wpuf_ini_get_byte( $post_max_size ) ) {
        return $post_max_size;
    }

    return $upload_max_filesize;
}

/**
 * Validate a boolean variable
 *
 * @since 3.3.0
 *
 * @param mixed $var
 *
 * @return bool
 */
function wpuf_validate_boolean( $var ) {
    return filter_var( $var, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Sanitize nested text field
 *
 * @param $arr
 *
 * @return array
 */
function wpuf_recursive_sanitize_text_field( $arr ) {
    foreach ( $arr as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = wpuf_recursive_sanitize_text_field( $value );
        } else {
            $value = sanitize_text_field( $value );
        }
    }

    return $arr;
}

/**
 * Function current_datetime() compatibility for wp version < 5.3
 *
 * @since 3.5.27
 *
 * @return DateTimeImmutable
 */
function wpuf_current_datetime() {
    if ( function_exists( 'current_datetime' ) ) {
        return current_datetime();
    }

    return new DateTimeImmutable( 'now', wpuf_wp_timezone() );
}

/**
 * Function wp_timezone() compatibility for wp version < 5.3
 *
 * @since 3.5.27
 *
 * @return DateTimeZone
 */
function wpuf_wp_timezone() {
    if ( function_exists( 'wp_timezone' ) ) {
        return wp_timezone();
    }

    return new DateTimeZone( wpuf_wp_timezone_string() );
}

/**
 * Function wp_timezone_string() compatibility for wp version < 5.3
 *
 * @since 3.5.27
 *
 * @return string
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_timezone_string() {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    if ( function_exists( 'wp_timezone_string' ) ) {
        return wp_timezone_string();
    }

    $timezone_string = get_option( 'timezone_string' );

    if ( $timezone_string ) {
        return $timezone_string;
    }

    $offset  = (float) get_option( 'gmt_offset' );
    $hours   = (int) $offset;
    $minutes = ( $offset - $hours );

    $sign      = ( $offset < 0 ) ? '-' : '+';
    $abs_hour  = abs( $hours );
    $abs_mins  = abs( $minutes * 60 );
    $tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

    return $tz_offset;
}

/**
 * Retrieves paginated links for queried pages
 * uses WordPress paginate_links() function for the final output
 *
 * @since 3.5.27_PRO
 *
 * @param int $total_items
 * @param int $per_page
 * @param array $pagination_args
 *
 * @return string
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_pagination( $total_items, $per_page, $pagination_args = [] ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $num_of_pages = ceil( $total_items / $per_page );

    $defaults = [
        'base'      => add_query_arg( 'pagenum', '%#%' ),
        'format'    => '',
        'prev_text' => '<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.248874 7.05115L7.19193 0.244361C7.35252 0.086801 7.56688 0 7.79545 0C8.02403 0 8.23839 0.086801 8.39898 0.244361L8.91029 0.745519C9.243 1.07208 9.243 1.60283 8.91029 1.9289L3.08003 7.64483L8.91675 13.3671C9.07734 13.5247 9.166 13.7347 9.166 13.9587C9.166 14.1829 9.07734 14.3929 8.91675 14.5506L8.40545 15.0517C8.24474 15.2092 8.0305 15.296 7.80192 15.296C7.57335 15.296 7.35898 15.2092 7.1984 15.0517L0.248874 8.23864C0.0879093 8.08058 -0.000500916 7.86955 2.13498e-06 7.64521C-0.000500916 7.42 0.0879093 7.20909 0.248874 7.05115Z" fill="#545D7A"/>
            </svg>',
        'next_text' => '<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.97963 7.05115L2.03657 0.244361C1.87599 0.086801 1.66162 0 1.43305 0C1.20448 0 0.99011 0.086801 0.829525 0.244361L0.318217 0.745519C-0.0144943 1.07208 -0.0144943 1.60283 0.318217 1.9289L6.14847 7.64483L0.311748 13.3671C0.151164 13.5247 0.0625 13.7347 0.0625 13.9587C0.0625 14.1829 0.151164 14.3929 0.311748 14.5506L0.823056 15.0517C0.983767 15.2092 1.19801 15.296 1.42658 15.296C1.65515 15.296 1.86952 15.2092 2.0301 15.0517L8.97963 8.23864C9.14059 8.08058 9.229 7.86955 9.2285 7.64521C9.229 7.42 9.14059 7.20909 8.97963 7.05115Z" fill="#545D7A"/>
            </svg>',
        'total'     => $num_of_pages,
        'current'   => $pagenum,
    ];

    $args = wp_parse_args( $pagination_args, $defaults );

    $page_links = paginate_links( $args );

    if ( $page_links ) {
        return '<div class="wpuf-pagination">' . $page_links . '</div>';
    }
}

/**
 * Require_once a file upon checking the existence of the file
 *
 * @since 4.0.0
 *
 * @param $file_location
 *
 * @return void
 */
function wpuf_require_once( $file_location ) {
    if ( file_exists( $file_location ) ) {
        require_once $file_location;
    }
}

/**
 * Include_once a file upon checking the existence of the file
 *
 * @since 4.0.0
 *
 * @param $file_location
 *
 * @return void
 */
function wpuf_include_once( $file_location ) {
    if ( file_exists( $file_location ) ) {
        include_once $file_location;
    }
}

/**
 * What a script needs to call the `wpuf/v1` REST routes: the namespace root
 * and the `wp_rest` nonce (sent as `X-WP-Nonce`). The classic scripts get it
 * in their localized objects (`rest`), so every request goes through REST.
 *
 * @since WPUF_SINCE
 *
 * @return array root, nonce
 */
function wpuf_rest_script_data() {
    return [
        'root'  => esc_url_raw( rest_url( 'wpuf/v1/' ) ),
        'nonce' => wp_create_nonce( 'wp_rest' ),
    ];
}

/**
 * A JavaScript expression that POSTs to a `wpuf/v1` route with the `wp_rest`
 * nonce and returns the fetch promise: for the inline scripts of the admin
 * notices, which print on any admin screen without the `wpuf-rest` script.
 *
 * @since WPUF_SINCE
 *
 * @param string $path Route under `wpuf/v1/`, e.g. `admin/notices/review/dismiss`
 * @param array  $data JSON body
 *
 * @return string JavaScript (built from wp_json_encode(), safe to print in a script)
 */
function wpuf_rest_post_js( $path, array $data = [] ) {
    $rest    = wpuf_rest_script_data();
    $options = [
        'method'      => 'POST',
        'credentials' => 'same-origin',
        'headers'     => [
            'X-WP-Nonce'   => $rest['nonce'],
            'Content-Type' => 'application/json',
        ],
        'body'        => wp_json_encode( (object) $data ),
    ];

    return sprintf(
        'window.fetch(%1$s,%2$s)',
        wp_json_encode( $rest['root'] . ltrim( $path, '/' ) ),
        wp_json_encode( $options )
    );
}

/*
 * Removed from wpuf-functions.php by the 5.0 clean-up; kept as deprecated public API
 * so outside code calling them does not fatal.
 */

/**
 * Upload the files of `wpuf_post_attachments` (the pre-2.0 attachment input)
 *
 * @param int $post_id Post id
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_upload_attachment( $post_id ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified by the submit/draft handler that calls this.
    if ( ! isset( $_FILES['wpuf_post_attachments'] ) ) {
        return false;
    }

    $fields = (int) wpuf_get_option( 'attachment_num' );

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified by the submit/draft handler that calls this.
    $wpuf_post_attachments = isset( $_FILES['wpuf_post_attachments'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_FILES['wpuf_post_attachments'] ) ) : [];

    for ( $i = 0; $i < $fields; $i++ ) {
        $file_name = basename( $wpuf_post_attachments['name'][ $i ] );

        if ( $file_name ) {
            if ( $file_name ) {
                $upload = [
                    'name'     => $wpuf_post_attachments['name'][ $i ],
                    'type'     => $wpuf_post_attachments['type'][ $i ],
                    'tmp_name' => $wpuf_post_attachments['tmp_name'][ $i ],
                    'error'    => $wpuf_post_attachments['error'][ $i ],
                    'size'     => $wpuf_post_attachments['size'][ $i ],
                ];

                wp_handle_upload( $upload );
            }//file exists
        }// end for
    }
}

/**
 * Render a settings text input (old settings screen markup)
 *
 * @param array $args Field arguments
 *
 * @return string
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_text( $args = [] ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $defaults = [
        'id'           => '',
        'name'         => isset( $name ) ? $name : 'text',
        'value'        => isset( $value ) ? $value : null,
        'label'        => isset( $label ) ? $label : null,
        'desc'         => isset( $desc ) ? $desc : null,
        'placeholder'  => '',
        'class'        => 'regular-text',
        'disabled'     => false,
        'autocomplete' => '',
        'data'         => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    $class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
    $disabled = '';

    if ( $args['disabled'] ) {
        $disabled = ' disabled="disabled"';
    }

    $data = '';

    if ( ! empty( $args['data'] ) ) {
        foreach ( $args['data'] as $key => $value ) {
            $data .= 'data-' . $key . '="' . esc_attr( $value ) . '" ';
        }
    }

    $output = '<span id="wpuf-' . $args['name'] . '-wrap">';

    if ( ! empty( $args['label'] ) ) {
        $output .= '<label class="wpuf-label" for="' . $args['id'] . '">' . esc_html( $args['label'] ) . '</label>';
    }

    if ( ! empty( $args['desc'] ) ) {
        $output .= '<span class="wpuf-description">' . wp_kses_post( $args['desc'] ) . '</span>';
    }

    $output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '/>';

    $output .= '</span>';

    return $output;
}

/**
 * Whether the license expired more than 15 days ago
 *
 * @since 2.3.13
 *
 * @return bool
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_is_license_expired() {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

    if ( in_array( $remote_addr, [ '127.0.0.1', '::1' ], true ) ) {
        return false;
    }

    $license_status = get_option( 'wpuf_license_status' );

    // seems like this wasn't activated at all
    if ( ! isset( $license_status->update ) ) {
        return false;
    }

    // if license has expired more than 15 days ago
    $update    = strtotime( $license_status->update );
    $threshold = strtotime( '+15 days', $update );

    // printf( 'Validity: %s, Threshold: %s', date( 'd-m-Y', $update), date( 'd-m-Y', $threshold ) );

    if ( time() >= $threshold ) {
        return true;
    }

    return false;
}

/**
 * Print a post status label for the user dashboard
 *
 * @param string $status Post status
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_admin_post_status( $status ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $fontcolor = '#000';
    $title = '';
    if ( 'publish' === $status ) {
        $title     = __( 'Published', 'wp-user-frontend' );
        $fontcolor = '#009200';
    } elseif ( 'draft' === $status || 'private' === $status ) {
        $title     = __( 'Draft', 'wp-user-frontend' );
        $fontcolor = '#bbbbbb';
    } elseif ( 'pending' === $status ) {
        $title     = __( 'Pending', 'wp-user-frontend' );
        $fontcolor = '#C00202';
    } elseif ( 'future' === $status ) {
        $title     = __( 'Scheduled', 'wp-user-frontend' );
        $fontcolor = '#bbbbbb';
    }

    echo wp_kses_post( '<span style="color:' . $fontcolor . ';">' . $title . '</span>' );
}

/**
 * URL of the Pro badge icon
 *
 * @since 4.1.0
 *
 * @return string
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_get_pro_icon() {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    return WPUF_ASSET_URI . '/images/pro-badge.svg';
}

/**
 * Print a value inside <pre> (debug helper)
 *
 * @param mixed $data Value
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_pre( $data = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- the helper's job.
    echo '<pre>' . esc_html( print_r( $data, true ) ) . '</pre>';
}

/**
 * Old settings screen multiselect field (the React settings screen renders its own)
 *
 * @param array $args Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_settings_multiselect( $args = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Old settings screen masked password field (the React settings screen renders its own)
 *
 * @param array $args Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_settings_password_preview( $args = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Old settings screen login layout field (the React settings screen renders its own)
 *
 * @param array $args Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_render_login_layout_field( $args = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Old settings screen AI key field (the React settings screen renders its own)
 *
 * @param array $args Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_ai_api_key_field( $args = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Old settings screen AI temperature field (the React settings screen renders its own)
 *
 * @param array $args Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_ai_temperature_field( $args = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Old settings screen login section header (the React settings screen renders its own)
 *
 * @param array $args Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_render_login_settings_section_header( $args = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Old settings screen profile form field (the React settings screen renders its own)
 *
 * @param mixed $form Field arguments
 *
 * @deprecated WPUF_SINCE The settings screen is React; prints nothing.
 */
function wpuf_settings_field_profile( $form = null ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );
}

/**
 * Neutralize Vue/mustache template delimiters in a display string
 *
 * Any text node inside a Vue-compiled root that contains {{ }} is compiled as an
 * expression, so a stored, lower-privilege string such as a term name could run
 * as JavaScript. HTML escaping does not help: the browser decodes entities
 * before Vue reads the template, and { } are not escaped.
 *
 * Inserting a zero-width space between the braces breaks the delimiter match
 * while keeping the visible text identical. Display only; the stored term or
 * post data is never touched.
 *
 * Kept on the React branch because the frontend render path still calls it
 * (Traits\FieldableTrait) and the free plugin can run alongside screens that
 * mount Vue.
 *
 * @since 4.3.14
 *
 * @param string $value
 *
 * @return string
 */
function wpuf_neutralize_template_delimiters( $value ) {
    if ( ! is_string( $value ) || '' === $value ) {
        return $value;
    }

    // Zero-width space (U+200B) between each pair of braces.
    return str_replace( [ '{{', '}}' ], [ "{\xE2\x80\x8B{", "}\xE2\x80\x8B}" ], $value );
}
