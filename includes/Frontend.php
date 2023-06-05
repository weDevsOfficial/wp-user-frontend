<?php

namespace Wp\User\Frontend;

/**
 * The class which will hold all the starting point of operations outside WordPress dashboard for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since WPUF_SINCE
 */

class Frontend {
    public function __construct() {
        wpuf()->add_to_container( 'frontend_form', new Frontend\Frontend_Form() );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueue CSS and JS related to WPUF
     *
     * @since WPUF_SINCE
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
                'wpuf-upload', 'wpuf_frontend_upload', [
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
                'wpuf-frontend-form', 'wpuf_frontend', [
                    'ajaxurl'       => admin_url( 'admin-ajax.php' ),
                    'error_message' => __( 'Please fix the errors to proceed', 'wp-user-frontend' ),
                    'nonce'         => wp_create_nonce( 'wpuf_nonce' ),
                    'word_limit'    => __( 'Word limit reached', 'wp-user-frontend' ),
                    'cancelSubMsg'  => __(
                        'Are you sure you want to cancel your current subscription ?', 'wp-user-frontend'
                    ),
                    'delete_it'     => __( 'Yes', 'wp-user-frontend' ),
                    'cancel_it'     => __( 'No', 'wp-user-frontend' ),
                ]
            );
        }
    }

    /**
     * Check if this is a dokan seller dashboard page
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function dokan_is_seller_dashboard() {
        return class_exists( 'WeDevs_Dokan' )
                && function_exists( 'dokan_is_seller_dashboard' )
                && dokan_is_seller_dashboard()
                && ! empty( $wp->query_vars['posts'] );
    }
}
