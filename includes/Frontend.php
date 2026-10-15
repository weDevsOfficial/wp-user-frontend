<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Platform\Providers\FrontendServiceProvider;
use WeDevs\WpUtils\ContainerTrait;

/**
 * The class which will hold all the starting point of operations outside WordPress dashboard for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since 4.0.0
 */
class Frontend {
    use ContainerTrait;

    public function __construct() {
        // Every frontend service comes from the platform container
        // (FrontendServiceProvider): one instance per request, built here in the
        // old order so the constructors add their hooks as before.
        $platform = wpuf()->platform();

        foreach ( array_keys( FrontendServiceProvider::SERVICES ) as $key ) {
            $this->container[ $key ] = $platform->get( FrontendServiceProvider::class_of( $key ) );
        }

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Render-time fallbacks. Page builders and theme templates render WPUF
        // shortcodes from places wpuf_has_shortcode() cannot see (Elementor widget
        // data, Oxygen, do_shortcode() in a template), so load the bundle the moment
        // a WPUF shortcode or form actually renders. Scripts print in the footer,
        // styles go through print_late_styles(), so this is safe after wp_head.
        add_filter( 'pre_do_shortcode_tag', [ $this, 'enqueue_on_shortcode_render' ], 10, 3 );
        add_action( 'wpuf_before_form_render', [ $this, 'enqueue_form_assets' ] );

        // show admin bar as per wpuf settings
        add_filter( 'show_admin_bar', [ $this, 'show_admin_bar' ] );
        add_action( 'wp_head', [ $this, 'print_custom_css' ], 100 );
    }

    /**
     * Enqueue CSS and JS related to WPUF
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( $this->should_load_form_assets() && ! $this->mounts_the_forms() ) {
            $this->enqueue_form_assets();
        }

        // Enqueue account page Tailwind CSS and JS
        if ( wpuf_has_shortcode( 'wpuf_editprofile' ) || ( wpuf_has_shortcode( 'wpuf_account' ) && ! $this->renderer()->mounts( 'account' ) ) ) {
            $this->enqueue_account_assets();
        }
    }

    /**
     * The React or classic decision for this request.
     *
     * @since WPUF_SINCE
     *
     * @return \WeDevs\Wpuf\Frontend\Renderer
     */
    private function renderer() {
        return wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Renderer::class );
    }

    /**
     * Whether this request's `[wpuf-login]` screen renders as an app (the
     * logged-in view and a theme-overridden screen stay classic).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function login_renders_app() {
        $part = \WeDevs\Wpuf\Free\Simple_Login::screen_part();

        return '' !== $part && $this->renderer()->mounts( $part );
    }

    /**
     * Whether the only reason to load the classic form bundle on this page is
     * a post form the React app will render. Every other trigger (payment
     * page, preview, Dokan, the block, Elementor, the other shortcodes) keeps
     * the classic bundle; a form the render-time filter sends back to classic
     * gets the bundle from enqueue_on_shortcode_render().
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function mounts_the_forms() {
        global $post;

        if ( ! $this->renderer()->mounts( 'post_form' ) ) {
            return false;
        }

        $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        if ( ( isset( $post->ID ) && $pay_page === (int) $post->ID )
            || isset( $_GET['wpuf_preview'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            || $this->dokan_is_seller_dashboard()
            || ( isset( $post->post_content ) && has_block( 'wpuf/post-form', $post ) )
            || $this->elementor_needs_form_assets() ) {
            return false;
        }

        // The Pro registration / profile shortcode renders on React too.
        $mount_tags = [ 'wpuf_form', 'wpuf_edit', 'wpuf_account' ];

        if ( $this->renderer()->mounts( 'registration' ) ) {
            $mount_tags[] = 'wpuf_profile';
        }

        if ( $this->login_renders_app() ) {
            $mount_tags[] = 'wpuf-login';
        }

        foreach ( $this->get_asset_shortcodes() as $shortcode ) {
            if ( ! in_array( $shortcode, $mount_tags, true ) && wpuf_has_shortcode( $shortcode ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Shortcodes whose output needs the frontend form bundle.
     *
     * @since 4.3.12
     *
     * @return string[]
     */
    public function get_asset_shortcodes() {
        $shortcodes = [
            'wpuf-login',
            'wpuf-registration',
            'wpuf-meta',
            'wpuf_form',
            'wpuf_edit',
            'wpuf_profile',
            'wpuf_dashboard',
            'weforms',
            'wpuf_account',
            'wpuf_editprofile',
            'wpuf_sub_pack',
        ];

        /**
         * Filters the shortcodes that trigger loading of the WPUF frontend form assets.
         *
         * @since 4.3.12
         *
         * @param string[] $shortcodes Shortcode tags.
         */
        return apply_filters( 'wpuf_asset_shortcodes', $shortcodes );
    }

    /**
     * Decide whether the current request needs the frontend form bundle.
     *
     * Only pages that actually hold a WPUF shortcode, block, Elementor widget,
     * or one of the special WPUF pages get the bundle. Nothing else loads it.
     *
     * @since 4.3.12
     *
     * @return bool
     */
    public function should_load_form_assets() {
        global $post;

        $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        foreach ( $this->get_asset_shortcodes() as $shortcode ) {
            if ( wpuf_has_shortcode( $shortcode ) ) {
                return true;
            }
        }

        $should_load = ( isset( $post->ID ) && ( $pay_page === (int) $post->ID ) )
            || isset( $_GET['wpuf_preview'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            || $this->dokan_is_seller_dashboard()
            || ( isset( $post->post_content ) && has_block( 'wpuf/post-form', $post ) )
            || $this->elementor_needs_form_assets();

        /**
         * Filters whether the WPUF frontend form assets should load on this request.
         *
         * @since 4.3.12
         *
         * @param bool $should_load Whether to load the frontend form bundle.
         */
        return (bool) apply_filters( 'wpuf_should_load_form_assets', $should_load );
    }

    /**
     * Whether Elementor needs the form bundle on this request.
     *
     * True inside the editor preview iframe (widgets can be dropped in without a
     * reload) and on documents that contain a WPUF widget or shortcode. A plain
     * Elementor page loads nothing.
     *
     * @since 4.3.12
     *
     * @return bool
     */
    private function elementor_needs_form_assets() {
        if ( ! class_exists( '\Elementor\Plugin' ) || ! did_action( 'elementor/loaded' ) ) {
            return false;
        }

        $elementor = \Elementor\Plugin::$instance;

        if ( isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) && $elementor->preview->is_preview_mode() ) {
            return true;
        }

        return $this->elementor_document_has_wpuf();
    }

    /**
     * Whether the Elementor document of the current post contains a WPUF widget or shortcode.
     *
     * Reads the raw `_elementor_data` JSON (a single cached meta read) and looks
     * for a `wpuf-*` widget type or a `[wpuf` shortcode inside any widget.
     *
     * @since 4.3.12
     *
     * @param int $post_id Post ID. Defaults to the queried post.
     *
     * @return bool
     */
    public function elementor_document_has_wpuf( $post_id = 0 ) {
        static $cache = [];

        $post_id = $post_id ? (int) $post_id : (int) get_the_ID();

        if ( ! $post_id ) {
            return false;
        }

        if ( isset( $cache[ $post_id ] ) ) {
            return $cache[ $post_id ];
        }

        $data = get_post_meta( $post_id, '_elementor_data', true );

        if ( ! is_string( $data ) || '' === $data ) {
            $cache[ $post_id ] = false;

            return false;
        }

        $cache[ $post_id ] = ( false !== strpos( $data, '"widgetType":"wpuf-' ) )
            || ( false !== strpos( $data, '[wpuf' ) )
            || ( false !== strpos( $data, '[weforms' ) );

        return $cache[ $post_id ];
    }

    /**
     * Load the form bundle when a WPUF shortcode is rendered.
     *
     * Hooked to `pre_do_shortcode_tag`; never changes the shortcode output.
     *
     * @since 4.3.12
     *
     * @param false|string $output Short-circuit return value.
     * @param string       $tag    Shortcode tag.
     *
     * @return false|string
     */
    public function enqueue_on_shortcode_render( $output, $tag, $attr = [] ) {
        // This runs before the handler (pre_do_shortcode_tag): a shortcode the
        // React app is about to render gets its assets from Renderer,
        // not the classic bundle. Same decision, same inputs as the handler.
        $renderer = $this->renderer();
        $atts     = is_array( $attr ) ? $attr : [];

        if ( 'wpuf_form' === $tag && $renderer->mounts( 'post_form' ) ) {
            return $output;
        }

        if ( 'wpuf_edit' === $tag && $renderer->mounts( 'edit_form' ) ) {
            return $output;
        }

        if ( 'wpuf_account' === $tag && is_user_logged_in() && $renderer->mounts( 'account' ) ) {
            return $output;
        }

        if ( 'wpuf-login' === $tag && $this->login_renders_app() ) {
            return $output;
        }

        if ( 'wpuf_profile' === $tag ) {
            $part = isset( $atts['type'] ) && 'profile' === $atts['type'] ? 'profile' : 'registration';

            if ( $renderer->mounts( $part ) ) {
                return $output;
            }
        }

        if ( in_array( $tag, $this->get_asset_shortcodes(), true ) ) {
            $this->enqueue_form_assets();
        }

        if ( 'wpuf_account' === $tag || 'wpuf_editprofile' === $tag ) {
            $this->enqueue_account_assets();
        }

        return $output;
    }

    /**
     * Enqueue the account page CSS and JS.
     *
     * @since 4.3.12
     *
     * @return void
     */
    public function enqueue_account_assets() {
        wp_enqueue_style( 'wpuf-account' );
        wp_enqueue_script( 'wpuf-account' );
    }

    /**
     * Enqueue the frontend form bundle and its localized data.
     *
     * Safe to call more than once per request; the second call is a no-op.
     * Fires `wpuf_enqueue_form_assets` so Pro and add-ons can attach their own
     * form assets exactly when the free bundle loads.
     *
     * @since 4.3.12
     *
     * @return void
     */
    public function enqueue_form_assets() {
        // The schema of a React form fires the render hooks this method listens to; the classic bundle stays off then.
        if ( $this->renderer()->building() ) {
            return;
        }

        global $post;

        // Once per request: the queue itself is the flag (a static survived across PHPUnit tests).
        if ( wp_script_is( 'wpuf-frontend-form', 'enqueued' ) ) {
            return;
        }

        wp_enqueue_style( 'wpuf-layout1' );
        wp_enqueue_style( 'wpuf-frontend-forms' );
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_style( 'wpuf-jquery-ui' );

        wp_enqueue_script( 'suggest' );
        wp_enqueue_script( 'wpuf-billing-address' );
        wp_enqueue_script( 'wpuf-upload' );
        wp_enqueue_script( 'wpuf-frontend-form' );
        wp_enqueue_script( 'wpuf-sweetalert2' );

        // Load appropriate subscription script based on shortcode
        if ( wpuf_has_shortcode( 'wpuf_sub_pack' ) ) {
            // Skip loading frontend-subscriptions CSS on Elementor pages (Elementor widget has its own CSS)
            $is_elementor_page = did_action( 'elementor/loaded' ) && isset( $post->ID ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $post->ID );
            if ( ! $is_elementor_page ) {
                wp_enqueue_style( 'wpuf-frontend-subscriptions' );
            }
            wp_enqueue_script( 'wpuf-frontend-subscriptions' );
        } else {
            // Load old subscriptions script for all other pages (dashboard, account, etc.)
            wp_enqueue_script( 'wpuf-subscriptions' );
        }

        wp_localize_script(
            'wpuf-upload', 'wpuf_upload', [
                'confirmMsg' => __( 'Are you sure?', 'wp-user-frontend' ),
                'delete_it'  => __( 'Yes, delete it', 'wp-user-frontend' ),
                'cancel_it'  => __( 'No, cancel it', 'wp-user-frontend' ),
                'rest'        => wpuf_rest_script_data(),
                'upload_nonce' => wp_create_nonce( 'wpuf-upload-nonce' ),
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
                'plupload'   => [
                    'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce(
                        'wpuf-upload-nonce'
                    ),
                    'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                    'filters'          => [
                        [
                            'title'      => __( 'Allowed Files', 'wp-user-frontend' ),
                            'extensions' => '*',
                        ],
                    ],
                    'multipart'        => true,
                    'urlstream_upload' => true,
                    'warning'          => __( 'Maximum number of files reached!', 'wp-user-frontend' ),
                    'size_error'       => __(
                        'The file you have uploaded exceeds the file size limit. Please try again.',
                        'wp-user-frontend'
                    ),
                    'type_error'       => __(
                        'You have uploaded an incorrect file type. Please try again.', 'wp-user-frontend'
                    ),
                ],
            ]
        );
        wp_localize_script(
            'wpuf-frontend-form', 'wpuf_frontend', apply_filters(
                'wpuf_frontend_object', [
                    'asset_url'                    => WPUF_ASSET_URI,
                    'rest'                          => wpuf_rest_script_data(),
                    'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
                    'error_message'                => __( 'Please fix the errors to proceed', 'wp-user-frontend' ),
                    'nonce'                        => wp_create_nonce( 'wpuf_nonce' ),
                    'word_limit'                   => __( 'Word limit reached', 'wp-user-frontend' ),
                    'cancelSubMsg'                 => __(
                        'Are you sure you want to cancel your current subscription ?', 'wp-user-frontend'
                    ),
                    'delete_it'                    => __( 'Yes', 'wp-user-frontend' ),
                    'cancel_it'                    => __( 'No', 'wp-user-frontend' ),
                    'word_max_title'               => __(
                        'Maximum word limit reached. Please shorten your texts.', 'wp-user-frontend'
                    ),
                    'word_max_details'             => __(
                        'This field supports a maximum of %number% words, and the limit is reached. Remove a few words to reach the acceptable limit of the field.',
                        'wp-user-frontend'
                    ),
                    'word_min_title'               => __( 'Minimum word required.', 'wp-user-frontend' ),
                    'word_min_details'             => __(
                        'This field requires minimum %number% words. Please add some more text.', 'wp-user-frontend'
                    ),
                    'char_max_title'               => __(
                        'Maximum character limit reached. Please shorten your texts.', 'wp-user-frontend'
                    ),
                    'char_max_details'             => __(
                        'This field supports a maximum of %number% characters, and the limit is reached. Remove a few characters to reach the acceptable limit of the field.',
                        'wp-user-frontend'
                    ),
                    'char_min_title'               => __( 'Minimum character required.', 'wp-user-frontend' ),
                    'char_min_details'             => __(
                        'This field requires minimum %number% characters. Please add some more character.',
                        'wp-user-frontend'
                    ),
                    'protected_shortcodes'         => wpuf_get_protected_shortcodes(),
                    // translators: %shortcode% is the shortcode name
                    'protected_shortcodes_message' => __( 'Using %shortcode% is restricted', 'wp-user-frontend' ),
                    'password_warning_weak'        => __( 'Your password should be at least weak in strength', 'wp-user-frontend' ),
                    'password_warning_medium'      => __( 'Your password needs to be medium strength for better protection', 'wp-user-frontend' ),
                    'password_warning_strong'      => __( 'Create a strong password for maximum security', 'wp-user-frontend' ),
                    // translators: %step% is the step number
                    'step_label'                   => __( 'Step %step%', 'wp-user-frontend' ),
                    // translators: %step% is the current step number, %total% is the total number of steps
                    'step_progress'                => __( 'Step %step% of %total%', 'wp-user-frontend' ),
                ]
            )
        );
        wp_localize_script(
            'wpuf-frontend-form', 'error_str_obj', [
                'required'   => __( 'is required', 'wp-user-frontend' ),
                'mismatch'   => __( 'does not match', 'wp-user-frontend' ),
                'validation' => __( 'is not valid', 'wp-user-frontend' ),
            ]
        );

        // Localize subscription script data for whichever script is loaded
        $subscription_script_handle = wpuf_has_shortcode( 'wpuf_sub_pack' ) ? 'wpuf-frontend-subscriptions' : 'wpuf-subscriptions';
        wp_localize_script(
            $subscription_script_handle, 'wpuf_subscription', apply_filters(
                'wpuf_subscription_js_data', [
                    'pack_notice'  => __( 'Please Cancel Your Currently Active Pack first!', 'wp-user-frontend' ),
                ]
            )
        );

        wp_localize_script(
            'wpuf-billing-address',
            'ajax_object',
            [
                'rest'         => wpuf_rest_script_data(),
                'ajaxurl'     => admin_url( 'admin-ajax.php' ),
                'fill_notice' => __( 'Some Required Fields are not filled!', 'wp-user-frontend' ),
            ]
        );

        /**
         * Fires right after the WPUF frontend form bundle has been enqueued.
         *
         * Pro and add-ons hook here to load their own form assets, so they
         * follow the same on-demand decision as the free bundle.
         *
         * @since 4.3.12
         */
        do_action( 'wpuf_enqueue_form_assets' );
    }

    /**
     * Check if this is a dokan seller dashboard page
     *
     * @since 4.0.0
     *
     * @return bool
     */
    private function dokan_is_seller_dashboard() {
        return class_exists( 'WeDevs_Dokan' )
                && function_exists( 'dokan_is_seller_dashboard' )
                && dokan_is_seller_dashboard();
    }

    /**
     * Settings > General > "Custom CSS codes": printed in the page head in a
     * style tag, as the setting says (the old output line had been commented
     * out since 2018, so the setting did nothing). Only when it has a value.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function print_custom_css() {
        $css = trim( wp_strip_all_tags( (string) wpuf_get_option( 'custom_css', 'wpuf_general', '' ) ) );

        if ( '' === $css ) {
            return;
        }

        // Admin-entered CSS (manage options); `</` could still close the tag.
        $css = str_replace( '</', '<\\/', $css );

        echo "<style id=\"wpuf-custom-css\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS context: tags stripped, `</` neutralised.
    }

    /**
     * Show/hide admin bar to the permitted user level
     *
     * @since 2.2.3
     *
     * @return bool
     */
    public function show_admin_bar( $val ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $roles        = wpuf_get_option( 'show_admin_bar', 'wpuf_general', [ 'administrator', 'editor', 'author', 'contributor', 'subscriber' ] );
        $roles        = $roles && is_string( $roles ) ? [ strtolower( $roles ) ] : $roles;
        $current_user = wp_get_current_user();

        if ( ! empty( $current_user->roles ) && ! empty( $current_user->roles[0] ) ) {
            if ( ! in_array( $current_user->roles[0], $roles, true ) ) {
                return false;
            }
        }

        return $val;
    }
}
