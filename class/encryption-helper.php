<?php

class WPUF_Encryption_Helper {

    public static function get_encryption_method() {
        return 'AES-256-CBC';
    }

    public static function get_encryption_nonce_length() {
        return function_exists( 'sodium_crypto_secretbox' ) ? SODIUM_CRYPTO_SECRETBOX_NONCEBYTES : openssl_cipher_iv_length( self::get_encryption_method() );
    }

    public static function get_encryption_key_length() {
        return function_exists( 'sodium_crypto_secretbox' ) ? SODIUM_CRYPTO_SECRETBOX_KEYBYTES : 32;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function get_encryption_auth_keys() {
        $defaults = [
            'auth_key'          => '',
            'auth_salt'         => '',
        ];
        $auth_keys = get_option( 'wpuf_auth_keys', $defaults );

        if ( empty( $auth_keys['auth_key'] ) || empty( $auth_keys['auth_salt'] ) ) {
            // check for saved key
            $key = random_bytes( self::get_encryption_key_length() );
            $auth_keys['auth_key'] = base64_encode( $key );

            // check for saved nonce
            $nonce = random_bytes( self::get_encryption_nonce_length() );
            $auth_keys['auth_salt'] = base64_encode( $nonce );

            update_option( 'wpuf_auth_keys', $auth_keys );
        }

        return [
            'auth_key'  => base64_decode( $auth_keys['auth_key'] ),
            'auth_salt' => base64_decode( $auth_keys['auth_salt'] )
        ];
    }
}
