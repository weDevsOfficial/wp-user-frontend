<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Registry of available 2FA methods
 *
 * Methods register themselves here; the login and enrollment flows look
 * up handlers by ID. This is what makes Email/SMS OTP additions a
 * register-and-go change rather than a controller rewrite.
 *
 * @since WPUF_SINCE
 */
class Method_Registry {

    /**
     * @var Method_Interface[] Keyed by method ID.
     */
    private $methods = [];

    public function register( Method_Interface $method ) {
        $this->methods[ $method->get_id() ] = $method;
    }

    /**
     * @return Method_Interface[]
     */
    public function all() {
        return $this->methods;
    }

    /**
     * @param string $id
     *
     * @return Method_Interface|null
     */
    public function get( $id ) {
        return isset( $this->methods[ $id ] ) ? $this->methods[ $id ] : null;
    }

    /**
     * Methods that the admin has enabled in Global Settings (active_2fa_methods).
     *
     * @return Method_Interface[]
     */
    public function active() {
        $active_ids = wpuf_get_option( 'active_2fa_methods', 'wpuf_2fa', [] );

        if ( ! is_array( $active_ids ) ) {
            $active_ids = [];
        }

        $active = [];
        foreach ( $active_ids as $id ) {
            if ( isset( $this->methods[ $id ] ) ) {
                $active[ $id ] = $this->methods[ $id ];
            }
        }

        return $active;
    }

    /**
     * Methods this user has actually completed enrollment for.
     *
     * @param int $user_id
     *
     * @return Method_Interface[]
     */
    public function enrolled_for( $user_id ) {
        $enrolled = [];
        foreach ( $this->methods as $id => $method ) {
            if ( $method->is_enrolled( $user_id ) ) {
                $enrolled[ $id ] = $method;
            }
        }

        return $enrolled;
    }
}
