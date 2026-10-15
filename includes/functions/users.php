<?php
/**
 * Users, avatars, login and logout, account sections, addresses and mail
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

/**
 * Get lists of users from database
 *
 * @return array
 */
function wpuf_list_users() {
    global $wpdb;

    $users = $wpdb->get_results( "SELECT ID, user_login from $wpdb->users" );

    $list = [];

    if ( $users ) {
        foreach ( $users as $user ) {
            $list[ $user->ID ] = $user->user_login;
        }
    }

    return $list;
}

/**
 * Get user role names
 *
 * @since 2.0
 *
 * @global WP_Roles $wp_roles
 *
 * @return array
 */
function wpuf_get_user_roles() {
    if ( ! function_exists( 'wp_roles' ) ) {
        require_once ABSPATH . WPINC . '/capabilities.php';
        $wp_roles = wp_roles();
    } else {
        $wp_roles = wp_roles();
    }

    return $wp_roles->get_names();
}

/**
 * Add custom avatar image size
 *
 * @since 3.3.0
 *
 * @return void
 */
function wpuf_avatar_add_image_size() {
    $avatar_size   = wpuf_get_option( 'avatar_size', 'wpuf_profile', '100x100' );
    $avatar_size   = explode( 'x', $avatar_size );
    $avatar_width  = $avatar_size[0];
    $avatar_height = $avatar_size[1];

    add_image_size( 'wpuf_avatar_image_size', $avatar_width, $avatar_height, true );
}

/**
 * Custom Avatar uploaded by user
 *
 * @since 3.3.0
 *
 * @param int $user_id
 *
 * @return string
 */
function wpuf_get_custom_avatar( $user_id ) {
    $avatar = get_user_meta( $user_id, 'user_avatar', true );

    if ( absint( $avatar ) > 0 ) {
        wpuf_avatar_add_image_size();

        $avatar_source = wp_get_attachment_image_src( $avatar, 'wpuf_avatar_image_size' );

        if ( $avatar_source ) {
            $avatar = $avatar_source[0];
        }
    }

    return $avatar;
}

/**
 * Conditionally ignore using WPUF avatar
 *
 * @since 3.3.0
 *
 * @return bool
 */
function wpuf_use_default_avatar() {
    if ( has_filter( 'pre_option_show_avatars', '__return_true' ) ) {
        return true;
    }

    return apply_filters( 'wpuf_use_default_avatar', false );
}

/**
 * User avatar wrapper for custom uploaded avatar
 *
 * @since 2.0
 *
 * @param string $avatar
 * @param mixed  $id_or_email
 * @param int    $size
 * @param string $default
 * @param string $alt
 *
 * @return string image tag of the user avatar
 */
function wpuf_get_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
    if ( wpuf_use_default_avatar() ) {
        return $avatar;
    }

    if ( is_numeric( $id_or_email ) ) {
        $user = get_user_by( 'id', $id_or_email );
    } elseif ( is_object( $id_or_email ) ) {
        if ( $id_or_email->user_id !== '0' ) {
            $user = get_user_by( 'id', $id_or_email->user_id );
        } else {
            return $avatar;
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( ! $user ) {
        return $avatar;
    }

    $custom_avatar = wpuf_get_custom_avatar( $user->ID );

    if ( empty( $custom_avatar ) ) {
        return $avatar;
    }

    return sprintf( '<img src="%1$s" alt="%2$s" height="%3$s" width="%3$s" class="avatar">', esc_url( $custom_avatar ), $alt, $size );
}

/**
 * Filters custom avatar url
 *
 * @param $args
 * @param $id_or_email
 *
 * @return mixed
 */
function wpuf_custom_avatar_data( $args, $id_or_email ) {
    if ( wpuf_use_default_avatar() ) {
        return $args;
    }

    $user_id = $id_or_email;

    if ( $id_or_email instanceof WP_Comment ) {
        $user_id = $id_or_email->user_id;
    } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user_id = email_exists( $id_or_email );
    }

    if ( $user_id ) {
        $custom_avatar_url = wpuf_get_custom_avatar( $user_id );

        if ( ! empty( $custom_avatar_url ) ) {
            $args['url'] = $custom_avatar_url;
        }
    }

    return $args;
}

/**
 * Get user avatar data with fallback to initials
 *
 * Checks for custom profile photo first, then Gravatar, and provides
 * initials as fallback. This follows the same logic as user directory.
 *
 * @since 4.2.7
 *
 * @param int|WP_User $user    User ID or WP_User object.
 * @param int         $size    Avatar size in pixels. Default 96.
 *
 * @return array {
 *     Avatar data array.
 *
 *     @type string|false $url       Avatar URL or false if no avatar.
 *     @type string       $initials  User initials for fallback display.
 *     @type int          $font_size Calculated font size for initials.
 * }
 */
function wpuf_get_user_avatar_data( $user, $size = 96 ) {
    // Get user object if ID is passed
    if ( is_numeric( $user ) ) {
        $user = get_user_by( 'id', $user );
    }

    if ( ! $user ) {
        return [
            'url'       => false,
            'initials'  => '',
            'font_size' => 16,
        ];
    }

    $avatar_url = false;

    // First check for wpuf_profile_photo meta (custom uploaded photo)
    $profile_photo_id = get_user_meta( $user->ID, 'wpuf_profile_photo', true );

    if ( $profile_photo_id ) {
        $photo_url = wp_get_attachment_url( $profile_photo_id );

        if ( $photo_url ) {
            $avatar_url = $photo_url;
        }
    }

    // If no custom photo, check for real Gravatar
    if ( ! $avatar_url ) {
        $email_hash         = md5( strtolower( trim( $user->user_email ) ) );
        $gravatar_check_url = "https://www.gravatar.com/avatar/{$email_hash}?d=404&s={$size}";
        $response           = wp_remote_head( $gravatar_check_url, [ 'timeout' => 2 ] );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            $avatar_url = "https://www.gravatar.com/avatar/{$email_hash}?s={$size}";
        }
    }

    // Get user initials for fallback
    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name  = get_user_meta( $user->ID, 'last_name', true );

    if ( $first_name && $last_name ) {
        $initials = strtoupper( substr( $first_name, 0, 1 ) . substr( $last_name, 0, 1 ) );
    } else {
        $name       = $user->display_name ? $user->display_name : $user->user_login;
        $name_parts = explode( ' ', $name );

        if ( count( $name_parts ) >= 2 ) {
            $initials = strtoupper( substr( $name_parts[0], 0, 1 ) . substr( $name_parts[1], 0, 1 ) );
        } else {
            $initials = strtoupper( substr( $name, 0, 2 ) );
        }
    }

    // Calculate font size for initials
    $font_size = max( $size / 2.5, 16 );

    return [
        'url'       => $avatar_url,
        'initials'  => $initials,
        'font_size' => $font_size,
    ];
}

/**
 * Unused helper.
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_update_avatar( $user_id, $attachment_id ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $upload_dir   = wp_upload_dir();
    $relative_url = wp_get_attachment_url( $attachment_id );

    if ( function_exists( 'wp_get_image_editor' ) ) {
        // try to crop the photo if it's big
        $file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $relative_url );

        // as the image upload process generated a bunch of images
        // try delete the intermediate sizes.
        $ext             = strrchr( $file_path, '.' );
        $file_path_w_ext = str_replace( $ext, '', $file_path );
        $small_url       = $file_path_w_ext . '-avatar' . $ext;
        $relative_url    = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $small_url );

        $editor = wp_get_image_editor( $file_path );

        if ( ! is_wp_error( $editor ) ) {
            $avatar_size    = wpuf_get_option( 'avatar_size', 'wpuf_profile', '100x100' );
            $avatar_size    = explode( 'x', $avatar_size );
            $avatar_width   = $avatar_size[0];
            $avatar_height  = $avatar_size[1];

            $editor->resize( $avatar_width, $avatar_height, true );
            $editor->save( $small_url );

            // if the file creation successfull, delete the original attachment
            if ( file_exists( $small_url ) ) {
                wp_delete_attachment( $attachment_id, true );
            }
        }
    }

    // delete any previous avatar
    $prev_avatar = get_user_meta( $user_id, 'user_avatar', true );

    if ( ! empty( $prev_avatar ) ) {
        $prev_avatar_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $prev_avatar );

        if ( file_exists( $prev_avatar_path ) ) {
            unlink( $prev_avatar_path );
        }
    }

    // now update new user avatar
    update_user_meta( $user_id, 'user_avatar', $relative_url );
}

function wpuf_admin_role() {
    return apply_filters( 'wpuf_admin_role', 'manage_options' );
}

/**
 * Get countries
 *
 * @since 2.4.1
 *
 * @param string $type (optional)
 *
 * @return array|string
 */
function wpuf_get_countries( $type = 'array' ) {
    $countries = include WPUF_ROOT . '/includes/Data/countries-formated.php';

    if ( 'json' === $type ) {
        $countries = wp_json_encode( $countries );
    }

    return $countries;
}

/**
 * Get account dashboard's sections
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_account_sections() {
    $sections = [
        'edit-profile'    => __( 'Edit Profile', 'wp-user-frontend' ),
        'change-password' => __( 'Change Password', 'wp-user-frontend' ),
        'subscription'    => __( 'Subscription', 'wp-user-frontend' ),
        'billing-address' => __( 'Billing Address', 'wp-user-frontend' ),
    ];

    $post_types   = wpuf_get_option( 'cp_on_acc_page', 'wpuf_my_account', [ 'post' ] );
    $cpt_sections = [];

    if ( is_array( $post_types ) && $post_types ) {
        foreach ( $post_types as $post_type ) {
            $post_type_object = get_post_type_object( $post_type );

            if ( $post_type_object ) {
                $cpt_sections[ $post_type ] = $post_type_object->label;
            }
        }
    }

    $sections = array_merge(
    // dashboard should be the first item
        [ 'dashboard' => __( 'Dashboard', 'wp-user-frontend' ) ],
        $cpt_sections,
        $sections
    );

    return apply_filters( 'wpuf_account_sections', $sections );
}

/**
 * Get account dashboard's sections in a list array
 *
 * @since 2.4.2
 *
 * @return array
 */
function wpuf_get_account_sections_list( $post_type = 'page' ) {
    $sections = wpuf_get_account_sections();
    $array    = [ '' => __( '&mdash; Select &mdash;', 'wp-user-frontend' ) ];

    if ( $sections ) {
        foreach ( $sections as $section => $label ) {
            $array[ $section ] = esc_attr( $label );
        }
    }

    return $array;
}

/**
 * Send guest verification mail
 *
 * @since 2.5.8
 *
 * @param string $post_id_encoded, $form_id_encoded, $charging_enabled, $flag
 *
 * @return void
 */
function wpuf_send_mail_to_guest( $post_id_encoded, $form_id_encoded, $charging_enabled, $flag ) {
    if ( 'on' !== wpuf_get_option( 'enable_guest_email_notification', 'wpuf_mails', 'on' ) ) {
        return;
    }

    // Skip nonce verification for guest email verification as it's called programmatically
    // $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
    // if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf_edit' ) ) {
    //     return;
    // }

    if ( $charging_enabled ) {
        $encoded_guest_url = add_query_arg(
            [
                'p_id'     => urlencode( $post_id_encoded ),
                'f_id'     => urlencode( $form_id_encoded ),
                'post_msg' => 'verified',
                'f'        => 2,
            ], get_home_url()
        );
    } else {
        $encoded_guest_url = add_query_arg(
            [
                'p_id'     => urlencode( $post_id_encoded ),
                'f_id'     => urlencode( $form_id_encoded ),
                'post_msg' => 'verified',
                'f'        => 1,
            ], get_home_url()
        );
    }

    $default_body     = 'Hey There, <br> <br> We just received your guest post and now we want you to confirm your email so that we can verify the content and move on to the publishing process. <br> <br> Please click the link below to verify: <br> <br> <a href="' . esc_url( $encoded_guest_url ) . '">Publish Post</a> <br> <br> Regards, <br> <br>' . bloginfo( 'name' );
    $to               = isset( $_POST['guest_email'] ) ? sanitize_email( wp_unslash( $_POST['guest_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- the submit handler (Post_Form_Handler) verified the form nonce before calling.
    $guest_email_sub  = wpuf_get_option( 'guest_email_subject', 'wpuf_mails', 'Please Confirm Your Email to Get the Post Published!' );
    $subject          = $guest_email_sub;
    $guest_email_body = wpuf_get_option( 'guest_email_body', 'wpuf_mails', $default_body );

    if ( ! empty( $guest_email_body ) ) {
        $blogname     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
        $field_search = [ '{activation_link}', '{sitename}' ];

        $field_replace = [
            '<a href="' . esc_url( $encoded_guest_url ) . '">Publish Post</a>',
            $blogname,
        ];

        $body = str_replace( $field_search, $field_replace, $guest_email_body );
    } else {
        $body = $default_body;
    }

    $body = get_formatted_mail_body( $body, $subject );

    wp_mail( $to, $subject, $body );
}

/**
 * Get a WP User
 *
 * @since 2.6.0
 *
 * @param int|WP_User $user_id
 *
 * @return WeDevs\Wpuf\WPUF_User
 */
function wpuf_get_user( $user = null ) {
    if ( ! $user ) {
        $user = wp_get_current_user();
    }

    return new WeDevs\Wpuf\WPUF_User( $user );
}

/**
 * Get formatted email body
 *
 * @since  2.9
 *
 * @param string $message
 *
 * @return string
 */
function get_formatted_mail_body( $message, $subject ) {
    if ( wpuf()->is_pro() && wpuf_pro_is_module_active( 'email-templates/email-templates.php' ) ) {
        $css    = '';
        $header = apply_filters( 'wpuf_email_header', '', $subject );
        $footer = apply_filters( 'wpuf_email_footer', '' );

        if ( empty( $header ) ) {
            ob_start();
            if ( function_exists( 'wpuf_load_pro_template' ) ) {
                wpuf_load_pro_template( 'email/header.php', [ 'subject' => $subject ] );
            }

            $header = ob_get_clean();
        }

        if ( empty( $footer ) ) {
            ob_start();

            if ( function_exists( 'wpuf_load_pro_template' ) ) {
                wpuf_load_pro_template( 'email/footer.php', [] );
            }

            $footer = ob_get_clean();
        }

        ob_start();

        if ( function_exists( 'wpuf_load_pro_template' ) ) {
            wpuf_load_pro_template( 'email/style.php', [] );
        }

        $css = apply_filters( 'wpuf_email_style', ob_get_clean() );

        $content = $header . '<pre>' . $message . '</pre>' . $footer;

        if ( ! class_exists( 'Emogrifier' ) ) {
            require_once WPUF_PRO_ROOT . '/assets/vendor/Emogrifier.php';
        }

        try {
            // apply CSS styles inline for picky email clients
            $emogrifier = new Emogrifier( $content, $css );
            $emogrifier->enableCssToHtmlMapping();

            return $emogrifier->emogrify();
        } catch ( Exception $e ) {
            echo esc_html( $e->getMessage() );
        }
    }

    return $message;
}

/**
 * The state field of a country: a drop-down of its states, or `nostates`
 * when it has none (the country select of the billing address).
 *
 * @since WPUF_SINCE
 *
 * @param string $country    Country code
 * @param string $field_name Name (and id, class) of the state field
 *
 * @return string The `<select>` markup, or `nostates`
 */
function wpuf_get_states_field_html( $country, $field_name ) {
    $cs        = new WeDevs\Wpuf\Data\Country_State();
    $countries = $cs->countries();
    $states    = isset( $countries[ $country ] ) ? $cs->getStates( $countries[ $country ] ) : [];

    if ( empty( $states ) ) {
        return 'nostates';
    }

    return wpuf_select(
        [
            'name'             => $field_name,
            'id'               => $field_name,
            'class'            => $field_name,
            'options'          => $states,
            'show_option_all'  => false,
            'show_option_none' => false,
        ]
    );
}

/**
 * Retrieve a states drop down
 *
 * @deprecated WPUF_SINCE The billing script calls the REST route
 *             `wpuf/v1/account/billing-address/states` (wpuf_get_states_field_html()).
 *
 * @return void
 */
function wpuf_ajax_get_states_field() {
    check_ajax_referer( 'wpuf_ajax_address' );

    $country    = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
    $field_name = isset( $_POST['field_name'] ) ? sanitize_text_field( wp_unslash( $_POST['field_name'] ) ) : '';

    wp_send_json( wpuf_get_states_field_html( $country, $field_name ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
}

/**
 * Save the billing address of a user and work out the tax of the item on the
 * pay page for it.
 *
 * @since WPUF_SINCE
 *
 * @param int   $user_id User
 * @param array $input   billing_add_line1, billing_add_line2, billing_city, billing_state,
 *                       billing_zip, billing_country, type (pack|post), id (unslashed)
 *
 * @return array|null `tax` and `cost` for the pay page, null when no tax applies (or no Pro)
 */
function wpuf_save_billing_address( $user_id, array $input ) {
    $value = function ( $key ) use ( $input ) {
        return isset( $input[ $key ] ) ? sanitize_text_field( (string) $input[ $key ] ) : '';
    };

    $address_fields = [
        'add_line_1' => $value( 'billing_add_line1' ),
        'add_line_2' => $value( 'billing_add_line2' ),
        'city'       => $value( 'billing_city' ),
        'state'      => $value( 'billing_state' ),
        'zip_code'   => $value( 'billing_zip' ),
        'country'    => $value( 'billing_country' ),
    ];

    update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );

    $post_data = [
        'type'            => $value( 'type' ),
        'id'              => $value( 'id' ),
        'billing_country' => $address_fields['country'],
        'billing_state'   => $address_fields['state'],
    ];

    /**
     * Filters the tax of the pay page item for a billing address (Pro answers it).
     *
     * @since WPUF_SINCE
     *
     * @param array|null $response  `tax` (rate) and `cost` (total), or null for no change
     * @param array      $post_data type, id, billing_country, billing_state
     */
    $response = apply_filters( 'wpuf_billing_tax_response', null, $post_data );

    return is_array( $response ) ? $response : null;
}

/**
 * Performs tax calculations and updates billing address
 *
 * @deprecated WPUF_SINCE The billing script calls the REST route
 *             `wpuf/v1/account/billing-address` (wpuf_save_billing_address()).
 *
 * @return void
 */
function wpuf_update_billing_address() {
    check_ajax_referer( 'wpuf_ajax_address' );

    $fields = [ 'billing_add_line1', 'billing_add_line2', 'billing_city', 'billing_state', 'billing_zip', 'billing_country', 'type', 'id' ];
    $input  = [];

    foreach ( $fields as $field ) {
        $input[ $field ] = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
    }

    $response = wpuf_save_billing_address( get_current_user_id(), $input );

    if ( null !== $response ) {
        wp_send_json( $response );
    }

    $post_data = [
        'type'            => $input['type'],
        'id'              => $input['id'],
        'billing_country' => $input['billing_country'],
        'billing_state'   => $input['billing_state'],
    ];

    // An older Pro answers through this action (it prints the JSON and exits).
    if ( wpuf()->is_pro() ) {
        do_action( 'wpuf_calculate_tax', $post_data );
    }

    die();
}

/**
 * Retrieve user address
 *
 * @return mixed
 */
function wpuf_get_user_address( $user_id = 0 ) {
    $user_id        = $user_id ? $user_id : get_current_user_id();
    $address_fields = [];

    if ( metadata_exists( 'user', $user_id, 'wpuf_address_fields' ) ) {
        $address_fields = get_user_meta( $user_id, 'wpuf_address_fields', true );
    } else {
        $address_fields = array_fill_keys( [ 'add_line_1', 'add_line_2', 'city', 'state', 'zip_code', 'country' ], '' );

        if ( class_exists( 'WooCommerce' ) ) {
            $customer_id = get_current_user_id();
            $woo_address = [];
            $customer    = new WC_Customer( $customer_id );

            $woo_address = $customer->get_billing();
            unset( $woo_address['email'], $woo_address['tel'], $woo_address['phone'], $woo_address['company'] );

            $countries_obj        = new WC_Countries();
            $countries_array      = $countries_obj->get_countries();
            $country_states_array = $countries_obj->get_states();
            $woo_address['state'] = isset( $country_states_array[ $woo_address['country'] ][ $woo_address['state'] ] ) ? $country_states_array[ $woo_address['country'] ][ $woo_address['state'] ] : '';
            $woo_address['state'] = strtolower( str_replace( ' ', '', $woo_address['state'] ) );

            if ( ! empty( $woo_address ) ) {
                $address_fields = [
                    'add_line_1'    => $woo_address['address_1'],
                    'add_line_2'    => $woo_address['address_2'],
                    'city'          => $woo_address['city'],
                    'state'         => $woo_address['state'],
                    'zip_code'      => $woo_address['postcode'],
                    'country'       => $woo_address['country'],
                ];
            }
        }
    }

    return $address_fields;
}

/**
 * Check user has certain roles
 *
 * @since 3.4.0
 *
 * @param  array  $roles   Permitted user roles to submit a post
 * @param  int    $user_id User id will submit post
 *
 * @return bool
 */
function wpuf_user_has_roles( $roles, $user_id = 0 ) {
    if ( empty( $roles ) ) {
        return false;
    }

    $user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

    if ( ! empty( array_intersect( $user->roles, $roles ) ) ) {
        return true;
    }

    return false;
}

/**
 *  Inconsistency with keys, remap keys, Back compat with keys
 *
 * @param $address_fields
 *
 * @return array
 */
function wpuf_map_address_fields( $address_fields ) {
    if ( array_key_exists( 'billing_country', $address_fields ) ) {
        foreach ( $address_fields as $key => $val ) {
            unset( $address_fields[ $key ] );
            $address_fields[ str_replace( [ 'billing_', 'line1', 'line2', 'zip' ], [ '', 'line_1', 'line_2', 'zip_code' ], $key ) ] = $val;
        }
    }

    return $address_fields;
}

/**
 * Guess a suitable username for registration based on email address
 *
 * @param string $email email address
 *
 * @return string username
 */
function wpuf_guess_username( $email ) {
    // username from email address
    $username = sanitize_user( substr( $email, 0, strpos( $email, '@' ) ) );

    if ( ! username_exists( $username ) ) {
        return $username;
    }

    // try to add some random number in username
    // and may be we got our username
    $username .= rand( 1, 199 );

    if ( ! username_exists( $username ) ) {
        return $username;
    }
}

/**
 * Get the current users roles as an array
 *
 * @since 3.6.6
 *
 * @return array|bool
 */
function wpuf_get_single_user_roles( $user_id ) {
    if ( ! is_numeric( $user_id ) ) {
        return false;
    }

    $user = get_user_by( 'id', $user_id );

    if ( ! $user ) {
        return [];
    }

    return (array) $user->roles;
}

/**
 * Fallback function for profile photo allowed extensions
 * Only used when Pro version is not active
 *
 * @since 4.1.8
 *
 * @return array
 */
if ( ! function_exists( 'wpuf_field_profile_photo_allowed_extensions' ) ) {
    function wpuf_field_profile_photo_allowed_extensions() {
        $allowed_extensions = [
            'jpg'  => __( 'JPG', 'wp-user-frontend' ),
            'jpeg' => __( 'JPEG', 'wp-user-frontend' ),
            'jfif' => __( 'JFIF', 'wp-user-frontend' ),
            'png'  => __( 'PNG', 'wp-user-frontend' ),
            'gif'  => __( 'GIF', 'wp-user-frontend' ),
        ];

        /**
         * Filter allowed profile photo extensions
         *
         * @since WPUF_PRO
         *
         * @param array $allowed_extensions Array of extension => label pairs
         */
        return apply_filters( 'wpuf_field_profile_photo_allowed_extensions', $allowed_extensions );
    }
}

/**
 * Fallback function for profile photo allowed MIME types
 * Only used when Pro version is not active
 *
 * @since 4.1.8
 *
 * @return array
 */
if ( ! function_exists( 'wpuf_field_profile_photo_allowed_mimes' ) ) {
    function wpuf_field_profile_photo_allowed_mimes() {
        // Get WordPress core allowed mime types for consistency
        $wp_mimes = get_allowed_mime_types();

        // Define our basic allowed image types
        $allowed_mimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
        ];

        // Only include mimes that are allowed by WordPress
        $profile_photo_mimes = array_intersect( $allowed_mimes, $wp_mimes );

        /**
         * Filter allowed profile photo MIME types
         *
         * @since 4.1.8
         *
         * @param array $profile_photo_mimes Array of allowed MIME types for profile photos
         */
        return apply_filters( 'wpuf_field_profile_photo_allowed_mimes', $profile_photo_mimes );
    }
}

/**
 * Get login layout options for settings
 *
 * @since 4.1.0
 *
 * @return array Layout options with labels and image URLs
 */
function wpuf_get_login_layout_options() {
    $image_url = WPUF_ASSET_URI . '/images/login-layouts/';

    $layouts = [
        'layout1' => __( 'Layout 1 - Classic', 'wp-user-frontend' ),
        'layout2' => __( 'Layout 2 - Modern Dark', 'wp-user-frontend' ),
        'layout3' => __( 'Layout 3 - Minimal', 'wp-user-frontend' ),
        'layout4' => __( 'Layout 4 - Bordered', 'wp-user-frontend' ),
        'layout5' => __( 'Layout 5 - Rounded', 'wp-user-frontend' ),
        'layout6' => __( 'Layout 6 - Clean', 'wp-user-frontend' ),
        'layout7' => __( 'Layout 7 - Premium', 'wp-user-frontend' ),
    ];

    $options = [];
    foreach ( $layouts as $key => $label ) {
        $options[ $key ] = [
            'label' => $label,
            'image' => $image_url . $key . '.png',
        ];
    }

    return $options;
}

/**
 * Get WPUF logout URL
 *
 * Returns the logout URL with proper nonce. If WPUF login override is enabled,
 * it returns the WPUF logout URL, otherwise falls back to WordPress default.
 *
 * @since 4.2.10
 *
 * @param string $redirect_to Optional. URL to redirect after logout.
 *
 * @return string The logout URL
 */
function wpuf_get_logout_url( $redirect_to = '' ) {
    $override = wpuf_get_option( 'register_link_override', 'wpuf_profile', 'off' );

    if ( 'on' === $override ) {
        $login_page_id = wpuf_get_option( 'login_page', 'wpuf_profile', false );

        if ( $login_page_id ) {
            $root_url   = get_permalink( $login_page_id );
            $logout_url = wp_nonce_url( add_query_arg( [ 'action' => 'logout' ], $root_url ), 'log-out' );

            if ( ! empty( $redirect_to ) ) {
                $logout_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $logout_url );
            }

            return $logout_url;
        }
    }

    return wp_logout_url( $redirect_to );
}

/**
 * Add logout link to WordPress navigation menu
 *
 * @since 4.2.10
 *
 * @param int    $menu_id     The menu ID to add the logout link to.
 * @param string $menu_label  Optional. The label for the logout menu item.
 * @param int    $parent_id   Optional. The parent menu item ID.
 *
 * @return int|WP_Error The menu item ID on success, WP_Error on failure.
 */
function wpuf_add_logout_to_menu( $menu_id, $menu_label = '', $parent_id = 0 ) {
    if ( empty( $menu_label ) ) {
        $menu_label = __( 'Logout', 'wp-user-frontend' );
    }

    $logout_url = wpuf_get_logout_url();

    $menu_item_data = [
        'menu-item-title'   => $menu_label,
        'menu-item-url'     => $logout_url,
        'menu-item-status'  => 'publish',
        'menu-item-type'    => 'custom',
        'menu-item-parent-id' => $parent_id,
    ];

    $menu_item_id = wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );

    // Add CSS class to identify WPUF logout menu items
    if ( ! is_wp_error( $menu_item_id ) ) {
        update_post_meta( $menu_item_id, '_menu_item_classes', [ 'wpuf-logout-link' ] );
    }

    return $menu_item_id;
}

/**
 * Filter navigation menu items to hide logout link when user is not logged in
 *
 * @since 4.2.10
 *
 * @param array $items The menu items.
 *
 * @return array Filtered menu items.
 */
function wpuf_filter_logout_menu_items( $items ) {
    // If user is logged in, show all items
    if ( is_user_logged_in() ) {
        return $items;
    }

    // Remove logout items for non-logged-in users
    foreach ( $items as $key => $item ) {
        // Check if this is a logout link by URL or CSS class
        if (
            strpos( $item->url, 'action=logout' ) !== false ||
            ( is_array( $item->classes ) && in_array( 'wpuf-logout-link', $item->classes, true ) )
        ) {
            unset( $items[ $key ] );
        }
    }

    return $items;
}

/**
 * Add CSS to hide logout links for non-logged-in users (for FSE themes)
 *
 * This handles cases where the logout link is in a block navigation
 * that doesn't go through wp_nav_menu_objects filter.
 *
 * @since 4.2.10
 *
 * @return void
 */
function wpuf_logout_visibility_css() {
    // Only output CSS if user is NOT logged in
    if ( is_user_logged_in() ) {
        return;
    }

    ?>
    <style type="text/css">
        /* Hide logout links for non-logged-in users */
        .wp-block-navigation a[href*="action=logout"],
        .wp-block-navigation-item a[href*="action=logout"],
        a.wpuf-logout-link,
        .wpuf-logout-link {
            display: none !important;
        }
    </style>
    <?php
}
