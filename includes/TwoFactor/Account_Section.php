<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Adds the "Security" tab to [wpuf_account]
 *
 * Registers the section via `wpuf_account_sections` filter and renders
 * its content via `wpuf_account_content_security` action — same pattern
 * Frontend_Account uses for its built-in tabs.
 *
 * The template iterates over `Method_Registry::active()` and renders
 * each method's card through the `wpuf_2fa_security_card_html` filter.
 * Methods (or Pro extensions) hook that filter to provide their own
 * markup; this class doesn't know what TOTP looks like.
 *
 * @since WPUF_SINCE
 */
class Account_Section {

    public const SECTION_ID = 'security';

    /** @var Method_Registry */
    private $registry;

    /** @var User_Storage */
    private $storage;

    public function __construct( Method_Registry $registry, User_Storage $storage ) {
        $this->registry = $registry;
        $this->storage  = $storage;

        add_filter( 'wpuf_account_sections', [ $this, 'register_section' ] );
        add_action( 'wpuf_account_content_' . self::SECTION_ID, [ $this, 'render_section' ], 10, 2 );
    }

    public function register_section( $sections ) {
        if ( ! $this->is_2fa_enabled_globally() ) {
            return $sections;
        }

        if ( ! is_array( $sections ) ) {
            $sections = (array) $sections;
        }

        $sections[ self::SECTION_ID ] = __( 'Security', 'wp-user-frontend' );

        return $sections;
    }

    public function render_section( $sections, $current_section ) {
        if ( ! $this->is_2fa_enabled_globally() ) {
            return;
        }

        $user_id = get_current_user_id();
        $methods = $this->registry->active();

        wp_enqueue_script( 'wpuf-2fa-account' );

        wpuf_load_template(
            'dashboard/security.php',
            [
                'sections'        => $sections,
                'current_section' => $current_section,
                'user_id'         => $user_id,
                'methods'         => $methods,
                'storage'         => $this->storage,
                'nonce'           => wp_create_nonce( Enrollment_Controller::NONCE_ACTION ),
                'ajax_url'        => admin_url( 'admin-ajax.php' ),
            ]
        );
    }

    private function is_2fa_enabled_globally(): bool {
        if ( wpuf_get_option( 'enable_2fa', 'wpuf_2fa', 'off' ) !== 'on' ) {
            return false;
        }

        $active = wpuf_get_option( 'active_2fa_methods', 'wpuf_2fa', [] );

        return is_array( $active ) && ! empty( $active );
    }
}
