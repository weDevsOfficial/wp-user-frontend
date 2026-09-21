<?php

namespace WeDevs\Wpuf;

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
        $this->container['frontend_form']      = new Frontend\Frontend_Form();
        $this->container['registration']       = new Frontend\Registration();
        $this->container['simple_login']       = new Free\Simple_Login();
        $this->container['frontend_account']   = new Frontend\Frontend_Account();
        $this->container['frontend_dashboard'] = new Frontend\Frontend_Dashboard();
        $this->container['shortcode']          = new Frontend\Shortcode();
        $this->container['payment']            = new Frontend\Payment();
        $this->container['form_preview']       = new Frontend\Form_Preview();

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Render-time fallbacks. Page builders and theme templates render WPUF
        // shortcodes from places wpuf_has_shortcode() cannot see (Elementor widget
        // data, Oxygen, do_shortcode() in a template), so load the bundle the moment
        // a WPUF shortcode or form actually renders. Scripts print in the footer,
        // styles go through print_late_styles(), so this is safe after wp_head.
        add_filter( 'pre_do_shortcode_tag', [ $this, 'enqueue_on_shortcode_render' ], 10, 2 );
        add_action( 'wpuf_before_form_render', [ $this, 'enqueue_form_assets' ] );

        // show admin bar as per wpuf settings
        add_filter( 'show_admin_bar', [ $this, 'show_admin_bar' ] );
    }

    /**
     * Enqueue CSS and JS related to WPUF
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( $this->should_load_form_assets() ) {
            $this->enqueue_form_assets();
        }

        // Enqueue account page Tailwind CSS and JS
        if ( wpuf_has_shortcode( 'wpuf_account' ) || wpuf_has_shortcode( 'wpuf_editprofile' ) ) {
            $this->enqueue_account_assets();
        }
    }

    /**
     * Shortcodes whose output needs the frontend form bundle.
     *
     * @since WPUF_SINCE
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
         * @since WPUF_SINCE
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
     * @since WPUF_SINCE
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
         * @since WPUF_SINCE
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
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
     *
     * @param false|string $output Short-circuit return value.
     * @param string       $tag    Shortcode tag.
     *
     * @return false|string
     */
    public function enqueue_on_shortcode_render( $output, $tag ) {
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
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_form_assets() {
        global $post;

        static $enqueued = false;

        if ( $enqueued ) {
            return;
        }

        $enqueued = true;

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
         * @since WPUF_SINCE
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
