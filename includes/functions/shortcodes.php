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
 * Find the first protected shortcode present in the given values
 *
 * Normalizes each value the same way the submitted value is normalized before
 * it is stored, so the text that is checked matches the text that ends up in
 * the database.
 *
 * @since 4.3.15
 *
 * @param array|string $values one or more values to check
 *
 * @return string the protected shortcode that was found, or an empty string
 */
function wpuf_find_protected_shortcode( $values ) {
    $protected_shortcodes = wpuf_get_protected_shortcodes();

    foreach ( (array) $values as $value ) {
        if ( ! is_string( $value ) || '' === $value ) {
            continue;
        }

        $value = wpuf_normalize_for_shortcode_check( $value );

        foreach ( $protected_shortcodes as $shortcode ) {
            if ( false !== stripos( $value, '[' . $shortcode ) ) {
                return $shortcode;
            }
        }
    }

    return '';
}

/**
 * Find a protected shortcode in the values a post form submission will store
 *
 * Walks every field of the form, including fields nested in columns and array
 * values such as checkboxes, plus post_title, post_content and post_excerpt,
 * which are stored whatever fields the form defines. The submit and the draft
 * endpoints both use it, so they accept and reject the same payloads.
 *
 * @since 4.3.15
 *
 * @param array $form_fields the fields of the submitted form
 *
 * @return string the protected shortcode that was found, or an empty string
 */
function wpuf_find_protected_shortcode_in_submission( $form_fields ) {
    $names = [ 'post_title', 'post_content', 'post_excerpt' ];

    foreach ( (array) $form_fields as $field ) {
        $names[] = isset( $field['name'] ) ? $field['name'] : '';

        $columns = isset( $field['inner_fields'] ) && is_array( $field['inner_fields'] ) ? $field['inner_fields'] : [];

        foreach ( $columns as $column ) {
            foreach ( (array) $column as $inner ) {
                $names[] = isset( $inner['name'] ) ? $inner['name'] : '';
            }
        }
    }

    $values = [];

    foreach ( array_unique( array_filter( $names, 'is_string' ) ) as $name ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- callers verify the nonce; values are normalized by the helper and only compared, never stored.
        if ( '' === $name || ! isset( $_POST[ $name ] ) ) {
            continue;
        }

        $posted = wp_unslash( $_POST[ $name ] );
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ( is_array( $posted ) ) {
            array_walk_recursive(
                $posted, function ( $value ) use ( &$values ) {
                    $values[] = $value;
                }
            );
        } else {
            $values[] = $posted;
        }
    }

    return wpuf_find_protected_shortcode( $values );
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
    $user_id = (int) get_post_meta( $post->ID, '_edit_last', true );

    if ( $user_id ) {
        $roles = wpuf_get_single_user_roles( $user_id );

        // last modified by an admin (or by a user that no longer exists), returns
        // the content as it is, as before
        if ( empty( $roles ) || in_array( 'administrator', $roles, true ) ) {
            return $content;
        }
    } else {
        // Posts inserted straight through wp_insert_post(), as the draft endpoint
        // and the REST API do, carry no _edit_last. Judge them by their author:
        // no user behind the content (created by code, WP-CLI or an import), an
        // administrator, or a user trusted with unfiltered HTML (editor) keeps it
        // as it is. Authors, contributors and subscribers get the protected
        // shortcodes escaped. The administrator check stays because administrators
        // lack unfiltered_html on multisite.
        $author = get_userdata( (int) $post->post_author );

        if ( ! $author || in_array( 'administrator', (array) $author->roles, true ) || user_can( $author, 'unfiltered_html' ) ) {
            return $content;
        }
    }

    $protected_shortcodes = wpuf_get_protected_shortcodes();

    foreach ( $protected_shortcodes as $shortcode ) {
        $search_for = '[' . $shortcode;

        if ( strpos( $content, $search_for ) !== false ) {
            // Break every opening "[tag" that WordPress would read as this shortcode,
            // whatever follows it: attributes may span lines, so matching up to the
            // closing bracket missed "[tag" followed by a newline.
            $pattern = '/\[(' . preg_quote( $shortcode, '/' ) . ')(?![\w-])/';

            $content = preg_replace( $pattern, '&lbrack;$1', $content );
        }
    }

    return $content;
}
