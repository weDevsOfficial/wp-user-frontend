<?php
/**
 * The WPUF_Encryption_Helper Class to handle the encryption
 */

class WPUF_Encryption_Helper {

    /**
     * Get the Advanced Encryption Standard we are using
     *
     * @since 3.5.29
     *
     * @return string
     */
    public static function get_encryption_method() {
        return 'AES-256-CBC';
    }

    /**
     * Get the nonce length for the encryption
     * Returns 24 If PHP version is 7.2 or above.
     * For PHP version below 7.2 it will send the length as per the encryption method.
     *
     * @since 3.5.29
     *
     * @return int|bool
     */
    public static function get_encryption_nonce_length() {
        return function_exists( 'sodium_crypto_secretbox' ) ? SODIUM_CRYPTO_SECRETBOX_NONCEBYTES : openssl_cipher_iv_length( self::get_encryption_method() );
    }

    /**
     * Get the encryption key length. Defaults to 32
     *
     * @since 3.5.29
     *
     * @return int
     */
    public static function get_encryption_key_length() {
        return function_exists( 'sodium_crypto_secretbox' ) ? SODIUM_CRYPTO_SECRETBOX_KEYBYTES : 32;
    }

    /**
     * Get the base64 encoded auth keys
     *
     * @since 3.5.29
     *
     * @return array
     * @throws Exception
     */
    public static function get_encryption_auth_keys() {
        $defaults = [
            'auth_key'  => '',
            'auth_salt' => '',
        ];
        $auth_keys = get_option( 'wpuf_auth_keys', $defaults );

        if ( empty( $auth_keys['auth_key'] ) || empty( $auth_keys['auth_salt'] ) ) {
            // check for saved key
            $key                   = random_bytes( self::get_encryption_key_length() );
            $auth_keys['auth_key'] = base64_encode( $key );    // phpcs:ignore

            // check for saved nonce
            $nonce                  = random_bytes( self::get_encryption_nonce_length() );
            $auth_keys['auth_salt'] = base64_encode( $nonce );    // phpcs:ignore

            update_option( 'wpuf_auth_keys', $auth_keys );
        }

        return [
            'auth_key'  => base64_decode( $auth_keys['auth_key'] ),    // phpcs:ignore
            'auth_salt' => base64_decode( $auth_keys['auth_salt'] ),    // phpcs:ignore
        ];
    }
}
