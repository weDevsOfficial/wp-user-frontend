<?php
/**
 * The JSON schema of a post form for the React renderer
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Forms;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Platform\Contracts\Form_Schema_Provider;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Traits\FieldableTrait;
use WP_Error;

/**
 * Everything `Frontend_Render_Form::render_form()` and the two shortcodes
 * decide at render time, as data: the fields (visibility applied, values in
 * edit mode), the settings the renderer needs, the gates (can the visitor
 * post, can they edit this post), the nonces, the libraries the fields need,
 * and the markup the printing hooks produce (`slots`), captured in the order
 * and with the arguments the classic renderer fires them.
 *
 * @since WPUF_SINCE
 */
class Form_Schema implements Form_Schema_Provider {

    use FieldableTrait;

    /**
     * Settings of the form (the trait reads them).
     *
     * @var array
     */
    public $form_settings = [];

    /**
     * Fields of the form (the trait reads them).
     *
     * @var array
     */
    private $form_fields = [];

    /**
     * Settings the renderer needs. Notification bodies, recipients and the
     * rest of the builder's admin-only data stay server-side.
     *
     * @since WPUF_SINCE
     */
    const SETTINGS = [
        'post_type',
        'post_status',
        'edit_post_status',
        'redirect_to',
        'page_id',
        'url',
        'message',
        'edit_redirect_to',
        'edit_page_id',
        'edit_url',
        'update_message',
        'submit_text',
        'update_text',
        'draft_post',
        'submit_button_cond',
        'form_layout',
        'label_position',
        'use_theme_css',
        'show_form_title',
        'form_description',
        'enable_multistep',
        'multistep_progressbar_type',
        'post_permission',
        'guest_details',
        'guest_email_verify',
        'name_label',
        'email_label',
        'message_restrict',
        'roles',
        'comment_status',
        'default_cat',
        'post_format',
        'limit_entries',
        'limit_number',
        'limit_message',
        'schedule_form',
        'schedule_start',
        'schedule_end',
        'form_pending_message',
        'form_expired_message',
        'lock_edit_post',
        'payment_options',
        'enable_pay_per_post',
        'pay_per_post_cost',
        'force_pack_purchase',
        'fallback_ppp_enable',
        'fallback_ppp_cost',
        // Profile forms (Pro registration / profile).
        'profile_form_layout',
        'reg_redirect_to',
        'reg_page_id',
        'registration_url',
        'profile_redirect_to',
        'profile_page_id',
        'profile_url',
        'update_message',
        'role',
        'wpuf_user_status',
        'notification_type',
        'user_notification',
        'register_subscription',
    ];

    /**
     * Templates whose classic render is kept as server markup (their output
     * carries state or scripts the field needs: captcha words, inline JS).
     *
     * @since WPUF_SINCE
     */
    const SERVER_RENDERED = [ 'shortcode', 'action_hook', 'really_simple_captcha', 'math_captcha', 'google_map', 'signature_field' ];

    /**
     * User fields (Pro profile forms): template => the WP_User property they edit.
     *
     * @since WPUF_SINCE
     */
    const USER_PROPS = [
        'user_login'   => 'user_login',
        'first_name'   => 'first_name',
        'last_name'    => 'last_name',
        'display_name' => 'display_name',
        'nickname'     => 'nickname',
        'user_email'   => 'user_email',
        'user_url'     => 'user_url',
        'user_bio'     => 'description',
    ];

    /**
     * TinyMCE toolbar buttons the TipTap editor of the React forms has a
     * control for (or, like `spellchecker`, the browser covers). A toolbar
     * with any other button (a plugin's TinyMCE button) keeps TinyMCE.
     *
     * @since WPUF_SINCE
     */
    const TIPTAP_BUTTONS = [
        '|',
        'formatselect',
        'bold',
        'italic',
        'underline',
        'strikethrough',
        'blockquote',
        'bullist',
        'numlist',
        'alignleft',
        'aligncenter',
        'alignright',
        'alignjustify',
        'link',
        'unlink',
        'undo',
        'redo',
        'fullscreen',
        'hr',
        'forecolor',
        'pastetext',
        'removeformat',
        'charmap',
        'outdent',
        'indent',
        'wp_more',
        'spellchecker',
        'wp_adv',
        'wp_help',
    ];

    /**
     * Field templates that need a library on the page.
     *
     * @since WPUF_SINCE
     */
    const NEEDS = [
        'image_upload'        => 'uploads',
        'featured_image'      => 'uploads',
        'file_upload'         => 'uploads',
        'profile_photo'       => 'uploads',
        'cover_photo'         => 'uploads',
        'avatar'              => 'uploads',
        'post_content'        => 'editor',
        'google_map'          => 'maps',
        'recaptcha'           => 'recaptcha',
        'cloudflare_turnstile' => 'turnstile',
        'date_field'          => 'dates',
        'time_field'          => 'dates',
        'phone_field'         => 'phone',
        'country_list_field'  => 'address',
        'address_field'       => 'address',
    ];

    /**
     * The schema of a form, for a new post or for editing one.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param int   $post_id Post being edited (0 for a new post)
     * @param array $atts    Shortcode attributes (`page_id` of the page that renders the form)
     *
     * @return array|WP_Error
     */
    public function build( $form_id, $post_id = 0, array $atts = [] ) {
        $form_id   = (int) $form_id;
        $post_id   = (int) $post_id;
        $form_type = get_post_type( $form_id );

        if ( 'wpuf_profile' === $form_type ) {
            return $this->build_profile( $form_id, isset( $atts['mode'] ) && 'profile' === $atts['mode'] ? 'profile' : 'registration', $atts );
        }

        if ( $post_id ) {
            $edit = $this->edit_gate( $post_id );

            if ( is_wp_error( $edit ) ) {
                return $edit;
            }

            $form_id = $edit;
        }

        if ( ! $form_id || 'wpuf_forms' !== $form_type ) {
            return new WP_Error( 'wpuf_form_not_found', __( 'Your selected form is no longer available.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        if ( 'publish' !== get_post_status( $form_id ) ) {
            return new WP_Error( 'wpuf_form_not_published', __( "Please make sure you've published your form.", 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );

        $form                = new Form( $form_id );
        $this->form_settings = (array) $form->get_settings();
        $this->form_fields   = (array) $form->get_fields();
        $settings            = $this->form_settings;
        $page_id             = isset( $atts['page_id'] ) ? (int) $atts['page_id'] : (int) get_the_ID();

        $state = $post_id ? $this->edit_state( $post_id ) : $this->create_state( $form, $form_id );

        $fields = apply_filters( 'wpuf_render_fields', $this->form_fields, $form_id );
        $fields = $this->describe_fields( (array) $fields, $post_id, 'post', $form_id );

        $schema = [
            'id'        => $form_id,
            'form_type' => 'post',
            'title'     => get_the_title( $form_id ),
            'mode'     => $post_id ? 'edit' : 'create',
            'post_id'  => $post_id,
            'page_id'  => $page_id,
            'post'     => $post_id ? $this->post_facts( $post_id ) : null,
            'state'    => $state,
            'settings' => $this->settings_subset( $settings, $form_id ),
            'layout'   => [
                'layout'         => 'wpuf_profile' === $form->data->post_type ? ( isset( $settings['profile_form_layout'] ) ? $settings['profile_form_layout'] : 'layout1' ) : ( ! empty( $settings['form_layout'] ) ? $settings['form_layout'] : 'layout1' ),
                'label_position' => isset( $settings['label_position'] ) ? $settings['label_position'] : 'left',
                'use_theme_css'  => isset( $settings['use_theme_css'] ) && 'wpuf-theme-style' === wpuf_form_style_class( $settings['use_theme_css'] ),
                // Frontend_Render_Form's class: `wpuf-theme-style` on layout1 with "use theme CSS".
                'form_class'     => ( empty( $settings['form_layout'] ) || 'layout1' === $settings['form_layout'] ) && isset( $settings['use_theme_css'] ) && 'wpuf-theme-style' === wpuf_form_style_class( $settings['use_theme_css'] ) ? 'wpuf-theme-style' : '',
                'card'           => false,
                'show_title'     => isset( $settings['show_form_title'] ) && wpuf_is_checkbox_or_toggle_on( $settings['show_form_title'] ),
                'description'    => isset( $settings['form_description'] ) ? wp_kses_post( $settings['form_description'] ) : '',
                'submit_text'    => isset( $settings['submit_text'] ) ? $settings['submit_text'] : __( 'Submit', 'wp-user-frontend' ),
                'update_text'    => isset( $settings['update_text'] ) ? $settings['update_text'] : __( 'Update', 'wp-user-frontend' ),
                'draft'          => isset( $settings['draft_post'] ) && wpuf_is_checkbox_or_toggle_on( $settings['draft_post'] ),
                'multistep'      => isset( $settings['enable_multistep'] ) && wpuf_is_checkbox_or_toggle_on( $settings['enable_multistep'] ),
                'progress'       => isset( $settings['multistep_progressbar_type'] ) ? $settings['multistep_progressbar_type'] : 'step_by_step',
            ],
            'guest'    => $this->guest_facts( $settings ),
            'featured' => $this->featured_facts( $settings, $post_id ),
            'fields'   => $fields,
            'needs'    => $this->needs( $fields ),
            'assets'   => untrailingslashit( WPUF_ASSET_URI ),
            'image_caption' => wpuf_is_checkbox_or_toggle_on( Stores::settings()->get( 'wpuf_frontend_posting', 'image_caption', 'off' ) ),
            // The shortcodes a rich editor may not use (the classic wpuf_frontend.protected_shortcodes).
            'protected_shortcodes' => function_exists( 'wpuf_get_protected_shortcodes' ) ? array_values( (array) wpuf_get_protected_shortcodes() ) : [],
            'messages' => $this->messages(),
            'nonces'   => [
                'submit' => wp_create_nonce( 'wpuf_form_add' ),
                'upload' => wp_create_nonce( 'wpuf-upload-nonce' ),
                'delete' => wp_create_nonce( 'wpuf_nonce' ),
            ],
            'slots'    => $state['open'] ? $this->slots( $form, $form_id, $post_id, $settings ) : [],
        ];

        /**
         * Filters the schema the React post form renders from.
         *
         * @since WPUF_SINCE
         *
         * @param array $schema  Schema
         * @param int   $form_id Form id
         * @param int   $post_id Post id (0 for a new post)
         */
        return apply_filters( 'wpuf_frontend_form_schema', $schema, $form_id, $post_id );
    }

    /**
     * The schema of a Pro profile form in registration mode (a visitor
     * creating an account) or profile mode (a user editing their own): the
     * gates of `Profile_Form::shortcode_handler`, the user's values, the
     * slots `profile_edit()` fires, the login link, the subscription pack
     * parameters a registration started from a pack carries.
     *
     * @since WPUF_SINCE
     *
     * @param int    $form_id Form id
     * @param string $mode    'registration' | 'profile'
     * @param array  $atts    Shortcode attributes (`page_id`)
     *
     * @return array|WP_Error
     */
    public function build_profile( $form_id, $mode, array $atts = [] ) {
        if ( 'publish' !== get_post_status( $form_id ) ) {
            return new WP_Error( 'wpuf_form_not_published', __( "Please make sure you've published your form.", 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );

        $form                = new Form( $form_id );
        $this->form_settings = (array) $form->get_settings();
        $this->form_fields   = (array) $form->get_fields();
        $settings            = $this->form_settings;
        $user_id             = 'profile' === $mode ? get_current_user_id() : 0;
        $page_id             = isset( $atts['page_id'] ) ? (int) $atts['page_id'] : (int) get_the_ID();

        if ( 'profile' === $mode && ! $user_id ) {
            $state = [ 'open' => false, 'reason' => 'login', 'message' => __( 'Please login to update your profile!', 'wp-user-frontend' ) ];
        } elseif ( 'registration' === $mode && is_user_logged_in() ) {
            $state = [ 'open' => false, 'reason' => 'logged_in', 'message' => __( 'You are already logged in!', 'wp-user-frontend' ) ];
        } elseif ( 'registration' === $mode && ! ( is_multisite() ? 'user' === Stores::settings()->wp_option( 'registration', false, true ) : 1 === (int) Stores::settings()->wp_option( 'users_can_register' ) ) ) {
            $state = [ 'open' => false, 'reason' => 'closed', 'message' => __( 'User registration disabled, please contact the admin to enable.', 'wp-user-frontend' ) ];
        } else {
            $state = [ 'open' => true, 'reason' => '', 'message' => '' ];
        }

        $fields = apply_filters( 'wpuf_render_fields', $this->form_fields, $form_id );
        $fields = $this->describe_fields( (array) $fields, $user_id, 'user', $form_id );

        // A registration started from a subscription pack carries the pack along (Profile_Form::submit_button()).
        $type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $pack_id = isset( $_GET['pack_id'] ) ? (int) $_GET['pack_id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $extra   = [];

        if ( 'registration' === $mode && 'wpuf_sub' === $type && $pack_id ) {
            $extra = [
                'wpuf_sub' => 'yes',
                'pack_id'  => $pack_id,
                'type'     => $type,
            ];
        }

        $layout = isset( $settings['profile_form_layout'] ) ? $settings['profile_form_layout'] : 'layout1';

        $schema = [
            'id'           => $form_id,
            'form_type'    => 'profile',
            'title'        => get_the_title( $form_id ),
            'mode'         => $mode,
            'post_id'      => 0,
            'user_id'      => $user_id,
            'page_id'      => $page_id,
            'post'         => null,
            'state'        => $state,
            'settings'     => $this->settings_subset( $settings, $form_id ),
            'layout'       => [
                'layout'         => $layout,
                'template_style' => 'layout1' !== $layout,
                'label_position' => isset( $settings['label_position'] ) ? $settings['label_position'] : 'left',
                'use_theme_css'  => isset( $settings['use_theme_css'] ) && 'wpuf-theme-style' === wpuf_form_style_class( $settings['use_theme_css'] ),
                // Profile_Form::profile_edit(): the theme style class on layout1, `wpuf-style` on the others, inside the card.
                'form_class'     => 'layout1' === $layout ? wpuf_form_style_class( isset( $settings['use_theme_css'] ) ? $settings['use_theme_css'] : '' ) : 'wpuf-style',
                'card'           => true,
                'show_title'     => false,
                'description'    => '',
                'submit_text'    => isset( $settings['submit_text'] ) ? $settings['submit_text'] : __( 'Register', 'wp-user-frontend' ),
                'update_text'    => isset( $settings['update_text'] ) ? $settings['update_text'] : __( 'Update Profile', 'wp-user-frontend' ),
                'draft'          => false,
                'multistep'      => isset( $settings['enable_multistep'] ) && wpuf_is_checkbox_or_toggle_on( $settings['enable_multistep'] ),
                'progress'       => isset( $settings['multistep_progressbar_type'] ) ? $settings['multistep_progressbar_type'] : 'step_by_step',
            ],
            'guest'        => [ 'enabled' => false, 'fields' => false ],
            'featured'     => [ 'enabled' => false ],
            'fields'       => $fields,
            'needs'        => $this->needs( $fields ),
            'assets'       => untrailingslashit( WPUF_ASSET_URI ),
            'image_caption' => wpuf_is_checkbox_or_toggle_on( Stores::settings()->get( 'wpuf_frontend_posting', 'image_caption', 'off' ) ),
            // The shortcodes a rich editor may not use (the classic wpuf_frontend.protected_shortcodes).
            'protected_shortcodes' => function_exists( 'wpuf_get_protected_shortcodes' ) ? array_values( (array) wpuf_get_protected_shortcodes() ) : [],
            'messages'     => $this->messages(),
            'nonces'       => [
                'submit' => wp_create_nonce( 'wpuf_form_add' ),
                'upload' => wp_create_nonce( 'wpuf-upload-nonce' ),
                'delete' => wp_create_nonce( 'wpuf_nonce' ),
            ],
            'extra_fields' => $extra,
            'links'        => [
                'login'    => 'registration' === $mode ? wp_login_url() : '',
                'register' => '',
            ],
            'slots'        => $state['open'] ? $this->profile_slots( $form, $form_id, $mode, $settings ) : [],
        ];

        if ( 'registration' === $mode ) {
            $schema['links']['login'] = $this->registration_login_url();
        }

        /** This filter is documented above in build(). */
        return apply_filters( 'wpuf_frontend_form_schema', $schema, $form_id, 0 );
    }

    /**
     * The markup the printing hooks of `Profile_Form::profile_edit()` produce.
     *
     * @since WPUF_SINCE
     *
     * @param Form   $form     Form
     * @param int    $form_id  Form id
     * @param string $mode     Mode
     * @param array  $settings Settings
     *
     * @return array
     */
    private function profile_slots( Form $form, $form_id, $mode, array $settings ) {
        $capture = function ( callable $fire ) {
            ob_start();

            try {
                $fire();
            } finally {
                $html = ob_get_clean();
            }

            return (string) $html;
        };

        $fields = $this->form_fields;
        $slots  = [
            'fields_top'   => $capture(
                function () use ( $form, $fields ) {
                    do_action( 'wpuf_form_fields_top', $form, $fields );
                }
            ),
            'submit'       => $capture(
                function () use ( $mode ) {
                    if ( 'registration' === $mode ) {
                        do_action( 'register_form' );
                    }
                }
            ),
            'action_links' => $capture(
                function () use ( $mode ) {
                    if ( 'registration' === $mode && isset( wpuf()->frontend->registration ) ) {
                        echo wpuf()->frontend->registration->get_action_links( [ 'register' => false ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the classic action links markup.
                    }
                }
            ),
            'form_bottom'  => $capture(
                function () use ( $form_id, $settings ) {
                    do_action( 'wpuf_add_profile_form_bottom', $form_id, $settings );
                }
            ),
        ];

        return array_filter( $slots, 'strlen' );
    }

    /**
     * Which form edits a post, after the edit checks of `edit_post_shortcode`.
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return int|WP_Error Form id
     */
    private function edit_gate( $post_id ) {
        $can_edit = wpuf_user_can_edit_post( $post_id );

        if ( is_wp_error( $can_edit ) ) {
            $can_edit->add_data( [ 'status' => is_user_logged_in() ? 403 : 401 ] );

            return $can_edit;
        }

        $form_id = Stores::submissions()->form_id( $post_id );

        if ( ! $form_id ) {
            $form_id = (int) Stores::settings()->get( 'wpuf_frontend_posting', 'default_post_form' );
        }

        if ( ! $form_id ) {
            return new WP_Error( 'wpuf_form_unknown', __( "I don't know how to edit this post, I don't have the form ID", 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        return $form_id;
    }

    /**
     * The gate of a new submission (`is_submission_open`, the login and role
     * checks of `render_form`), as `open` + the message to show instead.
     *
     * @since WPUF_SINCE
     *
     * @param Form $form    Form
     * @param int  $form_id Form id
     *
     * @return array open, message, reason
     */
    private function create_state( Form $form, $form_id ) {
        $settings = $this->form_settings;

        [ $user_can_post, $info ] = $form->is_submission_open( $form, $settings );
        $info                     = apply_filters( 'wpuf_addpost_notice', $info, $form_id, $settings );
        $user_can_post            = apply_filters( 'wpuf_can_post', $user_can_post, $form_id, $settings );

        if ( 'yes' !== $user_can_post ) {
            return [
                'open'    => false,
                'reason'  => 'closed',
                'message' => wp_kses_post( (string) $info ),
            ];
        }

        if ( ! is_user_logged_in() && ! empty( $settings['post_permission'] ) && 'guest_post' !== $settings['post_permission'] ) {
            $login    = wpuf()->frontend->simple_login->get_login_url();
            $register = wpuf()->frontend->simple_login->get_registration_url();
            $message  = str_replace(
                [ '{login}', '{register}' ],
                [ "<a href='" . esc_url( $login ) . "'>Login</a>", "<a href='" . esc_url( $register ) . "'>Register</a>" ],
                isset( $settings['message_restrict'] ) ? $settings['message_restrict'] : ''
            );

            return [
                'open'    => false,
                'reason'  => 'login',
                'message' => wp_kses_post( $message ),
            ];
        }

        if ( ! empty( $settings['post_permission'] ) && 'role_base' === $settings['post_permission'] && ! empty( $settings['roles'] ) && ! wpuf_user_has_roles( $settings['roles'] ) ) {
            return [
                'open'    => false,
                'reason'  => 'role',
                'message' => __( 'You do not have sufficient permissions to access this form.', 'wp-user-frontend' ),
            ];
        }

        return [
            'open'    => true,
            'reason'  => '',
            'message' => '',
            'locked'  => $this->submit_locked( $form_id, $settings ),
        ];
    }

    /**
     * Whether the classic form would show the submit button disabled: outside
     * the schedule window (wpuf_show_form_schedule_message()) or with the
     * entry limit reached (wpuf_show_form_limit_message()). The messages come
     * through the `before_form` slot; the button stays locked in React too.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id  Form id
     * @param array $settings Form settings
     *
     * @return bool
     */
    private function submit_locked( $form_id, array $settings ) {
        if ( isset( $settings['schedule_form'] ) && wpuf_is_checkbox_or_toggle_on( $settings['schedule_form'] ) ) {
            $start = ! empty( $settings['schedule_start'] ) ? strtotime( $settings['schedule_start'] ) : 0;
            $end   = ! empty( $settings['schedule_end'] ) ? strtotime( $settings['schedule_end'] ) : 0;
            $now   = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- same clock as the classic check.

            if ( $now < $start || $now > $end ) {
                return true;
            }
        }

        if ( isset( $settings['limit_entries'] ) && wpuf_is_checkbox_or_toggle_on( $settings['limit_entries'] ) ) {
            $limit = ! empty( $settings['limit_number'] ) ? (int) $settings['limit_number'] : 0;

            if ( $limit && $limit <= wpuf_form_posts_count( $form_id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * The "Already have an account? Login" link of a registration form, as
     * Pro's Profile_Form::login_url() builds it: wp-login.php unless "Login /
     * registration override" is on and a login page is set.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function registration_login_url() {
        $default = site_url( 'wp-login.php', 'login' );

        if ( 'on' !== Stores::settings()->get( 'wpuf_profile', 'register_link_override', 'off' ) ) {
            return $default;
        }

        $page_id = Stores::settings()->get( 'wpuf_profile', 'login_page', false );

        return $page_id ? home_url( basename( get_permalink( $page_id ) ), 'login' ) : $default;
    }

    /**
     * The gate of an edit (locks and the dashboard settings of `edit_post_shortcode`).
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return array
     */
    private function edit_state( $post_id ) {
        $closed = function ( $reason, $message ) {
            return [
                'open'    => false,
                'reason'  => $reason,
                'message' => wp_kses_post( $message ),
            ];
        };

        if ( 'yes' === Stores::submissions()->lock( $post_id ) ) {
            return $closed( 'locked', apply_filters( 'wpuf_edit_post_lock_user_notice', __( 'Your edit access for this post has been locked by an administrator.', 'wp-user-frontend' ) ) );
        }

        $lock_time = Stores::submissions()->lock_time( $post_id );

        if ( ! empty( $lock_time ) && $lock_time < time() ) {
            return $closed( 'expired', apply_filters( 'wpuf_edit_post_lock_expire_notice', __( 'Your allocated time for editing this post has been expired.', 'wp-user-frontend' ) ) );
        }

        $user = wpuf_get_user();

        if ( $user->edit_post_locked() ) {
            $reason = $user->edit_post_lock_reason();

            return $closed( 'user_locked', $reason ? $reason : apply_filters( 'wpuf_user_edit_post_lock_notice', __( 'Your post edit access has been locked by an administrator.', 'wp-user-frontend' ) ) );
        }

        if ( 'yes' !== Stores::settings()->get( 'wpuf_dashboard', 'enable_post_edit', 'yes' ) ) {
            return $closed( 'disabled', __( 'Post Editing is disabled', 'wp-user-frontend' ) );
        }

        $status = get_post_status( $post_id );

        if ( 'pending' === $status && 'on' === Stores::settings()->get( 'wpuf_dashboard', 'disable_pending_edit', 'on' ) ) {
            return $closed( 'pending', __( "You can't edit a post while in pending mode.", 'wp-user-frontend' ) );
        }

        if ( 'publish' === $status && 'off' !== Stores::settings()->get( 'wpuf_dashboard', 'disable_publish_edit', 'off' ) ) {
            return $closed( 'published', __( "You're not allowed to edit this post.", 'wp-user-frontend' ) );
        }

        // The schedule lock applies to edits too; the entry limit does not (classic skips it on edit pages).
        $settings = $this->form_settings;
        unset( $settings['limit_entries'] );

        return [
            'open'    => true,
            'reason'  => '',
            'message' => '',
            'locked'  => $this->submit_locked( 0, $settings ),
        ];
    }

    /**
     * The facts of the post being edited that the submit needs back.
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Post id
     *
     * @return array
     */
    private function post_facts( $post_id ) {
        $post = get_post( $post_id );

        return [
            'id'             => (int) $post->ID,
            'status'         => (string) $post->post_status,
            'date'           => (string) $post->post_date,
            'comment_status' => (string) $post->comment_status,
            'author'         => (int) $post->post_author,
            'permalink'      => (string) get_permalink( $post ),
            'thumbnail_id'   => (int) get_post_thumbnail_id( $post ),
        ];
    }

    /**
     * The settings subset the renderer gets.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings All settings
     * @param int   $form_id  Form id
     *
     * @return array
     */
    private function settings_subset( array $settings, $form_id ) {
        $subset = [];

        foreach ( self::SETTINGS as $key ) {
            if ( array_key_exists( $key, $settings ) ) {
                $subset[ $key ] = $settings[ $key ];
            }
        }

        /**
         * Filters the form settings the React renderer receives.
         *
         * @since WPUF_SINCE
         *
         * @param array $subset   Settings sent to the client
         * @param array $settings All stored settings
         * @param int   $form_id  Form id
         */
        return apply_filters( 'wpuf_frontend_form_schema_settings', $subset, $settings, $form_id );
    }

    /**
     * Guest posting facts (`guest_fields()` of the classic renderer).
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Settings
     *
     * @return array
     */
    private function guest_facts( array $settings ) {
        $guest   = ! empty( $settings['post_permission'] ) && 'guest_post' === $settings['post_permission'];
        $details = $guest && ! empty( $settings['guest_details'] ) && wpuf_is_checkbox_or_toggle_on( $settings['guest_details'] );

        return [
            'enabled'     => $guest,
            'fields'      => $details && ! is_user_logged_in(),
            'name_label'  => isset( $settings['name_label'] ) ? $settings['name_label'] : __( 'Name', 'wp-user-frontend' ),
            'email_label' => isset( $settings['email_label'] ) ? $settings['email_label'] : __( 'Email', 'wp-user-frontend' ),
            'verify'      => $guest && isset( $settings['guest_email_verify'] ) && wpuf_is_checkbox_or_toggle_on( $settings['guest_email_verify'] ),
        ];
    }

    /**
     * The "Featured" checkbox facts (`render_featured_field()`).
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Settings
     * @param int   $post_id  Post id
     *
     * @return array
     */
    private function featured_facts( array $settings, $post_id ) {
        $user_sub    = Subscription::get_user_pack( get_current_user_id() );
        $remaining   = ! empty( $user_sub['total_feature_item'] ) ? (int) $user_sub['total_feature_item'] : 0;
        $is_featured = $post_id && in_array( (int) $post_id, (array) Stores::settings()->wp_option( 'sticky_posts' ), true );

        return [
            'enabled'   => $remaining > 0 || $is_featured,
            'checked'   => (bool) $is_featured,
            'remaining' => $remaining,
            'post_type' => ! empty( $settings['post_type'] ) ? $settings['post_type'] : 'post',
        ];
    }

    /**
     * The fields as the renderer sees them: visibility applied, unknown
     * templates dropped, values filled in edit mode, column fields recursed.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields  Fields
     * @param int   $post_id Post id (0 for a new post)
     *
     * @return array
     */
    private function describe_fields( array $fields, $post_id, $type = 'post', $form_id = 0 ) {
        $manager = wpuf()->fields;
        $out     = [];

        foreach ( $fields as $field ) {
            if ( empty( $field['template'] ) ) {
                continue;
            }

            $object = $manager->field_exists( $field['template'] );

            if ( ! $object || ! $manager->check_field_visibility( $field ) ) {
                continue;
            }

            if ( 'column_field' === $field['template'] && ! empty( $field['inner_fields'] ) && is_array( $field['inner_fields'] ) ) {
                foreach ( $field['inner_fields'] as $column => $column_fields ) {
                    $field['inner_fields'][ $column ] = $this->describe_fields( (array) $column_fields, $post_id, $type, $form_id );
                }
            }

            if ( 'repeat_field' === $field['template'] && ! empty( $field['inner_fields'] ) && is_array( $field['inner_fields'] ) ) {
                $field['inner_fields'] = $this->describe_fields( (array) $field['inner_fields'], 0, $type, $form_id );
            }

            if ( $post_id ) {
                $field['value'] = 'user' === $type ? $this->user_value_of( $object, $field, $post_id ) : $this->value_of( $object, $field, $post_id );
            }

            if ( 'taxonomy' === $field['template'] ) {
                $field['choices'] = $this->term_choices( $field );
            }

            // The Events Calendar venue / organizer lists: the classic dropdown swaps its
            // options for the published venues / organizers when it renders.
            if ( 'dropdown_field' === $field['template'] && isset( $field['name'] ) && method_exists( $object, 'get_posts' ) ) {
                $event_lists = [
                    '_EventVenueID'     => 'tribe_venue',
                    '_EventOrganizerID' => 'tribe_organizer',
                ];

                if ( isset( $event_lists[ $field['name'] ] ) ) {
                    $field['options'] = $object->get_posts( $event_lists[ $field['name'] ] );
                }
            }

            if ( in_array( $field['template'], [ 'country_list_field', 'address_field', 'profile_phone', 'phone_field' ], true ) && class_exists( '\WeDevs\Wpuf\Data\Country_State' ) ) {
                $field['countries'] = $this->country_choices( $field );
            }

            if ( in_array( $field['template'], $this->server_rendered(), true ) ) {
                ob_start();

                try {
                    $object->render( $field, (int) $form_id, $type, $post_id ? $post_id : null );
                } finally {
                    $field['html']            = (string) ob_get_clean();
                    // Its inputs post with the form; the server and the field's own script check them.
                    $field['server_rendered'] = true;
                }
            }

            if ( $post_id && in_array( $field['template'], [ 'image_upload', 'featured_image', 'file_upload', 'avatar', 'profile_photo', 'cover_photo' ], true ) ) {
                $field['attachments'] = $this->attachment_facts( $field['value'] );
            }

            // print_label() prints the label through wp_kses_post(): a label with markup keeps its safe tags.
            if ( ! empty( $field['label'] ) && is_string( $field['label'] ) && wp_strip_all_tags( $field['label'] ) !== $field['label'] ) {
                $field['label_html'] = wp_kses_post( $field['label'] );
            }

            // The classic help_text() prints the help as HTML; only safe markup reaches the app.
            if ( ! empty( $field['help'] ) && is_string( $field['help'] ) ) {
                $field['help'] = wp_kses_post( stripslashes( $field['help'] ) );
            }

            if ( in_array( $field['template'], [ 'post_content', 'textarea_field' ], true ) && isset( $field['rich'] ) && in_array( $field['rich'], [ 'yes', 'teeny' ], true ) ) {
                $field['editor'] = $this->editor_settings( $field, (int) $form_id );
                $field['editor']['engine'] = $this->editor_engine( $field );
            }

            $field['kind'] = $field['template'];

            $out[] = 'post' === $type ? $this->texturize( $field ) : $field;
        }

        return $out;
    }

    /**
     * The terms a taxonomy field offers (what the classic field prints
     * through wp_dropdown_categories / the checklist), the field's own
     * exclusions applied.
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field settings
     *
     * @return array [ [ 'id', 'name', 'parent' ], ... ]
     */
    private function term_choices( array $field ) {
        $taxonomy = isset( $field['name'] ) ? $field['name'] : '';

        if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            return [];
        }

        $args = [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => ! empty( $field['orderby'] ) ? $field['orderby'] : 'name',
            'order'      => ! empty( $field['order'] ) && 'DESC' === strtoupper( $field['order'] ) ? 'DESC' : 'ASC',
        ];

        $exclude = ! empty( $field['exclude'] ) ? array_filter( array_map( 'intval', (array) $field['exclude'] ) ) : [];

        if ( $exclude ) {
            if ( ! empty( $field['exclude_type'] ) && 'child_of' === $field['exclude_type'] ) {
                $args['child_of'] = reset( $exclude );
            } elseif ( ! empty( $field['exclude_type'] ) && 'include' === $field['exclude_type'] ) {
                $args['include'] = $exclude;
            } else {
                $args['exclude'] = $exclude;
            }
        }

        /** This filter is documented in includes/Fields/Form_Field_Post_Taxonomy.php */
        $args  = apply_filters( 'wpuf_taxonomy_checklist_args', $args );
        $terms = get_terms( $args );
        $out   = [];

        foreach ( is_wp_error( $terms ) ? [] : $terms as $term ) {
            $out[] = [
                'id'     => (int) $term->term_id,
                'name'   => (string) $term->name,
                'parent' => (int) $term->parent,
            ];
        }

        return $out;
    }

    /**
     * The stored attachments of an upload field, for the previews in edit mode.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Attachment id(s)
     *
     * @return array
     */
    private function attachment_facts( $value ) {
        $ids = array_filter( array_map( 'intval', is_array( $value ) ? $value : explode( ',', (string) $value ) ) );
        $out = [];

        foreach ( $ids as $id ) {
            if ( 'attachment' !== get_post_type( $id ) ) {
                continue;
            }

            $thumb = wp_get_attachment_image_src( $id, 'thumbnail' );

            $out[] = [
                'id'    => $id,
                'url'   => (string) wp_get_attachment_url( $id ),
                'thumb' => $thumb ? $thumb[0] : '',
                'title' => get_the_title( $id ),
                'mime'  => (string) get_post_mime_type( $id ),
            ];
        }

        return $out;
    }

    /**
     * The stored value of a field for the post being edited.
     *
     * @since WPUF_SINCE
     *
     * @param object $object  Field object
     * @param array  $field   Field settings
     * @param int    $post_id Post id
     *
     * @return mixed
     */
    private function value_of( $object, array $field, $post_id ) {
        $post = get_post( $post_id );
        $name = isset( $field['name'] ) ? $field['name'] : '';

        switch ( $field['template'] ) {
            case 'post_title':
                return $post ? $post->post_title : '';
            case 'post_content':
                return $post ? $post->post_content : '';
            case 'post_excerpt':
                return $post ? $post->post_excerpt : '';
            case 'post_tags':
                return implode( ', ', wp_get_post_tags( $post_id, [ 'fields' => 'names' ] ) );
            case 'featured_image':
                return (int) get_post_thumbnail_id( $post_id );
            case 'taxonomy':
                $terms = $name ? wp_get_object_terms( $post_id, $name, [ 'fields' => 'ids' ] ) : [];

                return is_wp_error( $terms ) ? [] : array_map( 'intval', $terms );
        }

        if ( $name && method_exists( $object, 'is_meta' ) && $object->is_meta( $field ) ) {
            return $object->get_meta( $post_id, $name, 'post' );
        }

        return null;
    }

    /**
     * The stored value of a user field (profile mode): the WP_User property for
     * the core fields, the user meta for the rest.
     *
     * @since WPUF_SINCE
     *
     * @param object $object  Field object
     * @param array  $field   Field settings
     * @param int    $user_id User id
     *
     * @return mixed
     */
    private function user_value_of( $object, array $field, $user_id ) {
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return null;
        }

        if ( isset( self::USER_PROPS[ $field['template'] ] ) ) {
            $prop = self::USER_PROPS[ $field['template'] ];

            return (string) $user->$prop;
        }

        if ( 'password' === $field['template'] ) {
            return '';
        }

        $name = isset( $field['name'] ) ? $field['name'] : '';

        if ( ! $name ) {
            return null;
        }

        if ( in_array( $field['template'], [ 'avatar', 'profile_photo', 'cover_photo' ], true ) ) {
            $id = Stores::users()->photo_id( $user_id, $field['template'], $name );

            return $id ? $id : '';
        }

        $value = Stores::users()->meta( $user_id, $name );

        return '' === $value ? null : $value;
    }

    /**
     * The countries a country, address or phone field offers (the field's own
     * show / hide lists applied, as the classic field does with JS).
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field settings
     *
     * @return array [ [ 'code', 'name' ], ... ]
     */
    private function country_choices( array $field ) {
        $list = isset( $field['country_list'] ) ? (array) $field['country_list'] : ( isset( $field['address']['country_select'] ) ? (array) $field['address']['country_select'] : [] );
        $hide = ! empty( $list['country_select_hide_list'] ) ? (array) $list['country_select_hide_list'] : [];
        $show = ! empty( $list['country_select_show_list'] ) ? (array) $list['country_select_show_list'] : [];
        $mode = isset( $list['country_list_visibility_opt_name'] ) ? $list['country_list_visibility_opt_name'] : 'all';
        $out  = [];

        foreach ( ( new \WeDevs\Wpuf\Data\Country_State() )->countries() as $code => $name ) {
            if ( 'hide' === $mode && in_array( $code, $hide, true ) ) {
                continue;
            }

            if ( 'show' === $mode && $show && ! in_array( $code, $show, true ) ) {
                continue;
            }

            $out[] = [
                'code' => (string) $code,
                'name' => (string) $name,
            ];
        }

        return $out;
    }

    /**
     * The text a block theme shows texturized: get_the_block_template_html()
     * runs wptexturize() over the whole rendered page, so the classic form's
     * text nodes (labels, option text, help, descriptions) reach the visitor
     * with curly quotes and dashes ("- Select -" shows as "– Select –").
     * The React form renders on the client, so the schema carries that text
     * already texturized; attributes (placeholders, values) are left alone,
     * as wptexturize() leaves them. Classic themes do not texturize there,
     * and neither does the profile form's markup in practice: it opens with an
     * inline script, after which the page-level wptexturize() skips the form,
     * so only post forms get this.
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field
     *
     * @return array
     */
    private function texturize( array $field ) {
        if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
            return $field;
        }

        // Plain text (React prints it as text): the texturized characters, not their entities.
        $plain = function ( $text ) {
            return html_entity_decode( wptexturize( $text ), ENT_QUOTES, 'UTF-8' );
        };

        // The multiselect placeholder is an attribute in the classic markup (data-placeholder), so it stays as typed.
        $keys = 'multiple_select' === $field['template'] ? [ 'label' ] : [ 'label', 'first', 'first_option' ];

        foreach ( $keys as $key ) {
            if ( isset( $field[ $key ] ) && is_string( $field[ $key ] ) && '' !== $field[ $key ] ) {
                $field[ $key ] = $plain( $field[ $key ] );
            }
        }

        // Markup (rendered as HTML): entities are fine.
        foreach ( [ 'label_html', 'help', 'description', 'html' ] as $key ) {
            if ( isset( $field[ $key ] ) && is_string( $field[ $key ] ) && '' !== $field[ $key ] ) {
                $field[ $key ] = wptexturize( $field[ $key ] );
            }
        }

        if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
            foreach ( $field['options'] as $value => $label ) {
                if ( is_string( $label ) ) {
                    $field['options'][ $value ] = $plain( $label );
                }
            }
        }

        if ( ! empty( $field['choices'] ) && is_array( $field['choices'] ) ) {
            foreach ( $field['choices'] as $index => $choice ) {
                if ( isset( $choice['name'] ) && is_string( $choice['name'] ) ) {
                    $field['choices'][ $index ]['name'] = $plain( $choice['name'] );
                }
            }
        }

        return $field;
    }

    /**
     * The field templates the React form shows as their classic markup.
     *
     * @since WPUF_SINCE
     *
     * @return string[]
     */
    private function server_rendered() {
        /**
         * Filters the field templates the React forms render as their classic
         * server markup (fields whose render carries state or scripts the app
         * does not rebuild). Their inputs post with the form.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $templates Templates
         */
        return (array) apply_filters( 'wpuf_frontend_server_rendered_fields', self::SERVER_RENDERED );
    }

    /**
     * The editor settings of a rich field, computed the way the classic
     * field calls wp_editor(): no quicktags, no media buttons, the toolbar
     * from `text_editor_control`, `wpuf_textarea_editor_args`, then the
     * teeny / full buttons through `teeny_mce_buttons` / `mce_buttons(_2)`
     * and the dark palette of layouts 2 and 5 (Frontend_Render_Form), so
     * the React editor matches and filters written for the classic one
     * still apply.
     *
     * @since WPUF_SINCE
     *
     * @param array $field   Field settings
     * @param int   $form_id Form id
     *
     * @return array { teeny, rows, quicktags, media_buttons, tinymce: {...} }
     */
    private function editor_settings( array $field, $form_id ) {
        $teeny     = 'teeny' === $field['rich'];
        $editor_id = $field['name'] . '_' . $form_id;
        // Post content's full editor gets no row count from the classic field (wp_editor() then uses default_post_edit_rows).
        $rows      = 'post_content' === $field['template'] && ! $teeny ? (int) Stores::settings()->wp_option( 'default_post_edit_rows', 20 ) : ( isset( $field['rows'] ) ? (int) $field['rows'] : 5 );
        $settings  = [
            'textarea_rows' => $rows,
            'quicktags'     => false,
            'media_buttons' => false,
            'teeny'         => $teeny,
        ];
        $toolbar   = wpuf_filter_editor_toolbar( $field );

        if ( ! empty( $field['read_only'] ) ) {
            $toolbar['readonly'] = true;
        }

        if ( $toolbar ) {
            $settings['tinymce'] = $toolbar;
        }

        /** This filter is documented in includes/Fields/Form_Field_Textarea.php */
        $settings = (array) apply_filters( 'wpuf_textarea_editor_args', $settings );
        $tinymce  = isset( $settings['tinymce'] ) && is_array( $settings['tinymce'] ) ? $settings['tinymce'] : [];
        $teeny    = ! empty( $settings['teeny'] );

        if ( empty( $tinymce['toolbar1'] ) ) {
            if ( $teeny ) {
                /** This filter is documented in wp-includes/class-wp-editor.php */
                $tinymce['toolbar1'] = implode( ',', (array) apply_filters( 'teeny_mce_buttons', [ 'bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'alignleft', 'aligncenter', 'alignright', 'undo', 'redo', 'link', 'fullscreen' ], $editor_id ) );
                $tinymce['toolbar2'] = '';
            } else {
                /** This filter is documented in wp-includes/class-wp-editor.php */
                $tinymce['toolbar1'] = implode( ',', (array) apply_filters( 'mce_buttons', [ 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ], $editor_id ) );
                /** This filter is documented in wp-includes/class-wp-editor.php */
                $tinymce['toolbar2'] = implode( ',', (array) apply_filters( 'mce_buttons_2', [ 'strikethrough', 'hr', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help' ], $editor_id ) );
            }
        }

        $layout = isset( $this->form_settings['form_layout'] ) ? $this->form_settings['form_layout'] : 'layout1';
        $theme  = isset( $this->form_settings['use_theme_css'] ) ? $this->form_settings['use_theme_css'] : '';

        if ( 'wpuf-theme-style' !== wpuf_form_style_class( $theme ) && in_array( $layout, [ 'layout2', 'layout5' ], true ) ) {
            $background               = 'layout2' === $layout ? '#222C3C' : '#394141';
            $style                    = sprintf( 'body.mceContentBody { background: %s; color: #ffffff; caret-color: #ffffff; }', $background );
            $tinymce['content_style'] = isset( $tinymce['content_style'] ) ? $tinymce['content_style'] . ' ' . $style : $style;
        }

        // Only scalar TinyMCE settings travel as JSON (callbacks cannot).
        $tinymce = array_filter(
            $tinymce,
            function ( $value ) {
                return is_scalar( $value );
            }
        );

        return [
            'teeny'         => $teeny,
            'rows'          => isset( $settings['textarea_rows'] ) ? (int) $settings['textarea_rows'] : 5,
            'quicktags'     => ! empty( $settings['quicktags'] ),
            'media_buttons' => ! empty( $settings['media_buttons'] ),
            'tinymce'       => $tinymce,
        ];
    }

    /**
     * Which editor a rich field gets in the React form: `tiptap` (the modern
     * editor) when every toolbar button the editor filters produced has a
     * TipTap control and the stored content has no HTML comment other than
     * `<!--more-->` / `<!--nextpage-->` (block editor markup); `tinymce`
     * (WordPress' editor, as the classic form) otherwise. The client still
     * checks that the content round-trips through TipTap unchanged and opens
     * TinyMCE when it does not.
     *
     * @since WPUF_SINCE
     *
     * @param array $field Described field (with `editor` and `value`)
     *
     * @return string tiptap | tinymce
     */
    private function editor_engine( array $field ) {
        $tinymce = isset( $field['editor']['tinymce'] ) ? (array) $field['editor']['tinymce'] : [];
        $buttons = [];

        foreach ( [ 'toolbar1', 'toolbar2', 'toolbar3', 'toolbar4' ] as $row ) {
            if ( ! empty( $tinymce[ $row ] ) && is_string( $tinymce[ $row ] ) ) {
                $buttons = array_merge( $buttons, array_filter( array_map( 'trim', explode( ',', $tinymce[ $row ] ) ) ) );
            }
        }

        $value   = isset( $field['value'] ) && is_string( $field['value'] ) ? $field['value'] : '';
        $unknown = array_diff( $buttons, self::TIPTAP_BUTTONS );
        $comment = '' !== $value && preg_match( '/<!--(?!\s*(?:more|nextpage)\b)/i', $value );
        $engine  = ( $unknown || $comment ) ? 'tinymce' : 'tiptap';

        /**
         * Filters the editor of a rich field in the React frontend form.
         *
         * @since WPUF_SINCE
         *
         * @param string $engine `tiptap` or `tinymce`
         * @param array  $field  Described field
         */
        $engine = apply_filters( 'wpuf_frontend_editor_engine', $engine, $field );

        return 'tinymce' === $engine ? 'tinymce' : 'tiptap';
    }

    /**
     * Which libraries the fields need, with the keys the client must have.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Described fields
     *
     * @return array
     */
    private function needs( array $fields ) {
        $needs = [];

        $walk = function ( array $list ) use ( &$walk, &$needs ) {
            foreach ( $list as $field ) {
                if ( isset( self::NEEDS[ $field['template'] ] ) && 'editor' !== self::NEEDS[ $field['template'] ] ) {
                    $needs[ self::NEEDS[ $field['template'] ] ] = true;
                }

                // Rich fields: TinyMCE for a `tinymce` field, and for a `tiptap` field
                // with stored content (the client falls back to TinyMCE when that
                // content does not round-trip); a new, empty TipTap field needs only
                // WordPress' small `editor` script (wpautop).
                if ( in_array( $field['template'], [ 'post_content', 'textarea_field' ], true ) && ! empty( $field['editor'] ) ) {
                    $engine = isset( $field['editor']['engine'] ) ? $field['editor']['engine'] : 'tinymce';
                    $value  = isset( $field['value'] ) && is_string( $field['value'] ) ? trim( $field['value'] ) : '';

                    if ( 'tinymce' === $engine || '' !== $value ) {
                        $needs['editor'] = true;
                    } else {
                        $needs['editor_lite'] = true;
                    }
                }

                if ( 'recaptcha' === $field['template'] ) {
                    $needs['recaptcha_type'] = isset( $field['recaptcha_type'] ) ? $field['recaptcha_type'] : '';
                }

                if ( 'column_field' === $field['template'] && ! empty( $field['inner_fields'] ) ) {
                    foreach ( (array) $field['inner_fields'] as $column_fields ) {
                        $walk( (array) $column_fields );
                    }
                }
            }
        };

        $walk( $fields );

        if ( ! empty( $needs['recaptcha'] ) ) {
            $needs['recaptcha_key'] = (string) Stores::settings()->get( 'wpuf_general', 'recaptcha_public' );
        }

        // Form_Field_Cloudflare_Turnstile prints nothing unless Turnstile is on and has a key.
        if ( ! empty( $needs['turnstile'] ) && wpuf_is_checkbox_or_toggle_on( Stores::settings()->get( 'wpuf_general', 'enable_turnstile', 'off' ) ) ) {
            $needs['turnstile_key'] = (string) Stores::settings()->get( 'wpuf_general', 'turnstile_site_key' );
        }

        if ( ! empty( $needs['maps'] ) ) {
            $needs['maps_key'] = (string) Stores::settings()->get( 'wpuf_general', 'gmap_api_key' );
        }

        return $needs;
    }

    /**
     * The texts the classic form script got from `wpuf_frontend` (Frontend.php)
     * that field components show: the password strength warnings. Same strings,
     * same text domain, so the translations stay.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function messages() {
        return [
            'password_warning_weak'   => __( 'Your password should be at least weak in strength', 'wp-user-frontend' ),
            'password_warning_medium' => __( 'Your password needs to be medium strength for better protection', 'wp-user-frontend' ),
            'password_warning_strong' => __( 'Create a strong password for maximum security', 'wp-user-frontend' ),
        ];
    }

    /**
     * The markup the printing hooks produce, in the order the classic
     * renderer fires them and with the same arguments.
     *
     * @since WPUF_SINCE
     *
     * @param Form  $form     Form
     * @param int   $form_id  Form id
     * @param int   $post_id  Post id (0 for a new post)
     * @param array $settings Settings
     *
     * @return array slot => html
     */
    private function slots( Form $form, $form_id, $post_id, array $settings ) {
        $capture = function ( callable $fire ) {
            ob_start();

            try {
                $fire();
            } finally {
                $html = ob_get_clean();
            }

            return (string) $html;
        };

        $fields = $this->form_fields;

        $slots = [
            'before_form' => $capture(
                function () use ( $form_id ) {
                    do_action( 'wpuf_before_form_render', $form_id );
                }
            ),
            'fields_top'  => $capture(
                function () use ( $form, $fields ) {
                    do_action( 'wpuf_form_fields_top', $form, $fields );
                }
            ),
            'form_top'    => $capture(
                function () use ( $form_id, $post_id, $settings ) {
                    if ( ! $post_id ) {
                        do_action( 'wpuf_add_post_form_top', $form_id, $settings );
                    } else {
                        do_action( 'wpuf_edit_post_form_top', $form_id, $post_id, $settings );
                    }
                }
            ),
            'submit'      => $capture(
                function () use ( $form_id, $settings ) {
                    do_action( 'wpuf_submit_btn', $form_id, $settings );
                }
            ),
            'form_bottom' => $capture(
                function () use ( $form_id, $post_id, $settings ) {
                    if ( ! $post_id ) {
                        do_action( 'wpuf_add_post_form_bottom', $form_id, $settings );
                    } else {
                        do_action( 'wpuf_edit_post_form_bottom', $form_id, $post_id, $settings );
                    }
                }
            ),
            'after_form'  => $capture(
                function () use ( $form_id ) {
                    do_action( 'wpuf_after_form_render', $form_id );
                }
            ),
        ];

        return array_filter( $slots, 'strlen' );
    }
}
