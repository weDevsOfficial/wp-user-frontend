<?php

namespace WeDevs\Wpuf\Frontend;

class Shortcode {
    public function __construct() {
        add_action( 'init', [ $this, 'init_shortcode' ] );
    }

    /**
     * initialize the WPUF shortcodes
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function init_shortcode() {
        add_shortcode( 'wpuf_dashboard', [ wpuf()->frontend->frontend_dashboard, 'shortcode' ] );
        add_shortcode( 'wpuf-registration', [ wpuf()->frontend->registration, 'registration_form' ] );
        add_shortcode( 'wpuf_form', [ wpuf()->frontend->frontend_form, 'add_post_shortcode' ] );
        add_shortcode( 'wpuf_edit', [ wpuf()->frontend->frontend_form, 'edit_post_shortcode' ] );
        add_shortcode( 'wpuf_editprofile', [ wpuf()->frontend->frontend_account, 'shortcode' ] );
        add_shortcode( 'wpuf_account', [ wpuf()->frontend->frontend_account, 'shortcode' ] );
        add_shortcode( 'wpuf-login', [ wpuf()->frontend->simple_login, 'login_form' ] );
        add_shortcode( 'wpuf_sub_info', [ wpuf()->subscription, 'subscription_info' ] );
        add_shortcode( 'wpuf_sub_pack', [ wpuf()->subscription, 'subscription_packs' ] );
        add_shortcode( 'wpuf-edit-users', 'wpuf_edit_users' );
        add_shortcode( 'wpuf-meta', 'wpuf_meta_shortcode' );

        // Pro's shortcodes left in pages after Pro is switched off would print
        // their raw tag and, for the restriction ones, the protected text.
        if ( ! wpuf_is_pro_active() ) {
            foreach ( [ 'wpuf_partial_restriction', 'wpuf_restrict' ] as $tag ) {
                if ( ! shortcode_exists( $tag ) ) {
                    add_shortcode( $tag, [ $this, 'pro_restriction_fallback' ] );
                }
            }

            if ( ! shortcode_exists( 'wpuf_profile' ) ) {
                add_shortcode( 'wpuf_profile', [ $this, 'pro_profile_fallback' ] );
            }
        }
    }

    /**
     * Stand-in for Pro's content restriction shortcodes while Pro is off:
     * the protected text stays hidden (site admins still see it).
     *
     * @since WPUF_SINCE
     *
     * @param array|string $atts    Shortcode attributes
     * @param string|null  $content Protected content
     *
     * @return string
     */
    public function pro_restriction_fallback( $atts, $content = null ) {
        unset( $atts );

        if ( current_user_can( 'manage_options' ) ) {
            return do_shortcode( (string) $content );
        }

        return '<div class="wpuf-info wpuf-restrict-message">' . esc_html__( 'This content is available to members only.', 'wp-user-frontend' ) . '</div>';
    }

    /**
     * Stand-in for Pro's registration / profile form shortcode while Pro is off.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function pro_profile_fallback() {
        $message = current_user_can( 'manage_options' )
            ? __( 'This registration or profile form needs WP User Frontend Pro, which is not active.', 'wp-user-frontend' )
            : __( 'This form is not available right now.', 'wp-user-frontend' );

        return '<div class="wpuf-info">' . esc_html( $message ) . '</div>';
    }
}
