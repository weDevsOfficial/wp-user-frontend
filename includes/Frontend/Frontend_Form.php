<?php

namespace WeDevs\Wpuf\Frontend;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Frontend_Render_Form;
use WeDevs\Wpuf\Traits\FieldableTrait;
use WP_User;

class Frontend_Form extends Frontend_Render_Form {
    use FieldableTrait;

    public static $config_id = '_wpuf_form_id';

    public function __construct() {
        // // guest post hook
        add_action( 'init', [ $this, 'publish_guest_post' ] );
        // notification and other tasks after the guest verified the email
        add_action( 'wpuf_guest_post_email_verified', [ $this, 'send_mail_to_admin_after_guest_mail_verified' ] );

        $this->set_wp_post_types();

        // Enable post edit link for post authors in frontend
        if ( ! is_admin() ) {
            add_filter( 'user_has_cap', [ $this, 'map_capabilities_for_post_authors' ], 10, 4 );
            add_filter( 'get_edit_post_link', [ $this, 'get_edit_post_link' ], 10, 3 );
        }
    }

    /**
     * Edit post shortcode handler
     *
     * @param array $atts
     *
     * @return false|string
     */
    public function edit_post_shortcode( $atts ) {
        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );
        // @codingStandardsIgnoreStart
        extract( shortcode_atts( [ 'id' => 0 ], $atts ) );

        // @codingStandardsIgnoreEnd
        ob_start();

        global $userdata;

        ob_start();

        if ( ! is_user_logged_in() ) {
            echo wp_kses_post( '<div class="wpuf-message">' . __( 'You are not logged in', 'wp-user-frontend' ) . '</div>' ),

            wp_login_form();

            return '';
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf_edit' ) ) {
            return '<div class="wpuf-info">' . __( 'Please re-open the post', 'wp-user-frontend' ) . '</div>';
        }

        $post_id = isset( $_GET['pid'] ) ? intval( wp_unslash( $_GET['pid'] ) ) : 0;

        if ( ! $post_id ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wp-user-frontend' ) . '</div>';
        }

        $edit_post_lock      = get_post_meta( $post_id, '_wpuf_lock_editing_post', true );
        $edit_post_lock_time = get_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', true );

        if ( $edit_post_lock === 'yes' ) {
            return '<div class="wpuf-info">' . apply_filters( 'wpuf_edit_post_lock_user_notice', __( 'Your edit access for this post has been locked by an administrator.', 'wp-user-frontend' ) ) . '</div>';
        }

        if ( ! empty( $edit_post_lock_time ) && $edit_post_lock_time < time() ) {
            return '<div class="wpuf-info">' . apply_filters( 'wpuf_edit_post_lock_expire_notice', __( 'Your allocated time for editing this post has been expired.', 'wp-user-frontend' ) ) . '</div>';
        }

        if ( wpuf_get_user()->edit_post_locked() ) {
            if ( wpuf_get_user()->edit_post_lock_reason() ) {
                return '<div class="wpuf-info">' . wpuf_get_user()->edit_post_lock_reason() . '</div>';
            }

            return '<div class="wpuf-info">' . apply_filters( 'wpuf_user_edit_post_lock_notice', __( 'Your post edit access has been locked by an administrator.', 'wp-user-frontend' ) ) . '</div>';
        }

        //is editing enabled?
        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) !== 'yes' ) {
            return '<div class="wpuf-info">' . __( 'Post Editing is disabled', 'wp-user-frontend' ) . '</div>';
        }

        $curpost = get_post( $post_id );

        if ( ! $curpost ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wp-user-frontend' );
        }

        // has permission?
        if ( ! current_user_can( 'delete_others_posts' ) && ( $userdata->ID !== (int) $curpost->post_author ) ) {
            return '<div class="wpuf-info">' . __( 'You are not allowed to edit', 'wp-user-frontend' ) . '</div>';
        }

        $form_id = get_post_meta( $post_id, self::$config_id, true );

        // fallback to default form
        if ( ! $form_id ) {
            $form_id = wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting' );
        }

        if ( ! $form_id ) {
            return '<div class="wpuf-info">' . __( "I don't know how to edit this post, I don't have the form ID", 'wp-user-frontend' ) . '</div>';
        }

        $form = new Form( $form_id );

        $this->form_fields = $form->get_fields();
        $this->form_settings = $form->get_settings();

        $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );
        $disable_publish_edit = wpuf_get_option( 'disable_publish_edit', 'wpuf_dashboard', 'off' );

        if ( 'pending' === $curpost->post_status && 'on' === $disable_pending_edit ) {
            return '<div class="wpuf-info">' . __( 'You can\'t edit a post while in pending mode.', 'wp-user-frontend' );
        }

        if ( 'publish' === $curpost->post_status && 'off' !== $disable_publish_edit ) {
            return '<div class="wpuf-info">' . __( 'You\'re not allowed to edit this post.', 'wp-user-frontend' );
        }

        $msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';

        if ( $msg === 'post_updated' ) {
            echo wp_kses_post( '<div class="wpuf-success">' );
            echo wp_kses_post( str_replace( '{link}', get_permalink( $post_id ), $this->form_settings['update_message'] ) );
            echo wp_kses_post( '</div>' );
        }

        $this->render_form( $form_id, $post_id, $atts, $form );

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }

    /**
     * This will embed media to the editor
     */
    public function make_media_embed_code() {
        $nonce = isset( $_GET['nonce'] ) ? sanitize_key( wp_unslash( $_GET['nonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-upload-nonce' ) ) {
            exit;
        }

        $content = isset( $_POST['content'] ) ? sanitize_text_field( wp_unslash( $_POST['content'] ) ) : '';
        $embed_code = wp_oembed_get( $content );

        if ( $embed_code ) {
            echo esc_html( $embed_code );
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
        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

        $form_id             = isset( $_POST['form_id'] ) ? intval( wp_unslash( $_POST['form_id'] ) ) : 0;
        $form                = new Form( $form_id );
        $this->form_settings = $form->get_settings();
        $this->form_fields   = $form->get_fields();
        $pay_per_post        = $form->is_enabled_pay_per_post();

        [ $post_vars, $taxonomy_vars, $meta_vars ] = $this->get_input_fields( $this->form_fields );

        $entry_fields = $form->prepare_entries();
        $allowed_tags = wp_kses_allowed_html( 'post' );
        $post_content = isset( $_POST['post_content'] ) ? wp_kses( wp_unslash( $_POST['post_content'] ), $allowed_tags ) : '';
        $postarr = [
            'post_type'    => $this->form_settings['post_type'],
            'post_status'  => wpuf_get_draft_post_status( $this->form_settings ),
            'post_author'  => get_current_user_id(),
            'post_title'   => isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '',
            'post_content' => $post_content,
            'post_excerpt' => isset( $_POST['post_excerpt'] ) ? wp_kses( wp_unslash( $_POST['post_excerpt'] ), $allowed_tags ) : '',
        ];

        if ( ! empty( $this->form_fields ) ) {
            foreach ( $this->form_fields as $field ) {
                if ( $field['template'] === 'taxonomy' ) {
                    $category_name = $field['name'];

                    if ( isset( $_POST[ $category_name ] ) && is_array( $_POST[ $category_name ] ) ) { // WPCS: sanitization ok.
                        $category = isset( $_POST[ $category_name ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $category_name ] ) ) : [];
                    } else {
                        $category = isset( $_POST[ $category_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $category_name ] ) ) : '';
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
                }
            }
        }

        // set default post category if it's not been set yet and if post type supports
        if ( ! isset( $postarr['post_category'] ) && isset( $this->form_settings['default_cat'] ) && is_object_in_taxonomy( $this->form_settings['post_type'], 'category' ) ) {
            if ( is_array( $this->form_settings['default_cat'] ) ) {
                $postarr['post_category'] = $this->form_settings['default_cat'];
            } else {
                $postarr['post_category'] = [ $this->form_settings['default_cat'] ];
            }
        }

        if ( isset( $_POST['tags'] ) ) {
            $postarr['tags_input'] = explode( ',', sanitize_text_field( wp_unslash( $_POST['tags'] ) ) );
        }

        // if post_id is passed, we update the post
        if ( isset( $_POST['post_id'] ) ) {
            $is_update                 = true;
            $postarr['ID']             = intval( wp_unslash( $_POST['post_id'] ) );
            $postarr['comment_status'] = 'open';
        }

        $postarr = $this->adjust_thumbnail_id( $postarr );

        $post_id = wp_insert_post( $postarr );

        // add post revision when post edit from the frontend
        wpuf_frontend_post_revision( $post_id, $this->form_settings );

        if ( $post_id ) {
            self::update_post_meta( $meta_vars, $post_id );

            // set the post form_id for later usage
            update_post_meta( $post_id, self::$config_id, $form_id );

            // save post formats if have any
            if ( isset( $this->form_settings['post_format'] ) && $this->form_settings['post_format'] !== '0' ) {
                if ( post_type_supports( $this->form_settings['post_type'], 'post-formats' ) ) {
                    set_post_format( $post_id, $this->form_settings['post_format'] );
                }
            }

            if ( ! empty( $taxonomy_vars ) ) {
                $this->set_custom_taxonomy( $post_id, $taxonomy_vars );
            } else {
                $this->set_default_taxonomy( $post_id );
            }
        }

        //used to add code to run when the post is going to draft
        do_action( 'wpuf_draft_post_after_insert', $post_id, $form_id, $this->form_settings, $this->form_fields );

        wpuf_clear_buffer();

        echo json_encode(
            [
                'post_id'        => $post_id,
                'action'         => isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '',
                'date'           => current_time( 'mysql' ),
                'post_author'    => get_current_user_id(),
                'comment_status' => get_option( 'default_comment_status' ),
                'url'            => add_query_arg( 'preview', 'true', get_permalink( $post_id ) ),
                'message'        => __( 'Post Saved', 'wp-user-frontend' ),
            ]
        );

        exit;
    }

    /**
     * Add post shortcode handler
     *
     * @param array $atts
     * @return string
    */

    public function add_post_shortcode( $atts ) {
        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );

        // @codingStandardsIgnoreStart
        extract( shortcode_atts( [ 'id' => 0 ], $atts ) );

        // @codingStandardsIgnoreEnd
        ob_start();
        $form                         = new Form( $id );
        $this->form_fields            = $form->get_fields();
        $this->form_settings          = $form->get_settings();
        $this->generate_auth_link(); // Translate tag %login% %registration% to login registartion url
        [ $user_can_post, $info ]     = $form->is_submission_open( $form, $this->form_settings );
        $info                         = apply_filters( 'wpuf_addpost_notice', $info, $id, $this->form_settings );
        $user_can_post                = apply_filters( 'wpuf_can_post', $user_can_post, $id, $this->form_settings );

        if ( $user_can_post === 'yes' ) {
            $this->render_form( $id, null, $atts, $form );
        } else {
            echo wp_kses_post( '<div class="wpuf-info">' . $info . '</div>' );
        }
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Hook to publish verified guest post with payment
     *
     * @since 2.5.8
     */
    public function publish_guest_post() {
        $post_msg = isset( $_GET['post_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['post_msg'] ) ) : '';
        $pid      = isset( $_GET['p_id'] ) ? sanitize_text_field( wp_unslash( $_GET['p_id'] ) ) : '';
        $fid      = isset( $_GET['f_id'] ) ? sanitize_text_field( wp_unslash( $_GET['f_id'] ) ) : '';

        if ( $post_msg !== 'verified' ) {
            return;
        }

        $response       = [];
        $post_id        = wpuf_decryption( $pid );
        $form_id        = wpuf_decryption( $fid );
        $form_settings  = wpuf_get_form_settings( $form_id );
        $post_author_id = get_post_field( 'post_author', $post_id );
        $payment_status = new Subscription();
        $form           = new Form( $form_id );
        $pay_per_post   = $form->is_enabled_pay_per_post();
        $force_pack     = $form->is_enabled_force_pack();

        if ( $form->is_charging_enabled() && $pay_per_post ) {
            if ( ( $payment_status->get_payment_status( $post_id ) ) === 'pending' ) {
                $response['show_message'] = true;
                $response['redirect_to']  = add_query_arg(
                    [
                        'action'  => 'wpuf_pay',
                        'type'    => 'post',
                        'post_id' => $post_id,
                    ],
                    get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) )
                );

                wp_redirect( $response['redirect_to'] );
                wpuf_clear_buffer();
                wp_send_json( $response );
            }
        } else {
            $p_status = get_post_status( $post_id );

            if ( $p_status ) {
                wp_update_post(
                    [
                        'ID'          => $post_id,
                        'post_status' => isset( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'publish',
                    ]
                );

                echo wp_kses_post( "<div class='wpuf-success' style='text-align:center'>" . __( 'Email successfully verified. Please Login.', 'wp-user-frontend' ) . '</div>' );
            }
        }

        do_action( 'wpuf_guest_post_email_verified', $post_id );
    }

    /**
     * Enable edit post link for post authors
     *
     * @since 3.4.0
     *
     * @param array    $allcaps
     * @param array    $caps
     * @param array    $args
     * @param WP_User $wp_user
     *
     * @return array
    */
    public function map_capabilities_for_post_authors( $allcaps, $caps, $args, $wp_user ) {
        if (
            empty( $args )
            || count( $args ) < 3
            || empty( $caps )
            || 'edit_post' !== $args[0]
            || isset( $allcaps[ $caps[0] ] )
        ) {
            return $allcaps;
        }

        $post_id = $args[2];
        $post    = get_post( $post_id );

        // We'll show edit link only for posts, not page, product or other post types
        if (
            empty( $post->post_type )
            || 'post' !== $post->post_type
            || ! wpuf_validate_boolean( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) )
            || ! $this->get_frontend_post_edit_link( $post_id )
            || absint( $post->post_author ) !== $wp_user->ID
        ) {
            return $allcaps;
        }

        $allcaps['edit_published_posts'] = 1;

        return $allcaps;
    }

    /**
     * Filter hook for edit post link
     *
     * @since 3.4.0
     *
     * @param string $url
     * @param int    $post_id
     *
     * @return string
    */
    public function get_edit_post_link( $url, $post_id ) {
        if (
            current_user_can( 'edit_post', $post_id )
            && ! current_user_can( 'administrator' )
            && ! current_user_can( 'editor' )
            && ! current_user_can( 'author' )
            && ! current_user_can( 'contributor' )
        ) {
            $post    = get_post( $post_id );
            $form_id = get_post_meta( $post_id, '_wpuf_form_id', true );

            if ( absint( $post->post_author ) === get_current_user_id() && $form_id ) {
                return $this->get_frontend_post_edit_link( $post_id );
            }
        }

        return $url;
    }

    /**
     * Get post edit link
     *
     * @since 3.4.0
     *
     * @param int $post_id
     *
     * @return string
     */
    public function get_frontend_post_edit_link( $post_id ) {
        $edit_page = absint( wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' ) );

        if ( ! $edit_page ) {
            return '';
        }

        $url           = add_query_arg( [ 'pid' => $post_id ], get_permalink( $edit_page ) );
        $edit_page_url = apply_filters( 'wpuf_edit_post_link', $url );

        return wp_nonce_url( $edit_page_url, 'wpuf_edit' );
    }

    /**
     * Generate login registartion link for unauth message
     */
    private function generate_auth_link() {
        if ( ! is_user_logged_in() && $this->form_settings['guest_post'] !== 'true' ) {
            $login        = wpuf()->frontend->simple_login->get_login_url();
            $register     = wpuf()->frontend->simple_login->get_registration_url();
            $replace      = [ "<a href='" . $login . "'>Login</a>", "<a href='" . $register . "'>Register</a>" ];
            $placeholders = [ '%login%', '%register%' ];

            $this->form_settings['message_restrict'] = str_replace( $placeholders, $replace, $this->form_settings['message_restrict'] );
        }
    }

    /**
     * Send a notification mail after a guest verified his/her email
     *
     * @since WPUF
     *
     * @return void
     */
    public function send_mail_to_admin_after_guest_mail_verified() {
        $post_id = ! empty( $_GET['p_id'] ) ? wpuf_decryption( sanitize_text_field( wp_unslash( $_GET['p_id'] ) ) ) : 0;
        $form_id = ! empty( $_GET['f_id'] ) ? wpuf_decryption( sanitize_text_field( wp_unslash( $_GET['f_id'] ) ) ) : 0;

        if ( empty( $post_id ) || empty( $form_id ) ) {
            return;
        }

        $form = new Form( $form_id );

        if ( empty( $form->data ) ) {
            return;
        }

        $this->form_fields   = $form->get_fields();
        $this->form_settings = $form->get_settings();

        $author_id = get_post_field( 'post_author', $post_id );

        $is_email_varified = get_user_meta( $author_id, 'wpuf_guest_email_verified', true );

        // if user email already verified, no need to check again.
        // It will prevent mail flooding by clicking on the same link
        if ( $is_email_varified ) {
            return;
        }

        $mail_body   = $this->prepare_mail_body( $this->form_settings['notification']['new_body'], $author_id, $post_id );
        $to          = $this->prepare_mail_body( $this->form_settings['notification']['new_to'], $author_id, $post_id );
        $subject     = $this->prepare_mail_body( $this->form_settings['notification']['new_subject'], $author_id, $post_id );
        $subject     = wp_strip_all_tags( $subject );
        $mail_body   = get_formatted_mail_body( $mail_body, $subject );
        $headers     = [ 'Content-Type: text/html; charset=UTF-8' ];

        // update the information for future to check if the email is already verified
        update_user_meta( $author_id, 'wpuf_guest_email_verified', 1 );
        wp_mail( $to, $subject, $mail_body, $headers );
    }

    /**
     * Prepare the mail body
     *
     * @param $content
     * @param $user_id
     * @param $post_id
     *
     * @return array|string|string[]
     */
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
