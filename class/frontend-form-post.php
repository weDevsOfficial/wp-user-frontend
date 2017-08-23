<?php

class WPUF_Frontend_Form_Post extends WPUF_Render_Form {

    private static $_instance;
    private $post_expiration_date    = 'wpuf-post_expiration_date';
    private $expired_post_status     = 'wpuf-expired_post_status';
    private $post_expiration_message = 'wpuf-post_expiration_message';

    function __construct() {

        add_shortcode( 'wpuf_form', array( $this, 'add_post_shortcode' ) );
        add_shortcode( 'wpuf_edit', array( $this, 'edit_post_shortcode' ) );

        // ajax requests
        add_action( 'wp_ajax_wpuf_submit_post', array( $this, 'submit_post' ) );
        add_action( 'wp_ajax_nopriv_wpuf_submit_post', array( $this, 'submit_post' ) );
        add_action( 'wp_ajax_make_media_embed_code', array( $this, 'make_media_embed_code' ) );
        add_action( 'wp_ajax_nopriv_make_media_embed_code', array( $this, 'make_media_embed_code' ) );

        // draft
        add_action( 'wp_ajax_wpuf_draft_post', array( $this, 'draft_post' ) );

        // form preview
        add_action( 'wp_ajax_wpuf_form_preview', array( $this, 'preview_form' ) );
    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Add post shortcode handler
     *
     * @param array $atts
     * @return string
     */
    function add_post_shortcode( $atts ) {
        // wpuf()->plugin_scripts();
        ?>
        <style>
            <?php //echo $custom_css = wpuf_get_option( 'custom_css', 'wpuf_general' ); ?>
        </style>
        <?php
        extract( shortcode_atts( array( 'id' => 0 ), $atts ) );
        ob_start();

        $form_settings = wpuf_get_form_settings( $id );
        $info          = apply_filters( 'wpuf_addpost_notice', '', $id, $form_settings );
        $user_can_post = apply_filters( 'wpuf_can_post', 'yes', $id, $form_settings );

        if ( $user_can_post == 'yes' ) {
            $this->render_form( $id );
        } else {
            echo '<div class="wpuf-info">' . $info . '</div>';
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Edit post shortcode handler
     *
     * @param array $atts
     * @return string
     */
    function edit_post_shortcode( $atts ) {
        global $userdata;
        // wpuf()->plugin_scripts();
        ?>
        <style>
            <?php //echo $custom_css = wpuf_get_option( 'custom_css', 'wpuf_general' ); ?>
        </style>
        <?php
        ob_start();

        if ( !is_user_logged_in() ) {
            echo '<div class="wpuf-message">' . __( 'You are not logged in', 'wpuf' ) . '</div>';
            wp_login_form();
            return;
        }

        $post_id = isset( $_GET['pid'] ) ? intval( $_GET['pid'] ) : 0;

        if ( !$post_id ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wpuf' ) . '</div>';
        }

        //is editing enabled?
        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) != 'yes' ) {
            return '<div class="wpuf-info">' . __( 'Post Editing is disabled', 'wpuf' ) . '</div>';
        }

        $curpost = get_post( $post_id );

        if ( !$curpost ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wpuf' );
        }

        // has permission?
        if ( !current_user_can( 'delete_others_posts' ) && ( $userdata->ID != $curpost->post_author ) ) {
            return '<div class="wpuf-info">' . __( 'You are not allowed to edit', 'wpuf' ) . '</div>';
        }

        $form_id       = get_post_meta( $post_id, self::$config_id, true );
        $form_settings = wpuf_get_form_settings( $form_id );

        // fallback to default form
        if ( !$form_id ) {
            $form_id = wpuf_get_option( 'default_post_form', 'wpuf_general' );
        }

        if ( !$form_id ) {
            return '<div class="wpuf-info">' . __( "I don't know how to edit this post, I don't have the form ID", 'wpuf' ) . '</div>';
        }

        $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );

        if ( $curpost->post_status == 'pending' && $disable_pending_edit == 'on' ) {
            return '<div class="wpuf-info">' . __( 'You can\'t edit a post while in pending mode.', 'wpuf' );
        }

        if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'post_updated' ) {
            echo '<div class="wpuf-success">';
            echo str_replace( '%link%', get_permalink( $post_id ), $form_settings['update_message'] );
            echo '</div>';
        }

        $this->render_form( $form_id, $post_id );

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * New/Edit post submit handler
     *
     * @return void
     */
    function submit_post() {

        check_ajax_referer( 'wpuf_form_add' );

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

        $form_id       = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : 0;
        $form_vars     = $this->get_input_fields( $form_id );
        $form_settings = wpuf_get_form_settings( $form_id );

        list( $post_vars, $taxonomy_vars, $meta_vars ) = $form_vars;

        // don't check captcha on post edit
        if ( !isset( $_POST['post_id'] ) ) {

            // search if rs captcha is there
            if ( $this->search( $post_vars, 'input_type', 'really_simple_captcha' ) ) {
                $this->validate_rs_captcha();
            }

            // check recaptcha
            if ( $this->search( $post_vars, 'input_type', 'recaptcha' ) ) {

                $no_captcha = '';
                if ( isset( $_POST["g-recaptcha-response"] ) ) {
                    $no_captcha = 1;
                } else {
                    $no_captcha = 0;
                }
                $this->validate_re_captcha( $no_captcha );
            }
        }

        $is_update           = false;
        $post_author         = null;
        $default_post_author = wpuf_get_option( 'default_post_owner', 'wpuf_general', 1 );

        // Guest Stuffs: check for guest post
        if ( !is_user_logged_in() ) {

            if ( $form_settings['guest_post'] == 'true' && $form_settings['guest_details'] == 'true' ) {
                $guest_name  = trim( $_POST['guest_name'] );
                $guest_email = trim( $_POST['guest_email'] );

                // is valid email?
                if ( !is_email( $guest_email ) ) {
                    $this->send_error( __( 'Invalid email address.', 'wpuf' ) );
                }

                // check if the user email already exists
                $user = get_user_by( 'email', $guest_email );
                if ( $user ) {
                    // $post_author = $user->ID;
                    wp_send_json( array(
                        'success'     => false,
                        'error'       => __( "You already have an account in our site. Please login to continue.\n\nClicking 'OK' will redirect you to the login page and you will lost the form data.\nClick 'Cancel' to stay at this page.", 'wpuf' ),
                        'type'        => 'login',
                        'redirect_to' => wp_login_url( get_permalink( $_POST['page_id'] ) )
                    ) );
                } else {

                    // user not found, lets register him
                    // username from email address
                    $username  = $this->guess_username( $guest_email );
                    $user_pass = wp_generate_password( 12, false );

                    $errors = new WP_Error();
                    do_action( 'register_post', $username, $guest_email, $errors );

                    $user_id = wp_create_user( $username, $user_pass, $guest_email );

                    // if its a success and no errors found
                    if ( $user_id && !is_wp_error( $user_id ) ) {
                        update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

                        if ( class_exists( 'Theme_My_Login_Custom_Email' ) ) {
                            do_action( 'tml_new_user_registered', $user_id, $user_pass );
                        } else {
                            wp_send_new_user_notifications( $user_id );
                        }

                        // update display name to full name
                        wp_update_user( array( 'ID' => $user_id, 'display_name' => $guest_name ) );

                        $post_author = $user_id;
                    } else {
                        //something went wrong creating the user, set post author to the default author
                        $post_author = $default_post_author;
                    }
                }

                // guest post is enabled and details are off
            } elseif ( $form_settings['guest_post'] == 'true' && $form_settings['guest_details'] == 'false' ) {
                $post_author = $default_post_author;
            }

            // the user must be logged in already
        } else {
            $post_author = get_current_user_id();
        }

        $postarr = array(
            'post_type'    => $form_settings['post_type'],
            'post_status'  => isset( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'publish',
            'post_author'  => $post_author,
            'post_title'   => isset( $_POST['post_title'] ) ? trim( $_POST['post_title'] ) : '',
            'post_content' => isset( $_POST['post_content'] ) ? trim( $_POST['post_content'] ) : '',
            'post_excerpt' => isset( $_POST['post_excerpt'] ) ? trim( $_POST['post_excerpt'] ) : '',
        );

        //if date is set and assigned as publish date
        if ( isset( $_POST['wpuf_is_publish_time'] ) ) {

            if ( isset( $_POST[$_POST['wpuf_is_publish_time']] ) && !empty( $_POST[$_POST['wpuf_is_publish_time']] ) ) {
                $postarr['post_date'] = date( 'Y-m-d H:i:s', strtotime( str_replace( array( ':', '/' ), '-', $_POST[$_POST['wpuf_is_publish_time']] ) ) );
            }
        }

        if ( isset( $_POST['category'] ) ) {
            $category                 = $_POST['category'];
            $postarr['post_category'] = is_array( $category ) ? $category : array( $category );

            if ( !is_array( $category ) && is_string( $category ) ) {
                $category_strings = explode( ',', $category );

                $cat_ids = array();

                foreach ( $category_strings as $key => $each_cat_string ) {
                    $cat_ids[]                = get_cat_ID( trim( $each_cat_string ) );
                    $postarr['post_category'] = $cat_ids;
                }
            }
        }

        if ( isset( $_POST['tags'] ) ) {
            $postarr['tags_input'] = explode( ',', $_POST['tags'] );
        }

        // if post_id is passed, we update the post
        if ( isset( $_POST['post_id'] ) ) {

            $is_update                 = true;
            $postarr['ID']             = $_POST['post_id'];
            $postarr['post_date']      = $_POST['post_date'];
            $postarr['comment_status'] = $_POST['comment_status'];
            $postarr['post_author']    = $_POST['post_author'];
            $postarr['post_parent']    = get_post_field( 'post_parent', $_POST['post_id'] );

            if ( $form_settings['edit_post_status'] == '_nochange' ) {
                $postarr['post_status'] = get_post_field( 'post_status', $_POST['post_id'] );
            } else {
                $postarr['post_status'] = $form_settings['edit_post_status'];
            }
        } else {
            if ( isset( $form_settings['comment_status'] ) ) {
                $postarr['comment_status'] = $form_settings['comment_status'];
            }
        }

        // check the form status, it might be already a draft
        // in that case, it already has the post_id field
        // so, WPUF's add post action/filters won't work for new posts
        if ( isset( $_POST['wpuf_form_status'] ) && $_POST['wpuf_form_status'] == 'new' ) {
            $is_update = false;
        }

        // set default post category if it's not been set yet and if post type supports
        if ( !isset( $postarr['post_category'] ) && isset( $form_settings['default_cat'] ) && is_object_in_taxonomy( $form_settings['post_type'], 'category' ) ) {
            $postarr['post_category'] = array( $form_settings['default_cat'] );
        }

        // validation filter
        if ( $is_update ) {
            $error = apply_filters( 'wpuf_update_post_validate', '' );
        } else {
            $error = apply_filters( 'wpuf_add_post_validate', '' );
        }

        if ( !empty( $error ) ) {
            $this->send_error( $error );
        }

        // ############ It's Time to Save the World ###############
        if ( $is_update ) {
            $postarr = apply_filters( 'wpuf_update_post_args', $postarr, $form_id, $form_settings, $form_vars );
        } else {
            $postarr = apply_filters( 'wpuf_add_post_args', $postarr, $form_id, $form_settings, $form_vars );
        }

        $post_id = wp_insert_post( $postarr );

        if ( $post_id ) {

            self::update_post_meta( $meta_vars, $post_id );

            // if user has a subscription pack
            $user_wpuf_subscription_pack = get_user_meta( get_current_user_id(), '_wpuf_subscription_pack', true );

            if ( !empty( $user_wpuf_subscription_pack ) && isset( $user_wpuf_subscription_pack['_enable_post_expiration'] ) && isset( $user_wpuf_subscription_pack['expire'] ) && strtotime( $user_wpuf_subscription_pack['expire'] ) >= time()
            ) {
                $expire_date = date( 'Y-m-d', strtotime( "+" . $user_wpuf_subscription_pack['_post_expiration_time'] ) );
                update_post_meta( $post_id, $this->post_expiration_date, $expire_date );

                // save post status after expiration
                $expired_post_status = $user_wpuf_subscription_pack['_expired_post_status'];
                update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );

                // if mail active
                if ( isset( $user_wpuf_subscription_pack['_enable_mail_after_expired'] ) && $user_wpuf_subscription_pack['_enable_mail_after_expired'] == 'on' ) {
                    $post_expiration_message = $user_wpuf_subscription_pack['_post_expiration_message'];
                    update_post_meta( $post_id, $this->post_expiration_message, $post_expiration_message );
                }
            } elseif ( !empty( $user_wpuf_subscription_pack ) && isset( $user_wpuf_subscription_pack['expire'] ) && strtotime( $user_wpuf_subscription_pack['expire'] ) <= time() ) {

                if ( isset( $form_settings['expiration_settings']['enable_post_expiration'] ) ) {

                    $expire_date = date( 'Y-m-d', strtotime( "+" . $form_settings['expiration_settings']['expiration_time_value'] . ' ' . $form_settings['expiration_settings']['expiration_time_type'] . "" ) );
                    update_post_meta( $post_id, $this->post_expiration_date, $expire_date );

                    // save post status after expiration
                    $expired_post_status = $form_settings['expiration_settings']['expired_post_status'];
                    update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );

                    // if mail active
                    if ( isset( $form_settings['expiration_settings']['enable_mail_after_expired'] ) && $form_settings['expiration_settings']['enable_mail_after_expired'] == 'on' ) {
                        $post_expiration_message = $form_settings['expiration_settings']['post_expiration_message'];
                        update_post_meta( $post_id, $this->post_expiration_message, $post_expiration_message );
                    }
                }
            } elseif ( empty( $user_wpuf_subscription_pack ) ) {

                if ( isset( $form_settings['expiration_settings']['enable_post_expiration'] ) ) {
                    $expire_date = date( 'Y-m-d', strtotime( "+" . $form_settings['expiration_settings']['expiration_time_value'] . ' ' . $form_settings['expiration_settings']['expiration_time_type'] . "" ) );
                    update_post_meta( $post_id, $this->post_expiration_date, $expire_date );

                    // save post status after expiration
                    $expired_post_status = $form_settings['expiration_settings']['expired_post_status'];
                    update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );

                    // if mail active
                    if ( isset( $form_settings['expiration_settings']['enable_mail_after_expired'] ) && $form_settings['expiration_settings']['enable_mail_after_expired'] == 'on' ) {
                        $post_expiration_message = $form_settings['expiration_settings']['post_expiration_message'];
                        update_post_meta( $post_id, $this->post_expiration_message, $post_expiration_message );
                    }
                }
            }


            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );

            // save post formats if have any
            if ( isset( $form_settings['post_format'] ) && $form_settings['post_format'] != '0' ) {
                if ( post_type_supports( $form_settings['post_type'], 'post-formats' ) ) {
                    set_post_format( $post_id, $form_settings['post_format'] );
                }
            }

            // find our if any images in post content and associate them
            if ( !empty( $postarr['post_content'] ) ) {
                $dom    = new DOMDocument();
                @$dom->loadHTML( $postarr['post_content'] );
                $images = $dom->getElementsByTagName( 'img' );

                if ( $images->length ) {
                    foreach ( $images as $img ) {
                        $url           = $img->getAttribute( 'src' );
                        $url           = str_replace( array( '"', "'", "\\" ), '', $url );
                        $attachment_id = wpuf_get_attachment_id_from_url( $url );

                        if ( $attachment_id ) {
                            wpuf_associate_attachment( $attachment_id, $post_id );
                        }
                    }
                }
            }

            // save any custom taxonomies
            $woo_attr = array();

            foreach ( $taxonomy_vars as $taxonomy ) {
                if ( isset( $_POST[$taxonomy['name']] ) ) {

                    if ( is_object_in_taxonomy( $form_settings['post_type'], $taxonomy['name'] ) ) {
                        $tax = $_POST[$taxonomy['name']];

                        // if it's not an array, make it one
                        if ( !is_array( $tax ) ) {
                            $tax = array( $tax );
                        }

                        if ( $taxonomy['type'] == 'text' ) {

                            $hierarchical = array_map( 'trim', array_map( 'strip_tags', explode( ',', $_POST[$taxonomy['name']] ) ) );

                            wp_set_object_terms( $post_id, $hierarchical, $taxonomy['name'] );

                            // woocommerce check
                            if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && !empty( $_POST[$taxonomy['name']] ) ) {
                                $woo_attr[sanitize_title( $taxonomy['name'] )] = $this->woo_attribute( $taxonomy );
                            }
                        } else {

                            if ( is_taxonomy_hierarchical( $taxonomy['name'] ) ) {
                                wp_set_post_terms( $post_id, $_POST[$taxonomy['name']], $taxonomy['name'] );

                                // woocommerce check
                                if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && !empty( $_POST[$taxonomy['name']] ) ) {
                                    $woo_attr[sanitize_title( $taxonomy['name'] )] = $this->woo_attribute( $taxonomy );
                                }
                            } else {
                                if ( $tax ) {
                                    $non_hierarchical = array();

                                    foreach ( $tax as $value ) {
                                        $term = get_term_by( 'id', $value, $taxonomy['name'] );
                                        if ( $term && !is_wp_error( $term ) ) {
                                            $non_hierarchical[] = $term->name;
                                        }
                                    }

                                    wp_set_post_terms( $post_id, $non_hierarchical, $taxonomy['name'] );

                                    // woocommerce check
                                    if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && !empty( $_POST[$taxonomy['name']] ) ) {
                                        $woo_attr[sanitize_title( $taxonomy['name'] )] = $this->woo_attribute( $taxonomy );
                                    }

                                }
                            } // hierarchical
                        } // is text
                    } // is object tax
                } // isset tax
            }

            // if a woocommerce attribute
            if ( $woo_attr ) {
                update_post_meta( $post_id, '_product_attributes', $woo_attr );
            }

            if ( $is_update ) {

                // plugin API to extend the functionality
                do_action( 'wpuf_edit_post_after_update', $post_id, $form_id, $form_settings, $form_vars );

                //send mail notification
                if ( isset( $form_settings['notification'] ) && $form_settings['notification']['edit'] == 'on' ) {
                    $mail_body = $this->prepare_mail_body( $form_settings['notification']['edit_body'], $post_author, $post_id );
                    $to        = $this->prepare_mail_body( $form_settings['notification']['edit_to'], $post_author, $post_id );
                    $subject   = $this->prepare_mail_body( $form_settings['notification']['edit_subject'], $post_author, $post_id );

                    wp_mail( $to, $subject, $mail_body );
                }
            } else {

                // plugin API to extend the functionality
                do_action( 'wpuf_add_post_after_insert', $post_id, $form_id, $form_settings, $form_vars );

                // send mail notification
                if ( isset( $form_settings['notification'] ) && $form_settings['notification']['new'] == 'on' ) {
                    $mail_body = $this->prepare_mail_body( $form_settings['notification']['new_body'], $post_author, $post_id );
                    $to        = $this->prepare_mail_body( $form_settings['notification']['new_to'], $post_author, $post_id );
                    $subject   = $this->prepare_mail_body( $form_settings['notification']['new_subject'], $post_author, $post_id );

                    wp_mail( $to, $subject, $mail_body );
                }
            }

            //redirect URL
            $show_message = false;
            $redirect_to  = false;

            if ( $is_update ) {
                if ( $form_settings['edit_redirect_to'] == 'page' ) {
                    $redirect_to = get_permalink( $form_settings['edit_page_id'] );
                } elseif ( $form_settings['edit_redirect_to'] == 'url' ) {
                    $redirect_to = $form_settings['edit_url'];
                } elseif ( $form_settings['edit_redirect_to'] == 'same' ) {
                    $redirect_to = add_query_arg( array(
                        'pid'      => $post_id,
                        '_wpnonce' => wp_create_nonce( 'wpuf_edit' ),
                        'msg'      => 'post_updated'
                    ), get_permalink( $_POST['page_id'] )
                    );
                } else {
                    $redirect_to = get_permalink( $post_id );
                }
            } else {
                if ( $form_settings['redirect_to'] == 'page' ) {
                    $redirect_to = get_permalink( $form_settings['page_id'] );
                } elseif ( $form_settings['redirect_to'] == 'url' ) {
                    $redirect_to = $form_settings['url'];
                } elseif ( $form_settings['redirect_to'] == 'same' ) {
                    $show_message = true;
                } else {
                    $redirect_to = get_permalink( $post_id );
                }
            }

            // send the response
            $response = array(
                'success'      => true,
                'redirect_to'  => $redirect_to,
                'show_message' => $show_message,
                'message'      => $form_settings['message']
            );

            if ( $is_update ) {
                $response = apply_filters( 'wpuf_edit_post_redirect', $response, $post_id, $form_id, $form_settings );
            } else {
                $response = apply_filters( 'wpuf_add_post_redirect', $response, $post_id, $form_id, $form_settings );
            }

            wpuf_clear_buffer();

            wp_send_json( $response );
        }

        $this->send_error( __( 'Something went wrong', 'wpuf' ) );
    }

    /**
     * this will embed media to the editor
     */
    function make_media_embed_code() {
        if ( $embed_code = wp_oembed_get( $_POST['content'] ) ) {
            echo $embed_code;
        } else {
            echo '';
        }

        exit;
    }

    function draft_post() {
        check_ajax_referer( 'wpuf_form_add' );

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

        $form_id       = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : 0;
        $form_vars     = $this->get_input_fields( $form_id );
        $form_settings = wpuf_get_form_settings( $form_id );
        $post_content = isset( $_POST[ 'post_content' ] ) ? $_POST[ 'post_content' ] : '';

        list( $post_vars, $taxonomy_vars, $meta_vars ) = $form_vars;

        $postarr = array(
            'post_type'    => $form_settings['post_type'],
            'post_status'  => wpuf_get_draft_post_status( $form_settings ),
            'post_author'  => get_current_user_id(),
            'post_title'   => isset( $_POST['post_title'] ) ? trim( $_POST['post_title'] ) : '',
            'post_content' => $post_content,
            'post_excerpt' => isset( $_POST['post_excerpt'] ) ? trim( $_POST['post_excerpt'] ) : '',
        );

        if ( isset( $_POST['category'] ) ) {
            $category                 = $_POST['category'];
            $postarr['post_category'] = is_array( $category ) ? $category : array( $category );
        }

        if ( isset( $_POST['tags'] ) ) {
            $postarr['tags_input'] = explode( ',', $_POST['tags'] );
        }

        // if post_id is passed, we update the post
        if ( isset( $_POST['post_id'] ) ) {
            $is_update                 = true;
            $postarr['ID']             = $_POST['post_id'];
            $postarr['comment_status'] = 'open';
        }

        $post_id = wp_insert_post( $postarr );

        if ( $post_id ) {

            self::update_post_meta( $meta_vars, $post_id );

            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );

            // save post formats if have any
            if ( isset( $form_settings['post_format'] ) && $form_settings['post_format'] != '0' ) {
                if ( post_type_supports( $form_settings['post_type'], 'post-formats' ) ) {
                    set_post_format( $post_id, $form_settings['post_format'] );
                }
            }

            // save any custom taxonomies
            $woo_attr = array();

            foreach ( $taxonomy_vars as $taxonomy ) {
                if ( isset( $_POST[$taxonomy['name']] ) ) {

                    if ( is_object_in_taxonomy( $form_settings['post_type'], $taxonomy['name'] ) ) {
                        $tax = $_POST[$taxonomy['name']];

                        // if it's not an array, make it one
                        if ( !is_array( $tax ) ) {
                            $tax = array( $tax );
                        }

                        if ( $taxonomy['type'] == 'text' ) {

                            $hierarchical = array_map( 'trim', array_map( 'strip_tags', explode( ',', $_POST[$taxonomy['name']] ) ) );

                            wp_set_object_terms( $post_id, $hierarchical, $taxonomy['name'] );

                            // woocommerce check
                            if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && !empty( $_POST[$taxonomy['name']] ) ) {
                                $woo_attr[sanitize_title( $taxonomy['name'] )] = $this->woo_attribute( $taxonomy );
                            }
                        } else {

                            if ( is_taxonomy_hierarchical( $taxonomy['name'] ) ) {
                                wp_set_post_terms( $post_id, $_POST[$taxonomy['name']], $taxonomy['name'] );

                                // woocommerce check
                                if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && !empty( $_POST[$taxonomy['name']] ) ) {
                                    $woo_attr[sanitize_title( $taxonomy['name'] )] = $this->woo_attribute( $taxonomy );
                                }
                            } else {
                                if ( $tax ) {
                                    $non_hierarchical = array();

                                    foreach ( $tax as $value ) {
                                        $term = get_term_by( 'id', $value, $taxonomy['name'] );
                                        if ( $term && !is_wp_error( $term ) ) {
                                            $non_hierarchical[] = $term->name;
                                        }
                                    }

                                    wp_set_post_terms( $post_id, $non_hierarchical, $taxonomy['name'] );

                                    // woocommerce check
                                    if ( isset( $taxonomy['woo_attr'] ) && $taxonomy['woo_attr'] == 'yes' && !empty( $_POST[$taxonomy['name']] ) ) {
                                        $woo_attr[sanitize_title( $taxonomy['name'] )] = $this->woo_attribute( $taxonomy );
                                    }

                                }
                            } // hierarchical
                        } // is text
                    } // is object tax
                } // isset tax
            }

            // if a woocommerce attribute
            if ( $woo_attr ) {
                update_post_meta( $post_id, '_product_attributes', $woo_attr );
            }

        }

        //used to add code to run when the post is going to draft
        do_action( 'wpuf_draft_post_after_insert', $post_id, $form_id, $form_settings, $form_vars );


        wpuf_clear_buffer();

        echo json_encode( array(
            'post_id'        => $post_id,
            'action'         => $_POST['action'],
            'date'           => current_time( 'mysql' ),
            'post_author'    => get_current_user_id(),
            'comment_status' => get_option( 'default_comment_status' ),
            'url'            => add_query_arg( 'preview', 'true', get_permalink( $post_id ) )
        ) );

        exit;
    }

    public static function update_post_meta( $meta_vars, $post_id ) {

        // prepare the meta vars
        list( $meta_key_value, $multi_repeated, $files ) = self::prepare_meta_fields( $meta_vars );

        // set featured image if there's any
        if ( isset( $_POST['wpuf_files']['featured_image'] ) ) {
            $attachment_id = $_POST['wpuf_files']['featured_image'][0];

            wpuf_associate_attachment( $attachment_id, $post_id );
            set_post_thumbnail( $post_id, $attachment_id );

            $file_data = isset( $_POST['wpuf_files_data'][$attachment_id] ) ? $_POST['wpuf_files_data'][$attachment_id] : false;
            if ( $file_data ) {
                $args = array(
                    'ID'           => $attachment_id,
                    'post_title'   => $file_data['title'],
                    'post_content' => $file_data['desc'],
                    'post_excerpt' => $file_data['caption'],
                );
                wpuf_update_post( $args );

                update_post_meta( $attachment_id, '_wp_attachment_image_alt', $file_data['title'] );
            }
        }

        // save all custom fields
        foreach ( $meta_key_value as $meta_key => $meta_value ) {
            update_post_meta( $post_id, $meta_key, $meta_value );
        }

        // save any multicolumn repeatable fields
        foreach ( $multi_repeated as $repeat_key => $repeat_value ) {
            // first, delete any previous repeatable fields
            delete_post_meta( $post_id, $repeat_key );

            // now add them
            foreach ( $repeat_value as $repeat_field ) {
                add_post_meta( $post_id, $repeat_key, $repeat_field );
            }
        }

        // save any files attached
        foreach ( $files as $file_input ) {
            // delete any previous value
            delete_post_meta( $post_id, $file_input['name'] );

            //to track how many files are being uploaded
            $file_numbers = 0;

            foreach ( $file_input['value'] as $attachment_id ) {

                //if file numbers are greated than allowed number, prevent it from being uploaded
                if ( $file_numbers >= $file_input['count'] ) {
                    wp_delete_attachment( $attachment_id );
                    continue;
                }

                wpuf_associate_attachment( $attachment_id, $post_id );
                add_post_meta( $post_id, $file_input['name'], $attachment_id );

                // file title, caption, desc update
                $file_data = isset( $_POST['wpuf_files_data'][$attachment_id] ) ? $_POST['wpuf_files_data'][$attachment_id] : false;
                if ( $file_data ) {
                    $args = array(
                        'ID'           => $attachment_id,
                        'post_title'   => $file_data['title'],
                        'post_content' => $file_data['desc'],
                        'post_excerpt' => $file_data['caption'],
                    );
                    wpuf_update_post( $args );

                    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $file_data['title'] );
                }
                $file_numbers++;
            }
        }
    }

    function prepare_mail_body( $content, $user_id, $post_id ) {
        $user = get_user_by( 'id', $user_id );
        $post = get_post( $post_id );

        $post_field_search = array( '%post_title%', '%post_content%', '%post_excerpt%', '%tags%', '%category%',
            '%author%', '%author_email%', '%author_bio%', '%sitename%', '%siteurl%', '%permalink%', '%editlink%' );

        $post_field_replace = array(
            $post->post_title,
            $post->post_content,
            $post->post_excerpt,
            get_the_term_list( $post_id, 'post_tag', '', ', ' ),
            get_the_term_list( $post_id, 'category', '', ', ' ),
            $user->display_name,
            $user->user_email,
            ($user->description) ? $user->description : 'not available',
            get_bloginfo( 'name' ),
            home_url(),
            get_permalink( $post_id ),
            admin_url( 'post.php?action=edit&post=' . $post_id )
        );

        $content = str_replace( $post_field_search, $post_field_replace, $content );

        // custom fields
        preg_match_all( '/%custom_([\w-]*)\b%/', $content, $matches );
        list( $search, $replace ) = $matches;

        if ( $replace ) {
            foreach ( $replace as $index => $meta_key ) {
                $value     = get_post_meta( $post_id, $meta_key );
                $new_value = implode( '; ', $value );

                if ( get_post_mime_type( (int) $new_value ) ) {
                    $original_value = wp_get_attachment_url( $new_value );
                } else {
                    $original_value = $new_value;
                }

                $content = str_replace( $search[$index], $original_value, $content );
            }
        }

        return $content;
    }

    function woo_attribute( $taxonomy ) {
        return array(
            'name'         => $taxonomy['name'],
            'value'        => $_POST[$taxonomy['name']],
            'is_visible'   => $taxonomy['woo_attr_vis'] == 'yes' ? 1 : 0,
            'is_variation' => 0,
            'is_taxonomy'  => 1
        );
    }

}
