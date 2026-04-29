<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Symmetric encryption for 2FA secrets at rest
 *
 * Uses AES-256-GCM with a key derived from AUTH_KEY. If AUTH_KEY rotates,
 * stored ciphertexts become unreadable and affected users must re-enroll —
 * documented behavior in docs/totp.md.
 *
 * @since WPUF_SINCE
 */
class Crypto {

    const CIPHER          = 'aes-256-gcm';
    const STORAGE_VERSION = 'v1';

    /**
     * Encrypt a plaintext secret. Returns "v1.iv.tag.ciphertext" (all base64).
     *
     * @param string $plaintext
     *
     * @return string
     */
    public function encrypt( $plaintext ) {
        $iv         = random_bytes( openssl_cipher_iv_length( self::CIPHER ) );
        $tag        = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->derive_key(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        // base64 here is binary-to-text encoding for storage, not obfuscation.
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        return self::STORAGE_VERSION
            . '.' . base64_encode( $iv ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
            . '.' . base64_encode( $tag ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
            . '.' . base64_encode( $ciphertext ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    /**
     * Decrypt a value produced by encrypt(). Returns null on failure
     * (corrupted, key rotated, or version unsupported).
     *
     * @param string $payload
     *
     * @return string|null
     */
    public function decrypt( $payload ) {
        if ( ! is_string( $payload ) || $payload === '' ) {
            return null;
        }

        $parts = explode( '.', $payload );

        if ( count( $parts ) !== 4 || $parts[0] !== self::STORAGE_VERSION ) {
            return null;
        }

        // base64 here is binary-to-text decoding for storage, not obfuscation.
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $iv = base64_decode( $parts[1], true );
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $tag = base64_decode( $parts[2], true );
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $ciphertext = base64_decode( $parts[3], true );

        if ( $iv === false || $tag === false || $ciphertext === false ) {
            return null;
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->derive_key(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $plaintext === false ? null : $plaintext;
    }

    /**
     * Derive a 32-byte key from AUTH_KEY using HKDF-SHA256.
     *
     * Falls back to wp_salt() if AUTH_KEY is not defined (default WP installs
     * always define it; this guard is for malformed configs).
     */
    private function derive_key() {
        $material = defined( 'AUTH_KEY' ) && AUTH_KEY ? AUTH_KEY : wp_salt( 'auth' );

        return hash_hkdf( 'sha256', $material, 32, 'wpuf-2fa-secret-v1', '' );
    }
}
