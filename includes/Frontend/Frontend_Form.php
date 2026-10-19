<?php

namespace WeDevs\Wpuf\Frontend;

use WeDevs\Wpuf\Platform\Stores\Stores;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Frontend\Forms\Form_Schema;
use WeDevs\Wpuf\Frontend\Forms\Submission_Service;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Frontend_Render_Form;
use WeDevs\Wpuf\Traits\FieldableTrait;
use WP_User;

class Frontend_Form extends Frontend_Render_Form {
    use FieldableTrait;

    public static $config_id = '_wpuf_form_id';

    /**
     * Meta key marking a guest submission that is waiting for e-mail verification
     *
     * `yes` while the verification mail is outstanding, `no` once the link has
     * been used. Posts submitted before this marker existed carry no value at all.
     *
     * @since 4.3.12
     *
     * @var string
     */
    public static $guest_verify_id = '_wpuf_guest_email_verify';

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
        global $userdata;

        // Returned, not printed: printed output escaped the shortcode's place (on
        // block themes it landed before <!DOCTYPE html>).
        if ( ! is_user_logged_in() ) {
            return wp_kses_post( '<div class="wpuf-message">' . __( 'You are not logged in', 'wp-user-frontend' ) . '</div>' )
                . wp_login_form( [ 'echo' => false ] );
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf_edit' ) ) {
            return '<div class="wpuf-info">' . __( 'Please re-open the post', 'wp-user-frontend' ) . '</div>';
        }

        $post_id = isset( $_GET['pid'] ) ? intval( wp_unslash( $_GET['pid'] ) ) : 0;

        if ( ! $post_id ) {
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wp-user-frontend' ) . '</div>';
        }

        $edit_post_lock      = Stores::submissions()->lock( $post_id );
        $edit_post_lock_time = Stores::submissions()->lock_time( $post_id );

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
            return '<div class="wpuf-info">' . __( 'Invalid post', 'wp-user-frontend' ) . '</div>';
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
            return '<div class="wpuf-info">' . __( 'You can\'t edit a post while in pending mode.', 'wp-user-frontend' ) . '</div>';
        }

        if ( 'publish' === $curpost->post_status && 'off' !== $disable_publish_edit ) {
            return '<div class="wpuf-info">' . __( 'You\'re not allowed to edit this post.', 'wp-user-frontend' ) . '</div>';
        }

        // One buffer, opened after every early return (each closes nothing then).
        ob_start();

        $msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';

        if ( $msg === 'post_updated' ) {
            echo wp_kses_post( '<div class="wpuf-success">' );
            echo wp_kses_post( str_replace( '{link}', get_permalink( $post_id ), $this->form_settings['update_message'] ) );
            echo wp_kses_post( '</div>' );
        }

        if ( $this->renderer()->mounts( 'edit_form' ) ) {
            // What printed above (the "post updated" notice) stays before the app.
            return ob_get_clean() . $this->mount_form( (int) $form_id, (int) $post_id, $atts );
        }

        $this->render_form( $form_id, $post_id, $atts, $form );

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }

    /**
     * The React or classic decision for this request.
     *
     * @since WPUF_SINCE
     *
     * @return Renderer
     */
    protected function renderer() {
        return wpuf()->platform()->get( Renderer::class );
    }

    /**
     * The submissions service (create, update, draft).
     *
     * @since WPUF_SINCE
     *
     * @return Submission_Service
     */
    protected function submissions() {
        return wpuf()->platform()->get( Submission_Service::class );
    }

    /**
     * The markup the React post form mounts into: the classic wrapper classes
     * (so CSS written for them still applies), the schema as boot data and a
     * skeleton. A schema error prints the classic notice instead.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param int   $post_id Post id (0 for a new post)
     * @param array $atts    Shortcode attributes
     *
     * @return string
     */
    protected function mount_form( $form_id, $post_id, $atts ) {
        $schema = $this->renderer()->build(
            $post_id ? 'edit_form' : 'post_form',
            function () use ( $form_id, $post_id ) {
                return wpuf()->platform()->get( Form_Schema::class )->build( $form_id, $post_id, [ 'page_id' => (int) get_the_ID() ] );
            }
        );

        if ( is_wp_error( $schema ) ) {
            return '<div class="wpuf-info">' . wp_kses_post( $schema->get_error_message() ) . '</div>';
        }

        return $this->renderer()->form_markup( $schema, $post_id ? 'edit_form' : 'post_form' );
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
     *
     * Saves or updates a draft post via AJAX. For updates (when post_id is provided),
     * authorization is enforced using WordPress capabilities.
     *
     * - Logged-in users: Verifies post ownership and edit_post capability, or edit_others_posts
     *   capability for posts they don't own
     * - Guest users: Can create new drafts but cannot edit existing drafts (must log in)
     *
     * @since 4.0.0
     * @since 4.2.9 Enhanced authorization with proper capability checks. Removed guest draft editing.
     *
     * @return void
     */
    public function draft_post() {
        check_ajax_referer( 'wpuf_form_add' );

        if ( ! headers_sent() ) {
            header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
        }

        // The body lives in Submission_Service::draft() (shared with REST); $_POST is passed as PHP delivered it.
        $response = $this->submissions()->draft( $_POST, true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked above and in the service.

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( [ 'message' => $response->get_error_message() ] );
        }

        wpuf_clear_buffer();

        echo wp_json_encode( $response );

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

        if ( $this->renderer()->mounts( 'post_form' ) ) {
            ob_end_clean();

            return $this->mount_form( (int) $id, 0, $atts );
        }
        $this->generate_auth_link(); // Translate tag %login% %registration% to login registartion url

        // Who may post comes before plan and payment notices: render_form() prints the
        // login or role message, instead of telling them to buy a plan that would not help.
        if ( ! $this->may_post( $this->form_settings ) ) {
            $this->render_form( $id, null, $atts, $form );

            return ob_get_clean();
        }

        [ $user_can_post, $info ]     = $form->is_submission_open( $form, $this->form_settings );
        $info                         = apply_filters( 'wpuf_addpost_notice', $info, $id, $this->form_settings );
        $user_can_post                = apply_filters( 'wpuf_can_post', $user_can_post, $id, $this->form_settings );

        // Enqueue FontAwesome for field icons
        wp_enqueue_style( 'wpuf-font-awesome' );

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
     * Whether the current visitor passes the form's login and role rules (the
     * checks render_form() makes before printing the form).
     *
     * @since WPUF_SINCE
     *
     * @param array $form_settings Form settings
     *
     * @return bool
     */
    private function may_post( $form_settings ) {
        $permission = ! empty( $form_settings['post_permission'] ) ? $form_settings['post_permission'] : '';

        if ( ! is_user_logged_in() && $permission && 'guest_post' !== $permission ) {
            return false;
        }

        return ! ( 'role_base' === $permission && ! empty( $form_settings['roles'] ) && ! wpuf_user_has_roles( $form_settings['roles'] ) );
    }

    /**
     * Hook to publish verified guest post with payment
     *
     * @since 2.5.8
     */
    public function publish_guest_post() {
        // Email-verification flow: link is sent to guest's inbox; payload is validated
        // via wpuf_decryption() and post-author check below — no form nonce applies.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $post_msg = isset( $_GET['post_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['post_msg'] ) ) : '';
        $pid      = isset( $_GET['p_id'] ) ? sanitize_text_field( wp_unslash( $_GET['p_id'] ) ) : '';
        $fid      = isset( $_GET['f_id'] ) ? sanitize_text_field( wp_unslash( $_GET['f_id'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( $post_msg !== 'verified' ) {
            return;
        }

        $response       = [];
        $post_id        = wpuf_decryption( $pid );
        $form_id        = wpuf_decryption( $fid );

        $post = get_post( $post_id );

        if ( ! $post ) {
            wp_die( esc_html__( 'Invalid post.', 'wp-user-frontend' ) );
        }

        $current_status   = get_post_status( $post_id );
        $allowed_statuses = [ 'draft', 'pending', 'auto-draft' ];

        if ( ! in_array( $current_status, $allowed_statuses, true ) ) {
            wp_die( esc_html__( 'This post has already been published.', 'wp-user-frontend' ) );
        }

        // p_id and f_id are encrypted independently with the same primitive and
        // are never bound to each other. Replaying the post-id token as f_id makes
        // the charging decision run against a non-form object, whose empty settings
        // read as uncharged and publish the post for free. Trust only the form id
        // the post itself was submitted through, and refuse a f_id that does not
        // match it, so the paywall is always evaluated against the real form.
        $real_form_id = Stores::submissions()->form_id( $post_id );
        $form_id      = absint( $form_id );

        if ( ! $real_form_id || get_post_type( $real_form_id ) !== 'wpuf_forms' ) {
            wp_die( esc_html__( 'Invalid post.', 'wp-user-frontend' ) );
        }

        if ( $form_id !== $real_form_id ) {
            wp_die( esc_html__( 'This post cannot be published via email verification.', 'wp-user-frontend' ) );
        }

        $form_settings = wpuf_get_form_settings( $real_form_id );

        // This replaces a `post_author !== 0` gate that could never match a real
        // submission: the guest path always stores a real author (the auto-created
        // guest user, or the default post owner when guest details are off), so
        // every legitimate verification link died here. What actually has to hold
        // is that the post came from a guest form that asks for e-mail
        // verification, and that it is still waiting for that verification.
        $is_guest_verify_form = ! empty( $form_settings['post_permission'] )
            && 'guest_post' === $form_settings['post_permission']
            && ! empty( $form_settings['guest_email_verify'] )
            && wpuf_is_checkbox_or_toggle_on( $form_settings['guest_email_verify'] );

        if ( ! $is_guest_verify_form ) {
            wp_die( esc_html__( 'This post cannot be published via email verification.', 'wp-user-frontend' ) );
        }

        // `no` means the link was already used; an empty value means the post
        // predates the marker, where the form check above is the only gate.
        if ( 'no' === get_post_meta( $post_id, self::$guest_verify_id, true ) ) {
            wp_die( esc_html__( 'This post has already been published.', 'wp-user-frontend' ) );
        }

        $payment_status = wpuf()->subscription;
        $form           = new Form( $real_form_id );
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

                wp_safe_redirect( $response['redirect_to'] );
                wpuf_clear_buffer();
                wp_send_json_error( $response );
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

                update_post_meta( $post_id, self::$guest_verify_id, 'no' );

                echo wp_kses_post( "<div class='wpuf-success' style='text-align:center'>" . __( 'Email successfully verified. Please Login.', 'wp-user-frontend' ) . '</div>' );
            }
        }

        do_action( 'wpuf_guest_post_email_verified', $post_id );
    }

    /**
     * Enable edit post link for post authors
     *
     * @since 3.4.0
     * @since 4.3.6 Support all post types created via WPUF forms.
     *
     * @param array   $allcaps
     * @param array   $caps
     * @param array   $args
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

        $post_id = absint( $args[2] );
        $post    = get_post( $post_id );

        if ( empty( $post ) || empty( $post->post_type ) ) {
            return $allcaps;
        }

        // Only grant cap for posts genuinely created via a WPUF form.
        // Excludes arbitrary CPTs (page, product, etc.) and admin-created content.
        $wpuf_form_id = Stores::submissions()->form_id( $post_id );

        if ( empty( $wpuf_form_id ) ) {
            return $allcaps;
        }

        if (
            ! wpuf_validate_boolean( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) )
            || ! $this->get_frontend_post_edit_link( $post_id )
            || absint( $post->post_author ) !== absint( $wp_user->ID )
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
        // Role checks (not capabilities): only swap WP admin edit link for the WPUF
        // frontend edit link when the user is a custom role (e.g. subscriber) that
        // still has edit_post — standard core roles keep the admin edit link.
        // phpcs:disable WordPress.WP.Capabilities.RoleFound
        if (
            current_user_can( 'edit_post', $post_id )
            && ! current_user_can( 'administrator' )
            && ! current_user_can( 'editor' )
            && ! current_user_can( 'author' )
            && ! current_user_can( 'contributor' )
        ) {
            // phpcs:enable WordPress.WP.Capabilities.RoleFound
            $post    = get_post( $post_id );
            $form_id = Stores::submissions()->form_id( $post_id );

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
        if ( ! is_user_logged_in() && ! empty( $this->form_settings['post_permission'] ) && 'guest_post' === $this->form_settings['post_permission'] ) {
            $login        = wpuf()->frontend->simple_login->get_login_url();
            $register     = wpuf()->frontend->simple_login->get_registration_url();
            $replace      = [ "<a href='" . $login . "'>Login</a>", "<a href='" . $register . "'>Register</a>" ];
            $placeholders = [ '{login}', '{register}' ];

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
    public function send_mail_to_admin_after_guest_mail_verified( $post_id = 0 ) {
        // Email-verification flow: link is sent to guest's inbox; payload is validated
        // via wpuf_decryption() before use — no form nonce applies.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $post_id = absint( $post_id );

        if ( ! $post_id ) {
            $post_id = ! empty( $_GET['p_id'] ) ? absint( wpuf_decryption( sanitize_text_field( wp_unslash( $_GET['p_id'] ) ) ) ) : 0;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( ! $post_id || ! get_post( $post_id ) ) {
            return;
        }

        // The f_id query argument used to be trusted here. `empty( $form->data )`
        // does not reject an ordinary post id, so any post passed as f_id built a
        // Form around a non-form object whose settings are a string, and indexing
        // that string fatals on PHP 8. Bind to the post's own form, exactly as
        // publish_guest_post() does, instead of reading the parameter.
        $form_id = Stores::submissions()->form_id( $post_id );

        if ( ! $form_id || 'wpuf_forms' !== get_post_type( $form_id ) ) {
            return;
        }

        $form = new Form( $form_id );

        if ( empty( $form->data ) ) {
            return;
        }

        $this->form_fields   = $form->get_fields();
        $this->form_settings = $form->get_settings();

        if ( ! is_array( $this->form_settings ) || empty( $this->form_settings['notification'] )
            || ! is_array( $this->form_settings['notification'] ) ) {
            return;
        }

        $notification = $this->form_settings['notification'];
        $author_id    = get_post_field( 'post_author', $post_id );

        $is_email_varified = get_user_meta( $author_id, 'wpuf_guest_email_verified', true );

        // if user email already verified, no need to check again.
        // It will prevent mail flooding by clicking on the same link
        if ( $is_email_varified ) {
            return;
        }

        $mail_body = $this->prepare_mail_body( isset( $notification['new_body'] ) ? $notification['new_body'] : '', $author_id, $post_id );
        // Validate & sanitise recipient addresses before sending
        $to_raw      = $this->prepare_mail_body( isset( $notification['new_to'] ) ? $notification['new_to'] : '', $author_id, $post_id );
        $to          = implode(
            ',',
            array_filter(
                array_map(
                    static function ( $addr ) {
						$addr = trim( $addr );
						return is_email( $addr ) ? $addr : null;
					}, explode( ',', $to_raw )
                )
            )
        );
        $subject     = $this->prepare_mail_body( isset( $notification['new_subject'] ) ? $notification['new_subject'] : '', $author_id, $post_id );
        $subject     = wp_strip_all_tags( $subject );
        $mail_body   = get_formatted_mail_body( $mail_body, $subject );
        $headers     = [ 'Content-Type: text/html; charset=UTF-8' ];

        // update the information for future to check if the email is already verified
        update_user_meta( $author_id, 'wpuf_guest_email_verified', 1 );
        if ( ! empty( $to ) ) {
            wp_mail( $to, $subject, $mail_body, $headers );
        }
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
                        } elseif ( get_post_mime_type( (int) $val ) ) {
                                $meta_val = $meta_val . ', ' . wp_get_attachment_url( $val );
						} else {
							$meta_val = $meta_val . ', ' . $val;
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
