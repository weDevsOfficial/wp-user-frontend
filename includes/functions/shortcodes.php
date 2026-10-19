<?php
/**
 * Shortcodes and the shortcode maps
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

/**
 * Map display shortcode
 *
 * @param string $meta_key
 * @param int    $post_id
 * @param array  $args
 */
function wpuf_shortcode_map( $location, $post_id = null, $args = [], $meta_key = '' ) {
    if ( ! wpuf()->is_pro() || ! $location ) {
        return;
    }

    global $post;

    // compatibility
    if ( $post_id ) {
        wpuf_shortcode_map_post( $location, $post_id, $args );

        return;
    }

    $default        = [
        'width' => 450,
        'height' => 250,
        'zoom' => 12,
    ];
    $args           = wp_parse_args( $args, $default );

    if ( is_array( $location ) ) {
        $def_address = isset( $location['address'] ) ? $location['address'] : '';
        $def_lat     = isset( $location['lat'] ) ? $location['lat'] : '';
        $def_long    = isset( $location['lng'] ) ? $location['lng'] : '';
        $location    = implode( ' || ', $location );
    } else {
        list( $def_lat, $def_long ) = explode( ',', $location );
        $def_lat                    = $def_lat ? $def_lat : 0;
        $def_long                   = $def_long ? $def_long : 0;
    }
    ?>

    <div class="google-map" style="margin: 10px 0; height: <?php echo esc_attr( $args['height'] ); ?>px; width: <?php echo esc_attr( $args['width'] ); ?>px;" id="wpuf-map-<?php echo esc_attr( $meta_key . $post->ID ); ?>"></div>

    <script type="text/javascript">
        jQuery(function($){
            // Check if Google Maps API is loaded (may not be available in Elementor preview)
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                return;
            }

            var curpoint = new google.maps.LatLng(<?php echo esc_html( $def_lat ); ?>, <?php echo esc_html( $def_long ); ?>);

            var gmap = new google.maps.Map( $('#wpuf-map-<?php echo esc_attr( $meta_key . $post->ID ); ?>')[0], {
                center: curpoint,
                zoom: <?php echo esc_attr( $args['zoom'] ); ?>,
                mapTypeId: window.google.maps.MapTypeId.ROADMAP
            });

            var marker = new window.google.maps.Marker({
                position: curpoint,
                map: gmap,
                draggable: true
            });

        });
    </script>
    <?php
}

/**
 * Map shortcode for users
 *
 * @param string $meta_key
 * @param int    $user_id
 * @param array  $args
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_shortcode_map_user( $meta_key, $user_id = null, $args = [] ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $location = get_user_meta( $user_id, $meta_key, true );
    wpuf_shortcode_map( $location, null, $args, $meta_key );
}

/**
 * Map shortcode post posts
 *
 * @global object $post
 *
 * @param string $meta_key
 * @param int    $post_id
 * @param array  $args
 */
function wpuf_shortcode_map_post( $meta_key, $post_id = null, $args = [] ) {
    global $post;

    if ( ! $post_id ) {
        $post_id = $post->ID;
    }

    $location = get_post_meta( $post_id, $meta_key, true );
    wpuf_shortcode_map( $location, null, $args, $meta_key );
}

function wpuf_meta_shortcode( $atts ) {
    global $post;

    $attrs = shortcode_atts(
        [
            'name'   => '',
            'type'   => 'normal',
            'size'   => 'thumbnail',
            'height' => 250,
            'width'  => 450,
            'zoom'   => 12,
        ], $atts
    );

    $name   = $attrs['name'];
    $type   = $attrs['type'];
    $size   = $attrs['size'];
    $width  = $attrs['width'];
    $height = $attrs['height'];
    $zoom   = $attrs['zoom'];

    if ( empty( $name ) ) {
        return;
    }

    if ( 'image' === $type || 'file' === $type ) {
        $images = get_post_meta( $post->ID, $name, true );

        if ( ! is_array( $images ) ) {
            $images = (array) $images;
        }

        if ( $images ) {
            $html = '';

            foreach ( $images as $attachment_id ) {
                if ( 'image' === $type ) {
                    $thumb = wp_get_attachment_image( $attachment_id, $size );
                } else {
                    $thumb = esc_html( get_post_field( 'post_title', $attachment_id ) );
                }

                $full_size = wp_get_attachment_url( $attachment_id );
                $html      .= sprintf( '<a href="%s">%s</a> ', esc_url( $full_size ), $thumb );
            }

            return $html;
        }
    } elseif ( 'map' === $type ) {
        ob_start();
        wpuf_shortcode_map(
            $name, $post->ID, [
                'width' => $width,
                'height' => $height,
                'zoom' => $zoom,
            ]
        );

        return ob_get_clean();
    } elseif ( 'repeat' === $type ) {
        return wp_kses_post( implode( '; ', get_post_meta( $post->ID, $name ) ) );
    } elseif ( 'normal' === $type ) {
        return wp_kses_post( implode( ', ', get_post_meta( $post->ID, $name ) ) );
    } else {
        return wp_kses_post( make_clickable( strip_shortcodes( implode( ', ', get_post_meta( $post->ID, $name ) ) ) ) );
    }
}

/**
 * Check the current post for the existence of a short code
 *
 * @see http://wp.tutsplus.com/articles/quick-tip-improving-shortcodes-with-the-has_shortcode-function/
 *
 * @param string $shortcode
 *
 * @return bool
 */
function wpuf_has_shortcode( $shortcode = '', $post_id = false ) {
    $post_to_check = ( false === $post_id ) ? get_post( get_the_ID() ) : get_post( $post_id );

    if ( ! $post_to_check ) {
        return false;
    }

    // false because we have to search through the post content first
    $found = false;

    // if no short code was provided, return false
    if ( ! $shortcode ) {
        return $found;
    }

    // check the post content for the short code
    if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
        // we have found the short code
        $found = true;
    }

    return $found;
}

/**
 * Normalize submitted text before checking it for protected shortcodes
 *
 * Runs the value through the same wp_kses() pass used when the post is
 * saved, then removes NUL and other control bytes, so the text that is
 * checked matches the text that ends up stored.
 *
 * @since WPUF_SINCE
 *
 * @param string $value raw (unslashed) submitted value
 *
 * @return string
 */
function wpuf_normalize_for_shortcode_check( $value ) {
    if ( ! is_string( $value ) || '' === $value ) {
        return '';
    }

    $value = wp_kses( $value, wp_kses_allowed_html( 'post' ) );
    $value = wp_kses_no_null( $value );

    return (string) preg_replace( '/[\x00-\x1F\x7F]/', '', $value );
}

/**
 * Get the shortcodes that are protected on the post form.
 * User cannot submit post containing those shortcodes.
 *
 * @since 3.6.6
 *
 * @return array
 */
function wpuf_get_protected_shortcodes() {
    return [
        'wpuf-registration',
    ];
}

/**
 * Check and modify the post content not to render shortcode values
 * in the frontend for any user except admin.
 *
 * @since 3.6.6
 *
 * @return string
 */
function wpuf_modify_shortcodes( $content ) {
    global $pagenow;

    $post = get_post();

    if ( ! ( $post instanceof WP_Post ) ) {
        return $content;
    }

    if ( 'post.php' === $pagenow ) {
        return $content;
    }

    // get the id of the user who last edited the post
    $user_id = get_post_meta( get_post()->ID, '_edit_last', true );

    $roles = wpuf_get_single_user_roles( $user_id );

    if ( empty( $roles ) ) {
        return $content;
    }

    // last modified by an admin, returns the content as it is
    if ( in_array( 'administrator', $roles, true ) ) {
        return $content;
    }

    $protected_shortcodes = wpuf_get_protected_shortcodes();

    foreach ( $protected_shortcodes as $shortcode ) {
        $search_for = '[' . $shortcode;

        if ( strpos( $content, $search_for ) !== false ) {
            $pattern = '/\[' . $shortcode . '(.*?)\]/';

            $content = preg_replace_callback(
                $pattern, function ( $matches ) {
					return str_replace( [ '[', ']' ], [ '&lbrack;', '&rbrack;' ], $matches[0] );
				}, $content
            );
        }
    }

    return $content;
}
