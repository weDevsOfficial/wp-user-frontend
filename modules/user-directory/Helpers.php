<?php
/**
 * Helper functions for Free User Directory
 *
 * These functions mirror the Pro version helpers to ensure template compatibility.
 *
 * @package WPUF
 * @subpackage Modules/User_Directory
 * @since 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get layout colors for directory
 *
 * @since 4.3.0
 *
 * @param string $layout Layout name.
 *
 * @return array Colors array.
 */
function wpuf_ud_free_get_layout_colors( $layout = 'layout-3' ) {
    // Default emerald colors for Free version
    return [
        'primary_600'            => 'wpuf-bg-emerald-600',
        'primary_700'            => 'wpuf-bg-emerald-700',
        'text_primary_600'       => 'wpuf-text-emerald-600',
        'border_primary_600'     => 'wpuf-border-emerald-600',
        'hover_primary_600'      => 'hover:wpuf-text-emerald-600',
        'hover_primary_700'      => 'hover:wpuf-bg-emerald-700',
        'hover_border_primary_600' => 'hover:wpuf-border-emerald-600',
        'focus_ring_primary_500' => 'focus:wpuf-ring-emerald-500',
    ];
}

/**
 * Alias for wpuf_ud_get_layout_colors (Pro compatibility)
 *
 * @since 4.3.0
 *
 * @param string $layout Layout name.
 *
 * @return array Colors array.
 */
function wpuf_ud_get_layout_colors( $layout = 'layout-3' ) {
    return wpuf_ud_free_get_layout_colors( $layout );
}

/**
 * Get profile layout colors
 *
 * @since 4.3.0
 *
 * @param string $layout Layout name.
 *
 * @return array Colors array.
 */
function wpuf_ud_get_profile_layout_colors( $layout = 'layout-2' ) {
    return wpuf_ud_free_get_layout_colors( $layout );
}

/**
 * Build page URL for pagination
 *
 * @since 4.3.0
 *
 * @param string $base_url   Base URL.
 * @param array  $query_args Query args.
 * @param int    $page       Page number.
 *
 * @return string URL.
 */
function wpuf_ud_build_page_url( $base_url, $query_args, $page ) {
    global $wp_rewrite;

    // Ensure we have valid parameters
    if ( empty( $base_url ) ) {
        $base_url = '/';
    }

    if ( ! is_array( $query_args ) ) {
        $query_args = [];
    }

    $page = absint( $page );
    if ( $page < 1 ) {
        $page = 1;
    }

    $is_pretty = $wp_rewrite && $wp_rewrite->using_permalinks();

    // Remove trailing slash for consistency
    $base_url = untrailingslashit( $base_url );

    if ( $is_pretty ) {
        // Remove existing /page/X/ if present
        $base_url = preg_replace( '#/page/\\d+/?$#', '', $base_url );

        if ( $page > 1 ) {
            $url = $base_url . '/page/' . $page . '/';
        } else {
            $url = $base_url . '/';
        }

        if ( ! empty( $query_args ) ) {
            $url = add_query_arg( $query_args, $url );
        }
    } else {
        if ( $page > 1 ) {
            $query_args['page'] = $page;
        }
        $url = add_query_arg( $query_args, $base_url );
    }

    return esc_url( $url );
}

/**
 * Get avatar URL for a user (checks wpuf_profile_photo first, then gravatar)
 *
 * @since 4.3.0
 *
 * @param WP_User $user User object.
 * @param int     $size Size in pixels.
 *
 * @return string|false Avatar URL or false if no custom avatar.
 */
function wpuf_ud_get_avatar_url( $user, $size = 128 ) {
    if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
        return false;
    }

    // First check for wpuf_profile_photo meta
    $profile_photo_id = get_user_meta( $user->ID, 'wpuf_profile_photo', true );

    if ( $profile_photo_id ) {
        $photo_url = wp_get_attachment_url( $profile_photo_id );
        if ( $photo_url ) {
            return $photo_url;
        }
    }

    // Check if user has a real Gravatar (with caching to avoid repeated remote calls)
    $cache_key = 'wpuf_gravatar_check_' . md5( $user->user_email );
    $has_gravatar = get_transient( $cache_key );

    if ( false === $has_gravatar ) {
        $email_hash = md5( strtolower( trim( $user->user_email ) ) );
        $gravatar_check_url = "https://www.gravatar.com/avatar/{$email_hash}?d=404&s={$size}";

        $response = wp_remote_head( $gravatar_check_url, [ 'timeout' => 2 ] );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $has_gravatar = 'yes';
        } else {
            $has_gravatar = 'no';
        }

        // Cache for 1 day
        set_transient( $cache_key, $has_gravatar, DAY_IN_SECONDS );
    }

    if ( 'yes' === $has_gravatar ) {
        $email_hash = md5( strtolower( trim( $user->user_email ) ) );
        return "https://www.gravatar.com/avatar/{$email_hash}?s={$size}";
    }

    // No custom avatar found
    return false;
}

/**
 * Get user avatar HTML with fallback to initials (matching Pro behavior)
 *
 * @since 4.3.0
 *
 * @param WP_User $user  User object.
 * @param int     $size  Avatar size.
 * @param string  $class CSS classes.
 *
 * @return string Avatar HTML.
 */
function wpuf_ud_get_user_avatar_html( $user, $size = 128, $class = '' ) {
    // Get user object if ID is passed
    if ( is_numeric( $user ) ) {
        $user = get_user_by( 'id', $user );
    }

    if ( ! $user ) {
        return '';
    }

    // Get avatar URL (checks wpuf_profile_photo first, then gravatar)
    $avatar_url = wpuf_ud_get_avatar_url( $user, $size );

    // Get user's name for initials
    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name  = get_user_meta( $user->ID, 'last_name', true );

    // Calculate initials
    if ( $first_name && $last_name ) {
        $initials = strtoupper( substr( $first_name, 0, 1 ) . substr( $last_name, 0, 1 ) );
    } else {
        $name = $user->display_name ?: $user->user_login;
        $name_parts = explode( ' ', $name );
        if ( count( $name_parts ) >= 2 ) {
            $initials = strtoupper( substr( $name_parts[0], 0, 1 ) . substr( $name_parts[1], 0, 1 ) );
        } else {
            $initials = strtoupper( substr( $name, 0, 2 ) );
        }
    }

    // If we have an avatar URL, return just the img
    if ( $avatar_url ) {
        return sprintf(
            '<img src="%s" alt="%s" class="%s" width="%d" height="%d" style="width: %dpx; height: %dpx; object-fit: cover;" />',
            esc_url( $avatar_url ),
            esc_attr( $user->display_name ),
            esc_attr( $class ),
            $size,
            $size,
            $size,
            $size
        );
    }

    // No avatar URL, show initials directly
    return sprintf(
        '<div class="%s" style="width: %dpx; height: %dpx; font-size: %dpx; background-color: #9ca3af; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; border-radius: 50%%;">%s</div>',
        esc_attr( $class ),
        $size,
        $size,
        max( (int) ( $size / 2.5 ), 16 ),
        esc_html( $initials )
    );
}

/**
 * Get profile URL for a user
 *
 * @since 4.3.0
 *
 * @param WP_User $user User object.
 * @param array   $data Profile data.
 *
 * @return string Profile URL.
 */
function wpuf_ud_get_profile_url( $user, $data = [] ) {
    // Handle null user
    if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
        return '';
    }

    $profile_base = isset( $data['profile_base'] ) ? $data['profile_base'] : 'username';

    // Get current page permalink (directory page)
    $current_url = '';

    // First, check if base_url is provided in data (from AJAX requests)
    if ( ! empty( $data['base_url'] ) ) {
        // Use the base URL provided from AJAX request
        $current_url = $data['base_url'];

        // If it's just a path, build a full URL
        if ( strpos( $current_url, 'http' ) !== 0 ) {
            $scheme = is_ssl() ? 'https' : 'http';
            $host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
            $current_url = $scheme . '://' . $host . $current_url;
        }
    } else {
        // Fallback to detecting current URL
        // Try to get the current post/page URL
        if ( is_singular() ) {
            $current_url = get_permalink();
        } elseif ( is_home() ) {
            $current_url = home_url();
        } elseif ( is_front_page() ) {
            $current_url = home_url();
        } else {
            // Fallback to current request URL - strip query string
            $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
            $current_url = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ) . strtok( $request_uri, '?' );
        }
    }

    // Determine the user identifier based on profile base
    if ( 'user_id' === $profile_base ) {
        $user_identifier = $user->ID;
    } else {
        $user_identifier = $user->user_login;
    }

    // Check if this is a shortcode-based directory
    $is_shortcode = false;

    // First check if explicitly passed in data (from templates)
    if ( ! empty( $data['is_shortcode'] ) ) {
        $is_shortcode = true;
    } else {
        // Check multiple ways to detect shortcode context
        // Method 1: Check global post for shortcode
        global $post;
        if ( $post && ( has_shortcode( $post->post_content, 'wpuf_user_listing' ) ||
                       has_shortcode( $post->post_content, 'wpuf_user_listing_id' ) ) ) {
            $is_shortcode = true;
        }

        // Method 2: Try to get page ID from URL and check for shortcode
        if ( ! $is_shortcode ) {
            // Get page ID from current URL
            $page_id = url_to_postid( $current_url );

            // If we have pagination, try removing it to get the base page
            if ( ! $page_id && preg_match( '#/page/\d+/#', $current_url ) ) {
                $base_url = preg_replace( '#/page/\d+/#', '/', $current_url );
                $page_id  = url_to_postid( $base_url );
            }

            if ( $page_id ) {
                $page_content = get_post_field( 'post_content', $page_id );
                if ( $page_content && (
                    strpos( $page_content, '[wpuf_user_listing' ) !== false ||
                    strpos( $page_content, '[wpuf_user_listing_id' ) !== false
                ) ) {
                    $is_shortcode = true;
                }
            }
        }
    }

    // For shortcodes, ALWAYS use clean URL format - strictly no query parameters
    if ( $is_shortcode ) {
        // Generate clean URL: /directory/username or /directory/123
        $clean_base_url = strtok( $current_url, '?' );
        // Remove pagination from URL if present to get the base directory URL
        $clean_base_url = preg_replace( '#/page/\d+/?$#', '', $clean_base_url );
        $clean_base_url = rtrim( $clean_base_url, '/' );

        return esc_url( $clean_base_url . '/' . rawurlencode( (string) $user_identifier ) );
    }

    // Parse the current URL and clean it
    $parsed_url = wp_parse_url( $current_url );
    $scheme     = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'http';
    $host       = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
    $path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';

    // Remove any page numbers from path (e.g., /page/2/)
    $path = preg_replace( '#/page/\d+/?$#', '/', $path );
    $path = rtrim( $path, '/' );

    // Rebuild the base URL without query parameters
    $current_url = $scheme . '://' . $host . $path;

    // Check if pretty URLs are enabled
    if ( get_option( 'permalink_structure' ) ) {
        // Clean URL format matching Pro: /ud/username (no extra /user/ segment)
        return esc_url( $current_url . '/' . rawurlencode( (string) $user_identifier ) );
    }

    // Fallback to query parameter
    return esc_url( add_query_arg( 'wpuf_user', $user->ID, $current_url ) );
}

/**
 * Get profile data for template
 *
 * @since 4.3.0
 *
 * @param WP_User $user          User object.
 * @param array   $template_data Template data.
 * @param string  $layout        Layout name.
 *
 * @return array Profile data.
 */
function wpuf_ud_get_profile_data( $user, $template_data = [], $layout = 'layout-2' ) {
    // Handle case when user is null or not a WP_User object
    if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
        return [
            'user'            => null,
            'user_id'         => 0,
            'full_name'       => '',
            'first_name'      => '',
            'last_name'       => '',
            'email'           => '',
            'website'         => '',
            'bio'             => '',
            'avatar_size'     => 192,
            'settings'        => [],
            'back_url'        => '',
            'layout'          => $layout,
            'profile_tabs'    => [],
            'about_fields'    => [],
            'template_config' => wpuf_ud_get_default_template_config(),
            'user_meta'       => wpuf_ud_get_default_user_meta(),
            'contact_info'    => [],
            'social_media'    => [],
            'navigation'      => wpuf_ud_get_default_navigation(),
            'tab_config'      => wpuf_ud_get_default_tab_config(),
        ];
    }

    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name  = get_user_meta( $user->ID, 'last_name', true );
    $full_name  = trim( $first_name . ' ' . $last_name );

    if ( empty( $full_name ) ) {
        $full_name = $user->display_name;
    }

    $settings    = isset( $template_data['settings'] ) ? $template_data['settings'] : [];
    $avatar_size = isset( $template_data['avatar_size'] ) ? $template_data['avatar_size'] : 192;
    $back_url    = isset( $template_data['back_url'] ) ? $template_data['back_url'] : '';

    // Build user_meta array
    $user_meta = [
        'display_name' => $full_name,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'email'        => $user->user_email,
        'website'      => $user->user_url,
        'bio'          => get_user_meta( $user->ID, 'description', true ),
    ];

    // Build contact info - Using Pro-style icons with green circular background
    $contact_info = [];
    if ( ! empty( $user->user_email ) ) {
        $contact_info['email'] = [
            'label'         => __( 'Email', 'wp-user-frontend' ),
            'value'         => $user->user_email,
            'display_value' => $user->user_email,
            'icon'          => '<svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="44" height="44" rx="22" fill="#D1FAE5"/><path d="M30.125 17.125V25.875C30.125 26.9105 29.2855 27.75 28.25 27.75H15.75C14.7145 27.75 13.875 26.9105 13.875 25.875V17.125M30.125 17.125C30.125 16.0895 29.2855 15.25 28.25 15.25H15.75C14.7145 15.25 13.875 16.0895 13.875 17.125M30.125 17.125V17.3273C30.125 17.9784 29.7872 18.5829 29.2327 18.9241L22.9827 22.7703C22.38 23.1411 21.62 23.1411 21.0173 22.7703L14.7673 18.9241C14.2128 18.5829 13.875 17.9784 13.875 17.3273V17.125" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ];
    }
    if ( ! empty( $user->user_url ) ) {
        $parsed_url = wp_parse_url( $user->user_url );
        $contact_info['website'] = [
            'label'         => __( 'Website', 'wp-user-frontend' ),
            'value'         => $user->user_url,
            'display_value' => isset( $parsed_url['host'] ) ? $parsed_url['host'] : $user->user_url,
            'icon'          => '<svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="44" height="44" rx="22" fill="#D1FAE5"/><path d="M22 29C25.4938 29 28.4296 26.611 29.2631 23.3775M22 29C18.5062 29 15.5703 26.611 14.7369 23.3775M22 29C24.0711 29 25.75 25.6421 25.75 21.5C25.75 17.3579 24.0711 14 22 14M22 29C19.9289 29 18.25 25.6421 18.25 21.5C18.25 17.3579 19.9289 14 22 14M22 14C24.8043 14 27.2492 15.5391 28.5359 17.8187M22 14C19.1957 14 16.7508 15.5391 15.4641 17.8187M28.5359 17.8187C26.7831 19.3337 24.4986 20.25 22 20.25C19.5014 20.25 17.2169 19.3337 15.4641 17.8187M28.5359 17.8187C29.1497 18.9062 29.5 20.1622 29.5 21.5C29.5 22.1483 29.4177 22.7774 29.2631 23.3775M29.2631 23.3775C27.1111 24.5706 24.6349 25.25 22 25.25C19.3651 25.25 16.8889 24.5706 14.7369 23.3775M14.7369 23.3775C14.5823 22.7774 14.5 22.1483 14.5 21.5C14.5 20.1622 14.8503 18.9062 15.4641 17.8187" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ];
    }

    // Build template config - Read from template_data if available
    $default_tabs = [ 'about', 'posts', 'comments', 'file' ];
    if ( isset( $template_data['default_tabs'] ) && is_array( $template_data['default_tabs'] ) ) {
        $default_tabs = $template_data['default_tabs'];
    } elseif ( isset( $settings['default_tabs'] ) && is_array( $settings['default_tabs'] ) ) {
        $default_tabs = $settings['default_tabs'];
    }

    $template_config = [
        'show_avatar'       => isset( $template_data['show_avatar'] ) ? $template_data['show_avatar'] : true,
        'enable_tabs'       => isset( $template_data['enable_tabs'] ) ? $template_data['enable_tabs'] : true,
        'default_tabs'      => $default_tabs,
        'default_active_tab'=> isset( $template_data['default_active_tab'] ) ? $template_data['default_active_tab'] : 'about',
        'avatar_size'       => $avatar_size,
        'custom_tab_labels' => [
            'about'    => __( 'About', 'wp-user-frontend' ),
            'posts'    => __( 'Posts', 'wp-user-frontend' ),
            'comments' => __( 'Comments', 'wp-user-frontend' ),
            'file'     => __( 'Files', 'wp-user-frontend' ),
        ],
    ];

    // Build navigation
    $navigation = [
        'back_url'   => $back_url,
        'back_label' => __( 'Back to Directory', 'wp-user-frontend' ),
    ];

    // Build tab config
    $tab_config = [
        'about' => [
            'label'     => __( 'About', 'wp-user-frontend' ),
            'is_active' => true,
            'content'   => 'bio',
        ],
        'posts' => [
            'label'     => __( 'Posts', 'wp-user-frontend' ),
            'is_active' => false,
            'content'   => 'posts',
        ],
        'comments' => [
            'label'     => __( 'Comments', 'wp-user-frontend' ),
            'is_active' => false,
            'content'   => 'comments',
        ],
        'file' => [
            'label'     => __( 'Files', 'wp-user-frontend' ),
            'is_active' => false,
            'content'   => 'file',
        ],
    ];

    return [
        'user'            => $user,
        'user_id'         => $user->ID,
        'full_name'       => $full_name,
        'first_name'      => $first_name,
        'last_name'       => $last_name,
        'email'           => $user->user_email,
        'website'         => $user->user_url,
        'bio'             => get_user_meta( $user->ID, 'description', true ),
        'avatar_size'     => $avatar_size,
        'settings'        => $settings,
        'back_url'        => $back_url,
        'layout'          => $layout,
        'profile_tabs'    => isset( $settings['profile_tabs'] ) ? $settings['profile_tabs'] : [],
        'about_fields'    => isset( $settings['about_fields'] ) ? $settings['about_fields'] : [],
        'template_config' => $template_config,
        'user_meta'       => $user_meta,
        'contact_info'    => $contact_info,
        'social_media'    => [], // Pro feature
        'navigation'      => $navigation,
        'tab_config'      => $tab_config,
    ];
}

/**
 * Get default template config
 *
 * @since 4.3.0
 *
 * @return array Default config.
 */
function wpuf_ud_get_default_template_config() {
    return [
        'show_avatar'       => true,
        'enable_tabs'       => true,
        'default_tabs'      => [ 'about', 'posts', 'comments', 'file' ],
        'default_active_tab'=> 'about',
        'avatar_size'       => 192,
        'custom_tab_labels' => [
            'about'    => __( 'About', 'wp-user-frontend' ),
            'posts'    => __( 'Posts', 'wp-user-frontend' ),
            'comments' => __( 'Comments', 'wp-user-frontend' ),
            'file'     => __( 'Files', 'wp-user-frontend' ),
        ],
    ];
}

/**
 * Get default user meta
 *
 * @since 4.3.0
 *
 * @return array Default user meta.
 */
function wpuf_ud_get_default_user_meta() {
    return [
        'display_name' => '',
        'first_name'   => '',
        'last_name'    => '',
        'email'        => '',
        'website'      => '',
        'bio'          => '',
    ];
}

/**
 * Get default navigation
 *
 * @since 4.3.0
 *
 * @return array Default navigation.
 */
function wpuf_ud_get_default_navigation() {
    return [
        'back_url'   => '',
        'back_label' => __( 'Back to Directory', 'wp-user-frontend' ),
    ];
}

/**
 * Get default tab config
 *
 * @since 4.3.0
 *
 * @return array Default tab config.
 */
function wpuf_ud_get_default_tab_config() {
    return [
        'about' => [
            'label'     => __( 'About', 'wp-user-frontend' ),
            'is_active' => true,
            'content'   => 'bio',
        ],
        'posts' => [
            'label'     => __( 'Posts', 'wp-user-frontend' ),
            'is_active' => false,
            'content'   => 'posts',
        ],
        'comments' => [
            'label'     => __( 'Comments', 'wp-user-frontend' ),
            'is_active' => false,
            'content'   => 'comments',
        ],
    ];
}

/**
 * Get tab label
 *
 * @since 4.3.0
 *
 * @param string $tab          Tab key.
 * @param array  $profile_data Profile data.
 *
 * @return string Tab label.
 */
function wpuf_ud_get_tab_label( $tab, $profile_data = [] ) {
    $labels = [
        'about'    => __( 'About', 'wp-user-frontend' ),
        'posts'    => __( 'Posts', 'wp-user-frontend' ),
        'comments' => __( 'Comments', 'wp-user-frontend' ),
        'files'    => __( 'Files', 'wp-user-frontend' ),
        'activity' => __( 'Activity', 'wp-user-frontend' ),
    ];

    // Check for custom label in profile tabs
    if ( ! empty( $profile_data['profile_tabs'][ $tab ]['label'] ) ) {
        return $profile_data['profile_tabs'][ $tab ]['label'];
    }

    return isset( $labels[ $tab ] ) ? $labels[ $tab ] : ucfirst( $tab );
}

/**
 * Get block avatar data
 *
 * @since 4.3.0
 *
 * @param WP_User $user    User object.
 * @param int     $size    Avatar size.
 * @param string  $type    Avatar type.
 *
 * @return array Avatar data.
 */
function wpuf_ud_get_block_avatar_data( $user, $size = 128, $type = 'initials' ) {
    if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
        return [
            'url'      => '',
            'initials' => '??',
            'size'     => $size,
            'type'     => 'initials',
            'alt'      => '',
        ];
    }

    // Use our custom avatar URL function (checks wpuf_profile_photo first)
    $avatar_url = wpuf_ud_get_avatar_url( $user, $size );
    $initials   = wpuf_ud_get_user_initials( $user );

    if ( $avatar_url ) {
        return [
            'url'      => $avatar_url,
            'initials' => $initials,
            'size'     => $size,
            'type'     => 'custom',
            'alt'      => sprintf( __( '%s avatar', 'wp-user-frontend' ), $user->display_name ),
        ];
    }

    // No avatar, return initials type
    return [
        'url'      => '',
        'initials' => $initials,
        'size'     => $size,
        'type'     => 'initials',
        'alt'      => sprintf( __( '%s avatar', 'wp-user-frontend' ), $user->display_name ),
    ];
}

/**
 * Get user initials
 *
 * @since 4.3.0
 *
 * @param WP_User $user User object.
 *
 * @return string Initials.
 */
function wpuf_ud_get_user_initials( $user ) {
    if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
        return '??';
    }

    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name  = get_user_meta( $user->ID, 'last_name', true );

    $initials = '';
    if ( $first_name ) {
        $initials .= strtoupper( substr( $first_name, 0, 1 ) );
    }
    if ( $last_name ) {
        $initials .= strtoupper( substr( $last_name, 0, 1 ) );
    }

    if ( empty( $initials ) && isset( $user->display_name ) && $user->display_name ) {
        $initials = strtoupper( substr( $user->display_name, 0, 2 ) );
    }

    return $initials ? $initials : '??';
}

/**
 * Get user social links (Free version - returns empty)
 *
 * @since 4.3.0
 *
 * @param WP_User $user User object.
 *
 * @return array Social links (empty in free version).
 */
function wpuf_ud_get_user_social_links( $user ) {
    // Social links are Pro feature - return empty
    return [];
}

/**
 * Check if user has files (Free version - returns false)
 *
 * @since 4.3.0
 *
 * @param int    $user_id  User ID.
 * @param string $meta_key Meta key.
 *
 * @return bool Always false in free version.
 */
function wpuf_ud_user_has_files( $user_id, $meta_key ) {
    // Files are Pro feature
    return false;
}

/**
 * Render meta field (Free version - no-op)
 *
 * @since 4.3.0
 *
 * @param array   $field   Field data.
 * @param WP_User $user    User object.
 * @param int     $user_id User ID.
 * @param string  $layout  Layout.
 *
 * @return void
 */
function wpuf_ud_render_meta_field( $field, $user, $user_id, $layout ) {
    // Meta fields are Pro feature
}

/**
 * Get social fields (Free version - returns empty)
 *
 * @since 4.3.0
 *
 * @return array Empty array.
 */
function wpuf_ud_get_social_fields() {
    return [];
}

/**
 * Get social icons (Free version - returns empty)
 *
 * @since 4.3.0
 *
 * @param string $class CSS class.
 *
 * @return array Empty array.
 */
function wpuf_ud_get_social_icons( $class = '' ) {
    return [];
}

/**
 * Render posts table (Free version - no-op)
 *
 * @since 4.3.0
 *
 * @param array  $field   Field data.
 * @param int    $user_id User ID.
 * @param string $layout  Layout.
 *
 * @return void
 */
function wpuf_ud_render_posts_table( $field, $user_id, $layout ) {
    // Posts table is Pro feature
}

/**
 * Render files grid (Free version - no-op)
 *
 * @since 4.3.0
 *
 * @param array  $field         Field data.
 * @param int    $user_id       User ID.
 * @param array  $template_data Template data.
 * @param string $color         Color scheme.
 *
 * @return void
 */
function wpuf_ud_render_files_grid( $field, $user_id, $template_data, $color ) {
    // Files grid is Pro feature
}
