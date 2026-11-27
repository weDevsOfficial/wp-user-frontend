<?php

namespace WeDevs\Wpuf\TwoFactor;

use PragmaRX\Google2FA\Google2FA;
use WeDevs\WpUtils\ContainerTrait;

/**
 * Bootstrap for the 2FA subsystem
 *
 * Wires up the registry, storage, controllers, and the built-in TOTP
 * method. Accessed as `wpuf()->two_factor` from the rest of the plugin.
 *
 * Free ships TOTP as the only built-in method. Pro and third parties
 * register additional methods (Email OTP, SMS OTP, etc.) by hooking
 * `wpuf_2fa_register_methods`.
 *
 * @since WPUF_SINCE
 */
class Two_Factor_Manager {
    use ContainerTrait;

    public function __construct() {
        $crypto       = new Crypto();
        $storage      = new User_Storage( $crypto );
        $rate_limiter = new Rate_Limiter();
        $google2fa    = new Google2FA();
        $qr           = new QR_Renderer();
        $registry     = new Method_Registry();

        // Built-in: TOTP. Registered immediately so the
        // `wpuf_2fa_register_methods` hook (fired below) only sees it
        // alongside any externally-registered methods, not before them.
        $registry->register( new TOTP_Method( $google2fa, $storage, $rate_limiter, $qr ) );

        add_filter( 'wpuf_settings_sections', [ $this, 'add_settings_section' ] );
        add_filter( 'wpuf_settings_fields', [ $this, 'add_settings_fields' ] );

        $this->container['crypto']       = $crypto;
        $this->container['storage']      = $storage;
        $this->container['rate_limiter'] = $rate_limiter;
        $this->container['qr']           = $qr;
        $this->container['registry']     = $registry;

        // Default consumer for `wpuf_2fa_security_card_html`. Registered
        // before user-land hooks so Pro / third parties can override at
        // a later priority.
        $this->container['default_card'] = new Default_Card_Renderer();

        $this->container['enrollment'] = new Enrollment_Controller( $registry, $storage );
        $this->container['login']      = new Login_Controller( $registry );
        $this->container['account']    = new Account_Section( $registry, $storage );

        // Admin_User_Profile registers admin-only hooks; instantiate
        // only in admin context so those hooks aren't attached on
        // front-end requests.
        if ( is_admin() ) {
            $this->container['admin_user'] = new Admin_User_Profile( $registry, $storage );
        }

        // Pass storage + rate_limiter alongside the registry so
        // consumers can construct their methods without going through
        // `wpuf()->two_factor` — which is still null at this point
        // because the manager's instance is mid-construction and hasn't
        // been assigned to free's container yet.
        do_action( 'wpuf_2fa_register_methods', $registry, $storage, $rate_limiter );

        // Post-registration inspection hook. Read-only — to add or
        // remove methods, hook `wpuf_2fa_register_methods` instead.
        // Useful for plugins that want to log the final method list,
        // or for tests asserting on registration.
        do_action( 'wpuf_2fa_methods', $registry->all() );
    }

    /**
     * Add the 2FA tab after Login / Registration on User Frontend > Settings
     *
     * @since WPUF_SINCE
     *
     * @param array $sections Settings sections.
     *
     * @return array
     */
    public function add_settings_section( $sections ) {
        $section = [
            'id'    => 'wpuf_2fa',
            'title' => __( '2FA', 'wp-user-frontend' ),
            'icon'  => 'dashicons-id',
        ];

        $position = array_search( 'wpuf_profile', wp_list_pluck( $sections, 'id' ), true );

        if ( false === $position ) {
            $sections[] = $section;

            return $sections;
        }

        array_splice( $sections, $position + 1, 0, [ $section ] );

        return $sections;
    }

    /**
     * Add the 2FA settings fields
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Settings fields keyed by section.
     *
     * @return array
     */
    public function add_settings_fields( $fields ) {
        $fields['wpuf_2fa'] = apply_filters(
            'wpuf_options_2fa',
            [
                [
                    'name'    => 'enable_2fa',
                    'label'   => __( 'Enable Two-Factor Authentication', 'wp-user-frontend' ),
                    'desc'    => __( 'When enabled, users can secure their accounts with a second verification step.', 'wp-user-frontend' ),
                    'type'    => 'toggle',
                    'default' => 'off',
                ],
                [
                    'name'       => 'active_2fa_methods',
                    'label'      => __( '2FA Methods', 'wp-user-frontend' ),
                    'desc'       => __( 'Select which methods users can use to verify their identity.', 'wp-user-frontend' ),
                    'type'       => 'method_selector',
                    'options'    => wpuf_get_2fa_methods(),
                    'depends_on' => 'enable_2fa',
                ],
                [
                    'name'       => 'totp_issuer_label',
                    'label'      => __( 'App Display Name', 'wp-user-frontend' ),
                    'desc'       => __( "Name shown inside the user's authenticator app (e.g., Google Authenticator).", 'wp-user-frontend' ),
                    'type'       => 'text',
                    'default'    => get_bloginfo( 'name' ),
                    'depends_on' => 'enable_2fa',
                ],
            ]
        );

        return $fields;
    }
}
