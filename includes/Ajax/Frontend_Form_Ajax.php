<?php

namespace WeDevs\Wpuf\Ajax;

use DOMDocument;
use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Traits\FieldableTrait;
use WeDevs\Wpuf\User_Subscription;
use WP_Error;

class Frontend_Form_Ajax {

    use FieldableTrait;

    public $form_settings = [];

    private $post_expiration_date = 'wpuf-post_expiration_date';

    private $expired_post_status = 'wpuf-expired_post_status';

    private $post_expiration_message = 'wpuf-post_expiration_message';

    /**
     *  An array of form fields retrieved from the form configuration.
     *
     * @var array
     */
    private $form_fields;

    /**
     * New/Edit post submit handler
     *
     * @return void
     */
    public function submit_post() {
        check_ajax_referer( 'wpuf_form_add' );
        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

        $form_id               = isset( $_POST['form_id'] ) ? intval( wp_unslash( $_POST['form_id'] ) ) : 0;
        $form                  = new Form( $form_id );
        $this->form_settings   = $form->get_settings();
        $this->form_fields     = $form->get_fields();
        $guest_mode            = isset( $this->form_settings['guest_post'] ) ? $this->form_settings['guest_post'] : '';
        $guest_verify          = isset( $this->form_settings['guest_email_verify'] ) ? $this->form_settings['guest_email_verify'] : 'false';
        $attachments_to_delete = isset( $_POST['delete_attachments'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['delete_attachments'] ) ) : [];

        // check each form field for content restriction
        foreach ( $this->form_fields as $single_field ) {
            if ( empty( $single_field['content_restriction'] ) ) {
                continue;
            }

            $restricted_num = $single_field['content_restriction'];
            $restriction_to = ! empty( $single_field['restriction_to'] ) ? $single_field['restriction_to'] : 'min';
            $restriction_type = ! empty( $single_field['restriction_type'] ) ? $single_field['restriction_type'] : 'word';

            $current_data = ! empty( $_POST[ $single_field['name'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $single_field['name'] ] ) ) : '';
            $label = ! empty( $single_field['label'] ) ? $single_field['label'] : '';

            // if restriction by character count
            if ( 'character' === $restriction_type && 'min' === $restriction_to ) {
                if ( strlen( $current_data ) > 0 && strlen( $current_data ) < $restricted_num ) {
                    wpuf()->ajax->send_error(
                        sprintf(
                            __( 'Minimum %d character is required for %s', 'wp-user-frontend' ), $restricted_num, $label
                        )
                    );
                }
            } elseif ( 'character' === $restriction_type && 'max' === $restriction_to ) {
                if ( strlen( $current_data ) > 0 && strlen( $current_data ) > $restricted_num ) {
                    wpuf()->ajax->send_error(
                        sprintf(
                            __( 'Maximum %d character is allowed for %s', 'wp-user-frontend' ), $restricted_num, $label
                        )
                    );
                }
            }

            // if restriction by word count
            if ( 'word' === $restriction_type && 'min' === $restriction_to ) {
                if ( str_word_count( $current_data ) > 0 && str_word_count( $current_data ) < $restricted_num ) {
                    wpuf()->ajax->send_error(
                        sprintf(
                            __( 'Minimum %d word is required for %s', 'wp-user-frontend' ), $restricted_num, $label
                        )
                    );
                }
            } elseif ( 'word' === $restriction_type && 'max' === $restriction_to ) {
                if ( str_word_count( $current_data ) > 0 && str_word_count( $current_data ) > $restricted_num ) {
                    wpuf()->ajax->send_error(
                        sprintf(
                            __( 'Maximum %d word is allowed for %s', 'wp-user-frontend' ), $restricted_num, $label
                        )
                    );
                }
            }
        }

        $protected_shortcodes = wpuf_get_protected_shortcodes();

        // check each form field for restricted shortcodes
        foreach ( $this->form_fields as $single_field ) {
            if ( empty( $single_field['rich'] ) || 'yes' !== $single_field['rich'] ) {
                continue;
            }

            $current_data = ! empty( $_POST[ $single_field['name'] ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $single_field['name'] ] ) ) : '';

            foreach ( $protected_shortcodes as $shortcode ) {
                $search_for = '[' . $shortcode;
                if ( strpos( $current_data, $search_for ) !== false ) {
                    wpuf()->ajax->send_error( sprintf( __( 'Using %s as shortcode is restricted', 'wp-user-frontend' ), $shortcode ) );
                }
            }
        }

        foreach ( $attachments_to_delete as $attach_id ) {
            wp_delete_attachment( $attach_id, true );
        }

        [ $post_vars, $taxonomy_vars, $meta_vars ] = $this->get_input_fields( $this->form_fields );

        if ( ! isset( $_POST['post_id'] ) ) {
            $has_limit = isset( $this->form_settings['limit_entries'] ) && $this->form_settings['limit_entries'] === 'true';

            if ( $has_limit ) {
                $limit        = (int) ! empty( $this->form_settings['limit_number'] ) ? $this->form_settings['limit_number'] : 0;
                $form_entries = wpuf_form_posts_count( $form_id );

                if ( $limit && $limit <= $form_entries ) {
                    wpuf()->ajax->send_error( $this->form_settings['limit_message'] );
                }
            }
            $this->on_edit_no_check_recaptcha( $post_vars );
        }

        $is_update           = false;
        // $default_post_author = wpuf_get_option( 'default_post_owner', 'wpuf_frontend_posting', 1 );
        $post_author         = $this->wpuf_get_post_user();

        $allowed_tags = wp_kses_allowed_html( 'post' );
        $postarr = [
            'post_type'    => ! empty( $this->form_settings['post_type'] ) ? $this->form_settings['post_type'] : 'post',
            'post_status'  => isset( $this->form_settings['post_status'] ) ? $this->form_settings['post_status'] : 'publish',
            'post_author'  => $post_author,
            'post_title'   => isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '',
            'post_content' => isset( $_POST['post_content'] ) ? wp_kses( wp_unslash( $_POST['post_content'] ), $allowed_tags ) : '',
            'post_excerpt' => isset( $_POST['post_excerpt'] ) ? wp_kses( wp_unslash( $_POST['post_excerpt'] ), $allowed_tags ) : '',
        ];

        // $charging_enabled = wpuf_get_option( 'charge_posting', 'wpuf_payment' );
        $charging_enabled = '';
        $form             = new Form( $form_id );
        $payment_options  = $form->is_charging_enabled();
        $ppp_cost_enabled = $form->is_enabled_pay_per_post();
        $current_user     = wpuf_get_user();

        if ( ! $payment_options ) {
            $charging_enabled = 'no';
        } else {
            $charging_enabled = 'yes';
        }

        if ( 'true' === $guest_mode && 'true' === $guest_verify && ! is_user_logged_in() && 'yes' === $charging_enabled ) {
            $postarr['post_status'] = wpuf_get_draft_post_status( $this->form_settings );
        } elseif ( 'true' === $guest_mode && 'true' === $guest_verify && ! is_user_logged_in() ) {
            $postarr['post_status'] = 'draft';
        }
        //if date is set and assigned as publish date
        if ( isset( $_POST['wpuf_is_publish_time'] ) ) {
            if ( ! empty( $_POST[ $_POST['wpuf_is_publish_time'] ] ) ) {
                // $postarr['post_date'] = date( 'Y-m-d H:i:s', strtotime( str_replace( array( ':', '/' ), '-', $_POST[$_POST['wpuf_is_publish_time']] ) ) );
                $date_time = explode( ' ', sanitize_text_field( wp_unslash( ( $_POST[ $_POST['wpuf_is_publish_time'] ] ) ) ) );

                if ( ! empty( $date_time[0] ) ) {
                    $timestamp = strtotime( str_replace( [ '/' ], '-', $date_time[0] ) );
                }

                if ( ! empty( $date_time[1] ) ) {
                    $time       = explode( ':', $date_time[1] );
                    $seconds    = ( $time[0] * 60 * 60 ) + ( $time[1] * 60 );
                    $timestamp  = $timestamp + $seconds;
                }
                $postarr['post_date'] = gmdate( 'Y-m-d H:i:s', $timestamp );
            }
        }

        if ( isset( $_POST['category'] ) && is_array( $_POST['category'] ) ) {
            $category = isset( $_POST['category'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['category'] ) ) : [];
        } else {
            $category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
        }

        if ( $category !== '' && $category !== '0' && $category[0] !== '-1' ) {
            if ( ! is_array( $category ) && is_string( $category ) ) {
                $category_strings = explode( ',', $category );
                $cat_ids          = [];

                foreach ( $category_strings as $key => $each_cat_string ) {
                    $cat_ids[]                = get_cat_ID( trim( $each_cat_string ) );
                    $postarr['post_category'] = $cat_ids;
                }
            } else {
                $postarr['post_category'] = $category;
            }
        }

        if ( isset( $_POST['tags'] ) ) {
            $postarr['tags_input'] = explode( ',', sanitize_text_field( wp_unslash( $_POST['tags'] ) ) );
        }

        // if post_id is passed, we update the post
        if ( isset( $_POST['post_id'] ) ) {
            $post_id                   = intval( wp_unslash( $_POST['post_id'] ) );
            $is_update                 = true;
            $postarr['ID']             = $post_id;
            $postarr['post_date']      = isset( $_POST['post_date'] ) ? sanitize_text_field( wp_unslash( $_POST['post_date'] ) ) : '';
            $postarr['comment_status'] = isset( $_POST['comment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_status'] ) ) : '';
            $postarr['post_author']    = isset( $_POST['post_author'] ) ? sanitize_text_field( wp_unslash( $_POST['post_author'] ) ) : '';
            $postarr['post_parent']    = get_post_field( 'post_parent', $post_id );

            $menu_order = get_post_field( 'menu_order', $post_id );

            if ( ! empty( $menu_order ) ) {
                $postarr['menu_order'] = $menu_order;
            }

            if ( $this->form_settings['edit_post_status'] === '_nochange' ) {
                $postarr['post_status'] = get_post_field( 'post_status', $post_id );
            } else {
                $postarr['post_status'] = $this->form_settings['edit_post_status'];
            }
            // handle for falback ppp
            if ( 'pending' === get_post_meta( $post_id, '_wpuf_payment_status', true ) ) {
                $postarr['post_status'] = 'pending';
            }
        } else {
            if ( isset( $this->form_settings['comment_status'] ) ) {
                $postarr['comment_status'] = $this->form_settings['comment_status'];
            }
        }

        // check the form status, it might be already a draft
        // in that case, it already has the post_id field
        // so, WPUF's add post action/filters won't work for new posts
        $wpuf_form_status = isset( $_POST['wpuf_form_status'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_form_status'] ) ) : '';
        if ( $wpuf_form_status === 'new' ) {
            $is_update = false;
        }

        // set default post category if it's not been set yet and if post type supports
        if ( ! isset( $postarr['post_category'] ) && isset( $this->form_settings['default_cat'] ) && is_object_in_taxonomy( $this->form_settings['post_type'], 'category' ) ) {
            if ( is_array( $this->form_settings['default_cat'] ) ) {
                $postarr['post_category'] = $this->form_settings['default_cat'];
            } else {
                $postarr['post_category'] = [ $this->form_settings['default_cat'] ];
            }
        }

        // validation filter
        if ( $is_update ) {
            $error = apply_filters( 'wpuf_update_post_validate', '' );
        } else {
            $error = apply_filters( 'wpuf_add_post_validate', '' );
        }

        if ( ! empty( $error ) ) {
            wpuf()->ajax->send_error( $error );
        }
        // ############ It's Time to Save the World ###############
        if ( $is_update ) {
            $postarr = apply_filters( 'wpuf_update_post_args', $postarr, $form_id, $this->form_settings, $this->form_fields );
        } else {
            $postarr = apply_filters( 'wpuf_add_post_args', $postarr, $form_id, $this->form_settings, $this->form_fields );
        }

        $postarr = $this->adjust_thumbnail_id( $postarr );

        $post_id = wp_insert_post( $postarr, $wp_error = false );

        // add post revision when post edit from the frontend
        wpuf_frontend_post_revision( $post_id, $this->form_settings );

        // add _wpuf_lock_editing_post_time meta to
        // lock user from editing the published post after a certain time
        if ( ! $is_update ) {
            $lock_edit_post = isset( $this->form_settings['lock_edit_post'] ) ? floatval( $this->form_settings['lock_edit_post'] ) : 0;

            if ( $post_id && $lock_edit_post > 0 ) {
                $lock_edit_post_time = time() + ( $lock_edit_post * 60 * 60 );
                update_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', $lock_edit_post_time );
            }
        }

        if ( $post_id ) {
            $this->update_post_meta( $meta_vars, $post_id );
            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );
            // if user has a subscription pack
            $this->wpuf_user_subscription_pack( $this->form_settings, $post_id );
            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );

            // save post formats if have any
            if ( isset( $this->form_settings['post_format'] ) && $this->form_settings['post_format'] !== '0' ) {
                if ( post_type_supports( $this->form_settings['post_type'], 'post-formats' ) ) {
                    set_post_format( $post_id, $this->form_settings['post_format'] );
                }
            }

            // find our if any images in post content and associate them
            if ( ! empty( $postarr['post_content'] ) ) {
                $dom = new DOMDocument();
                @$dom->loadHTML( $postarr['post_content'] );
                $images = $dom->getElementsByTagName( 'img' );

                if ( $images->length ) {
                    foreach ( $images as $img ) {
                        $url           = $img->getAttribute( 'src' );
                        $url           = str_replace( [ '"', "'", '\\' ], '', $url );
                        $attachment_id = wpuf_get_attachment_id_from_url( $url );

                        if ( $attachment_id ) {
                            wpuf_associate_attachment( $attachment_id, $post_id );
                        }
                    }
                }
            }

            if ( ! empty( $taxonomy_vars ) ) {
                $this->set_custom_taxonomy( $post_id, $taxonomy_vars );
            } else {
                $this->set_default_taxonomy( $post_id );
            }

            $response = $this->send_mail_for_guest( $charging_enabled, $post_id, $form_id, $is_update, $post_author, $meta_vars );
            wpuf_clear_buffer();
            wp_send_json( $response );
        }
        wpuf()->ajax->send_error( __( 'Something went wrong', 'wp-user-frontend' ) );
    }

    public function send_mail_for_guest( $charging_enabled, $post_id, $form_id, $is_update, $post_author, $meta_vars ) {
        global $wp;
        check_ajax_referer( 'wpuf_form_add' );
        $show_message = false;
        $redirect_to  = false;
        $response     = [];
        $page_id      = isset( $_POST['page_id'] ) ? intval( wp_unslash( $_POST['page_id'] ) ) : '';

        if ( $is_update ) {
            if ( $this->form_settings['edit_redirect_to'] === 'page' ) {
                $redirect_to = get_permalink( $this->form_settings['edit_page_id'] );
            } elseif ( $this->form_settings['edit_redirect_to'] === 'url' ) {
                $redirect_to = $this->form_settings['edit_url'];
            } elseif ( $this->form_settings['edit_redirect_to'] === 'same' ) {
                $redirect_to = add_query_arg(
                    [
                        'pid'      => $post_id,
                        '_wpnonce' => wp_create_nonce( 'wpuf_edit' ),
                        'msg'      => 'post_updated',
                    ],
                    get_permalink( $page_id )
                );
            } else {
                $redirect_to = get_permalink( $post_id );
            }
        } else {
            if ( $this->form_settings['redirect_to'] === 'page' ) {
                $redirect_to = get_permalink( $this->form_settings['page_id'] );
            } elseif ( $this->form_settings['redirect_to'] === 'url' ) {
                $redirect_to = $this->form_settings['url'];
            } elseif ( $this->form_settings['redirect_to'] === 'same' ) {
                $show_message = true;
            } else {
                $redirect_to = get_permalink( $post_id );
            }
        }

        if ( $charging_enabled === 'yes' && isset( $this->form_settings['enable_pay_per_post'] )
             && wpuf_validate_boolean( $this->form_settings['enable_pay_per_post'] )
             && ! $is_update
        ) {
            $redirect_to = add_query_arg(
                [
                    'action'  => 'wpuf_pay',
                    'type'    => 'post',
                    'post_id' => $post_id,
                ],
                get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) )
            );
        }

        $response = [
            'success'      => true,
            'redirect_to'  => $redirect_to,
            'show_message' => $show_message,
            'message'      => $this->form_settings['message'],
        ];

        $guest_mode     = isset( $this->form_settings['guest_post'] ) ? $this->form_settings['guest_post'] : '';
        $guest_verify   = isset( $this->form_settings['guest_email_verify'] ) ? $this->form_settings['guest_email_verify'] : 'false';

        if ( $guest_mode === 'true' && $guest_verify === 'true' && ! is_user_logged_in() && $charging_enabled !== 'yes' ) {
            $post_id_encoded          = wpuf_encryption( $post_id );
            $form_id_encoded          = wpuf_encryption( $form_id );

            wpuf_send_mail_to_guest( $post_id_encoded, $form_id_encoded, 'no', 1 );

            $response['show_message'] = true;
            $response['redirect_to']  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
            $response['message']      = __( 'Thank you for posting on our site. We have sent you an confirmation email. Please check your inbox!', 'wp-user-frontend' );
        } elseif ( $guest_mode === 'true' && $guest_verify === 'true' && ! is_user_logged_in() && $charging_enabled === 'yes' ) {
            $post_id_encoded          = wpuf_encryption( $post_id );
            $form_id_encoded          = wpuf_encryption( $form_id );
            $response['show_message'] = true;
            $response['redirect_to']  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
            $response['message']      = __( 'Thank you for posting on our site. We have sent you an confirmation email. Please check your inbox!', 'wp-user-frontend' );

            update_post_meta( $post_id, '_wpuf_payment_status', 'pending' );
            wpuf_send_mail_to_guest( $post_id_encoded, $form_id_encoded, 'yes', 2 );
        }

        if ( $guest_mode === 'true' && $guest_verify === 'true' && ! is_user_logged_in() ) {
            $response = apply_filters( 'wpuf_edit_post_redirect', $response, $post_id, $form_id, $this->form_settings );
        } elseif ( $is_update ) {
            //now perform some post related actions
            do_action( 'wpuf_edit_post_after_update', $post_id, $form_id, $this->form_settings, $this->form_fields ); // plugin API to extend the functionality

            //send mail notification
            if ( isset( $this->form_settings['notification'] ) && $this->form_settings['notification']['edit'] === 'on' ) {
                $mail_body = $this->prepare_mail_body( $this->form_settings['notification']['edit_body'], $post_author, $post_id );
                $to        = $this->prepare_mail_body( $this->form_settings['notification']['edit_to'], $post_author, $post_id );
                $subject   = $this->prepare_mail_body( $this->form_settings['notification']['edit_subject'], $post_author, $post_id );
                $subject   = wp_strip_all_tags( $subject );
                $mail_body = get_formatted_mail_body( $mail_body, $subject );
                $headers   = [ 'Content-Type: text/html; charset=UTF-8' ];

                wp_mail( $to, $subject, $mail_body, $headers );
            }

            //now redirect the user
            $response = apply_filters( 'wpuf_edit_post_redirect', $response, $post_id, $form_id, $this->form_settings );
        } else {
            // send mail notification
            if ( isset( $this->form_settings['notification'] ) && $this->form_settings['notification']['new'] === 'on' ) {
                $mail_body = $this->prepare_mail_body( $this->form_settings['notification']['new_body'], $post_author, $post_id );
                $to        = $this->prepare_mail_body( $this->form_settings['notification']['new_to'], $post_author, $post_id );
                $subject   = $this->prepare_mail_body( $this->form_settings['notification']['new_subject'], $post_author, $post_id );
                $subject   = wp_strip_all_tags( $subject );
                $mail_body = get_formatted_mail_body( $mail_body, $subject );
                $headers   = [ 'Content-Type: text/html; charset=UTF-8' ];

                wp_mail( $to, $subject, $mail_body, $headers );
            }

            //redirect the user
            $response = apply_filters( 'wpuf_add_post_redirect', $response, $post_id, $form_id, $this->form_settings );
        }

        // now perform some post related actions. it should be done after other action. either count related problem emerge
        do_action( 'wpuf_add_post_after_insert', $post_id, $form_id, $this->form_settings, $meta_vars ); // plugin API to extend the functionality

        return $response;
    }

    public function wpuf_get_post_user() {
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf_form_add' ) ) {
            return;
        }

        $default_post_author = wpuf_get_option( 'default_post_owner', 'wpuf_frontend_posting', 1 );

        if ( ! is_user_logged_in() ) {
            if ( isset( $this->form_settings['guest_post'] ) && $this->form_settings['guest_post'] === 'true' && $this->form_settings['guest_details'] === 'true' ) {
                $guest_name = isset( $_POST['guest_name'] ) ? sanitize_text_field( wp_unslash( $_POST['guest_name'] ) ) : '';

                $guest_email = isset( $_POST['guest_email'] ) ? sanitize_email( wp_unslash( $_POST['guest_email'] ) ) : '';
                $page_id = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';

                // is valid email?
                if ( ! is_email( $guest_email ) ) {
                    echo json_encode(
                        [
                            'success' => false,
                            'error'   => __( 'Invalid email address.', 'wp-user-frontend' ),
                        ]
                    );

                    die();

//                    $this->send_error( __( 'Invalid email address.', 'wp-user-frontend' ) );
//                    wp_send_json(
//                        [
//                            'success'     => false,
//                            'error'       => __( "You already have an account in our site. Please login to continue.\n\nClicking 'OK' will redirect you to the login page and you will lose the form data.\nClick 'Cancel' to stay at this page.", 'wp-user-frontend' ),
//                            'type'        => 'login',
//                            'redirect_to' => wp_login_url( get_permalink( $page_id ) ),
//                        ]
//                    );
                    // wpuf()->ajax->send_error( __( 'Invalid email address.', 'wp-user-frontend' ) );
                }

                // check if the user email already exists
                $user = get_user_by( 'email', $guest_email );

                if ( $user ) {
                    // $post_author = $user->ID;
                    wp_send_json(
                        [
                            'success'     => false,
                            'error'       => __( "You already have an account in our site. Please login to continue.\n\nClicking 'OK' will redirect you to the login page and you will lose the form data.\nClick 'Cancel' to stay at this page.", 'wp-user-frontend' ),
                            'type'        => 'login',
                            'redirect_to' => wp_login_url( get_permalink( $page_id ) ),
                        ]
                    );
                } else {

                    // user not found, lets register him
                    // username from email address
                    $username = wpuf_guess_username( $guest_email );

                    $user_pass = wp_generate_password( 12, false );

                    $errors = new WP_Error();

                    do_action( 'register_post', $username, $guest_email, $errors );

                    $user_id = wp_create_user( $username, $user_pass, $guest_email );

                    // if its a success and no errors found

                    if ( $user_id && ! is_wp_error( $user_id ) ) {
                        update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

                        if ( class_exists( 'Theme_My_Login_Custom_Email' ) ) {
                            do_action( 'tml_new_user_registered', $user_id, $user_pass );
                        } else {
                            wp_send_new_user_notifications( $user_id );
                        }

                        // update display name to full name
                        wp_update_user(
                            [
                                'ID' => $user_id,
                                'display_name' => $guest_name,
                            ]
                        );

                        $post_author = $user_id;
                    } else {
                        //something went wrong creating the user, set post author to the default author
                        $post_author = $default_post_author;
                    }
                }

                // guest post is enabled and details are off
            } elseif ( isset( $this->form_settings['guest_post'] ) && $this->form_settings['guest_post'] === 'true' && $this->form_settings['guest_details'] === 'false' ) {
                $post_author = $default_post_author;
            } elseif ( isset( $this->form_settings['guest_post'] ) && $this->form_settings['guest_post'] !== 'true' ) {
                wpuf()->ajax->send_error( $this->form_settings['message_restrict'] );
            }

            // the user must be logged in already
        } elseif ( isset( $this->form_settings['role_base'] ) && $this->form_settings['role_base'] === 'true' && ! wpuf_user_has_roles( $this->form_settings['roles'] ) ) {
            wpuf()->ajax->send_error( __( 'You do not have sufficient permissions to access this form.', 'wp-user-frontend' ) );
        } else {
            $post_author = get_current_user_id();
        }

        return $post_author;
    }

    public function wpuf_user_subscription_pack( $form_settings, $post_id = null ) {

        // if user has a subscription pack
        $user_wpuf_subscription_pack = get_user_meta( get_current_user_id(), '_wpuf_subscription_pack', true );
        $wpuf_user               = wpuf_get_user();
        $user_subscription       = new User_Subscription( $wpuf_user );
        if ( ! empty( $user_wpuf_subscription_pack ) && isset( $user_wpuf_subscription_pack['_enable_post_expiration'] )
             && isset( $user_wpuf_subscription_pack['expire'] ) && strtotime( $user_wpuf_subscription_pack['expire'] ) >= time() ) {
            $expire_date = gmdate( 'Y-m-d', strtotime( '+' . $user_wpuf_subscription_pack['_post_expiration_time'] ) );
            update_post_meta( $post_id, $this->post_expiration_date, $expire_date );
            // save post status after expiration
            $expired_post_status = $user_wpuf_subscription_pack['_expired_post_status'];
            update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );
            // if mail active
            if ( isset( $user_wpuf_subscription_pack['_enable_mail_after_expired'] ) && $user_wpuf_subscription_pack['_enable_mail_after_expired'] === 'on' ) {
                $post_expiration_message = $user_subscription->get_subscription_exp_msg( $user_wpuf_subscription_pack['pack_id'] );
                update_post_meta( $post_id, $this->post_expiration_message, $post_expiration_message );
            }
        } elseif ( ! empty( $user_wpuf_subscription_pack ) && isset( $user_wpuf_subscription_pack['expire'] ) && strtotime( $user_wpuf_subscription_pack['expire'] ) <= time() ) {
            if ( isset( $form_settings['expiration_settings']['enable_post_expiration'] ) ) {
                $expire_date = gmdate( 'Y-m-d', strtotime( '+' . $form_settings['expiration_settings']['expiration_time_value'] . ' ' . $form_settings['expiration_settings']['expiration_time_type'] . '' ) );

                update_post_meta( $post_id, $this->post_expiration_date, $expire_date );
                // save post status after expiration
                $expired_post_status = $form_settings['expiration_settings']['expired_post_status'];
                update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );
                // if mail active
                if ( isset( $form_settings['expiration_settings']['enable_mail_after_expired'] ) && $form_settings['expiration_settings']['enable_mail_after_expired'] === 'on' ) {
                    $post_expiration_message = $form_settings['expiration_settings']['post_expiration_message'];
                    update_post_meta( $post_id, $this->post_expiration_message, $post_expiration_message );
                }
            }
        } elseif ( empty( $user_wpuf_subscription_pack ) || $user_wpuf_subscription_pack === 'Cancel' || $user_wpuf_subscription_pack === 'cancel' ) {
            if ( isset( $form_settings['expiration_settings']['enable_post_expiration'] ) ) {
                $expire_date = gmdate( 'Y-m-d', strtotime( '+' . $form_settings['expiration_settings']['expiration_time_value'] . ' ' . $form_settings['expiration_settings']['expiration_time_type'] . '' ) );
                update_post_meta( $post_id, $this->post_expiration_date, $expire_date );
                // save post status after expiration
                $expired_post_status = $form_settings['expiration_settings']['expired_post_status'];
                update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );
                // if mail active
                if ( isset( $form_settings['expiration_settings']['enable_mail_after_expired'] ) && $form_settings['expiration_settings']['enable_mail_after_expired'] === 'on' ) {
                    $post_expiration_message = $form_settings['expiration_settings']['post_expiration_message'];
                    update_post_meta( $post_id, $this->post_expiration_message, $post_expiration_message );
                }
            }
        }

        //Handle featured item when edit
        $sub_meta = $user_subscription->handle_featured_item( $post_id, $user_wpuf_subscription_pack );
        $user_subscription->update_meta( $sub_meta );
    }

    public function prepare_mail_body( $content, $user_id, $post_id ) {
        $user = get_user_by( 'id', $user_id );
        $post = get_post( $post_id );

        $post_field_search = [
            '{post_title}',
            '{post_content}',
            '{post_excerpt}',
            '{tags}',
            '{category}',
            '{author}',
            '{author_email}',
            '{author_bio}',
            '{sitename}',
            '{siteurl}',
            '{permalink}',
            '{editlink}',
        ];

        $home_url = sprintf( '<a href="%s">%s</a>', home_url(), home_url() );
        $post_url = sprintf( '<a href="%s">%s</a>', get_permalink( $post_id ), get_permalink( $post_id ) );
	    $post_edit_link = sprintf( '<a href="%s">%s</a>', admin_url( 'post.php?action=edit&post=' . $post_id ), admin_url( 'post.php?action=edit&post=' . $post_id ) );

        $post_field_replace = [
            $post->post_title,
            $post->post_content,
            $post->post_excerpt,
            get_the_term_list( $post_id, 'post_tag', '', ', ' ),
            get_the_term_list( $post_id, 'category', '', ', ' ),
            $user->display_name,
            $user->user_email,
            ( $user->description ) ? $user->description : 'not available',
            get_bloginfo( 'name' ),
            $home_url,
            $post_url,
            $post_edit_link,
        ];

        if ( class_exists( 'WooCommerce' ) ) {
            $post_field_search[] = '{product_cat}';
            $post_field_replace[] = get_the_term_list( $post_id, 'product_cat', '', ', ' );
        }

        $content = str_replace( $post_field_search, $post_field_replace, $content );

        // custom fields
        preg_match_all( '/{custom_([\w-]*)\b}/', $content, $matches );
        [ $search, $replace ] = $matches;

        if ( $replace ) {
            foreach ( $replace as $index => $meta_key ) {
                $value = get_post_meta( $post_id, $meta_key, false );

                if ( isset( $value[0] ) && is_array( $value[0] ) ) {
                    $new_value = implode( '; ', $value[0] );
                } else {
                    $new_value = implode( '; ', $value );
                }

                $original_value = '';
                $meta_val       = '';

                if ( count( $value ) > 1 ) {
                    $is_first = true;

                    foreach ( $value as $val ) {
                        if ( $is_first ) {
                            if ( get_post_mime_type( (int) $val ) ) {
                                $meta_val = wp_get_attachment_url( $val );
                            } else {
                                $meta_val = $val;
                            }
                            $is_first = false;
                        } else {
                            if ( get_post_mime_type( (int) $val ) ) {
                                $meta_val = $meta_val . ', ' . wp_get_attachment_url( $val );
                            } else {
                                $meta_val = $meta_val . ', ' . $val;
                            }
                        }

                        if ( get_post_mime_type( (int) $val ) ) {
                            $meta_val = $meta_val . ',' . wp_get_attachment_url( $val );
                        } else {
                            $meta_val = $meta_val . ',' . $val;
                        }
                    }
                    $original_value = $original_value . $meta_val;
                } else {
                    if ( 'address_field' === $meta_key ) {
                        $value     = get_post_meta( $post_id, $meta_key, true );
                        $new_value = implode( ', ', $value );
                    }

                    if ( get_post_mime_type( (int) $new_value ) ) {
                        $original_value = wp_get_attachment_url( $new_value );
                    } else {
                        $original_value = $new_value;
                    }
                }

                $content = str_replace( $search[ $index ], $original_value, $content );
            }
        }

        return $content;
    }
}
