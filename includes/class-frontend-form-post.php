<?php

class WPUF_Frontend_Form extends WPUF_Frontend_Render_Form{



    private static $_instance;
    private $post_expiration_date    = 'wpuf-post_expiration_date';
    private $expired_post_status     = 'wpuf-expired_post_status';
    private $post_expiration_message = 'wpuf-post_expiration_message';

	public function __construct() {
        add_shortcode( 'wpuf_form', array( $this, 'add_post_shortcode'));
        add_shortcode( 'wpuf_edit', array( $this, 'edit_post_shortcode' ) );
        // ajax requests
        add_action( 'wp_ajax_wpuf_form_preview', array( $this, 'preview_form' ) );
        add_action( 'wp_ajax_wpuf_submit_post', array( $this, 'submit_post' ) );
        add_action( 'wp_ajax_nopriv_wpuf_submit_post', array( $this, 'submit_post' ) );
        add_action( 'wp_ajax_make_media_embed_code', array( $this, 'make_media_embed_code' ) );
        add_action( 'wp_ajax_nopriv_make_media_embed_code', array( $this, 'make_media_embed_code' ) );
        // draft
        add_action( 'wp_ajax_wpuf_draft_post', array( $this, 'draft_post' ) );
        // form preview
        add_action( 'wp_ajax_wpuf_form_preview', array( $this, 'preview_form' ) );
        $this->set_wp_post_types();
    }

    /**
     * Edit post shortcode handler
     *
     * @param array $atts
     * @return
     **/
    function edit_post_shortcode( $atts ) {
        add_filter( 'wpuf-form-fields', array( $this, 'add_field_settings'));
        extract( shortcode_atts( array( 'id' => 0 ), $atts ) );
        ob_start();
        global $userdata;
        ob_start();
        if ( !is_user_logged_in() ) {
            echo '<div class="wpuf-message">' . __( 'You are not logged in', 'wp-user-frontend' ) . '</div>';
            wp_login_form();
            return;
        }
        $post_id = isset( $_GET['pid'] ) ? intval( $_GET['pid'] ) : 0;
        if ( !$post_id ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wp-user-frontend' ) . '</div>';
        }

        $edit_post_lock      = get_post_meta( $post_id, '_wpuf_lock_editing_post', true );
        $edit_post_lock_time = get_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', true );

        if ( $edit_post_lock == 'yes' ) {
            return '<div class="wpuf-info">' . apply_filters( 'wpuf_edit_post_lock_user_notice', __( 'Your edit access for this post has been locked by an administrator.', 'wp-user-frontend' ) ) . '</div>';
        }

        if ( !empty( $edit_post_lock_time ) &&  $edit_post_lock_time < time() ) {
            return '<div class="wpuf-info">' . apply_filters( 'wpuf_edit_post_lock_expire_notice', __( 'Your allocated time for editing this post has been expired.', 'wp-user-frontend' ) ) . '</div>';
        }

        if ( wpuf_get_user()->edit_post_locked() ) {
            if ( wpuf_get_user()->edit_post_lock_reason() ) {
                return '<div class="wpuf-info">' . wpuf_get_user()->edit_post_lock_reason() . '</div>';
            }
            return '<div class="wpuf-info">' . apply_filters( 'wpuf_user_edit_post_lock_notice', __( 'Your post edit access has been locked by an administrator.', 'wp-user-frontend' ) ) . '</div>';
        }

        //is editing enabled?
        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) != 'yes' ) {
            return '<div class="wpuf-info">' . __( 'Post Editing is disabled', 'wp-user-frontend' ) . '</div>';
        }

        $curpost = get_post( $post_id );

        if ( !$curpost ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wp-user-frontend' );
        }

        // has permission?
        if ( !current_user_can( 'delete_others_posts' ) && ( $userdata->ID != $curpost->post_author ) ) {
            return '<div class="wpuf-info">' . __( 'You are not allowed to edit', 'wp-user-frontend' ) . '</div>';
        }

        $form_id       = get_post_meta( $post_id, self::$config_id, true );

        // fallback to default form
        if ( !$form_id ) {
            $form_id = wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting' );
        }

        if ( !$form_id ) {
            return '<div class="wpuf-info">' . __( "I don't know how to edit this post, I don't have the form ID", 'wp-user-frontend' ) . '</div>';
        }

        $form = new WPUF_Form( $form_id );

        $this->form_fields      = $form->get_fields();
        // $form_settings = wpuf_get_form_settings( $form_id );
        $this->form_settings    = $form->get_settings();

        $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );

        if ( $curpost->post_status == 'pending' && $disable_pending_edit == 'on' ) {
            return '<div class="wpuf-info">' . __( 'You can\'t edit a post while in pending mode.', 'wp-user-frontend' );
        }

        if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'post_updated' ) {
            echo '<div class="wpuf-success">';
            echo str_replace( '%link%', get_permalink( $post_id ), $this->form_settings['update_message'] );
            echo '</div>';
        }

        $this->render_form( $form_id,$post_id,$atts,$form);

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }



    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self;
        }
        return self::$_instance;
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

    /**
     * Draft Post
     */
    public function draft_post() {

        check_ajax_referer( 'wpuf_form_add' );
        add_filter( 'wpuf-form-fields', array( $this, 'add_field_settings'));
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

        $form_id             = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : 0;
        $form                = new WPUF_Form( $form_id );
        $this->form_settings = $form->get_settings();
        $this->form_fields   = $form->get_fields();
        $pay_per_post        = $form->is_enabled_pay_per_post();

        list( $post_vars, $taxonomy_vars, $meta_vars ) =$this->get_input_fields($this->form_fields);

        $entry_fields = $form->prepare_entries();
        $post_content = isset( $_POST[ 'post_content' ] ) ? $_POST[ 'post_content' ] : '';

        $postarr = array(
            'post_type'    => $this->form_settings['post_type'],
            'post_status'  => wpuf_get_draft_post_status( $this->form_settings ),
            'post_author'  => get_current_user_id(),
            'post_title'   => isset( $_POST['post_title'] ) ? trim( $_POST['post_title'] ) : '',
            'post_content' => $post_content,
            'post_excerpt' => isset( $_POST['post_excerpt'] ) ? trim( $_POST['post_excerpt'] ) : '',
        );

        if ( isset( $_POST['category'] ) && ( $_POST['category'] !='' && $_POST['category'] !='0' && $_POST['category'][0] !='-1' ) ) {
            $category                 = $_POST['category'];
            $postarr['post_category'] = is_array( $category ) ? $category : array( $category );
        }

        // set default post category if it's not been set yet and if post type supports
        if ( !isset( $postarr['post_category'] ) && isset( $this->form_settings['default_cat'] ) && is_object_in_taxonomy( $this->form_settings['post_type'], 'category' ) ) {
            if ( is_array( $this->form_settings['default_cat'] ) ) {
                $postarr['post_category'] = $this->form_settings['default_cat'];
            } else {
                $postarr['post_category'] = array( $this->form_settings['default_cat'] );
            }
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

        // add post revision when post edit from the frontend
        wpuf_frontend_post_revision( $post_id, $this->form_settings );

        if ( $post_id ) {

            self::update_post_meta( $meta_vars, $post_id );

            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );

            // save post formats if have any
            if ( isset( $this->form_settings['post_format'] ) && $this->form_settings['post_format'] != '0' ) {
                if ( post_type_supports( $this->form_settings['post_type'], 'post-formats' ) ) {
                    set_post_format( $post_id, $this->form_settings['post_format'] );
                }
            }

            // if pay per post is enabled then update payment status as pending
            if ( $pay_per_post ) {
                update_post_meta ( $post_id, '_wpuf_payment_status', 'pending' );
            }

            if(!empty($taxonomy_vars)) {
                $this->set_custom_taxonomy($post_id,$taxonomy_vars);
            }
        }

            //used to add code to run when the post is going to draft
        do_action( 'wpuf_draft_post_after_insert', $post_id, $form_id, $this->form_settings, $this->form_fields );

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


    /**
     * New/Edit post submit handler
     *
     * @return void
     */
    function submit_post() {

        check_ajax_referer( 'wpuf_form_add' );
        add_filter( 'wpuf-form-fields', array( $this, 'add_field_settings'));
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

        $form_id               = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : 0;
        $form                  = new WPUF_Form( $form_id );
        $this->form_settings   = $form->get_settings();
        $this->form_fields     = $form->get_fields();
        $guest_mode            = isset( $this->form_settings['guest_post'] ) ? $this->form_settings['guest_post'] : '';
        $guest_verify          = isset( $this->form_settings['guest_email_verify'] ) ? $this->form_settings['guest_email_verify'] : 'false' ;
        $attachments_to_delete = isset( $_POST['delete_attachments'] ) ? $_POST['delete_attachments'] : array();
        foreach ( $attachments_to_delete as $attach_id ) {
            wp_delete_attachment( $attach_id, true );
        }
        list( $post_vars, $taxonomy_vars, $meta_vars ) =$this->get_input_fields($this->form_fields);
        if ( !isset( $_POST['post_id'] ) ) {
            $has_limit    = ( isset( $this->form_settings['limit_entries'] ) && $this->form_settings['limit_entries'] == 'true' ) ? true : false;
            if ( $has_limit ) {
                $limit        = (int) !empty( $this->form_settings['limit_number'] ) ? $this->form_settings['limit_number'] : 0;
                $form_entries = wpuf_form_posts_count( $form_id );
                if ( $limit && $limit <= $form_entries ) {
                    $this->send_error( $this->form_settings['limit_message'] );
                }
            }
            $this->on_edit_no_check_recaptcha( $post_vars );
        }

        $is_update           = false;
        $post_author         = null;
        $default_post_author = wpuf_get_option( 'default_post_owner', 'wpuf_frontend_posting', 1 );
        $post_author = $this->wpuf_get_post_user();
        $postarr = array(
            'post_type'    => $this->form_settings['post_type'],
            'post_status'  => isset( $this->form_settings['post_status'] ) ? $this->form_settings['post_status'] : 'publish',
            'post_author'  => $post_author,
            'post_title'   => isset( $_POST['post_title'] ) ? trim( $_POST['post_title'] ) : '',
            'post_content' => isset( $_POST['post_content'] ) ? trim( $_POST['post_content'] ) : '',
            'post_excerpt' => isset( $_POST['post_excerpt'] ) ? trim( $_POST['post_excerpt'] ) : '',
        );
        // $charging_enabled = wpuf_get_option( 'charge_posting', 'wpuf_payment' );
        $charging_enabled = '';
        $form             = new WPUF_Form( $form_id );
        $payment_options  = $form->is_charging_enabled();
        $ppp_cost_enabled = $form->is_enabled_pay_per_post();
        $current_user     = wpuf_get_user();
        if ( !$payment_options ) {
            $charging_enabled = 'no';
        } else {
            $charging_enabled = 'yes';
        }
        if ( $guest_mode == 'true' && $guest_verify == 'true' && !is_user_logged_in() && $charging_enabled == 'yes' ) {
            $postarr['post_status'] = wpuf_get_draft_post_status( $this->form_settings );
        } elseif ( $guest_mode == 'true' && $guest_verify == 'true' && !is_user_logged_in() ) {
            $postarr['post_status'] = 'draft';
        }
        //if date is set and assigned as publish date
        if ( isset( $_POST['wpuf_is_publish_time'] ) ) {
            if ( isset( $_POST[$_POST['wpuf_is_publish_time']] ) && !empty( $_POST[$_POST['wpuf_is_publish_time']] ) ) {
                // $postarr['post_date'] = date( 'Y-m-d H:i:s', strtotime( str_replace( array( ':', '/' ), '-', $_POST[$_POST['wpuf_is_publish_time']] ) ) );
                $date_time = explode(" ", $_POST[$_POST['wpuf_is_publish_time']] );
                if ( !empty ( $date_time[0] ) ) {
                    $timestamp = strtotime( str_replace( array( '/' ), '-', $date_time[0] ) );
                }
                if ( !empty ( $date_time[1] ) ) {
                    $time       = explode(':', $date_time[1] );
                    $seconds    = ( $time[0] * 60 * 60 ) + ($time[1] * 60);
                    $timestamp  = $timestamp + $seconds;
                }
                $postarr['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
            }
        }

        if ( isset( $_POST['category'] ) && ( $_POST['category'] !='' && $_POST['category'] !='0' && $_POST['category'][0] !='-1' ) ) {
            $category                 = $_POST['category'];
            if ( !is_array( $category ) && is_string( $category ) ) {
                $category_strings = explode( ',', $category );
                $cat_ids = array();
                foreach ( $category_strings as $key => $each_cat_string ) {
                    $cat_ids[]                = get_cat_ID( trim( $each_cat_string ) );
                    $postarr['post_category'] = $cat_ids;
                }
            } else {
                $postarr['post_category'] =$category;
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
            if ( $this->form_settings['edit_post_status'] == '_nochange' ) {
                $postarr['post_status'] = get_post_field( 'post_status', $_POST['post_id'] );
            } else {
                $postarr['post_status'] = $this->form_settings['edit_post_status'];
            }
        } else {
            if ( isset( $this->form_settings['comment_status'] ) ) {
                $postarr['comment_status'] = $this->form_settings['comment_status'];
            }
        }
        // check the form status, it might be already a draft
        // in that case, it already has the post_id field
        // so, WPUF's add post action/filters won't work for new posts
        if ( isset( $_POST['wpuf_form_status'] ) && $_POST['wpuf_form_status'] == 'new' ) {
            $is_update = false;
        }

        // set default post category if it's not been set yet and if post type supports
        if ( !isset( $postarr['post_category'] ) && isset( $this->form_settings['default_cat'] ) && is_object_in_taxonomy( $this->form_settings['post_type'], 'category' ) ) {
            if ( is_array( $this->form_settings['default_cat'] ) ) {
                $postarr['post_category'] = $this->form_settings['default_cat'];
            } else {
                $postarr['post_category'] = array( $this->form_settings['default_cat'] );
            }
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
            $postarr = apply_filters( 'wpuf_update_post_args', $postarr, $form_id, $this->form_settings, $this->form_fields );
        } else {
            $postarr = apply_filters( 'wpuf_add_post_args', $postarr, $form_id, $this->form_settings, $this->form_fields );
        }

        $post_id = wp_insert_post( $postarr, $wp_error = false );

        // add post revision when post edit from the frontend
        wpuf_frontend_post_revision( $post_id, $this->form_settings );

        // add _wpuf_lock_editing_post_time meta to
        // lock user from editing the published post after a certain time
        if ( !$is_update ) {
            $lock_edit_post = isset( $this->form_settings['lock_edit_post'] ) ? floatval( $this->form_settings['lock_edit_post'] ) : 0;

            if ( $post_id && $lock_edit_post > 0 ) {
                $lock_edit_post_time    = time() + ( $lock_edit_post * 60 * 60 );
                update_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', $lock_edit_post_time );
            }
        }

       if ( $post_id ) {

            self::update_post_meta( $meta_vars, $post_id );
            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );
            // if user has a subscription pack
            $this->wpuf_user_subscription_pack($this->form_settings);
            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );

            // save post formats if have any
            if ( isset( $this->form_settings['post_format'] ) && $this->form_settings['post_format'] != '0' ) {
                if ( post_type_supports( $this->form_settings['post_type'], 'post-formats' ) ) {
                    set_post_format( $post_id, $this->form_settings['post_format'] );
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
            if(!empty($taxonomy_vars)) {
                $this->set_custom_taxonomy($post_id,$taxonomy_vars);
            }
            $response = $this->send_mail_for_guest($charging_enabled,$post_id,$form_id,$is_update,$post_author,$meta_vars);
            wpuf_clear_buffer();
            wp_send_json( $response );

        }
        $this->send_error( __( 'Something went wrong', 'wp-user-frontend' ) );
    }


    public function wpuf_get_post_user() {

        if ( !is_user_logged_in() ) {

            if ( isset( $this->form_settings['guest_post'] ) && $this->form_settings['guest_post'] == 'true' && $this->form_settings['guest_details'] == 'true' ) {

                $guest_name  = trim( $_POST['guest_name'] );

                $guest_email = trim( $_POST['guest_email'] );

                // is valid email?
                if ( !is_email( $guest_email ) ) {
                    $this->send_error( __( 'Invalid email address.', 'wp-user-frontend' ) );
                }

                // check if the user email already exists
                $user = get_user_by( 'email', $guest_email );

                if ( $user ) {
                    // $post_author = $user->ID;
                    wp_send_json( array(
                        'success'     => false,
                        'error'       => __( "You already have an account in our site. Please login to continue.\n\nClicking 'OK' will redirect you to the login page and you will lose the form data.\nClick 'Cancel' to stay at this page.", 'wp-user-frontend' ),
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
            } elseif ( isset( $this->form_settings['guest_post'] ) && $this->form_settings['guest_post'] == 'true' && $this->form_settings['guest_details'] == 'false' ) {
                $post_author = $default_post_author;
            } elseif ( isset( $this->form_settings['guest_post'] ) && $this->form_settings['guest_post'] != 'true' ) {
                $this->send_error( $this->form_settings['message_restrict'] );
            }

            // the user must be logged in already
        } elseif ( isset( $this->form_settings['role_base'] ) && $this->form_settings['role_base'] == 'true' ) {

            $current_user = wp_get_current_user();

            if ( !in_array( $current_user->roles[0], $this->form_settings['roles'] ) ) {
                $this->send_error( __( 'You do not have sufficient permissions to access this form.', 'wp-user-frontend' ) );
            }

        } else {
            $post_author = get_current_user_id();
        }


        return $post_author;
    }

    /**w
     * Add post shortcode handler
     *
     * @param array $atts
     * @return string
    */

    function add_post_shortcode( $atts ) {
        add_filter( 'wpuf-form-fields', array( $this, 'add_field_settings'));
        extract( shortcode_atts( array( 'id' => 0 ), $atts ) );
        ob_start();
        $form                      = new WPUF_Form( $id );
        $this->form_fields         = $form->get_fields();
        $this->form_settings       = $form->get_settings();
        list($user_can_post,$info) = $form->is_submission_open($form ,$this->form_settings);
        $info                      = apply_filters( 'wpuf_addpost_notice', $info, $id, $this->form_settings );
        $user_can_post             = apply_filters( 'wpuf_can_post', $user_can_post, $id, $this->form_settings );
        if ( $user_can_post == 'yes' ) {
            $this->render_form( $id,null,$atts,$form);
        } else {
            echo '<div class="wpuf-info">' . $info . '</div>';
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
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

            if ( count( $file_input['value'] ) > 1  ) {
                $image_ids = maybe_serialize( $file_input['value'] );
            } else {
                $image_ids = $file_input['value'][0];
            }

            add_post_meta( $post_id, $file_input['name'], $image_ids );

            //to track how many files are being uploaded
            $file_numbers = 0;

            foreach ( $file_input['value'] as $attachment_id ) {

                //if file numbers are greated than allowed number, prevent it from being uploaded
                if ( $file_numbers >= $file_input['count'] ) {
                    wp_delete_attachment( $attachment_id );
                    continue;
                }

                wpuf_associate_attachment( $attachment_id, $post_id );
                //add_post_meta( $post_id, $file_input['name'], $attachment_id );

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
                $value     = get_post_meta( $post_id, $meta_key, false );
                $new_value = implode( '; ', $value );

                $original_value = '';
                $meta_val = '';
                if ( count( $value ) > 1 ) {
                    $isFirst = true;
                    foreach ($value as $val) {
                        if ( $isFirst ) {
                            if ( get_post_mime_type( (int) $val ) ) {
                                $meta_val = wp_get_attachment_url( $val );
                            } else {
                                $meta_val = $val;
                            }
                            $isFirst = false;
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
                    $original_value = $original_value . $meta_val ;
                } else {
                    if ( get_post_mime_type( (int) $new_value ) ) {
                        $original_value = wp_get_attachment_url( $new_value );
                    } else {
                        $original_value = $new_value;
                    }
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

    /**
     * Hook to publish verified guest post with payment
     *
     * @since 2.5.8
     */

    function publish_guest_post () {

        if ( isset($_GET['post_msg']) && $_GET['post_msg'] == 'verified' ) {

            $response       = array();
            $post_id        = wpuf_decryption( $_GET['p_id'] );
            $form_id        = wpuf_decryption( $_GET['f_id'] );
            $form_settings  = wpuf_get_form_settings( $form_id );
            $post_author_id = get_post_field( 'post_author', $post_id );
            $payment_status = new WPUF_Subscription();
            $form           = new WPUF_Form( $form_id );
            $pay_per_post   = $form->is_enabled_pay_per_post();
            $force_pack     = $form->is_enabled_force_pack();

            if ( $form->is_charging_enabled() && $pay_per_post ) {
                if ( ($payment_status->get_payment_status( $post_id ) ) == 'pending') {

                    $response['show_message'] = true;
                    $response['redirect_to'] = add_query_arg( array(
                        'action'  => 'wpuf_pay',
                        'type'    => 'post',
                        'post_id' => $post_id
                    ), get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) ) );

                    wp_redirect( $response['redirect_to'] );
                    wpuf_clear_buffer();
                    wpuf_send_json( $response );
                }
            } else {
                $p_status = get_post_status( $post_id );
                if ( $p_status ) {
                    wp_update_post(array(
                        'ID'            =>  $post_id,
                        'post_status'   =>  isset( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'publish'
                    ));
                    echo "<div class='wpuf-success' style='text-align:center'>" . __( 'Email successfully verified. Please Login.', 'wp-user-frontend' ) ."</div>";
                }

            }
        }
    }

    function wpuf_user_subscription_pack($form_settings) {

        // if user has a subscription pack
        $user_wpuf_subscription_pack = get_user_meta( get_current_user_id(), '_wpuf_subscription_pack', true );

        if ( !empty( $user_wpuf_subscription_pack ) && isset( $user_wpuf_subscription_pack['_enable_post_expiration'] )
            && isset( $user_wpuf_subscription_pack['expire'] ) && strtotime( $user_wpuf_subscription_pack['expire'] ) >= time() ) {

            $expire_date = date( 'Y-m-d', strtotime( "+" . $user_wpuf_subscription_pack['_post_expiration_time'] ) );
            update_post_meta( $post_id, $this->post_expiration_date, $expire_date );
            // save post status after expiration
            $expired_post_status = $user_wpuf_subscription_pack['_expired_post_status'];
            update_post_meta( $post_id, $this->expired_post_status, $expired_post_status );
                // if mail active
            if ( isset( $user_wpuf_subscription_pack['_enable_mail_after_expired'] ) && $user_wpuf_subscription_pack['_enable_mail_after_expired'] == 'on' ) {
                    $wpuf_user  = wpuf_get_user();
                    $user_subscription = new WPUF_User_Subscription( $wpuf_user );
                    $post_expiration_message = $user_subscription->get_subscription_exp_msg( $user_wpuf_subscription_pack['pack_id'] );
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
        } elseif ( empty( $user_wpuf_subscription_pack ) || $user_wpuf_subscription_pack == 'Cancel' || $user_wpuf_subscription_pack == 'cancel' ) {
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
    }


    public function send_mail_for_guest($charging_enabled,$post_id,$form_id,$is_update,$post_author,$meta_vars) {

            $show_message = false;
            $redirect_to  = false;
            $response = array();
            if ( $is_update ) {
                if ( $this->form_settings['edit_redirect_to'] == 'page' ) {
                    $redirect_to = get_permalink( $this->form_settings['edit_page_id'] );
                } elseif ( $this->form_settings['edit_redirect_to'] == 'url' ) {
                    $redirect_to = $this->form_settings['edit_url'];
                } elseif ( $this->form_settings['edit_redirect_to'] == 'same' ) {
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
                if ( $this->form_settings['redirect_to'] == 'page' ) {
                    $redirect_to = get_permalink( $this->form_settings['page_id'] );
                } elseif ( $this->form_settings['redirect_to'] == 'url' ) {
                    $redirect_to = $this->form_settings['url'];
                } elseif ( $this->form_settings['redirect_to'] == 'same' ) {
                    $show_message = true;
                } else {
                    $redirect_to = get_permalink( $post_id );
                }
            }
            $response = array(
                'success'      => true,
                'redirect_to'  => $redirect_to,
                'show_message' => $show_message,
                'message'      => $this->form_settings['message']
            );
            global $wp;
            $guest_mode = isset( $this->form_settings['guest_post'] ) ? $this->form_settings['guest_post'] : '';
            $guest_verify = isset( $this->form_settings['guest_email_verify'] ) ? $this->form_settings['guest_email_verify'] : 'false' ;
            if ( $guest_mode == 'true' && $guest_verify == 'true' && !is_user_logged_in()  && $charging_enabled != 'yes') {
                $post_id_encoded          = wpuf_encryption( $post_id ) ;
                $form_id_encoded          = wpuf_encryption( $form_id ) ;
                wpuf_send_mail_to_guest ( $post_id_encoded, $form_id_encoded, 'no', 1 );
                $response['show_message'] = true;
                $response['redirect_to']  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
                $response['message']      = __( 'Thank you for posting on our site. We have sent you an confirmation email. Please check your inbox!', 'wp-user-frontend' );
            } elseif ( $guest_mode == 'true' && $guest_verify == 'true' && !is_user_logged_in() && $charging_enabled == 'yes' ) {
                $post_id_encoded          = wpuf_encryption( $post_id ) ;
                $form_id_encoded          = wpuf_encryption( $form_id ) ;
                $response['show_message'] = true;
                $response['redirect_to']  = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
                $response['message']      = __( 'Thank you for posting on our site. We have sent you an confirmation email. Please check your inbox!', 'wp-user-frontend' );
                update_post_meta ( $post_id, '_wpuf_payment_status', 'pending' );
                wpuf_send_mail_to_guest ( $post_id_encoded, $form_id_encoded, 'yes', 2 );
            }
            if ( $guest_mode == 'true' && $guest_verify == 'true' && !is_user_logged_in() ) {
                $response = apply_filters( 'wpuf_edit_post_redirect', $response, $post_id, $form_id, $this->form_settings );
            } elseif ( $is_update ) {
                //send mail notification
                if ( isset( $this->form_settings['notification'] ) && $this->form_settings['notification']['edit'] == 'on' ) {
                    $mail_body = $this->prepare_mail_body( $this->form_settings['notification']['edit_body'], $post_author, $post_id );
                    $to        = $this->prepare_mail_body( $this->form_settings['notification']['edit_to'], $post_author, $post_id );
                    $subject   = $this->prepare_mail_body( $this->form_settings['notification']['edit_subject'], $post_author, $post_id );
                    $subject   = wp_strip_all_tags( $subject );
                    $headers   = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail( $to, $subject, $mail_body, $headers );
                }
                //now redirect the user
                $response = apply_filters( 'wpuf_edit_post_redirect', $response, $post_id, $form_id, $this->form_settings );
                //now perform some post related actions
                do_action( 'wpuf_edit_post_after_update', $post_id, $form_id, $this->form_settings, $this->form_fields ); // plugin API to extend the functionality
            } else {
                // send mail notification
                if ( isset( $this->form_settings['notification'] ) && $this->form_settings['notification']['new'] == 'on' ) {
                    $mail_body = $this->prepare_mail_body( $this->form_settings['notification']['new_body'], $post_author, $post_id );
                    $to        = $this->prepare_mail_body( $this->form_settings['notification']['new_to'], $post_author, $post_id );
                    $subject   = $this->prepare_mail_body( $this->form_settings['notification']['new_subject'], $post_author, $post_id );
                    $subject   = wp_strip_all_tags( $subject );
                    wp_mail( $to, $subject, $mail_body );
                }
                //redirect the user
                $response = apply_filters( 'wpuf_add_post_redirect', $response, $post_id, $form_id, $this->form_settings );
                //now perform some post related actions
                do_action( 'wpuf_add_post_after_insert', $post_id, $form_id, $this->form_settings, $meta_vars ); // plugin API to extend the functionality
            }

            return $response;
    }

}
