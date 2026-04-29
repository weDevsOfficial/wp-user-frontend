<?php

namespace WeDevs\Wpuf\TwoFactor;

use PragmaRX\Google2FA\Google2FA;
use WeDevs\WpUtils\ContainerTrait;

/**
 * Bootstrap for the 2FA subsystem
 *
 * Wires up methods, controllers, and storage. Accessed as
 * `wpuf()->two_factor` from the rest of the plugin. Free ships TOTP only;
 * Pro can register additional methods (Email/SMS OTP) by extending this
 * manager or hooking the `wpuf_2fa_register_methods` action.
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
        $totp         = new TOTP_Method( $google2fa, $storage, $rate_limiter );
        $qr           = new QR_Renderer();
        $registry     = new Method_Registry();

        $registry->register( $totp );

        $this->container['crypto']       = $crypto;
        $this->container['storage']      = $storage;
        $this->container['rate_limiter'] = $rate_limiter;
        $this->container['totp']         = $totp;
        $this->container['qr']           = $qr;
        $this->container['registry']     = $registry;

        $this->container['enrollment']   = new Enrollment_Controller( $totp, $storage, $qr );
        $this->container['login']        = new Login_Controller( $registry, $totp );
        $this->container['account']      = new Account_Section( $totp, $storage );

        // Admin_User_Profile registers `show_user_profile` / `edit_user_profile`
        // hooks; instantiate only in admin context so those hooks aren't
        // attached on front-end requests.
        if ( is_admin() ) {
            $this->container['admin_user'] = new Admin_User_Profile( $totp, $storage );
        }

        do_action( 'wpuf_2fa_register_methods', $registry );
    }
}
