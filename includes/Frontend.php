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
        global $post;

        $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        if ( wpuf_has_shortcode( 'wpuf-login' )
            || wpuf_has_shortcode( 'wpuf-registration' )
            || wpuf_has_shortcode( 'wpuf-meta' )
            || wpuf_has_shortcode( 'wpuf_form' )
            || wpuf_has_shortcode( 'wpuf_edit' )
            || wpuf_has_shortcode( 'wpuf_profile' )
            || wpuf_has_shortcode( 'wpuf_dashboard' )
            || wpuf_has_shortcode( 'weforms' )
            || wpuf_has_shortcode( 'wpuf_account' )
            || wpuf_has_shortcode( 'wpuf_sub_pack' )
            || ( isset( $post->ID ) && ( $pay_page == $post->ID ) )
            || isset( $_GET['wpuf_preview'] )
            || class_exists( '\Elementor\Plugin' )
            || $this->dokan_is_seller_dashboard() ) {
            wp_enqueue_style( 'wpuf-layout1' );
            wp_enqueue_style( 'wpuf-frontend-forms' );
            wp_enqueue_style( 'wpuf-sweetalert2' );
            wp_enqueue_style( 'wpuf-jquery-ui' );

            wp_enqueue_script( 'suggest' );
            wp_enqueue_script( 'wpuf-billing-address' );
            wp_enqueue_script( 'wpuf-upload' );
            wp_enqueue_script( 'wpuf-frontend-form' );
            wp_enqueue_script( 'wpuf-sweetalert2' );
            wp_enqueue_script( 'wpuf-subscriptions' );

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

            wp_localize_script(
                'wpuf-subscriptions', 'wpuf_subscription', apply_filters(
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
        }
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
            if ( ! in_array( $current_user->roles[0], $roles ) ) {
                return false;
            }
        }

        return $val;
    }
}
